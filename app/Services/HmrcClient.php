<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class HmrcClient
{
    public function exchangeCode(string $code): array
    {
        $res = Http::asForm()->post(config('hmrc.token_url'), [
            'grant_type'    => 'authorization_code',
            'client_id'     => config('hmrc.client_id'),
            'client_secret' => config('hmrc.client_secret'),
            'redirect_uri'  => config('hmrc.redirect_uri'),
            'code'          => $code,
        ]);
        $res->throw();
        return $res->json();
    }

    public function refresh(string $refreshToken): array
    {
        $res = Http::asForm()->post(config('hmrc.token_url'), [
            'grant_type'    => 'refresh_token',
            'client_id'     => config('hmrc.client_id'),
            'client_secret' => config('hmrc.client_secret'),
            'refresh_token' => $refreshToken,
        ]);
        $res->throw();
        return $res->json();
    }

    public function getObligations(string $accessToken, string $vrn, array $params = []): array
    {
        $res = Http::baseUrl(config('hmrc.base_url'))
            ->accept('application/vnd.hmrc.1.0+json')
            ->withToken($accessToken)
            ->get("/organisations/vat/{$vrn}/obligations", $params);

        $res->throw();
        return $res->json();
    }
}
