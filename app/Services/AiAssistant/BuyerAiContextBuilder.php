<?php

namespace App\Services\AiAssistant;

use App\Models\ActivityLog;
use App\Models\Buyer;
use App\Models\Organization;
use App\Models\Product;
use App\Models\User;
use App\Support\OrganizationContext;

class BuyerAiContextBuilder
{
    public function __construct(
        protected OrganizationContext $organizationContext,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Buyer $buyer, User $user): array
    {
        $buyer->loadMissing([
            'globalBuyer',
            'currentScore',
            'contacts',
            'notes' => fn ($query) => $query->latest()->limit(5),
            'tags',
            'tasks' => fn ($query) => $query->latest()->limit(5),
            'owner',
        ]);

        $organization = $this->organizationContext->get();

        return [
            'exporter' => $this->exporterContext($organization, $user),
            'buyer' => [
                'id' => $buyer->id,
                'name' => $buyer->name,
                'country_code' => $buyer->country_code,
                'city' => $buyer->city,
                'website' => $buyer->website,
                'industry' => $buyer->industry,
                'company_type' => $buyer->company_type?->value,
                'status' => $buyer->status->value,
                'is_favorite' => $buyer->is_favorite,
                'is_dnc' => $buyer->is_dnc,
                'last_contacted_at' => $buyer->last_contacted_at?->toIso8601String(),
                'owner' => $buyer->owner?->fullName(),
            ],
            'firmographics' => [
                'employee_range' => $buyer->globalBuyer?->employee_range,
                'import_activity_level' => $buyer->globalBuyer?->import_activity_level,
                'import_profile' => $buyer->globalBuyer?->import_profile,
                'firmographics' => $buyer->globalBuyer?->firmographics,
            ],
            'score' => $buyer->currentScore ? [
                'score' => $buyer->currentScore->score,
                'band' => $buyer->currentScore->score_band->value,
                'explanation' => $buyer->currentScore->explanation,
                'factors' => $buyer->currentScore->factors,
            ] : null,
            'contacts' => $buyer->contacts->map(fn ($contact) => [
                'name' => $contact->full_name,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'title' => $contact->title,
                'is_primary' => $contact->is_primary,
            ])->values()->all(),
            'notes' => $buyer->notes->map(fn ($note) => $note->body)->values()->all(),
            'tags' => $buyer->tags->pluck('tag')->values()->all(),
            'open_tasks' => $buyer->tasks->map(fn ($task) => [
                'title' => $task->title,
                'status' => $task->status->value,
                'due_at' => $task->due_at?->toIso8601String(),
                'priority' => $task->priority->value,
            ])->values()->all(),
            'recent_activity' => $this->recentActivity($buyer),
            'products' => $this->organizationProducts(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function exporterContext(?Organization $organization, User $user): array
    {
        $brandVoice = $organization?->getSetting('brand_voice', []);

        return [
            'organization_name' => $organization?->name,
            'country_code' => $organization?->country_code,
            'website' => $organization?->website,
            'rep_name' => $user->fullName(),
            'rep_email' => $user->email,
            'brand_voice' => $brandVoice,
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function recentActivity(Buyer $buyer): array
    {
        return ActivityLog::query()
            ->where('entity_type', Buyer::class)
            ->where('entity_id', $buyer->id)
            ->latest('occurred_at')
            ->limit(10)
            ->pluck('summary')
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function organizationProducts(): array
    {
        return Product::query()
            ->with('hsCodes')
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(fn (Product $product) => [
                'name' => $product->name,
                'description' => $product->description,
                'hs_codes' => $product->hsCodes->pluck('hs_code')->values()->all(),
            ])
            ->values()
            ->all();
    }
}
