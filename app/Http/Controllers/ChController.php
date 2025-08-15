<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;

class ChController extends Controller
{
    /**
     * Render the search UI (resources/views/ch.blade.php).
     */
    public function page()
    {
        return view('ch');
    }

    /**
     * Lightweight proxy to Companies House API that returns a stable,
     * UI-friendly shape and normalises keys so the modal can show:
     * - officers_active / officers_resigned
     * - pscs_current / pscs_former
     *
     * Accepts:
     *   GET /api/ch?q=SEARCH or NUMBER[&full=1]
     *
     * Behaviour:
     *   - If full=1 or q looks like a company number => fetch full details
     *   - Else => run CH search and return results
     */
    public function search(Request $request)
    {
        $q    = trim((string) $request->query('q', ''));
        $full = $request->boolean('full');

        if ($q === '') {
            return response()->json(['data' => []]);
        }

        // Companies House REST API settings
        $base   = rtrim(env('CH_BASE', 'https://api.company-information.service.gov.uk'), '/');
        $apiKey = (string) env('CH_API_KEY', '');

        // Quick number heuristic (6–10 digits)
        $looksLikeNumber = preg_match('/^\d{6,10}$/', $q) === 1;

        try {
            if ($full || $looksLikeNumber) {
                // Fetch full company payload: details + officers + PSCs (current + ceased)
                $number = $looksLikeNumber ? $q : $this->extractNumber($q);

                if (!$number) {
                    // fall back to search if we couldn't parse a number
                    return $this->searchCompanies($base, $apiKey, $q);
                }

                return $this->companyFull($base, $apiKey, $number);
            }

            // else: standard search by query
            return $this->searchCompanies($base, $apiKey, $q);

        } catch (\Throwable $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /* --------------------------------------------------------------------
     | Helpers
     * ------------------------------------------------------------------ */

    /**
     * Search companies endpoint wrapper.
     */
    protected function searchCompanies(string $base, string $apiKey, string $q)
    {
        $resp = Http::withBasicAuth($apiKey, '')
            ->timeout(20)
            ->get($base . '/search/companies', ['q' => $q, 'items_per_page' => 25]);

        if (!$resp->ok()) {
            return response()->json($resp->json(), $resp->status());
        }

        $json = $resp->json();

        // Normalise search results for the UI
        $items = array_map(function ($it) {
            return [
                'number'  => Arr::get($it, 'company_number'),
                'name'    => Arr::get($it, 'title'),
                'address' => Arr::get($it, 'address_snippet'),
                'status'  => Arr::get($it, 'company_status'),
                'type'    => Arr::get($it, 'company_type'),
            ];
        }, Arr::get($json, 'items', []));

        return response()->json(['data' => $items, 'total_results' => Arr::get($json, 'total_results', 0)]);
    }

    /**
     * Full company payload:
     *  - /company/{number}
     *  - /company/{number}/officers
     *  - /company/{number}/persons-with-significant-control
     *  - /company/{number}/persons-with-significant-control/ceased
     */
    protected function companyFull(string $base, string $apiKey, string $number)
    {
        $pool = Http::pool(fn ($pool) => [
            'company' => $pool->withBasicAuth($apiKey, '')
                ->timeout(20)
                ->get($base . "/company/{$number}"),

            'officers' => $pool->withBasicAuth($apiKey, '')
                ->timeout(20)
                ->get($base . "/company/{$number}/officers", ['items_per_page' => 100]),

            'pscs' => $pool->withBasicAuth($apiKey, '')
                ->timeout(20)
                ->get($base . "/company/{$number}/persons-with-significant-control", ['items_per_page' => 100]),

            'pscsCeased' => $pool->withBasicAuth($apiKey, '')
                ->timeout(20)
                ->get($base . "/company/{$number}/persons-with-significant-control/ceased", ['items_per_page' => 100]),
        ]);

        /** @var \Illuminate\Http\Client\Response $company */
        $company    = $pool['company'];
        $officers   = $pool['officers'];
        $pscs       = $pool['pscs'];
        $pscsCeased = $pool['pscsCeased'];

        if (!$company->ok()) {
            // Bubble up CH error for visibility
            return response()->json($company->json(), $company->status());
        }

        $co  = $company->json();
        $ofs = $officers->ok() ? $officers->json() : ['items' => []];
        $pc  = $pscs->ok() ? $pscs->json() : ['items' => []];
        $pcz = $pscsCeased->ok() ? $pscsCeased->json() : ['items' => []];

        // Officers: split into active/resigned
        $offActive = [];
        $offRes    = [];

        foreach ((array) Arr::get($ofs, 'items', []) as $o) {
            $item = [
                'name'              => Arr::get($o, 'name'),
                'role'              => Arr::get($o, 'officer_role'),
                'appointed'         => Arr::get($o, 'appointed_on'),
                'resigned'          => Arr::get($o, 'resigned_on'),
                'nationality'       => Arr::get($o, 'nationality'),
                'occupation'        => Arr::get($o, 'occupation'),
                'country'           => Arr::get($o, 'country_of_residence'),
            ];
            if (!empty($item['resigned'])) {
                $offRes[] = $item;
            } else {
                $offActive[] = $item;
            }
        }

        // PSCs current
        $pscsCurrent = [];
        foreach ((array) Arr::get($pc, 'items', []) as $p) {
            $pscsCurrent[] = [
                'name'               => Arr::get($p, 'name'),
                'kind'               => Arr::get($p, 'kind'),
                'natures_of_control' => Arr::get($p, 'natures_of_control', []),
                'control'            => Arr::get($p, 'identification.legal_authority') ? [] : Arr::get($p, 'control', []),
                'notified_on'        => Arr::get($p, 'notified_on'),
                'ceased_on'          => Arr::get($p, 'ceased_on'),
            ];
        }

        // PSCs former (ceased)
        $pscsFormer = [];
        foreach ((array) Arr::get($pcz, 'items', []) as $p) {
            $pscsFormer[] = [
                'name'               => Arr::get($p, 'name'),
                'kind'               => Arr::get($p, 'kind'),
                'natures_of_control' => Arr::get($p, 'natures_of_control', []),
                'control'            => Arr::get($p, 'control', []),
                'notified_on'        => Arr::get($p, 'notified_on'),
                'ceased_on'          => Arr::get($p, 'ceased_on'),
            ];
        }

        // Normalise top-level company details the UI reads
        $out = [
            'number'   => Arr::get($co, 'company_number'),
            'name'     => Arr::get($co, 'company_name'),
            'status'   => Arr::get($co, 'company_status'),
            'type'     => Arr::get($co, 'type'),
            'created'  => Arr::get($co, 'date_of_creation'),
            'address'  => $this->formatAddress(Arr::get($co, 'registered_office_address', [])),

            // Dates the modal shows
            'accounts' => [
                'next_due' => Arr::get($co, 'accounts.next_due'),
            ],
            'confirmation_statement' => [
                'next_made_up_to' => Arr::get($co, 'confirmation_statement.next_made_up_to'),
                'overdue'         => Arr::get($co, 'confirmation_statement.overdue'),
            ],

            // These are the keys expected by the working UI
            'officers_active'   => $offActive,
            'officers_resigned' => $offRes,
            'pscs_current'      => $pscsCurrent,
            'pscs_former'       => $pscsFormer,
        ];

        return response()->json(['data' => $out]);
    }

    /**
     * Best-effort number extraction if someone passes "ACME LTD (01234567)".
     */
    protected function extractNumber(string $s): ?string
    {
        if (preg_match('/\b(\d{6,10})\b/', $s, $m)) {
            return $m[1];
        }
        return null;
    }

    /**
     * Format a CH registered_office_address object into a single line.
     */
    protected function formatAddress(array $addr): string
    {
        $parts = [
            Arr::get($addr, 'address_line_1'),
            Arr::get($addr, 'address_line_2'),
            Arr::get($addr, 'locality'),
            Arr::get($addr, 'region'),
            Arr::get($addr, 'postal_code'),
            Arr::get($addr, 'country'),
        ];
        $parts = array_values(array_filter(array_map('trim', $parts)));
        return implode(', ', $parts);
    }
}
