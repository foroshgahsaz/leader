<?php

namespace App\Actions\LeadFinder;

use App\Contracts\Repositories\BuyerRepositoryInterface;
use App\Contracts\Repositories\GlobalBuyerRepositoryInterface;
use App\Data\LeadFinder\SaveBuyerData;
use App\Enums\ActivityAction;
use App\Models\Buyer;
use App\Models\GlobalBuyer;
use App\Models\User;
use App\Services\LeadFinder\CompanySummaryService;
use App\Services\LeadFinder\LeadScoringService;
use App\Services\Logging\ActivityLogger;
use Illuminate\Support\Facades\DB;

class SaveBuyerAction
{
    public function __construct(
        protected BuyerRepositoryInterface $buyerRepository,
        protected GlobalBuyerRepositoryInterface $globalBuyerRepository,
        protected LeadScoringService $leadScoringService,
        protected CompanySummaryService $companySummaryService,
        protected ActivityLogger $activityLogger,
    ) {}

    public function execute(SaveBuyerData $data, User $actor, ?string $sourceSearchId = null): Buyer
    {
        if ($this->buyerRepository->existsForGlobalBuyer($data->globalBuyerId)) {
            throw new \DomainException('This buyer has already been saved to your organization.');
        }

        $globalBuyer = $this->globalBuyerRepository->find($data->globalBuyerId);

        if (! $globalBuyer) {
            throw new \InvalidArgumentException('Global buyer not found.');
        }

        return DB::transaction(function () use ($data, $actor, $sourceSearchId, $globalBuyer): Buyer {
            $buyer = $this->buyerRepository->create($data, $actor->id, $sourceSearchId);

            $this->persistScore($buyer, $globalBuyer);
            $this->companySummaryService->generate($globalBuyer, $buyer);

            $this->activityLogger->log(
                action: ActivityAction::Created,
                summary: "{$actor->fullName()} saved buyer {$buyer->name}",
                entityType: Buyer::class,
                entityId: $buyer->id,
                actor: $actor,
                metadata: [
                    'global_buyer_id' => $globalBuyer->id,
                    'source' => $data->source->value,
                ],
            );

            return $buyer->fresh(['globalBuyer', 'currentScore']);
        });
    }

    protected function persistScore(Buyer $buyer, GlobalBuyer $globalBuyer): void
    {
        $scoreResult = $this->leadScoringService->score($globalBuyer);

        $buyer->scores()->create([
            'global_buyer_id' => $globalBuyer->id,
            'score' => $scoreResult['score'],
            'score_band' => $scoreResult['score_band'],
            'factors' => $scoreResult['factors'],
            'explanation' => $scoreResult['explanation'],
            'model_version' => LeadScoringService::MODEL_VERSION,
            'is_current' => true,
            'scored_at' => now(),
        ]);
    }
}
