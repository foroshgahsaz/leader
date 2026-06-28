<?php

return [

    'name' => env('APP_NAME', 'ExportOS'),

    'default_timezone' => 'UTC',

    'default_locale' => env('APP_LOCALE', 'fa'),

    'roles' => [
        'admin',
        'manager',
        'rep',
    ],

    'activity_log' => [
        'retention_days' => 730,
    ],

    'audit_log' => [
        'retention_days' => 2555,
    ],

    'ai' => [
        'enabled' => env('AI_ENABLED', true),
        'daily_generation_limit' => env('AI_DAILY_GENERATION_LIMIT', 100),
    ],

];
