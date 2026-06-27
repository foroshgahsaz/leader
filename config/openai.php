<?php

return [

    'api_key' => env('OPENAI_API_KEY'),

    'organization' => env('OPENAI_ORGANIZATION'),

    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),

    'timeout' => (int) env('OPENAI_TIMEOUT', 60),

    'max_tokens' => (int) env('OPENAI_MAX_TOKENS', 2000),

    'temperature' => (float) env('OPENAI_TEMPERATURE', 0.7),

    'model_version' => env('OPENAI_MODEL_VERSION', 'openai-v1'),

];
