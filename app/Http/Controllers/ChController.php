<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class ChController extends Controller
{
    /**
     * Small helper to call the Companies House API.
     *
     * @param  string  $path  e.g. "/search/companies" or "/company/{number}"
     * @param  array   $query Query string params
     * @return array          Decoded JSON
     *
     * @throws \RuntimeException on API error
     */
    private function callApi(string $path, array $query = []): array
    {
        $base    = rtrim(env('CH_BASE', 'https://api.company-information.service.gov.uk'), '/');
        $timeout = (int) env('CH_TIMEOUT', 20);
        $key     = env('CH_API_KEY');

        if (!$key) {
            throw new \RuntimeException('Missing CH_API_KEY in .env');
        }

        $resp = Http::withBasicAuth($key, '')
            ->timeout($timeout)
            ->acceptJson()
            ->get($base . $path, $query);

        if (!$resp->successful()) {
            $body = $resp->json() ?? $resp->body();
            throw new \RuntimeException("Companies House API error (HTTP {$resp->status()}): " . (is_string($body) ? substr($body, 0, 200) : json_encode($body)));
        }

        return (array) $resp->json();
    }

    /**
     * GET /api/ch?q=term  → JSON search results (used by the /ch page)
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim($request->query('q', ''));
        if ($q === '') {
            return response()->json(['data' => []]);
        }

        try {
            $json = $this->callApi('/search/companies', [
                'q' => $q,
                'items_per_page' => 10,
            ]);

            $items = collect($json['items'] ?? [])->map(function ($it) {
                return [
                    'name'    => $it['title'] ?? null,
                    'number'  => $it['company_number'] ?? null,
                    'status'  => $it['company_status'] ?? null,
                    'address' => $it['address_snippet'] ?? null,
                    'date'    => $it['date_of_creation'] ?? null,
                ];
            })->values();

            return response()->json(['data' => $items]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Exception', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Map an officer item to a compact array.
     */
    private function mapOfficer(array $it): array
    {
        $dob = $it['date_of_birth'] ?? null; // usually month + year only
        $dobStr = null;
        if (is_array($dob)) {
            $y = $dob['year'] ?? null;
            $m = $dob['month'] ?? null;
            if ($y && $m) {
                $dobStr = sprintf('%02d/%04d', (int)$m, (int)$y);
            } elseif ($y) {
                $dobStr = (string)$y;
            }
        }

        $addr = $it['address'] ?? [];
        $address = implode(', ', array_filter([
            $addr['premises'] ?? null,
            $addr['address_line_1'] ?? null,
            $addr['address_line_2'] ?? null,
            $addr['locality'] ?? null,
            $addr['region'] ?? null,
            $addr['postal_code'] ?? null,
            $addr['country'] ?? null,
        ]));

        return [
            'name' => $it['name'] ?? null,
            'role' => $it['officer_role'] ?? null,
            'appointed_on' => $it['appointed_on'] ?? null,
            'resigned_on'  => $it['resigned_on'] ?? null,
            'nationality'  => $it['nationality'] ?? null,
            'country_of_residence' => $it['country_of_residence'] ?? null,
            'occupation'   => $it['occupation'] ?? null,
            'dob'          => $dobStr,
            'address'      => $address ?: null,
        ];
    }

    /**
     * Map a PSC item to a compact array.
     */
    private function mapPsc(array $it): array
    {
        // Name can vary by kind; fallbacks cover individual/corporate/legal-person
        $name = $it['name'] ??
                ($it['name_elements']['forename'] ?? null) .
                (isset($it['name_elements']['forename'], $it['name_elements']['surname']) ? ' ' : '') .
                ($it['name_elements']['surname'] ?? '') ??
                $it['legal_person_name'] ??
                $it['company_name'] ??
                null;

        $addr = $it['address'] ?? [];
        $address = implode(', ', array_filter([
            $addr['premises'] ?? null,
            $addr['address_line_1'] ?? null,
            $addr['address_line_2'] ?? null,
            $addr['locality'] ?? null,
            $addr['region'] ?? null,
            $addr['postal_code'] ?? null,
            $addr['country'] ?? null,
        ]));

        $noc = $it['natures_of_control'] ?? [];

        return [
            'name'        => $name ?: null,
            'kind'        => $it['kind'] ?? null,
            'notified_on' => $it['notified_on'] ?? null,
            'ceased_on'   => $it['ceased_on'] ?? null,
            'address'     => $address ?: null,
            'natures'     => is_array($noc) ? array_values($noc) : [],
        ];
    }

    /**
     * GET /ch/company/{number} → HTML detail page for a company.
     */
    public function company(string $number)
    {
        $errors = [];
        $company = [];

        try {
            // Company profile
            $profile = $this->callApi('/company/' . urlencode($number));

            $addr = $profile['registered_office_address'] ?? [];
            $address = implode(', ', array_filter([
                $addr['premises'] ?? null,
                $addr['address_line_1'] ?? null,
                $addr['address_line_2'] ?? null,
                $addr['locality'] ?? null,
                $addr['region'] ?? null,
                $addr['postal_code'] ?? null,
                $addr['country'] ?? null,
            ]));

            $company = [
                'name'         => $profile['company_name'] ?? ($profile['title'] ?? $number),
                'number'       => $profile['company_number'] ?? $number,
                'status'       => $profile['company_status'] ?? null,
                'type'         => $profile['type'] ?? null,
                'jurisdiction' => $profile['jurisdiction'] ?? null,
                'created'      => $profile['date_of_creation'] ?? null,
                'sic_codes'    => $profile['sic_codes'] ?? [],
                'address'      => $address ?: null,
            ];
        } catch (\Throwable $e) {
            abort(404, 'Company not found: ' . $number);
        }

        // Officers (fetch but don’t fail the whole page)
        $directors = [];
        try {
            $officersJson = $this->callApi('/company/' . urlencode($number) . '/officers', [
                'items_per_page' => 50,
            ]);
            $officers = collect($officersJson['items'] ?? [])
                ->map(fn($it) => $this->mapOfficer($it))
                ->values();

            // Keep only active directors (no resigned_on, role contains "director")
            $directors = $officers->filter(function ($o) {
                return $o['resigned_on'] === null && is_string($o['role']) && stripos($o['role'], 'director') !== false;
            })->values()->all();
        } catch (\Throwable $e) {
            $errors['officers'] = $e->getMessage();
        }

        // PSCs (fetch but don’t fail the whole page)
        $pscs = [];
        try {
            $pscsJson = $this->callApi('/company/' . urlencode($number) . '/persons-with-significant-control', [
                'items_per_page' => 50,
            ]);
            $pscs = collect($pscsJson['items'] ?? [])
                ->map(fn($it) => $this->mapPsc($it))
                ->values()
                ->all();
        } catch (\Throwable $e) {
            $errors['psc'] = $e->getMessage();
        }

        // External links
        $company['links'] = [
            'companies_house' => "https://find-and-update.company-information.service.gov.uk/company/" . urlencode($company['number']),
            'officers'        => "https://find-and-update.company-information.service.gov.uk/company/" . urlencode($company['number']) . "/officers",
            'psc'             => "https://find-and-update.company-information.service.gov.uk/company/" . urlencode($company['number']) . "/persons-with-significant-control",
        ];

        return view('company', [
            'company'   => $company,
            'directors' => $directors,
            'pscs'      => $pscs,
            'errors'    => $errors,
        ]);
    }
}
