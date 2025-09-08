<?php

$env = env('HMRC_ENV', 'sandbox');

$authorize = $env === 'live'
    ? 'https://www.tax.service.gov.uk/oauth/authorize'
    : 'https://test-api.service.hmrc.gov.uk/oauth/authorize';

$token = $env === 'live'
    ? 'https://api.service.hmrc.gov.uk/oauth/token'
    : 'https://test-api.service.hmrc.gov.uk/oauth/token';

$api = $env === 'live'
    ? 'https://api.service.hmrc.gov.uk'
    : 'https://test-api.service.hmrc.gov.uk';

return [
    'env'            => $env,

    // use either key name in your controller
    'authorize_url'  => $authorize,
    'auth_url'       => $authorize,   // ← alias to avoid code changes

    'token_url'      => $token,
    'api_base'       => $api,

    'client_id'      => env('HMRC_CLIENT_ID'),
    'client_secret'  => env('HMRC_CLIENT_SECRET'),
    'redirect_uri'   => env('HMRC_REDIRECT_URI'),
    'scope'          => 'read:vat',
];
