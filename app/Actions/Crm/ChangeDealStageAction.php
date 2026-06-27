<?php

namespace App\Actions\Crm;

use App\Contracts\Repositories\DealRepositoryInterface;
use App\Data\Crm\ChangeDealStageData;
use App\Events\Crm\DealStageChanged;
use App\Models\Deal;
use App\Models\User;

class ChangeDealStageAction
{
    public function __construct(
        protected DealRepositoryInterface $dealRepository,
    ) {}

    public function execute(ChangeDealStageData $data, User $actor): Deal
    {
        $deal = $this->dealRepository->find($data->dealId);

        if (! $deal) {
            throw new \InvalidArgumentException('Deal not found.');
        }

        $fromStage = $deal->stage;
        $deal = $this->dealRepository->changeStage($data, $actor->id);

        event(new DealStageChanged($deal, $fromStage, $deal->stage, $actor, $data->reason));

        return $deal;
    }
}
