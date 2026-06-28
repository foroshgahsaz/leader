<?php

namespace App\Services\LeadFinder\Providers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class ApolloHttpClient
{
    public static function make(): PendingRequest
    {
        $client = Http::baseUrl(rtrim((string) config('apollo.base_url'), '/'))
            ->timeout((int) config('apollo.timeout', 30))
            ->withHeaders([
                'X-Api-Key' => (string) config('apollo.api_key'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Cache-Control' => 'no-cache',
                'User-Agent' => (string) config(
                    'apollo.user_agent',
                    'ExportOS/1.0 (+https://github.com/foroshgahsaz/leader)',
                ),
            ]);

        $proxy = config('apollo.http_proxy');

        if (is_string($proxy) && $proxy !== '') {
            $client = $client->withOptions(['proxy' => $proxy]);
        }

        return $client;
    }
}
