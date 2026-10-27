<?php

/*
|--------------------------------------------------------------------------
| HRM (source of truth for sectors)
|--------------------------------------------------------------------------
|
| Weight Scale keeps its own sector master (name based, used for the shard
| tables) but the canonical `sectorCode` is owned by HRM. The sync below is
| one-way: HRM -> Weight Scale.
|
*/

return [

    'base_url' => env('HRM_BASE_URL', 'https://hrm.peoplesitsolution.com'),

    'token' => env('HRM_API_TOKEN', ''),

    'timeout' => (int) env('HRM_TIMEOUT', 60),

    'sync_enabled' => env('HRM_SECTOR_SYNC_ENABLED', true),

];
