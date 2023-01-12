<?php

return [
    'key'    => env('TRON_KEY'),
    'host'   => [
        'full'     => env('TRON_FULL', 'https://api.trongrid.io'),
        'solidity' => env('TRON_SOLIDITY', 'https://api.trongrid.io'),
        'event'    => env('TRON_EVENT', 'https://api.trongrid.io'),
        'sign'     => env('TRON_SIGN', 'https://api.trongrid.io'),
        'scan'     => env('TRON_SCAN', 'https://apilist.tronscan.org/api'),
    ],
    'wallet' => [
        'address'     => env('TRON_ADDRESS', ''),
        'private_key' => env('TRON_PRIVATE_KEY', ''),
    ],

];
