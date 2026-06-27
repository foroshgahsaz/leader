<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\DealRepositoryInterface;
use App\Data\Crm\ChangeDealStageData;
use App\Data\Crm\CreateDealData;
use App\Models\Buyer;
use App\Models\Deal;
use App\Models\DealStageHistory;
use App\Models\PipelineStage;
use Illuminate\Support\Facades\DB;

class DealRepository implements DealRepositoryInterface
{
    public function find(string $id): ?Deal
    {
        return Deal::query()->find($id);
    }

    public function findByBuyerId(string $buyerId): ?Deal
    {
        return Deal::query()->where('buyer_id', $buyerId)->first();
    }

    public function create(CreateDealData $data, int $createdByUserId, string $stageId): Deal
    {
        return DB::transaction(function () use ($data, $createdByUserId, $stageId): Deal {
            $stage = PipelineStage::query()->findOrFail($stageId);

            $deal = Deal::query()->create([
                'buyer_id' => $data->buyerId,
                'stage_id' => $stage->id,
                'owner_id' => $data->ownerId ?? $createdByUserId,
                'title' => $data->title,
                'estimated_value' => $data->estimatedValue,
                'currency_code' => strtoupper($data->currencyCode),
                'expected_close_date' => $data->expectedCloseDate,
                'last_stage_change_at' => now(),
                'created_by' => $createdByUserId,
            ]);

            DealStageHistory::query()->create([
                'deal_id' => $deal->id,
                'from_stage_id' => null,
                'to_stage_id' => $stage->id,
                'changed_by' => $createdByUserId,
                'reason' => 'Deal created',
                'changed_at' => now(),
            ]);

            Buyer::query()->whereKey($data->buyerId)->update([
                'pipeline_stage' => $stage->key,
                'last_activity_at' => now(),
            ]);

            return $deal->fresh(['stage', 'buyer']);
        });
    }

    public function changeStage(ChangeDealStageData $data, int $changedByUserId): Deal
    {
        return DB::transaction(function () use ($data, $changedByUserId): Deal {
            $deal = Deal::query()->findOrFail($data->dealId);
            $newStage = PipelineStage::query()->findOrFail($data->stageId);
            $previousStageId = $deal->stage_id;

            $deal->fill([
                'stage_id' => $newStage->id,
                'last_stage_change_at' => now(),
                'lost_reason' => $newStage->is_closed && ! $newStage->is_won ? $data->lostReason : null,
                'lost_notes' => $newStage->is_closed && ! $newStage->is_won ? $data->lostNotes : null,
                'closed_at' => $newStage->is_closed ? now() : null,
            ])->save();

            DealStageHistory::query()->create([
                'deal_id' => $deal->id,
                'from_stage_id' => $previousStageId,
                'to_stage_id' => $newStage->id,
                'changed_by' => $changedByUserId,
                'reason' => $data->reason,
                'changed_at' => now(),
            ]);

            Buyer::query()->whereKey($deal->buyer_id)->update([
                'pipeline_stage' => $newStage->key,
                'last_activity_at' => now(),
            ]);

            return $deal->fresh(['stage', 'buyer']);
        });
    }
}
