<?php

namespace App\Actions\LeadFinder;

use App\Contracts\Repositories\BuyerRepositoryInterface;
use App\Enums\ActivityAction;
use App\Models\Buyer;
use App\Models\BuyerTag;
use App\Models\User;
use App\Services\Logging\ActivityLogger;
use Illuminate\Support\Str;

class RemoveBuyerTagAction
{
    public function __construct(
        protected BuyerRepositoryInterface $buyerRepository,
        protected ActivityLogger $activityLogger,
    ) {}

    public function execute(string $buyerId, string $tag, User $actor): bool
    {
        $buyer = $this->buyerRepository->find($buyerId);

        if (! $buyer) {
            throw new \InvalidArgumentException('Buyer not found.');
        }

        $normalizedTag = Str::lower(trim($tag));

        $deleted = BuyerTag::query()
            ->where('buyer_id', $buyer->id)
            ->where('tag', $normalizedTag)
            ->delete();

        if ($deleted) {
            $buyer->update(['last_activity_at' => now()]);

            $this->activityLogger->log(
                action: ActivityAction::Updated,
                summary: "{$actor->fullName()} removed tag \"{$normalizedTag}\" from {$buyer->name}",
                entityType: Buyer::class,
                entityId: $buyer->id,
                actor: $actor,
                metadata: ['tag' => $normalizedTag],
            );
        }

        return (bool) $deleted;
    }
}
