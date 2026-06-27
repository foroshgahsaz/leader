<?php

namespace App\Support;

use App\Models\Organization;

class OrganizationContext
{
    protected ?Organization $organization = null;

    public function set(Organization $organization): void
    {
        $this->organization = $organization;
    }

    public function setId(string $organizationId): void
    {
        $this->organization = Organization::query()->findOrFail($organizationId);
    }

    public function get(): ?Organization
    {
        return $this->organization;
    }

    public function id(): ?string
    {
        return $this->organization?->id;
    }

    public function has(): bool
    {
        return $this->organization !== null;
    }

    public function clear(): void
    {
        $this->organization = null;
    }
}
