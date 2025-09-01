<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class CompaniesHouseClient
{
    private Client $http;

    public function __construct(?string $apiKey = null)
    {
        $apiKey = $apiKey ?? env('CH_API_KEY', '');
        $this->http = new Client([
            'base_uri' => 'https://api.company-information.service.gov.uk/',
            'auth'     => [$apiKey, ''], // Basic auth with key:password
            'headers'  => ['Accept' => 'application/json'],
            'timeout'  => 20,
        ]);
    }

    /** @return array<string,mixed> */
    public function getCompanyProfile(string $companyNumber): array
    {
        $res = $this->http->get('company/' . urlencode($companyNumber));
        return json_decode((string) $res->getBody(), true);
    }

    /** @return array<string,mixed> */
    public function getFilingHistory(string $companyNumber, array $query = []): array
    {
        $res = $this->http->get('company/' . urlencode($companyNumber) . '/filing-history', [
            'query' => $query,
        ]);
        return json_decode((string) $res->getBody(), true);
    }
}

