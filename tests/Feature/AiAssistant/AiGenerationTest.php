<?php

namespace Tests\Feature\AiAssistant;

use App\Actions\LeadFinder\SaveBuyerAction;
use App\Data\LeadFinder\SaveBuyerData;
use App\Enums\AiGenerationStatus;
use App\Enums\AiGenerationType;
use App\Enums\LeadSource;
use App\Models\AiGeneration;
use App\Models\BuyerSummary;
use App\Models\GlobalBuyer;
use Database\Seeders\GlobalBuyerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Concerns\CreatesAuthenticatedOrganizationUser;
use Tests\TestCase;

class AiGenerationTest extends TestCase
{
    use CreatesAuthenticatedOrganizationUser;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(GlobalBuyerSeeder::class);
    }

    public function test_user_can_request_email_generation(): void
    {
        $user = $this->createOrganizationUser();
        $buyer = $this->createSavedBuyer($user);

        Livewire::actingAs($user)
            ->test(\App\Livewire\AiAssistant\LeadAiAssistant::class, ['buyer' => $buyer])
            ->call('generateEmail')
            ->assertSet('selectedGenerationId', fn ($id) => $id !== null);

        $generation = AiGeneration::query()->first();

        $this->assertNotNull($generation);
        $this->assertSame(AiGenerationType::Email, $generation->type);
        $this->assertSame(AiGenerationStatus::Completed, $generation->status, $generation->error_message ?? 'unknown error');
        $this->assertArrayHasKey('subject', $generation->output);
        $this->assertArrayHasKey('body', $generation->output);
    }

    public function test_company_summary_persists_buyer_summary(): void
    {
        $user = $this->createOrganizationUser();
        $buyer = $this->createSavedBuyer($user);

        Livewire::actingAs($user)
            ->test(\App\Livewire\AiAssistant\LeadAiAssistant::class, ['buyer' => $buyer])
            ->call('summarizeCompany');

        $this->assertDatabaseHas('buyer_summaries', [
            'buyer_id' => $buyer->id,
        ]);

        $this->assertTrue(
            BuyerSummary::query()
                ->where('buyer_id', $buyer->id)
                ->where('model_version', config('openai.model_version'))
                ->exists()
        );
    }

    public function test_translate_generation_requires_source_text(): void
    {
        $user = $this->createOrganizationUser();
        $buyer = $this->createSavedBuyer($user);

        Livewire::actingAs($user)
            ->test(\App\Livewire\AiAssistant\LeadAiAssistant::class, ['buyer' => $buyer])
            ->set('sourceText', 'Hello, we would like to discuss a partnership.')
            ->call('translate');

        $generation = AiGeneration::query()->where('type', AiGenerationType::Translate)->first();

        $this->assertNotNull($generation);
        $this->assertSame(AiGenerationStatus::Completed, $generation->status);
        $this->assertArrayHasKey('translated_text', $generation->output);
    }

    public function test_follow_up_requires_parent_email(): void
    {
        $user = $this->createOrganizationUser();
        $buyer = $this->createSavedBuyer($user);

        $component = Livewire::actingAs($user)
            ->test(\App\Livewire\AiAssistant\LeadAiAssistant::class, ['buyer' => $buyer]);

        $component->call('generateFollowUp');
        $component->assertSee(__('Select a previous email generation for follow-up.'));

        $component->call('generateEmail');

        $emailGeneration = AiGeneration::query()
            ->where('type', AiGenerationType::Email)
            ->first();

        $component
            ->set('followUpParentId', $emailGeneration->id)
            ->call('generateFollowUp');

        $followUp = AiGeneration::query()
            ->where('type', AiGenerationType::FollowUp)
            ->first();

        $this->assertNotNull($followUp);
        $this->assertSame($emailGeneration->id, $followUp->parent_id);
    }

    protected function createSavedBuyer($user): \App\Models\Buyer
    {
        $globalBuyer = GlobalBuyer::factory()->create();

        return app(SaveBuyerAction::class)->execute(
            new SaveBuyerData($globalBuyer->id, LeadSource::Search),
            $user,
        );
    }
}
