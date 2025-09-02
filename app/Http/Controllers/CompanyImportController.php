<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Deadline;
use App\Services\CompaniesHouseClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class CompanyImportController extends Controller
{
    public function store(Request $request, CompaniesHouseClient $ch)
    {
        $data = $request->validate([
            'company_number' => ['required', 'string', 'max:20'],
        ]);

        $user = Auth::user();
        if (! $user) {
            throw ValidationException::withMessages(['auth' => 'You must be signed in.']);
        }

        $cn = trim($data['company_number']);

        // Pull Companies House profile
        $profile = $ch->getCompanyProfile($cn);

        // Basic fields
        $name        = $profile['company_name'] ?? $profile['title'] ?? 'Unknown';
        $type        = $profile['type'] ?? null; // e.g. 'ltd', 'plc'
        $status      = $profile['company_status'] ?? null;
        $incDateStr  = $profile['date_of_creation'] ?? null;
        $incDate     = $incDateStr ? Carbon::parse($incDateStr) : null;

        $accounts    = $profile['accounts'] ?? [];
        $nextAcc     = $accounts['next_accounts'] ?? [];
        $conf        = $profile['confirmation_statement'] ?? [];

        // Figure out which key column exists (new: company_number; legacy: number)
        $hasCompanyNumber = Schema::hasColumn('companies', 'company_number');
        $hasLegacyNumber  = Schema::hasColumn('companies', 'number');

        $lookupKey  = $hasCompanyNumber ? 'company_number' : ($hasLegacyNumber ? 'number' : 'company_number');
        $lookupPair = [$lookupKey => $cn];

        // Values to upsert (write to BOTH columns if they exist)
        $values = [
            'name'                         => $name,
            'status'                       => $status,
            'company_type'                 => $type,
            'date_of_creation'             => $incDateStr,

            'accounts_next_due'            => Arr::get($nextAcc, 'due_on'),
            'accounts_next_period_end_on'  => Arr::get($nextAcc, 'period_end_on'),
            'accounts_overdue'             => (bool) Arr::get($nextAcc, 'overdue', false),

            'confirmation_next_due'        => Arr::get($conf, 'next_due'),
            'confirmation_next_made_up_to' => Arr::get($conf, 'next_made_up_to'),
            'confirmation_overdue'         => (bool) Arr::get($conf, 'overdue', false),

            'registered_office_address'    => $profile['registered_office_address'] ?? null,
            'raw_profile_json'             => json_encode($profile, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];

        if ($hasCompanyNumber) {
            $values['company_number'] = $cn;
        }
        if ($hasLegacyNumber) {
            $values['number'] = $cn; // keep legacy NOT NULL satisfied
        }

        // Upsert company and attach to user
        $company = DB::transaction(function () use ($user, $lookupPair, $values) {
            /** @var Company $company */
            $company = Company::updateOrCreate($lookupPair, $values);
            $user->companies()->syncWithoutDetaching([$company->id]);
            return $company;
        });

        // Upcoming deadlines (from profile)
        $this->upsertUpcomingDeadlines($company);

        // Late filings from filing history (accounts category only)
        $history = $ch->getFilingHistory($cn, [
            'category'       => 'accounts',
            'items_per_page' => 250,
            'start_index'    => 0,
        ]);
        $this->insertLateAccountDeadlinesFromHistory($company, $history, $type, $incDate);

        return response()->json([
            'ok'         => true,
            'company_id' => $company->id,
            'message'    => "{$company->name} added and deadlines populated.",
        ]);
    }

    private function upsertUpcomingDeadlines(Company $company): void
    {
        if ($company->accounts_next_due) {
            Deadline::updateOrCreate(
                [
                    'company_id'    => $company->id,
                    'type'          => 'accounts',
                    'period_end_on' => $company->accounts_next_period_end_on,
                    'due_on'        => $company->accounts_next_due,
                ],
                [
                    'status' => $company->accounts_overdue ? 'overdue' : 'upcoming',
                    'notes'  => 'Next accounts deadline from CH profile',
                ]
            );
        }

        if ($company->confirmation_next_due) {
            Deadline::updateOrCreate(
                [
                    'company_id'    => $company->id,
                    'type'          => 'confirmation_statement',
                    'period_end_on' => $company->confirmation_next_made_up_to,
                    'due_on'        => $company->confirmation_next_due,
                ],
                [
                    'status' => $company->confirmation_overdue ? 'overdue' : 'upcoming',
                    'notes'  => 'Next confirmation statement deadline from CH profile',
                ]
            );
        }
    }

    /**
     * Create 'filed_late' deadlines from filing history when filed after due date.
     *
     * @param array<string,mixed> $history
     */
    private function insertLateAccountDeadlinesFromHistory(Company $company, array $history, ?string $companyType, ?Carbon $incDate): void
    {
        $items = $history['items'] ?? [];

        $periods = [];
        foreach ($items as $item) {
            $descVals    = $item['description_values'] ?? [];
            $madeUp      = $descVals['made_up_date'] ?? null;
            $periodEnd   = $this->parseDateFlexible($madeUp);
            $filedOn     = $this->parseDateFlexible($item['date'] ?? null);
            $periodStart = $this->parseDateFlexible($descVals['period_start_on'] ?? null);

            if ($periodEnd && $filedOn) {
                $periods[] = [
                    'period_end_on'   => $periodEnd,
                    'period_start_on' => $periodStart,
                    'filed_on'        => $filedOn,
                ];
            }
        }

        if (!$periods) return;

        usort($periods, fn ($a, $b) => $a['period_end_on']->lt($b['period_end_on']) ? -1 : 1);

        $isPlc = strtolower((string) $companyType) === 'plc';
        $ard   = $incDate ? $incDate->copy()->addYear()->endOfMonth() : null;

        foreach ($periods as $idx => $p) {
            $isFirst = $idx === 0;

            $dueOn = $this->computeAccountsDueDate(
                periodEnd: $p['period_end_on'],
                isPlc: $isPlc,
                isFirstAccounts: $isFirst,
                incorporationDate: $incDate,
                firstArd: $ard
            );

            if ($p['filed_on']->gt($dueOn)) {
                Deadline::updateOrCreate(
                    [
                        'company_id'    => $company->id,
                        'type'          => 'accounts',
                        'period_end_on' => $p['period_end_on']->toDateString(),
                        'due_on'        => $dueOn->toDateString(),
                    ],
                    [
                        'period_start_on' => $p['period_start_on']?->toDateString(),
                        'filed_on'        => $p['filed_on']->toDateString(),
                        'status'          => 'filed_late',
                        'notes'           => 'Late filing identified from CH filing history',
                    ]
                );
            }
        }
    }

    private function parseDateFlexible(?string $val): ?Carbon
    {
        if (!$val) return null;

        try { return Carbon::parse($val); } catch (\Throwable $e) {}

        if (preg_match('~^\d{2}/\d{2}/\d{4}$~', $val)) {
            [$d,$m,$y] = explode('/', $val);
            return Carbon::createFromFormat('d/m/Y', "$d/$m/$y");
        }
        if (preg_match('~^\d{2}/\d{2}/\d{2}$~', $val)) {
            [$d,$m,$y] = explode('/', $val);
            $y = (int)$y + 2000;
            return Carbon::createFromFormat('d/m/Y', "$d/$m/$y");
        }
        return null;
    }

    private function computeAccountsDueDate(
        Carbon $periodEnd,
        bool $isPlc,
        bool $isFirstAccounts,
        ?Carbon $incorporationDate,
        ?Carbon $firstArd
    ): Carbon {
        if ($isFirstAccounts && $incorporationDate) {
            $ruleA = $incorporationDate->copy()->addMonths($isPlc ? 18 : 21);
            $ruleB = $firstArd ? $firstArd->copy()->addMonths(3) : $ruleA;
            return $ruleA->gt($ruleB) ? $ruleA : $ruleB;
        }

        return $periodEnd->copy()->addMonths($isPlc ? 6 : 9);
    }
}
