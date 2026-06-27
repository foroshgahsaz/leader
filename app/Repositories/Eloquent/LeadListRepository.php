<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\LeadListRepositoryInterface;
use App\Models\Buyer;
use App\Models\LeadList;
use App\Models\LeadListItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LeadListRepository implements LeadListRepositoryInterface
{
    public function find(string $id): ?LeadList
    {
        return LeadList::query()->find($id);
    }

    public function listForOrganization(?int $ownerId = null): Collection
    {
        return LeadList::query()
            ->with(['owner', 'creator'])
            ->when($ownerId !== null, fn ($query) => $query->where('owner_id', $ownerId))
            ->orderBy('name')
            ->get();
    }

    public function create(array $attributes, int $createdByUserId): LeadList
    {
        return LeadList::query()->create([
            ...$attributes,
            'created_by' => $createdByUserId,
        ]);
    }

    public function update(string $id, array $attributes): LeadList
    {
        $leadList = LeadList::query()->findOrFail($id);

        $leadList->fill($attributes)->save();

        return $leadList->fresh();
    }

    public function delete(string $id): bool
    {
        $leadList = LeadList::query()->find($id);

        if (! $leadList) {
            return false;
        }

        return (bool) $leadList->delete();
    }

    public function addBuyer(string $listId, string $buyerId, int $addedByUserId): LeadListItem
    {
        return DB::transaction(function () use ($listId, $buyerId, $addedByUserId): LeadListItem {
            $leadList = LeadList::query()->findOrFail($listId);
            Buyer::query()->findOrFail($buyerId);

            $item = LeadListItem::query()->firstOrCreate(
                [
                    'lead_list_id' => $leadList->id,
                    'buyer_id' => $buyerId,
                ],
                [
                    'added_by' => $addedByUserId,
                ],
            );

            $leadList->update([
                'buyer_count' => $leadList->items()->count(),
            ]);

            return $item;
        });
    }

    public function removeBuyer(string $listId, string $buyerId): bool
    {
        return DB::transaction(function () use ($listId, $buyerId): bool {
            $leadList = LeadList::query()->findOrFail($listId);

            $deleted = LeadListItem::query()
                ->where('lead_list_id', $listId)
                ->where('buyer_id', $buyerId)
                ->delete();

            if ($deleted) {
                $leadList->update([
                    'buyer_count' => $leadList->items()->count(),
                ]);
            }

            return (bool) $deleted;
        });
    }

    public function getBuyers(string $listId, int $perPage = 25): LengthAwarePaginator
    {
        $leadList = LeadList::query()->findOrFail($listId);

        return $leadList->buyers()
            ->with(['owner', 'globalBuyer', 'tags'])
            ->orderByDesc('lead_list_items.created_at')
            ->paginate($perPage);
    }
}
