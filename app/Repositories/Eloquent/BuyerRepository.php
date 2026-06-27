<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\BuyerRepositoryInterface;
use App\Data\LeadFinder\CreateBuyerNoteData;
use App\Data\LeadFinder\CreateBuyerTaskData;
use App\Data\LeadFinder\SaveBuyerData;
use App\Data\LeadFinder\UpdateBuyerStatusData;
use App\Enums\BuyerStatus;
use App\Enums\TaskStatus;
use App\Models\Buyer;
use App\Models\BuyerNote;
use App\Models\GlobalBuyer;
use App\Models\Task;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BuyerRepository implements BuyerRepositoryInterface
{
    public function find(string $id): ?Buyer
    {
        return Buyer::query()->find($id);
    }

    public function findByGlobalBuyerId(string $globalBuyerId): ?Buyer
    {
        return Buyer::query()
            ->where('global_buyer_id', $globalBuyerId)
            ->first();
    }

    public function existsForGlobalBuyer(string $globalBuyerId): bool
    {
        return Buyer::query()
            ->where('global_buyer_id', $globalBuyerId)
            ->exists();
    }

    public function create(SaveBuyerData $data, int $createdByUserId, ?string $sourceSearchId = null): Buyer
    {
        $globalBuyer = GlobalBuyer::query()->findOrFail($data->globalBuyerId);

        return DB::transaction(function () use ($data, $createdByUserId, $sourceSearchId, $globalBuyer): Buyer {
            return Buyer::query()->create([
                'global_buyer_id' => $globalBuyer->id,
                'provider_key' => $globalBuyer->provider_key,
                'name' => $globalBuyer->display_name,
                'country_code' => $globalBuyer->country_code,
                'city' => $globalBuyer->city,
                'website' => $globalBuyer->website,
                'industry' => $globalBuyer->industry,
                'company_type' => $globalBuyer->company_type,
                'employee_range' => $globalBuyer->employee_range,
                'source' => $data->source,
                'source_search_id' => $sourceSearchId,
                'status' => BuyerStatus::Saved,
                'is_favorite' => false,
                'is_dnc' => false,
                'last_activity_at' => now(),
                'snapshot' => $this->buildSnapshot($globalBuyer),
                'created_by' => $createdByUserId,
            ]);
        });
    }

    public function updateStatus(string $id, UpdateBuyerStatusData $data): Buyer
    {
        $buyer = Buyer::query()->findOrFail($id);

        $buyer->fill([
            'status' => $data->status,
            'is_dnc' => $data->status === BuyerStatus::DoNotContact,
            'last_activity_at' => now(),
        ])->save();

        return $buyer->fresh();
    }

    public function updateFavorite(string $id, bool $isFavorite): Buyer
    {
        $buyer = Buyer::query()->findOrFail($id);

        $buyer->fill([
            'is_favorite' => $isFavorite,
            'last_activity_at' => now(),
        ])->save();

        return $buyer->fresh();
    }

    public function assignOwner(string $id, ?int $ownerId): Buyer
    {
        $buyer = Buyer::query()->findOrFail($id);

        $buyer->fill([
            'owner_id' => $ownerId,
            'last_activity_at' => now(),
        ])->save();

        return $buyer->fresh();
    }

    public function markContacted(string $id): Buyer
    {
        $buyer = Buyer::query()->findOrFail($id);

        $buyer->fill([
            'last_contacted_at' => now(),
            'last_activity_at' => now(),
            'status' => $buyer->status === BuyerStatus::Saved
                ? BuyerStatus::Contacted
                : $buyer->status,
        ])->save();

        return $buyer->fresh();
    }

    public function delete(string $id): bool
    {
        $buyer = Buyer::query()->find($id);

        if (! $buyer) {
            return false;
        }

        return (bool) $buyer->delete();
    }

    public function addNote(string $buyerId, CreateBuyerNoteData $data, int $createdByUserId): BuyerNote
    {
        $buyer = Buyer::query()->findOrFail($buyerId);

        $note = BuyerNote::query()->create([
            'buyer_id' => $buyer->id,
            'body' => $data->body,
            'is_pinned' => $data->isPinned,
            'created_by' => $createdByUserId,
        ]);

        $buyer->update(['last_activity_at' => now()]);

        return $note;
    }

    public function createTask(
        string $buyerId,
        CreateBuyerTaskData $data,
        int $assignedToUserId,
        int $createdByUserId,
    ): Task {
        $buyer = Buyer::query()->findOrFail($buyerId);

        $task = Task::query()->create([
            'buyer_id' => $buyer->id,
            'assigned_to' => $assignedToUserId,
            'created_by' => $createdByUserId,
            'title' => $data->title,
            'description' => $data->description,
            'task_type' => 'follow_up',
            'priority' => $data->priority,
            'status' => TaskStatus::Pending,
            'due_at' => $data->dueAt,
        ]);

        $buyer->update(['last_activity_at' => now()]);

        return $task;
    }

    public function paginateForOrganization(
        ?int $ownerId = null,
        ?string $status = null,
        int $perPage = 25,
    ): LengthAwarePaginator {
        return Buyer::query()
            ->with(['owner', 'globalBuyer', 'tags'])
            ->when($ownerId !== null, fn ($query) => $query->where('owner_id', $ownerId))
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->orderByDesc('last_activity_at')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildSnapshot(GlobalBuyer $globalBuyer): array
    {
        return [
            'legal_name' => $globalBuyer->legal_name,
            'display_name' => $globalBuyer->display_name,
            'country_code' => $globalBuyer->country_code,
            'city' => $globalBuyer->city,
            'website' => $globalBuyer->website,
            'industry' => $globalBuyer->industry,
            'company_type' => $globalBuyer->company_type?->value,
            'employee_range' => $globalBuyer->employee_range,
            'import_activity_level' => $globalBuyer->import_activity_level,
            'firmographics' => $globalBuyer->firmographics,
            'import_profile' => $globalBuyer->import_profile,
            'data_freshness_at' => $globalBuyer->data_freshness_at?->toIso8601String(),
            'captured_at' => now()->toIso8601String(),
        ];
    }
}
