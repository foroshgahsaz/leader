<?php

namespace Tests\Feature\LeadFinder;

use App\Actions\LeadFinder\SaveBuyerAction;
use App\Data\LeadFinder\SaveBuyerData;
use App\Enums\LeadSource;
use App\Models\GlobalBuyer;
use Database\Seeders\GlobalBuyerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\CreatesAuthenticatedOrganizationUser;
use Tests\TestCase;

class LeadExportTest extends TestCase
{
    use CreatesAuthenticatedOrganizationUser;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(GlobalBuyerSeeder::class);
    }

    public function test_user_can_export_saved_leads_csv(): void
    {
        $user = $this->createOrganizationUser();
        $globalBuyer = GlobalBuyer::factory()->create(['display_name' => 'Export Target Ltd']);

        app(SaveBuyerAction::class)->execute(
            new SaveBuyerData($globalBuyer->id, LeadSource::Search),
            $user,
        );

        $response = $this->actingAs($user)->get(route('discover.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Export Target Ltd', $response->streamedContent());
    }

    public function test_user_can_toggle_favorite_on_saved_lead(): void
    {
        $user = $this->createOrganizationUser();
        $globalBuyer = GlobalBuyer::factory()->create();

        $buyer = app(SaveBuyerAction::class)->execute(
            new SaveBuyerData($globalBuyer->id, LeadSource::Search),
            $user,
        );

        Livewire::actingAs($user)
            ->test(\App\Livewire\LeadFinder\LeadDetail::class, ['buyer' => $buyer])
            ->call('toggleFavorite')
            ->assertSet('buyer.is_favorite', true);

        $this->assertTrue($buyer->fresh()->is_favorite);
    }
}
