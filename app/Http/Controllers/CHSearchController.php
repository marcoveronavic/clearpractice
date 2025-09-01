<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CHSearchController extends Controller
{
    /**
     * Proxy to Companies House search endpoint.
     * Route: GET /p/{practice:slug}/ch/search?q=term&limit=8
     *
     * Returns: { items: [{number,name,status,address,date}, ...], error?:string }
     */
    public function search(Request $request, $practice = null)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json(['items' => []]);
        }

        $limit = (int) $request->query('limit', 8);
        $limit = max(1, min(20, $limit));

        // Set CH_API_KEY in .env (optionally also config/services.php)
        $apiKey = config('services.ch.api_key') ?? env('CH_API_KEY');
        if (! $apiKey) {
            // Still return 200 so the UI can show a friendly message
            return response()->json(['items' => [], 'error' => 'no-key']);
        }

        try {
            $res = Http::withBasicAuth($apiKey, '')
                ->acceptJson()
                ->get('https://api.company-information.service.gov.uk/search/companies', [
                    'q'              => $q,
                    'items_per_page' => $limit,
                ]);

            if (! $res->successful()) {
                return response()->json([
                    'items'  => [],
                    'error'  => 'ch-error',
                    'status' => $res->status(),
                ], $res->status());
            }

            $data = $res->json();

            $items = collect($data['items'] ?? [])->map(function ($item) {
                $addr    = $item['address'] ?? [];
                $address = collect($addr)->filter()->implode(', ');

                return [
                    'number'  => $item['company_number']    ?? null,
                    'name'    => $item['title']             ?? null,
                    'status'  => $item['company_status']    ?? null,
                    'address' => $address ?: null,
                    'date'    => $item['date_of_creation']  ?? null,
                ];
            })->values();

            return response()->json(['items' => $items]);
        } catch (\Throwable $e) {
            return response()->json([
                'items'   => [],
                'error'   => 'exception',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
