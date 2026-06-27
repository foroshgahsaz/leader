<?php

namespace Tests\Feature\Dashboard;

use App\Actions\LeadFinder\SaveBuyerAction;
use App\Data\LeadFinder\SaveBuyerData;
use App\Enums\CrmActivityType;
use App\Enums\LeadSource;
use App\Models\Buyer;
use App\Models\BuyerScore;
use App\Models\CrmActivity;
use App\Models\GlobalBuyer;
use App\Models\Task;
use App\Enums\ScoreBand;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Services\Crm\PipelineStageService;
use Database\Seeders\GlobalBuyerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\CreatesAuthenticatedOrganizationUser;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use CreatesAuthenticatedOrganizationUser;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(GlobalBuyerSeeder::class);
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = $this->createOrganizationUser();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee("Today's Leads");
    }

    public function test_dashboard_shows_todays_leads_and_kpis(): void
    {
        $user = $this->createOrganizationUser();
        app(PipelineStageService::class)->ensureDefaults($user->currentOrganization);

        $globalBuyer = GlobalBuyer::factory()->create(['display_name' => 'Dashboard Test Co']);
        app(SaveBuyerAction::class)->execute(
            new SaveBuyerData($globalBuyer->id, LeadSource::Search),
            $user,
        );

        $buyer = Buyer::query()->first();
        BuyerScore::query()->create([
            'buyer_id' => $buyer->id,
            'global_buyer_id' => $globalBuyer->id,
            'score' => 85,
            'score_band' => ScoreBand::High,
            'factors' => [],
            'explanation' => 'Strong fit',
            'model_version' => 'rules-v1',
            'is_current' => true,
            'scored_at' => now(),
        ]);

        CrmActivity::query()->create([
            'buyer_id' => $buyer->id,
            'logged_by' => $user->id,
            'activity_type' => CrmActivityType::Email,
            'subject' => 'Intro email',
            'occurred_at' => now(),
        ]);

        Task::query()->create([
            'buyer_id' => $buyer->id,
            'assigned_to' => $user->id,
            'created_by' => $user->id,
            'title' => 'Follow up call',
            'priority' => TaskPriority::High,
            'status' => TaskStatus::Pending,
            'due_at' => now()->endOfDay(),
        ]);

        Livewire::actingAs($user)
            ->test(\App\Livewire\Dashboard\DashboardIndex::class)
            ->assertSee('Dashboard Test Co')
            ->assertSee('Follow up call')
            ->assertSee('Performance');
    }
}
