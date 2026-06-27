<?php

namespace App\Livewire\AiAssistant;

use App\Actions\AiAssistant\RequestAiGenerationAction;
use App\Data\AiAssistant\RequestAiGenerationData;
use App\Enums\AiGenerationStatus;
use App\Enums\AiGenerationType;
use App\Models\AiGeneration;
use App\Models\Buyer;
use Livewire\Component;

class LeadAiAssistant extends Component
{
    public Buyer $buyer;

    public string $tone = 'professional';

    public string $language = 'en';

    public string $additionalInstructions = '';

    public string $sourceText = '';

    public string $targetLanguage = 'es';

    public ?string $selectedGenerationId = null;

    public ?string $followUpParentId = null;

    public function mount(Buyer $buyer): void
    {
        $this->authorize('view', $buyer);
        $this->buyer = $buyer;
    }

    public function generateEmail(RequestAiGenerationAction $action): void
    {
        $this->dispatchGeneration($action, AiGenerationType::Email);
    }

    public function generateWhatsapp(RequestAiGenerationAction $action): void
    {
        $this->dispatchGeneration($action, AiGenerationType::Whatsapp);
    }

    public function generateFollowUp(RequestAiGenerationAction $action): void
    {
        if (! $this->followUpParentId) {
            session()->flash('ai_error', __('Select a previous email generation for follow-up.'));

            return;
        }

        $this->dispatchGeneration($action, AiGenerationType::FollowUp, [
            'parentId' => $this->followUpParentId,
        ]);
    }

    public function translate(RequestAiGenerationAction $action): void
    {
        $this->validate([
            'sourceText' => ['required', 'string', 'max:5000'],
            'targetLanguage' => ['required', 'string', 'max:10'],
        ]);

        $this->dispatchGeneration($action, AiGenerationType::Translate, [
            'sourceText' => $this->sourceText,
            'targetLanguage' => $this->targetLanguage,
        ]);
    }

    public function summarizeCompany(RequestAiGenerationAction $action): void
    {
        $this->dispatchGeneration($action, AiGenerationType::CompanySummary);
    }

    public function nextBestAction(RequestAiGenerationAction $action): void
    {
        $this->dispatchGeneration($action, AiGenerationType::NextBestAction);
    }

    public function riskAnalysis(RequestAiGenerationAction $action): void
    {
        $this->dispatchGeneration($action, AiGenerationType::RiskAnalysis);
    }

    public function selectGeneration(string $generationId): void
    {
        $this->selectedGenerationId = $generationId;
    }

    public function refreshGenerations(): void
    {
        // Livewire polling hook — re-renders generation list.
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function dispatchGeneration(
        RequestAiGenerationAction $action,
        AiGenerationType $type,
        array $overrides = [],
    ): void {
        $this->authorize('generate', AiGeneration::class);

        $generation = $action->execute(
            new RequestAiGenerationData(
                type: $type,
                buyerId: array_key_exists('buyerId', $overrides) ? $overrides['buyerId'] : $this->buyer->id,
                parentId: $overrides['parentId'] ?? null,
                tone: $this->tone !== '' ? $this->tone : null,
                language: $this->language !== '' ? $this->language : null,
                additionalInstructions: $this->additionalInstructions !== '' ? $this->additionalInstructions : null,
                sourceText: $overrides['sourceText'] ?? null,
                targetLanguage: $overrides['targetLanguage'] ?? null,
            ),
            auth()->user(),
        );

        $this->selectedGenerationId = $generation->id;
        session()->flash('ai_status', __('AI generation queued. Results will appear shortly.'));
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $generations = AiGeneration::query()
            ->where('buyer_id', $this->buyer->id)
            ->with('parent')
            ->latest()
            ->limit(20)
            ->get();

        $selected = $generations->firstWhere('id', $this->selectedGenerationId)
            ?? $generations->first();

        $emailGenerations = $generations
            ->where('type', AiGenerationType::Email)
            ->where('status', AiGenerationStatus::Completed);

        $hasPending = $generations->contains(
            fn (AiGeneration $generation) => in_array($generation->status, [
                AiGenerationStatus::Pending,
                AiGenerationStatus::Processing,
            ], true),
        );

        return view('livewire.ai-assistant.lead-ai-assistant', [
            'generations' => $generations,
            'selected' => $selected,
            'emailGenerations' => $emailGenerations,
            'hasPending' => $hasPending,
        ]);
    }
}
