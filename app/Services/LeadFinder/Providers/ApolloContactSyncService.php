<?php

namespace App\Services\LeadFinder\Providers;

use App\Models\Buyer;
use App\Models\BuyerContact;
use App\Models\GlobalBuyer;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApolloContactSyncService
{
    public function syncForBuyer(Buyer $buyer, User $actor): int
    {
        if (! config('apollo.enabled') || ! filled(config('apollo.api_key'))) {
            return 0;
        }

        $globalBuyer = $buyer->globalBuyer;

        if (! $globalBuyer || ! str_starts_with((string) $globalBuyer->provider_key, 'apollo_')) {
            return 0;
        }

        $apolloId = $globalBuyer->firmographics['apollo_id'] ?? str_replace('apollo_', '', $globalBuyer->provider_key);

        if (! is_string($apolloId) || $apolloId === '') {
            return 0;
        }

        try {
            $response = Http::baseUrl(rtrim((string) config('apollo.base_url'), '/'))
                ->timeout((int) config('apollo.timeout', 30))
                ->withHeaders([
                    'X-Api-Key' => (string) config('apollo.api_key'),
                    'Content-Type' => 'application/json',
                ])
                ->post('/mixed_people/organization_top_people', [
                    'organization_id' => $apolloId,
                ])
                ->throw();
        } catch (ConnectionException|RequestException $exception) {
            Log::warning('Apollo contact sync failed', [
                'buyer_id' => $buyer->id,
                'message' => $exception->getMessage(),
            ]);

            return 0;
        }

        $people = $response->json('people', []);

        if (! is_array($people)) {
            return 0;
        }

        $created = 0;

        foreach ($people as $index => $person) {
            if (! is_array($person)) {
                continue;
            }

            $email = $person['email'] ?? null;

            if (! is_string($email) || $email === '') {
                continue;
            }

            $contact = BuyerContact::query()->updateOrCreate(
                [
                    'buyer_id' => $buyer->id,
                    'email' => $email,
                ],
                [
                    'organization_id' => $buyer->organization_id,
                    'full_name' => trim(($person['first_name'] ?? '').' '.($person['last_name'] ?? '')) ?: null,
                    'title' => $person['title'] ?? null,
                    'phone' => data_get($person, 'phone_numbers.0.sanitized_number'),
                    'is_primary' => $index === 0,
                    'is_verified' => (bool) ($person['email_status'] ?? false),
                    'source' => 'apollo',
                    'created_by' => $actor->id,
                ],
            );

            if ($contact->wasRecentlyCreated) {
                $created++;
            }
        }

        return $created;
    }
}
