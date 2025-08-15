<?php

return [
    'base'    => env('CH_BASE', 'https://api.company-information.service.gov.uk'),
    'key'     => env('CH_API_KEY', ''),
    'timeout' => (int) env('CH_TIMEOUT', 15),
];
