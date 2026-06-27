<?php

namespace App\Services\LeadFinder;

use App\Models\EntityHistory;
use App\Support\OrganizationContext;
use BackedEnum;
use Carbon\CarbonInterface;
use DateTimeInterface;

class EntityHistoryService
{
    public function __construct(
        protected OrganizationContext $organizationContext,
    ) {}

    public function recordChange(
        string $entityType,
        string $entityId,
        string $fieldName,
        mixed $oldValue,
        mixed $newValue,
        int $changedByUserId,
        ?string $reason = null,
    ): ?EntityHistory {
        $normalizedOld = $this->normalizeValue($oldValue);
        $normalizedNew = $this->normalizeValue($newValue);

        if ($normalizedOld === $normalizedNew) {
            return null;
        }

        $organizationId = $this->organizationContext->id();

        if (! $organizationId) {
            throw new \RuntimeException('Cannot record entity history without organization context.');
        }

        return EntityHistory::query()->create([
            'organization_id' => $organizationId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'field_name' => $fieldName,
            'old_value' => $normalizedOld,
            'new_value' => $normalizedNew,
            'changed_by' => $changedByUserId,
            'change_reason' => $reason,
            'changed_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $changes
     * @return array<int, EntityHistory>
     */
    public function recordChanges(
        string $entityType,
        string $entityId,
        array $changes,
        int $changedByUserId,
        ?string $reason = null,
    ): array {
        $records = [];

        foreach ($changes as $fieldName => $change) {
            if (! is_array($change) || ! array_key_exists('old', $change) || ! array_key_exists('new', $change)) {
                continue;
            }

            $record = $this->recordChange(
                entityType: $entityType,
                entityId: $entityId,
                fieldName: (string) $fieldName,
                oldValue: $change['old'],
                newValue: $change['new'],
                changedByUserId: $changedByUserId,
                reason: $reason,
            );

            if ($record) {
                $records[] = $record;
            }
        }

        return $records;
    }

    protected function normalizeValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        if ($value instanceof CarbonInterface || $value instanceof DateTimeInterface) {
            return $value->format(DateTimeInterface::ATOM);
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_THROW_ON_ERROR);
        }

        return (string) $value;
    }
}
