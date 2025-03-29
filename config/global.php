<?php

return [
    'PAGINATE' => [
        'limit'=> 25
    ],
    'EXTERNAL_URLS' => [
        'ptl_connection' => env('PTL_URL'),
        'paper_hub_connection' => env('PAPER_HUB_URL'),
        'parekh_connection' => env('PAREKH_URL'),
    ]
];