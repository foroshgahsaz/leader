<?php

namespace App\Events\Team;

use App\Models\OrgMember;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeamMemberInvited
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public OrgMember $membership,
        public User $inviter,
    ) {}
}
