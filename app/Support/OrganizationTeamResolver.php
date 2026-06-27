<?php

namespace App\Support;

use App\Models\Organization;
use Spatie\Permission\Contracts\PermissionsTeamResolver;

class OrganizationTeamResolver implements PermissionsTeamResolver
{
    protected int|string|null $teamId = null;

    public function getPermissionsTeamId(): int|string|null
    {
        if ($this->teamId !== null) {
            return $this->teamId;
        }

        $context = app(OrganizationContext::class);

        if ($context->has()) {
            return $context->id();
        }

        $user = auth()->user();

        return $user?->current_organization_id;
    }

    public function setPermissionsTeamId($id): void
    {
        $this->teamId = $id;

        if ($id && app()->bound(OrganizationContext::class)) {
            $context = app(OrganizationContext::class);

            if (! $context->has() || $context->id() !== $id) {
                $organization = Organization::query()->find($id);

                if ($organization) {
                    $context->set($organization);
                }
            }
        }
    }
}
