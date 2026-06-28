<?php

namespace Tests\Feature\LeadFinder;

use App\Actions\LeadFinder\SaveBuyerAction;
use App\Data\LeadFinder\BuyerSearchCriteriaData;
use App\Data\LeadFinder\SaveBuyerData;
use App\Enums\LeadSource;
use App\Models\BuyerContact;
use App\Models\GlobalBuyer;
use App\Models\SearchExecution;
use App\Services\LeadFinder\BuyerSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\Concerns\CreatesAuthenticatedOrganizationUser;
use Tests\TestCase;

class ApolloLeadSearchTest extends TestCase
{
    use CreatesAuthenticatedOrganizationUser;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'apollo.enabled' => true,
            'apollo.api_key' => 'test-apollo-key',
            'apollo.fetch_on_search' => true,
            'apollo.base_url' => 'https://api.apollo.io/api/v1',
        ]);
    }

    public function test_search_fetches_buyers_from_apollo_and_displays_them(): void
    {
        Http::fake([
            'api.apollo.io/api/v1/organizations/search' => Http::response([
                'organizations' => [
                    [
                        'id' => 'apollo-org-1',
                        'name' => 'Ankara Steel Importers',
                        'country' => 'Turkey',
                        'city' => 'Ankara',
                        'industry' => 'Steel',
                        'website_url' => 'ankara-steel.example',
                    ],
                ],
            ]),
        ]);

        $user = $this->createOrganizationUser();

        Livewire::actingAs($user)
            ->test(\App\Livewire\LeadFinder\LeadSearch::class)
            ->set('query', 'Ankara Steel')
            ->set('countries', 'TR')
            ->call('search')
            ->assertSee('Ankara Steel Importers')
            ->assertSee('1 buyers imported from Apollo');

        $this->assertDatabaseHas('global_buyers', [
            'provider_key' => 'apollo_apollo-org-1',
            'display_name' => 'Ankara Steel Importers',
            'country_code' => 'TR',
        ]);

        Http::assertSent(function ($request): bool {
            return $request->url() === 'https://api.apollo.io/api/v1/organizations/search'
                && $request->hasHeader('X-Api-Key', 'test-apollo-key')
                && $request['organization_locations'] === ['Turkey']
                && $request['q_organization_name'] === 'Ankara Steel';
        });

        $this->assertDatabaseHas('search_executions', [
            'user_id' => $user->id,
            'credit_consumed' => 1,
        ]);
    }

    public function test_search_does_not_call_apollo_when_disabled(): void
    {
        config(['apollo.enabled' => false]);

        $user = $this->createOrganizationUser();

        GlobalBuyer::factory()->create([
            'display_name' => 'Local Only Buyer',
            'country_code' => 'TR',
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\LeadFinder\LeadSearch::class)
            ->set('countries', 'TR')
            ->call('search')
            ->assertSee('Local Only Buyer');

        Http::assertNothingSent();
    }

    public function test_search_shows_error_when_apollo_request_fails(): void
    {
        Http::fake([
            'api.apollo.io/api/v1/organizations/search' => Http::response(['error' => 'Unauthorized'], 401),
        ]);

        $user = $this->createOrganizationUser();

        Livewire::actingAs($user)
            ->test(\App\Livewire\LeadFinder\LeadSearch::class)
            ->set('countries', 'TR')
            ->call('search')
            ->assertSee('Unable to fetch buyers from Apollo');
    }

    public function test_pagination_does_not_re_fetch_from_apollo(): void
    {
        Http::fake([
            'api.apollo.io/api/v1/organizations/search' => Http::response([
                'organizations' => array_map(
                    fn (int $index): array => [
                        'id' => "apollo-org-{$index}",
                        'name' => "Apollo Buyer {$index}",
                        'country' => 'Turkey',
                    ],
                    range(1, 30),
                ),
            ]),
        ]);

        $user = $this->createOrganizationUser();

        $component = Livewire::actingAs($user)
            ->test(\App\Livewire\LeadFinder\LeadSearch::class)
            ->set('countries', 'TR')
            ->set('perPage', 10)
            ->call('search');

        Http::assertSentCount(1);

        $component->call('gotoPage', 2);

        Http::assertSentCount(1);
    }

    public function test_save_apollo_buyer_syncs_contacts(): void
    {
        $user = $this->createOrganizationUser();

        $globalBuyer = GlobalBuyer::factory()->create([
            'provider_key' => 'apollo_apollo-org-99',
            'display_name' => 'Apollo Sync Co',
            'firmographics' => ['apollo_id' => 'apollo-org-99'],
        ]);

        Http::fake([
            'api.apollo.io/api/v1/mixed_people/organization_top_people' => Http::response([
                'people' => [
                    [
                        'first_name' => 'Ali',
                        'last_name' => 'Yilmaz',
                        'email' => 'ali@apollo-sync.example',
                        'title' => 'Procurement Manager',
                        'email_status' => 'verified',
                    ],
                ],
            ]),
        ]);

        $buyer = app(SaveBuyerAction::class)->execute(
            new SaveBuyerData($globalBuyer->id, LeadSource::Search),
            $user,
        );

        $this->assertDatabaseHas('buyer_contacts', [
            'buyer_id' => $buyer->id,
            'email' => 'ali@apollo-sync.example',
            'source' => 'apollo',
        ]);

        $this->assertSame(1, BuyerContact::query()->where('buyer_id', $buyer->id)->count());
    }

    public function test_buyer_search_service_skips_external_fetch_when_requested(): void
    {
        Http::fake();

        $user = $this->createOrganizationUser();

        $service = app(BuyerSearchService::class);

        $criteria = new BuyerSearchCriteriaData(
            countries: ['TR'],
            perPage: 25,
        );

        $response = $service->search($criteria, $user, fetchExternal: false);

        Http::assertNothingSent();

        $this->assertNull($response->externalImported);
        $this->assertDatabaseCount('search_executions', 1);
    }
}
