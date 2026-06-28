<?php

return [

    'supported' => ['fa', 'en', 'de', 'tr'],

    'default' => env('APP_LOCALE', 'fa'),

    'locales' => [
        'fa' => [
            'name' => 'فارسی',
            'rtl' => true,
            'font_url' => 'https://fonts.bunny.net/css?family=vazirmatn:400,500,600,700&display=swap',
        ],
        'en' => [
            'name' => 'English',
            'rtl' => false,
            'font_url' => 'https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap',
        ],
        'de' => [
            'name' => 'Deutsch',
            'rtl' => false,
            'font_url' => 'https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap',
        ],
        'tr' => [
            'name' => 'Türkçe',
            'rtl' => false,
            'font_url' => 'https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap',
        ],
    ],

];
