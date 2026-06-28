<?php

namespace App\Services\LeadFinder\Providers;

use App\Contracts\LeadFinder\BuyerDataProviderInterface;
use App\Contracts\Repositories\GlobalBuyerRepositoryInterface;
use App\Data\LeadFinder\BuyerSearchCriteriaData;
use App\Enums\CompanyType;
use App\Support\CountryName;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ApolloBuyerDataProvider implements BuyerDataProviderInterface
{
    public function __construct(
        protected GlobalBuyerRepositoryInterface $globalBuyerRepository,
    ) {}

    public function name(): string
    {
        return 'apollo';
    }

    public function isConfigured(): bool
    {
        return config('apollo.enabled')
            && filled(config('apollo.api_key'));
    }

    public function fetch(BuyerSearchCriteriaData $criteria): int
    {
        if (! $this->isConfigured()) {
            return 0;
        }

        $payload = $this->buildPayload($criteria);

        if ($payload === null) {
            return 0;
        }

        try {
            $response = ApolloHttpClient::make()
                ->post((string) config('apollo.organization_search_path', '/mixed_companies/search'), $payload)
                ->throw();
        } catch (ConnectionException $exception) {
            Log::warning('Apollo organization search failed', [
                'message' => $exception->getMessage(),
                'payload' => $payload,
            ]);

            throw new \RuntimeException(
                __('Unable to reach Apollo API. Check your internet connection, firewall, or APOLLO_HTTP_PROXY setting.'),
                previous: $exception,
            );
        } catch (RequestException $exception) {
            $body = (string) ($exception->response?->body() ?? '');

            Log::warning('Apollo organization search failed', [
                'message' => $exception->getMessage(),
                'status' => $exception->response?->status(),
                'response' => $exception->response?->json(),
                'response_snippet' => Str::limit($body, 500),
                'cloudflare_block' => ApolloApiException::isCloudflareBlock($body),
                'payload' => $payload,
            ]);

            throw ApolloApiException::fromRequestException($exception);
        }

        $organizations = $response->json('organizations', []);

        if (! is_array($organizations)) {
            return 0;
        }

        $imported = 0;

        foreach ($organizations as $organization) {
            if (! is_array($organization)) {
                continue;
            }

            $this->globalBuyerRepository->upsertFromProvider(
                $this->providerKey($organization),
                $this->mapOrganization($organization, $criteria),
            );

            $imported++;
        }

        return $imported;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function buildPayload(BuyerSearchCriteriaData $criteria): ?array
    {
        $keywords = $this->keywords($criteria);

        if ($keywords === [] && $criteria->countries === [] && $criteria->query === null) {
            return null;
        }

        $payload = [
            'page' => 1,
            'per_page' => (int) config('apollo.per_page', 25),
        ];

        if ($criteria->countries !== []) {
            $payload['organization_locations'] = CountryName::fromCodes($criteria->countries);
        }

        if ($keywords !== []) {
            $payload['q_organization_keyword_tags'] = $keywords;
        }

        if ($criteria->query) {
            $payload['q_organization_name'] = $criteria->query;
        }

        return $payload;
    }

    /**
     * @return list<string>
     */
    protected function keywords(BuyerSearchCriteriaData $criteria): array
    {
        $parts = array_filter([
            $criteria->product,
            $criteria->industry,
        ]);

        $keywords = [];

        foreach ($parts as $part) {
            foreach (preg_split('/[,;]+/', trim((string) $part)) ?: [] as $token) {
                $token = trim($token);

                if ($token !== '') {
                    $keywords[] = $token;
                }
            }
        }

        return array_values(array_unique($keywords));
    }

    /**
     * @param  array<string, mixed>  $organization
     */
    protected function providerKey(array $organization): string
    {
        $apolloId = (string) ($organization['id'] ?? $organization['organization_id'] ?? '');

        if ($apolloId !== '') {
            return 'apollo_'.$apolloId;
        }

        $name = Str::lower(trim((string) ($organization['name'] ?? 'unknown')));

        return 'apollo_'.hash('sha256', $name);
    }

    /**
     * @param  array<string, mixed>  $organization
     * @return array<string, mixed>
     */
    protected function mapOrganization(array $organization, BuyerSearchCriteriaData $criteria): array
    {
        $name = (string) ($organization['name'] ?? $organization['organization_name'] ?? 'Unknown Company');
        $countryCode = $this->resolveCountryCode($organization, $criteria);
        $website = $organization['website_url'] ?? $organization['primary_domain'] ?? null;

        if (is_string($website) && $website !== '' && ! str_starts_with($website, 'http')) {
            $website = 'https://'.$website;
        }

        return [
            'legal_name' => $name,
            'display_name' => $name,
            'country_code' => $countryCode,
            'city' => $organization['city'] ?? $organization['organization_city'] ?? null,
            'website' => is_string($website) ? $website : null,
            'industry' => $organization['industry'] ?? $organization['industry_tag_id'] ?? $criteria->industry,
            'company_type' => $this->resolveCompanyType($organization),
            'import_activity_level' => (int) config('apollo.import_activity_default', 3),
            'data_freshness_at' => now(),
            'firmographics' => [
                'provider' => 'apollo',
                'apollo_id' => $organization['id'] ?? null,
                'linkedin_url' => $organization['linkedin_url'] ?? null,
                'phone' => data_get($organization, 'primary_phone.sanitized_number')
                    ?? data_get($organization, 'phone'),
                'estimated_num_employees' => $organization['estimated_num_employees'] ?? null,
                'raw_industry' => $organization['industry'] ?? null,
            ],
            'import_profile' => [
                'source' => 'apollo',
                'keywords' => $this->keywords($criteria),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $organization
     */
    protected function resolveCountryCode(array $organization, BuyerSearchCriteriaData $criteria): string
    {
        $country = $organization['country'] ?? $organization['organization_country'] ?? null;

        if (is_string($country)) {
            $code = CountryName::toCode($country);

            if ($code !== null) {
                return $code;
            }
        }

        return strtoupper($criteria->countries[0] ?? 'US');
    }

    /**
     * @param  array<string, mixed>  $organization
     */
    protected function resolveCompanyType(array $organization): ?CompanyType
    {
        $keywords = Str::lower(implode(' ', array_filter([
            $organization['industry'] ?? '',
            $organization['keywords'] ?? '',
            is_array($organization['tags'] ?? null) ? implode(' ', $organization['tags']) : '',
        ])));

        return match (true) {
            str_contains($keywords, 'import') => CompanyType::Importer,
            str_contains($keywords, 'distribut') => CompanyType::Distributor,
            str_contains($keywords, 'retail') => CompanyType::Retailer,
            str_contains($keywords, 'manufactur') => CompanyType::Manufacturer,
            str_contains($keywords, 'wholesale') => CompanyType::Wholesaler,
            default => null,
        };
    }
}
