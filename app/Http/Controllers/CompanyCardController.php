<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CompanyCardController extends Controller
{
    /**
     * Return an HTML partial with Companies House info for a company number.
     * The view expects a $card array with keys: name, number, status, incorporated,
     * registered_office, year_end, accounts_due, confirmation_due, directors[], psc[].
     */
    public function show(Request $request, string $practice, string $companyNumber)
    {
        $card = $this->fetchCard($companyNumber);

        // Return the Blade partial as HTML for the modal
        return response()->view('companies.card', ['card' => $card]);
    }

    /**
     * Fetch data from Companies House (if API key configured), otherwise return a minimal stub.
     */
    protected function fetchCard(string $number): array
    {
        $card = [
            'number'            => $number,
            'name'              => null,
            'status'            => null,
            'incorporated'      => null,
            'registered_office' => null,
            'year_end'          => null,
            'accounts_due'      => null,
            'confirmation_due'  => null,
            'directors'         => [],
            'psc'               => [],
        ];

        // You can place your CH API key in config/services.php:
        // 'companies_house' => ['key' => env('CH_API_KEY')]
        $apiKey = config('services.companies_house.key')
            ?? config('services.ch.api_key')
            ?? env('CH_API_KEY');

        if (! $apiKey) {
            // No API key configured; return minimal card so the modal still opens.
            $card['name'] = 'Company '.$number;
            return $card;
        }

        try {
            $base = 'https://api.company-information.service.gov.uk';

            // Basic company profile
            $company = Http::withBasicAuth($apiKey, '')
                ->get("$base/company/$number")
                ->json();

            if (is_array($company)) {
                $card['name']         = $company['company_name']   ?? null;
                $card['status']       = $company['company_status'] ?? null;
                $card['incorporated'] = $company['date_of_creation'] ?? null;

                if (! empty($company['registered_office_address'])) {
                    $addr = array_filter($company['registered_office_address']);
                    $card['registered_office'] = implode(', ', $addr);
                }

                if (! empty($company['accounts'])) {
                    $acc = $company['accounts'];
                    if (! empty($acc['accounting_reference_date'])) {
                        $d   = $acc['accounting_reference_date']['day']   ?? null;
                        $m   = $acc['accounting_reference_date']['month'] ?? null;
                        $card['year_end'] = ($d && $m) ? sprintf('%02d/%02d', $d, $m) : null;
                    }
                    $card['accounts_due'] = $acc['next_due'] ?? null;
                }

                if (! empty($company['confirmation_statement'])) {
                    $card['confirmation_due'] = $company['confirmation_statement']['next_due'] ?? null;
                }
            }

            // Officers (directors)
            $officers = Http::withBasicAuth($apiKey, '')
                ->get("$base/company/$number/officers", ['items_per_page' => 50])
                ->json();

            if (! empty($officers['items']) && is_array($officers['items'])) {
                foreach ($officers['items'] as $o) {
                    $card['directors'][] = [
                        'name'      => $o['name'] ?? 'Unknown',
                        'role'      => $o['officer_role'] ?? '',
                        'appointed' => $o['appointed_on'] ?? null,
                        'resigned'  => $o['resigned_on'] ?? null,
                    ];
                }
            }

            // PSC
            $psc = Http::withBasicAuth($apiKey, '')
                ->get("$base/company/$number/persons-with-significant-control")
                ->json();

            if (! empty($psc['items']) && is_array($psc['items'])) {
                foreach ($psc['items'] as $p) {
                    $card['psc'][] = [
                        'name'    => $p['name'] ?? 'PSC',
                        'ceased_on' => $p['ceased_on'] ?? null,
                        'natures' => $p['natures_of_control'] ?? [],
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Swallow errors and return whatever we have.
        }

        return $card;
    }
}
