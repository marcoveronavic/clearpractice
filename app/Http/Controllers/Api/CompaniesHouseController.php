<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class CompaniesHouseController extends Controller
{
    /**
     * GET /api/companies-house/{companyNumber}
     */
    public function show(string $companyNumber): JsonResponse
    {
        try {
            $response = Http::withBasicAuth(config('companies_house.api_key'), '')
                ->get("https://api.company-information.service.gov.uk/company/{$companyNumber}")
                ->throw();

            return response()->json($response->json(), 200, [], JSON_UNESCAPED_UNICODE);
        } catch (RequestException $e) {
            $status = optional($e->response)->status() ?? 500;

            return response()->json([
                'message' => 'Companies House request failed',
                'status'  => $status,
                'error'   => optional($e->response)->json() ?? ['detail' => $e->getMessage()],
            ], $status);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Unexpected error',
                'status'  => 500,
                'error'   => ['detail' => $e->getMessage()],
            ], 500);
        }
    }
}
