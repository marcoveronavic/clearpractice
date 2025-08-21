<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChController extends Controller
{
    /**
     * Simple page that shows the CH search UI (resources/views/ch.blade.php).
     */
    public function page()
    {
        return view('ch');
    }

    /**
     * Helper cURL request to Companies House API.
     */
    private function chRequest(string $path): ?array
    {
        $base = rtrim(env('CH_BASE', 'https://api.company-information.service.gov.uk'), '/');
        $key  = env('CH_API_KEY', '');

        // If no API key, fail gracefully (UI will just show no results)
        if ($key === '') {
            return null;
        }

        $url = $base . '/' . ltrim($path, '/');

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

        if ($err || $code < 200 || $code >= 300) {
            return null;
        }

        $json = json_decode($body, true);
        return is_array($json) ? $json : null;
    }

    /**
     * AJAX: /api/ch?q=tesco
     * Returns a compact list your autocomplete uses.
     */
    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json(['data' => []]);
        }

        $json = $this->chRequest('search/companies?q=' . rawurlencode($q) . '&items_per_page=10');
        if (!$json || empty($json['items'])) {
            return response()->json(['data' => []]);
        }

        $data = [];
        foreach ($json['items'] as $it) {
            $data[] = [
                'name'    => $it['title'] ?? '',
                'number'  => $it['company_number'] ?? '',
                'status'  => $it['company_status'] ?? '',
                'address' => $it['address_snippet'] ?? '',
                'date'    => $it['date_of_creation'] ?? null,
            ];
        }

        return response()->json(['data' => $data]);
    }

    /**
     * Optional: /api/ch/company/{number}
     * Fetches the company profile and returns a small subset.
     */
    public function company(string $number)
    {
        $json = $this->chRequest('company/' . rawurlencode($number));
        if (!$json) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $acc = $json['accounts'] ?? [];
        $cs  = $json['confirmation_statement'] ?? [];

        return response()->json([
            'number'  => $json['company_number'] ?? $number,
            'name'    => $json['company_name'] ?? '',
            'status'  => $json['company_status'] ?? '',
            'address' => $json['registered_office_address'] ?? [],
            'created' => $json['date_of_creation'] ?? null,
            'accounts' => [
                'next_due'        => $acc['next_due']        ?? null,
                'next_made_up_to' => $acc['next_made_up_to'] ?? null,
            ],
            'confirmation_statement' => [
                'next_due'        => $cs['next_due']        ?? null,
                'next_made_up_to' => $cs['next_made_up_to'] ?? null,
            ],
        ]);
    }
}
