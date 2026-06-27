<?php

namespace App\Actions\Crm;

use App\Contracts\Repositories\DealRepositoryInterface;
use App\Data\Crm\CreateDealData;
use App\Enums\ActivityAction;
use App\Events\Crm\DealStageChanged;
use App\Models\Deal;
use App\Models\PipelineStage;
use App\Models\User;
use App\Services\Crm\PipelineStageService;
use App\Services\Logging\ActivityLogger;
use App\Support\OrganizationContext;

class CreateDealAction
{
    public function __construct(
        protected DealRepositoryInterface $dealRepository,
        protected PipelineStageService $pipelineStageService,
        protected OrganizationContext $organizationContext,
        protected ActivityLogger $activityLogger,
    ) {}

    public function execute(CreateDealData $data, User $actor): Deal
    {
        if ($this->dealRepository->findByBuyerId($data->buyerId)) {
            throw new \DomainException('This company already has an active deal.');
        }

        $organization = $this->organizationContext->get();
        $stage = $data->stageId
            ? PipelineStage::query()->findOrFail($data->stageId)
            : $this->pipelineStageService->defaultStage($organization);

        if (! $stage) {
            throw new \RuntimeException('No pipeline stages configured.');
        }

        $deal = $this->dealRepository->create($data, $actor->id, $stage->id);

        $this->activityLogger->log(
            action: ActivityAction::Created,
            summary: "{$actor->fullName()} created deal {$deal->title}",
            entityType: Deal::class,
            entityId: $deal->id,
            actor: $actor,
            buyerId: $deal->buyer_id,
            dealId: $deal->id,
        );

        event(new DealStageChanged($deal, null, $stage, $actor, 'Deal created'));

        return $deal;
    }
}
