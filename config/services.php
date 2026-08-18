<?php

return [
    'cj_dropshipping' => [
        'base_uri' => env('CJ_API_BASE_URI', 'https://developers.cjdropshipping.com/api2.0/v1/'),
        'email' => env('CJ_API_EMAIL'),
        'api_key' => env('CJ_API_KEY'),
        // false hasta probar submitOrder() con un pedido real de bajo valor (ver CjOrderService).
        'auto_submit_orders' => env('CJ_AUTO_SUBMIT_ORDERS', false),
    ],

    'mercadopago' => [
        'access_token' => env('MP_ACCESS_TOKEN'),
        'public_key' => env('MP_PUBLIC_KEY'),
    ],
];
