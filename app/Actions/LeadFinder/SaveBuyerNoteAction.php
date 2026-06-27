<?php

namespace App\Actions\LeadFinder;

use App\Contracts\Repositories\BuyerRepositoryInterface;
use App\Data\LeadFinder\CreateBuyerNoteData;
use App\Enums\ActivityAction;
use App\Models\Buyer;
use App\Models\BuyerNote;
use App\Models\User;
use App\Services\Logging\ActivityLogger;

class SaveBuyerNoteAction
{
    public function __construct(
        protected BuyerRepositoryInterface $buyerRepository,
        protected ActivityLogger $activityLogger,
    ) {}

    public function execute(string $buyerId, CreateBuyerNoteData $data, User $actor): BuyerNote
    {
        $buyer = $this->buyerRepository->find($buyerId);

        if (! $buyer) {
            throw new \InvalidArgumentException('Buyer not found.');
        }

        $note = $this->buyerRepository->addNote($buyerId, $data, $actor->id);

        $this->activityLogger->log(
            action: ActivityAction::Created,
            summary: "{$actor->fullName()} added a note to {$buyer->name}",
            entityType: Buyer::class,
            entityId: $buyer->id,
            actor: $actor,
            metadata: [
                'note_id' => $note->id,
                'is_pinned' => $data->isPinned,
            ],
        );

        return $note;
    }
}
