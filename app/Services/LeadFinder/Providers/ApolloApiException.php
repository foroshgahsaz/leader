<?php

namespace App\Services\LeadFinder\Providers;

use Illuminate\Http\Client\RequestException;

class ApolloApiException
{
    public static function fromRequestException(RequestException $exception): \RuntimeException
    {
        $response = $exception->response;
        $status = $response?->status();
        $body = (string) ($response?->body() ?? '');
        $json = $response?->json();
        $apolloMessage = is_array($json) ? ($json['message'] ?? $json['error'] ?? null) : null;

        if (self::isCloudflareBlock($body)) {
            return new \RuntimeException(
                __('Apollo blocked the request at the network level (Cloudflare). If you are behind a restricted network, use a VPN or set APOLLO_HTTP_PROXY in .env.'),
                previous: $exception,
            );
        }

        return match ($status) {
            401 => new \RuntimeException(
                is_string($apolloMessage) && $apolloMessage !== ''
                    ? $apolloMessage
                    : __('Invalid Apollo API key. Create a Master API key in Apollo Settings → Integrations → API.'),
                previous: $exception,
            ),
            403 => new \RuntimeException(
                is_string($apolloMessage) && $apolloMessage !== ''
                    ? $apolloMessage
                    : __('Apollo denied access. Use a Master API key and confirm your plan includes Organization Search.'),
                previous: $exception,
            ),
            429 => new \RuntimeException(
                is_string($apolloMessage) && $apolloMessage !== ''
                    ? $apolloMessage
                    : __('Apollo rate limit exceeded. Please wait and try again.'),
                previous: $exception,
            ),
            default => new \RuntimeException(
                __('Unable to fetch buyers from Apollo. Please check your API key and try again.'),
                previous: $exception,
            ),
        };
    }

    public static function isCloudflareBlock(string $body): bool
    {
        if ($body === '') {
            return false;
        }

        return str_contains(strtolower($body), '<!doctype html')
            || str_contains(strtolower($body), 'cloudflare')
            || str_contains($body, 'Enable JavaScript and cookies');
    }
}
