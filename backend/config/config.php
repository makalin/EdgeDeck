<?php

declare(strict_types=1);

$apiKey = getenv('EDGEDECK_API_KEY');

return [
    'device_name' => 'EdgeDeck',
    'api_version' => '0.2.0',
    'sync' => [
        'batch_limit' => 50,
        'retry_seconds' => 30,
    ],
    'http' => [
        'cors_allow_origin' => getenv('EDGEDECK_CORS_ORIGIN') ?: '*',
        'cors_allow_headers' => 'Content-Type, Authorization, X-API-Key',
    ],
    'security' => [
        'api_key' => is_string($apiKey) && $apiKey !== '' ? $apiKey : '',
        'public_paths' => ['/health'],
    ],
    'limits' => [
        'list_default' => 20,
        'list_max' => 100,
        'sync_history_max' => 50,
    ],
    'features' => [
        'wifi_scan' => true,
        'ble_scan' => true,
        'quick_notes' => true,
        'telemetry' => true,
        'jobs' => true,
        'offline_sync' => true,
        'scan_snapshots' => true,
        'command_macros' => true,
    ],
];
