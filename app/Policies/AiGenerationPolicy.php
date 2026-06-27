<?php

namespace App\Policies;

use App\Models\AiGeneration;
use App\Models\User;

class AiGenerationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ai.view');
    }

    public function view(User $user, AiGeneration $generation): bool
    {
        return $user->can('ai.view')
            && $user->belongsToOrganization($generation->organization);
    }

    public function generate(User $user): bool
    {
        return $user->can('ai.generate');
    }
}
