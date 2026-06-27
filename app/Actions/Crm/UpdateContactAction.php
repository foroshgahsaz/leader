<?php

namespace App\Actions\Crm;

use App\Data\Crm\UpdateContactData;
use App\Enums\ActivityAction;
use App\Models\Buyer;
use App\Models\BuyerContact;
use App\Models\User;
use App\Services\Logging\ActivityLogger;

class UpdateContactAction
{
    public function __construct(
        protected ActivityLogger $activityLogger,
    ) {}

    public function execute(string $contactId, UpdateContactData $data, User $actor): BuyerContact
    {
        $contact = BuyerContact::query()->findOrFail($contactId);

        if ($data->isPrimary) {
            BuyerContact::query()
                ->where('buyer_id', $contact->buyer_id)
                ->whereKeyNot($contact->id)
                ->update(['is_primary' => false]);
        }

        $contact->fill([
            'full_name' => $data->fullName,
            'title' => $data->title,
            'email' => $data->email,
            'phone' => $data->phone,
            'is_primary' => $data->isPrimary,
        ])->save();

        Buyer::query()->whereKey($contact->buyer_id)->update(['last_activity_at' => now()]);

        $this->activityLogger->log(
            action: ActivityAction::Updated,
            summary: "{$actor->fullName()} updated contact {$contact->full_name}",
            entityType: Buyer::class,
            entityId: $contact->buyer_id,
            actor: $actor,
            buyerId: $contact->buyer_id,
        );

        return $contact->fresh();
    }
}
