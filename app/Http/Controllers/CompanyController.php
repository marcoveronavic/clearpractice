<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class CompanyController extends Controller
{
    /**
     * Companies list page
     * NOTE: uses your original flat view name: resources/views/companies.blade.php
     */
    public function index()
    {
        $companies = Company::orderBy('name')->get();
        return view('companies', compact('companies'));
    }

    /**
     * Company detail page (DB first, fallback to Companies House so you still see a page)
     * You can keep this as-is; we’ll wire its view in the next step if needed.
     */
    public function show(string $number)
    {
        $company = Company::where('number', $number)->first();
        $raw = [];

        if (!$company) {
            try {
                // Try your internal CH controller first (if present)
                if (class_exists(\App\Http\Controllers\ChController::class)) {
                    $resp = app(\App\Http\Controllers\ChController::class)->show($number);
                    $payload = $resp->getData(true);
                    $data = $payload['data'] ?? [];
                    $raw  = $payload['raw']  ?? [];

                    $company = new Company();
                    $company->exists                   = false;
                    $company->number                   = $data['number'] ?? $number;
                    $company->name                     = $data['name'] ?? '';
                    $company->status                   = $data['status'] ?? null;
                    $company->type                     = $data['type'] ?? null;
                    $company->created                  = $data['created'] ?? null;
                    $company->address                  = $data['address'] ?? null;
                    $company->sic_codes                = $data['sic_codes'] ?? [];
                    $company->accounts                 = $data['accounts'] ?? [];
                    $company->confirmation_statement   = $data['confirmation_statement'] ?? [];
                    $company->raw                      = $raw;
                } else {
                    throw new \RuntimeException('CH controller not available');
                }
            } catch (\Throwable $e) {
                // Hard fallback: call CH API directly
                try {
                    $base = rtrim(env('CH_BASE', 'https://api.company-information.service.gov.uk'), '/');
                    $res  = Http::timeout(20)
                        ->withBasicAuth(env('CH_API_KEY'), '')
                        ->acceptJson()
                        ->get($base . '/company/' . urlencode($number));

                    if (!$res->successful()) {
                        abort(404);
                    }

                    $ch = $res->json();
                    $company = new Company();
                    $company->exists  = false;
                    $company->number  = $number;
                    $company->name    = $ch['company_name'] ?? '';
                    $company->status  = $ch['company_status'] ?? null;
                    $company->type    = $ch['type'] ?? null;
                    $company->created = $ch['date_of_creation'] ?? null;

                    // Flatten address if present
                    $addrParts = [];
                    foreach (['registered_office_address','address'] as $k) {
                        if (!empty($ch[$k]) && is_array($ch[$k])) {
                            $addrParts = array_values(array_filter($ch[$k]));
                            break;
                        }
                    }
                    $company->address = empty($addrParts) ? null : implode(', ', $addrParts);

                    $company->sic_codes = isset($ch['sic_codes']) ? (array) $ch['sic_codes'] : [];
                    $company->accounts  = [];
                    $company->confirmation_statement = [];
                    $raw = ['officers' => ['active'=>[], 'resigned'=>[]], 'pscs' => ['current'=>[], 'former'=>[]]];
                } catch (\Throwable $e2) {
                    abort(404);
                }
            }
        } else {
            $raw = is_array($company->raw) ? $company->raw : (empty($company->raw) ? [] : json_decode($company->raw, true));
        }

        // Normalize arrays for the view
        $officersActive   = Arr::get($raw, 'officers.active', []);
        $officersResigned = Arr::get($raw, 'officers.resigned', []);
        $pscsCurrent      = Arr::get($raw, 'pscs.current', []);
        $pscsFormer       = Arr::get($raw, 'pscs.former', []);

        $accounts = $company->accounts;
        if (is_string($accounts)) $accounts = json_decode($accounts, true);
        if (!is_array($accounts)) $accounts = [];

        $confirmation = $company->confirmation_statement;
        if (is_string($confirmation)) $confirmation = json_decode($confirmation, true);
        if (!is_array($confirmation)) $confirmation = [];

        // For now we keep rendering the new show view; next step we can point this to your original
        return view('companies.show', [
            'company'          => $company,
            'raw'              => $raw,
            'officersActive'   => $officersActive,
            'officersResigned' => $officersResigned,
            'pscsCurrent'      => $pscsCurrent,
            'pscsFormer'       => $pscsFormer,
            'accounts'         => $accounts,
            'confirmation'     => $confirmation,
        ]);
    }

    /** Save a company (used by “Add company”) */
    public function store(Request $request)
    {
        $data = $request->validate([
            'number'   => 'required|string|max:32',
            'name'     => 'required|string|max:255',
            'status'   => 'nullable|string|max:50',
            'type'     => 'nullable|string|max:50',
            'created'  => 'nullable|string|max:50',
            'address'  => 'nullable|string',
            'sic_codes'=> 'nullable|array',
            'accounts' => 'nullable|array',
            'confirmation_statement' => 'nullable|array',
            'raw'      => 'nullable|array',
        ]);

        $company = Company::firstOrNew(['number' => $data['number']]);
        $company->fill($data);
        $company->save();

        return response()->json(['saved' => true, 'number' => $company->number]);
    }

    /** Update editable fields (VAT, UTR, etc.) */
    public function update(Request $request, string $number)
    {
        $company = Company::where('number', $number)->firstOrFail();

        $data = $request->validate([
            'vat_number'          => 'nullable|string|max:50',
            'authentication_code' => 'nullable|string|max:50',
            'utr'                 => 'nullable|string|max:50',
            'email'               => 'nullable|string|max:255',
            'telephone'           => 'nullable|string|max:50',
            'vat_period'          => 'nullable|in:monthly,quarterly',
            'vat_quarter_group'   => 'nullable|in:Jan/Apr/Jul/Oct,Feb/May/Aug/Nov,Mar/Jun/Sep/Dec',
        ]);

        $company->fill($data);
        $company->save();

        return response()->json(['saved' => true]);
    }

    /** Deadlines page (returns your existing deadlines view name in next step if needed) */
    public function deadlines()
    {
        // Keep your existing logic; returning plain view soon.
        $companies = Company::orderBy('name')->get();
        return view('deadlines.index', compact('companies'));
    }
}
