<?php

namespace App\Actions\Crm;

use App\Data\LeadFinder\CreateBuyerNoteData;
use App\Enums\ActivityAction;
use App\Models\Buyer;
use App\Models\BuyerNote;
use App\Models\User;
use App\Services\Logging\ActivityLogger;

class SaveCrmNoteAction
{
    public function __construct(
        protected ActivityLogger $activityLogger,
    ) {}

    public function execute(string $buyerId, CreateBuyerNoteData $data, User $actor, ?string $dealId = null): BuyerNote
    {
        $note = BuyerNote::query()->create([
            'buyer_id' => $buyerId,
            'deal_id' => $dealId,
            'body' => $data->body,
            'is_pinned' => $data->isPinned,
            'created_by' => $actor->id,
        ]);

        Buyer::query()->whereKey($buyerId)->update(['last_activity_at' => now()]);

        $this->activityLogger->log(
            action: ActivityAction::Created,
            summary: "{$actor->fullName()} added a note",
            entityType: Buyer::class,
            entityId: $buyerId,
            actor: $actor,
            buyerId: $buyerId,
            dealId: $dealId,
        );

        return $note;
    }
}
