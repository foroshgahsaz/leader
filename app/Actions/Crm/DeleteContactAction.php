<?php

namespace App\Actions\Crm;

use App\Enums\ActivityAction;
use App\Models\Buyer;
use App\Models\BuyerContact;
use App\Models\User;
use App\Services\Logging\ActivityLogger;

class DeleteContactAction
{
    public function __construct(
        protected ActivityLogger $activityLogger,
    ) {}

    public function execute(string $contactId, User $actor): void
    {
        $contact = BuyerContact::query()->findOrFail($contactId);
        $buyerId = $contact->buyer_id;
        $name = $contact->full_name;

        $contact->delete();

        $this->activityLogger->log(
            action: ActivityAction::Deleted,
            summary: "{$actor->fullName()} removed contact {$name}",
            entityType: Buyer::class,
            entityId: $buyerId,
            actor: $actor,
            buyerId: $buyerId,
        );
    }
}
