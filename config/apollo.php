<?php

return [

    'enabled' => env('APOLLO_ENABLED', false),

    'api_key' => env('APOLLO_API_KEY'),

    'base_url' => env('APOLLO_BASE_URL', 'https://api.apollo.io/api/v1'),

    'organization_search_path' => env(
        'APOLLO_ORGANIZATION_SEARCH_PATH',
        '/mixed_companies/search',
    ),

    'user_agent' => env('APOLLO_USER_AGENT', 'ExportOS/1.0 (+https://github.com/foroshgahsaz/leader)'),

    'http_proxy' => env('APOLLO_HTTP_PROXY'),

    'timeout' => (int) env('APOLLO_TIMEOUT', 30),

    'per_page' => (int) env('APOLLO_PER_PAGE', 25),

    'fetch_on_search' => env('APOLLO_FETCH_ON_SEARCH', true),

    'import_activity_default' => 3,

];
