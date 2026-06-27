<?php

namespace Tests\Feature\LeadFinder;

use App\Actions\LeadFinder\SaveBuyerAction;
use App\Data\LeadFinder\SaveBuyerData;
use App\Enums\LeadSource;
use App\Models\Buyer;
use App\Models\GlobalBuyer;
use Database\Seeders\GlobalBuyerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\CreatesAuthenticatedOrganizationUser;
use Tests\TestCase;

class LeadSearchTest extends TestCase
{
    use CreatesAuthenticatedOrganizationUser;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(GlobalBuyerSeeder::class);
    }

    public function test_discover_search_page_requires_authentication(): void
    {
        $this->get(route('discover.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_search_page(): void
    {
        $user = $this->createOrganizationUser();

        $this->actingAs($user)
            ->get(route('discover.index'))
            ->assertOk()
            ->assertSee('Lead Finder');
    }

    public function test_user_can_search_global_buyers(): void
    {
        $user = $this->createOrganizationUser();

        GlobalBuyer::factory()->create([
            'display_name' => 'Unique Olive Importers',
            'industry' => 'Food & Beverage',
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\LeadFinder\LeadSearch::class)
            ->set('query', 'Unique Olive')
            ->call('search')
            ->assertSee('Unique Olive Importers');
    }

    public function test_user_can_save_buyer_from_search(): void
    {
        $user = $this->createOrganizationUser();
        $globalBuyer = GlobalBuyer::factory()->create([
            'display_name' => 'Save Me Trading Co',
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\LeadFinder\LeadSearch::class)
            ->call('saveLead', $globalBuyer->id)
            ->assertRedirect(route('discover.leads.show', Buyer::query()->first()));

        $this->assertDatabaseHas('buyers', [
            'global_buyer_id' => $globalBuyer->id,
            'name' => 'Save Me Trading Co',
        ]);

        $buyer = Buyer::query()->first();
        $this->assertNotNull($buyer);
        $this->assertDatabaseHas('buyer_scores', [
            'buyer_id' => $buyer->id,
            'is_current' => true,
        ]);
        $this->assertDatabaseHas('buyer_summaries', [
            'buyer_id' => $buyer->id,
        ]);
    }

    public function test_save_buyer_action_prevents_duplicates(): void
    {
        $user = $this->createOrganizationUser();
        $globalBuyer = GlobalBuyer::factory()->create();

        $action = app(SaveBuyerAction::class);
        $action->execute(new SaveBuyerData($globalBuyer->id, LeadSource::Search), $user);

        $this->expectException(\DomainException::class);

        $action->execute(new SaveBuyerData($globalBuyer->id, LeadSource::Search), $user);
    }
}
