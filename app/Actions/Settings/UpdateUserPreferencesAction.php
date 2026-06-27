<?php

namespace App\Actions\Settings;

use App\Models\User;
use App\Models\UserPreference;
use App\Support\OrganizationContext;

class UpdateUserPreferencesAction
{
    public function __construct(
        protected OrganizationContext $organizationContext,
    ) {}

    public function execute(User $user, array $preferences): UserPreference
    {
        $organizationId = $this->organizationContext->id();

        if (! $organizationId) {
            throw new \RuntimeException('Organization context is required.');
        }

        $record = UserPreference::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'organization_id' => $organizationId,
            ],
            [
                'preferences' => UserPreference::defaults(),
            ]
        );

        $record->update([
            'preferences' => array_merge(UserPreference::defaults(), $record->preferences ?? [], $preferences),
        ]);

        return $record->fresh();
    }
}
