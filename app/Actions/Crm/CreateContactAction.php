<?php

namespace App\Actions\Crm;

use App\Data\Crm\CreateContactData;
use App\Enums\ActivityAction;
use App\Models\Buyer;
use App\Models\BuyerContact;
use App\Models\User;
use App\Services\Logging\ActivityLogger;

class CreateContactAction
{
    public function __construct(
        protected ActivityLogger $activityLogger,
    ) {}

    public function execute(CreateContactData $data, User $actor): BuyerContact
    {
        if ($data->isPrimary) {
            BuyerContact::query()
                ->where('buyer_id', $data->buyerId)
                ->update(['is_primary' => false]);
        }

        $contact = BuyerContact::query()->create([
            'buyer_id' => $data->buyerId,
            'full_name' => $data->fullName,
            'title' => $data->title,
            'email' => $data->email,
            'phone' => $data->phone,
            'is_primary' => $data->isPrimary,
            'is_verified' => false,
            'source' => 'manual',
            'created_by' => $actor->id,
        ]);

        Buyer::query()->whereKey($data->buyerId)->update(['last_activity_at' => now()]);

        $this->activityLogger->log(
            action: ActivityAction::Created,
            summary: "{$actor->fullName()} added contact {$contact->full_name}",
            entityType: Buyer::class,
            entityId: $data->buyerId,
            actor: $actor,
            buyerId: $data->buyerId,
        );

        return $contact;
    }
}
