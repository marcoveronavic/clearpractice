<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class DeadlinesController extends Controller
{
    /* -------------------- Files & helpers -------------------- */
    private function companiesPath(): string { return 'companies.json'; }
    private function manualPath(): string    { return 'deadlines.json'; }
    private function chCachePath(string $number): string { return "ch_deadlines/{$number}.json"; }

    private function readCompanies(): array
    {
        if (!Storage::exists($this->companiesPath())) return [];
        $rows = json_decode(Storage::get($this->companiesPath()), true);
        return is_array($rows) ? $rows : [];
    }

    private function readManual(): array
    {
        if (!Storage::exists($this->manualPath())) return [];
        $rows = json_decode(Storage::get($this->manualPath()), true);
        return is_array($rows) ? $rows : [];
    }

    private function writeManual(array $rows): void
    {
        Storage::put($this->manualPath(), json_encode(array_values($rows), JSON_PRETTY_PRINT));
    }

    private function readChCache(string $number): array
    {
        $path = $this->chCachePath($number);
        if (!Storage::exists($path)) return [];
        $rows = json_decode(Storage::get($path), true);
        return is_array($rows) ? $rows : [];
    }

    private function writeChCache(string $number, array $data): void
    {
        $path = $this->chCachePath($number);
        Storage::put($path, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function fmtDMY(?string $iso): ?string
    {
        if (!$iso) return null;
        try { return Carbon::parse($iso)->format('d/m/Y'); }
        catch (\Throwable $e) { return null; }
    }

    private function cacheIsStale(array $cache): bool
    {
        $today = Carbon::now()->format('d/m/Y');
        return (($cache['fetched_at'] ?? '') !== $today);
    }

    /* -------------------- CH API -------------------- */
    private function chGetCompanyProfile(string $number): ?array
    {
        $base = rtrim(env('CH_BASE', 'https://api.company-information.service.gov.uk'), '/');
        $key  = env('CH_API_KEY', '');
        if ($key === '') return null;

        $url = $base . '/company/' . urlencode($number);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_USERPWD        => $key . ':',
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);
        $body = curl_exec($ch);
        $err  = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err || $code < 200 || $code >= 300) return null;
        $json = json_decode($body, true);
        return is_array($json) ? $json : null;
    }

    /* -------- Cache helper for profile -> display cache -------- */
    private function cacheFromProfile(string $number, array $profile): void
    {
        $acc = $profile['accounts'] ?? [];
        $cs  = $profile['confirmation_statement'] ?? [];

        // Compute CS due if next_due is missing (fallback: next_made_up_to + 14 days)
        $csDueIso = $cs['next_due'] ?? null;
        if (!$csDueIso && !empty($cs['next_made_up_to'])) {
            try { $csDueIso = Carbon::parse($cs['next_made_up_to'])->addDays(14)->toDateString(); }
            catch (\Throwable $e) { /* ignore */ }
        }

        $cache = [
            'status'       => 'OK',
            'accounts_due' => $this->fmtDMY($acc['next_due'] ?? null),
            'cs_due'       => $this->fmtDMY($csDueIso),
            'vat_due'      => null, // not from CH
            'fetched_at'   => Carbon::now()->format('d/m/Y'),

            // store both shapes for backwards-compat
            'raw'          => [
                'accounts' => [
                    'next_due'        => $acc['next_due']        ?? null,
                    'next_made_up_to' => $acc['next_made_up_to'] ?? null,
                ],
                'confirmation_statement' => [
                    'next_due'        => $cs['next_due']        ?? null,
                    'next_made_up_to' => $cs['next_made_up_to'] ?? null,
                ],
                'cs' => [ // duplicate for old readers
                    'next_due'        => $cs['next_due']        ?? null,
                    'next_made_up_to' => $cs['next_made_up_to'] ?? null,
                ],
            ],
        ];
        $this->writeChCache($number, $cache);
    }

    /* ---------- Manual: upsert a single deadline by title+related ---------- */
    private function upsertManual(string $title, string $dueDMY, string $related, string $kind, string $notes = ''): void
    {
        $rows = $this->readManual();

        foreach ($rows as &$r) {
            if ((string)($r['title'] ?? '') === $title && (string)($r['related'] ?? '') === $related) {
                $r['due']   = $dueDMY;
                $r['notes'] = $notes;
                $r['kind']  = $kind;
                $this->writeManual($rows);
                return;
            }
        }

        $rows[] = [
            'id'      => uniqid('dl_', true),
            'title'   => $title,
            'due'     => $dueDMY,
            'related' => $related,
            'notes'   => $notes,
            'status'  => 'Open',
            'added'   => Carbon::now()->toDateTimeString(),
            'kind'    => $kind, // 'accounts' | 'confirmation' | 'vat' | 'manual'
        ];
        $this->writeManual($rows);
    }

    /* ==========================================================
       VAT helpers
       ========================================================== */
    private function getCompanyMeta(array $company): array
    {
        $meta = [];
        if (isset($company['meta']) && is_array($company['meta'])) {
            $meta = $company['meta'];
        }
        return array_merge($meta, $company);
    }

    private function parseMonthMaybe(string $val): ?int
    {
        $val = trim($val);
        if ($val === '') return null;

        if (ctype_digit($val)) {
            $m = (int)$val;
            return ($m >= 1 && $m <= 12) ? $m : null;
        }

        $map = [
            'jan'=>1,'january'=>1,'01'=>1,
            'feb'=>2,'february'=>2,'02'=>2,
            'mar'=>3,'march'=>3,'03'=>3,
            'apr'=>4,'april'=>4,'04'=>4,
            'may'=>5,'05'=>5,
            'jun'=>6,'june'=>6,'06'=>6,
            'jul'=>7,'july'=>7,'07'=>7,
            'aug'=>8,'august'=>8,'08'=>8,
            'sep'=>9,'sept'=>9,'september'=>9,'09'=>9,
            'oct'=>10,'october'=>10,'10'=>10,
            'nov'=>11,'november'=>11,'11'=>11,
            'dec'=>12,'december'=>12,'12'=>12,
        ];

        $toks = preg_split('~[^A-Za-z0-9]+~', strtolower($val));
        foreach ($toks as $t) {
            if (isset($map[$t])) return $map[$t];
        }
        return null;
    }

    private function getVatAnchorMonth(array $company): ?int
    {
        $meta = $this->getCompanyMeta($company);
        $keys = ['vat_quarter', 'vat_quarter_end', 'vat_qtr', 'vat_quarter_month', 'vat_anchor', 'vat_month'];

        foreach ($keys as $k) {
            if (!empty($meta[$k])) {
                $m = $this->parseMonthMaybe((string)$meta[$k]);
                if ($m) return $m;
            }
        }
        return null;
    }

    private function nextVatPeriodEnd(int $anchorMonth): Carbon
    {
        $months = [];
        for ($k = 0; $k < 4; $k++) {
            $m = (($anchorMonth - 1) + 3*$k) % 12 + 1;
            $months[] = $m;
        }
        sort($months);

        $today = Carbon::now()->startOfDay();
        $year  = (int)$today->format('Y');

        foreach ($months as $m) {
            $end = Carbon::create($year, $m, 1)->endOfMonth()->startOfDay();
            if ($end >= $today) return $end;
        }
        return Carbon::create($year + 1, $months[0], 1)->endOfMonth()->startOfDay();
    }

    private function vatDueFromCompany(array $company): ?array
    {
        $anchor = $this->getVatAnchorMonth($company);
        if (!$anchor) return null;

        $periodEnd = $this->nextVatPeriodEnd($anchor);
        // HMRC rule: period end + 1 month + 7 days
        $due = $periodEnd->copy()->addMonth()->addDays(7)->startOfDay();

        return [
            'period_end' => $periodEnd,
            'due'        => $due,
        ];
    }

    private function addVatManualDeadline(array $company): void
    {
        $number = (string)($company['number'] ?? '');
        $name   = trim((string)($company['name'] ?? ''));
        if ($number === '') return;

        $vat = $this->vatDueFromCompany($company);
        if (!$vat) return;

        $periodText = ' (period end ' . $vat['period_end']->format('d/m/Y') . ')';
        $title = ($name ? ($name . ' — ') : '') . 'VAT deadline' . $periodText;

        $this->upsertManual(
            $title,
            $vat['due']->format('d/m/Y'),
            $number,
            'vat',
            'Auto from VAT settings'
        );
    }

    /* ==========================================================
       ACCOUNTS & CONFIRMATION
       ========================================================== */
    private function backfillAccountsDeadlines(array $company, array $profile): void
    {
        $number = (string)($company['number'] ?? '');
        $name   = trim((string)($company['name'] ?? ''));
        if ($number === '') return;

        $acc = $profile['accounts'] ?? [];
        $nextDue      = $acc['next_due']        ?? null;
        $nextMadeUpTo = $acc['next_made_up_to'] ?? null;
        if (!$nextDue || !$nextMadeUpTo) return;

        try {
            $due  = Carbon::parse($nextDue);
            $made = Carbon::parse($nextMadeUpTo);
        } catch (\Throwable $e) {
            return;
        }

        $nowYear = Carbon::now()->year;
        while ((int)$due->format('Y') <= $nowYear) {
            $title = ($name ? ($name . ' — ') : '') . 'Accounts deadline (YE ' . $made->format('d/m/Y') . ')';
            $this->upsertManual($title, $due->format('d/m/Y'), $number, 'accounts', 'Auto backfill from CH (accounts)');
            $due  = $due->copy()->addYear();
            $made = $made->copy()->addYear();
        }
    }

    private function addCsManualDeadline(array $company, array $profileOrCache): void
    {
        $number = (string)($company['number'] ?? '');
        $name   = trim((string)($company['name'] ?? ''));
        if ($number === '') return;

        $cs  = $profileOrCache['confirmation_statement'] ?? [];
        $csDueIso  = $cs['next_due'] ?? null;
        $periodIso = $cs['next_made_up_to'] ?? null;

        if (!$csDueIso && $periodIso) {
            try { $csDueIso = Carbon::parse($periodIso)->addDays(14)->toDateString(); }
            catch (\Throwable $e) { /* ignore */ }
        }
        if (!$csDueIso) return;

        try { $due = Carbon::parse($csDueIso); } catch (\Throwable $e) { return; }

        $periodText = '';
        if ($periodIso) {
            try { $periodText = ' (period end ' . Carbon::parse($periodIso)->format('d/m/Y') . ')'; }
            catch (\Throwable $e) { /* ignore */ }
        }

        $title = ($name ? ($name . ' — ') : '') . 'Confirmation statement deadline' . $periodText;
        $this->upsertManual($title, $due->format('d/m/Y'), $number, 'confirmation', 'Auto from CH (CS)');
    }

    /* helper for fallback when we only have a dd/mm/yyyy string */
    private function addCsManualByDates(array $company, string $dueDMY, ?string $periodIso): void
    {
        $number = (string)($company['number'] ?? '');
        $name   = trim((string)($company['name'] ?? ''));
        if ($number === '') return;

        $periodText = '';
        if ($periodIso) {
            try { $periodText = ' (period end ' . Carbon::parse($periodIso)->format('d/m/Y') . ')'; }
            catch (\Throwable $e) { /* ignore */ }
        }

        $title = ($name ? ($name . ' — ') : '') . 'Confirmation statement deadline' . $periodText;
        $this->upsertManual($title, $dueDMY, $number, 'confirmation', 'Auto from CH (CS)');
    }

    /* -------------------- Pages -------------------- */
    public function index()
    {
        $companies = $this->readCompanies();

        foreach ($companies as $co) {
            $num = (string)($co['number'] ?? '');
            if (!$num) continue;

            $cache = $this->readChCache($num);

            if ($this->cacheIsStale($cache)) {
                $profile = $this->chGetCompanyProfile($num);
                if ($profile) {
                    $this->cacheFromProfile($num, $profile);
                    $this->backfillAccountsDeadlines($co, $profile);
                    $this->addCsManualDeadline($co, $profile);
                } else {
                    $this->writeChCache($num, [
                        'status'     => 'ERR',
                        'fetched_at' => Carbon::now()->format('d/m/Y'),
                    ]);
                }
            } else {
                // Support both old and new cache shapes + fallback to cs_due string
                $rawCs = $cache['raw']['confirmation_statement'] ?? $cache['raw']['cs'] ?? null;

                if ($rawCs) {
                    $this->addCsManualDeadline($co, ['confirmation_statement' => $rawCs]);
                } else {
                    $csDueDMY  = $cache['cs_due'] ?? null; // already dd/mm/yyyy
                    $periodIso = ($cache['raw']['confirmation_statement']['next_made_up_to'] ?? null)
                              ?? ($cache['raw']['cs']['next_made_up_to'] ?? null);
                    if ($csDueDMY) {
                        $this->addCsManualByDates($co, $csDueDMY, $periodIso);
                    }
                }
            }

            // Always (re)create VAT deadline from company settings
            $this->addVatManualDeadline($co);
        }

        // Build auto table (including VAT)
        $auto = [];
        foreach ($companies as $co) {
            $num   = (string)($co['number'] ?? '');
            $cache = $num ? $this->readChCache($num) : [];
            $vat   = $this->vatDueFromCompany($co);

            $auto[] = [
                'number'       => $num,
                'name'         => $co['name'] ?? '',
                'accounts_due' => $cache['accounts_due'] ?? null,
                'cs_due'       => $cache['cs_due'] ?? null,
                'vat_due'      => $vat ? $vat['due']->format('d/m/Y') : null,
                'fetched_at'   => $cache['fetched_at'] ?? null,
                'status'       => $cache['status'] ?? null,
            ];
        }

        $manual = $this->readManual();

        return view('deadlines', [
            'auto'      => $auto,
            'manual'    => $manual,
            'companies' => $companies,
        ]);
    }

    /* Manual add (kept) */
    public function store(Request $req)
    {
        $rows = $this->readManual();
        $rows[] = [
            'id'      => uniqid('dl_', true),
            'title'   => trim((string)$req->input('title')),
            'due'     => trim((string)$req->input('due')),
            'related' => trim((string)$req->input('related')),
            'notes'   => trim((string)$req->input('notes')),
            'status'  => trim((string)$req->input('status', 'Open')),
            'added'   => Carbon::now()->toDateTimeString(),
            'kind'    => 'manual',
        ];
        $this->writeManual($rows);
        return redirect()->route('deadlines.index')->with('success', 'Deadline added.');
    }

    public function destroy(string $id)
    {
        $rows = $this->readManual();
        $rows = array_values(array_filter($rows, fn($r) => (string)($r['id'] ?? '') !== (string)$id));
        $this->writeManual($rows);
        return redirect()->route('deadlines.index')->with('success', 'Deadline removed.');
    }

    /* Optional manual refresh endpoints */
    public function storeCompany(string $number)
    {
        $number = trim($number);
        if ($number === '') return redirect()->route('deadlines.index');

        $co = null;
        foreach ($this->readCompanies() as $c) {
            if ((string)($c['number'] ?? '') === $number) { $co = $c; break; }
        }
        if (!$co) return redirect()->route('deadlines.index')->with('error', "Company $number not found.");

        $profile = $this->chGetCompanyProfile($number);
        if (!$profile) {
            $this->writeChCache($number, [
                'status'     => 'ERR',
                'fetched_at' => Carbon::now()->format('d/m/Y'),
            ]);
            $this->addVatManualDeadline($co);
            return redirect()->route('deadlines.index')->with('error', "CH fetch failed for $number.");
        }

        $this->cacheFromProfile($number, $profile);
        $this->backfillAccountsDeadlines($co, $profile);
        $this->addCsManualDeadline($co, $profile);
        $this->addVatManualDeadline($co);

        return redirect()->route('deadlines.index')->with('success', "Updated from CH: $number");
    }

    public function refreshAll()
    {
        $ok = 0; $err = 0;
        foreach ($this->readCompanies() as $co) {
            $num = (string)($co['number'] ?? '');
            if (!$num) continue;

            $profile = $this->chGetCompanyProfile($num);
            if (!$profile) {
                $this->writeChCache($num, [
                    'status'     => 'ERR',
                    'fetched_at' => Carbon::now()->format('d/m/Y'),
                ]);
                $this->addVatManualDeadline($co);
                $err++; continue;
            }
            $this->cacheFromProfile($num, $profile);
            $this->backfillAccountsDeadlines($co, $profile);
            $this->addCsManualDeadline($co, $profile);
            $this->addVatManualDeadline($co);
            $ok++;
        }
        return redirect()->route('deadlines.index')->with('success', "CH refresh: $ok OK, $err ERR");
    }
}
