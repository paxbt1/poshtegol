<?php

return [
    'enabled' => env('FOOTBALL_DATA_ENABLED', true),
    'base_url' => env('FOOTBALL_DATA_BASE_URL', 'https://api.football-data.org/v4'),
    'token' => env('FOOTBALL_DATA_API_TOKEN'),
    'competition_code' => env('FOOTBALL_DATA_COMPETITION_CODE', 'WC'),
    'season' => env('FOOTBALL_DATA_SEASON', 2026),
    'timeout' => env('FOOTBALL_DATA_TIMEOUT', 20),
    'download_crests' => env('FOOTBALL_DATA_DOWNLOAD_CRESTS', true),
    'sync_during_live_seconds' => env('FOOTBALL_DATA_SYNC_DURING_LIVE_SECONDS', 120),
    'sync_normal_minutes' => env('FOOTBALL_DATA_SYNC_NORMAL_MINUTES', 180),
    'sync_pre_tournament_hours' => env('FOOTBALL_DATA_SYNC_PRE_TOURNAMENT_HOURS', 6),
];
