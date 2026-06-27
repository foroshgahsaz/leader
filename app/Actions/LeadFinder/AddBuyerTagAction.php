<?php

namespace App\Actions\LeadFinder;

use App\Contracts\Repositories\BuyerRepositoryInterface;
use App\Enums\ActivityAction;
use App\Models\Buyer;
use App\Models\BuyerTag;
use App\Models\User;
use App\Services\Logging\ActivityLogger;
use Illuminate\Support\Str;

class AddBuyerTagAction
{
    public function __construct(
        protected BuyerRepositoryInterface $buyerRepository,
        protected ActivityLogger $activityLogger,
    ) {}

    public function execute(string $buyerId, string $tag, User $actor): BuyerTag
    {
        $buyer = $this->buyerRepository->find($buyerId);

        if (! $buyer) {
            throw new \InvalidArgumentException('Buyer not found.');
        }

        $normalizedTag = Str::lower(trim($tag));

        if ($normalizedTag === '') {
            throw new \InvalidArgumentException('Tag cannot be empty.');
        }

        $buyerTag = BuyerTag::query()->firstOrCreate(
            [
                'buyer_id' => $buyer->id,
                'tag' => $normalizedTag,
            ],
            [
                'created_by' => $actor->id,
            ],
        );

        $buyer->update(['last_activity_at' => now()]);

        $this->activityLogger->log(
            action: ActivityAction::Updated,
            summary: "{$actor->fullName()} added tag \"{$normalizedTag}\" to {$buyer->name}",
            entityType: Buyer::class,
            entityId: $buyer->id,
            actor: $actor,
            metadata: ['tag' => $normalizedTag],
        );

        return $buyerTag;
    }
}
