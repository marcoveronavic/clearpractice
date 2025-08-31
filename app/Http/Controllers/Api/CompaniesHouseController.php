<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CompaniesHouseController extends Controller
{
    protected string $base;
    protected int $timeout;

    public function __construct()
    {
        $this->base    = rtrim(env('CH_BASE', 'https://api.company-information.service.gov.uk'), '/');
        $this->timeout = (int) env('CH_TIMEOUT', 20);
    }

    protected function client()
    {
        // Companies House: HTTP Basic, API key as username, blank password
        $key  = env('CH_API_KEY') ?: env('COMPANIES_HOUSE_KEY') ?: env('CH_KEY');
        $http = Http::timeout($this->timeout);
        if ($key) {
            $http = $http->withBasicAuth($key, '');
        }
        return $http;
    }

    // GET /api/companies-house/search?q=tesco
    public function search(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if (strlen($q) < 2) {
            return response()->json(['data' => []]);
        }

        $res = $this->client()->get("{$this->base}/search/companies", ['q' => $q]);

        if ($res->failed()) {
            return response()->json([
                'data'  => [],
                'error' => [
                    'status'  => $res->status(),
                    'message' => $res->json('error') ?? $res->body(),
                ],
            ], $res->status());
        }

        $items = collect($res->json('items', []))->map(fn ($it) => [
            'number'  => $it['company_number']   ?? '',
            'name'    => $it['title']            ?? '',
            'address' => $it['address_snippet']  ?? '',
            'status'  => $it['company_status']   ?? null,
            'date'    => $it['date_of_creation'] ?? null,
        ])->values();

        return response()->json(['data' => $items]);
    }

    // GET /api/companies-house/{companyNumber}
    public function show(string $companyNumber)
    {
        $res = $this->client()->get("{$this->base}/company/{$companyNumber}");

        if ($res->failed()) {
            return response()->json([
                'error' => [
                    'status'  => $res->status(),
                    'message' => $res->json('error') ?? $res->body(),
                ],
            ], $res->status());
        }

        return response()->json($res->json());
    }
}
