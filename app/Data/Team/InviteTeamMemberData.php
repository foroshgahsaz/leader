<?php

namespace App\Data\Team;

use App\Enums\OrganizationRole;

readonly class InviteTeamMemberData
{
    public function __construct(
        public string $email,
        public OrganizationRole $role,
        public string $organizationId,
        public int $invitedByUserId,
    ) {}
}
