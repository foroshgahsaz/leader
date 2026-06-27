<?php

namespace App\Livewire\Settings;

use App\Actions\Team\ChangeTeamMemberRoleAction;
use App\Actions\Team\DeactivateTeamMemberAction;
use App\Actions\Team\InviteTeamMemberAction;
use App\Data\Team\InviteTeamMemberData;
use App\Enums\OrganizationRole;
use App\Models\OrgMember;
use App\Support\OrganizationContext;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class TeamManagement extends Component
{
    use WithPagination;

    public string $invite_email = '';

    public string $invite_role = 'rep';

    public function mount(): void
    {
        $this->authorize('viewAny', OrgMember::class);
    }

    public function invite(InviteTeamMemberAction $inviteTeamMemberAction, OrganizationContext $organizationContext): void
    {
        $this->authorize('invite', OrgMember::class);

        $validated = $this->validate([
            'invite_email' => ['required', 'email', 'max:255'],
            'invite_role' => ['required', Rule::enum(OrganizationRole::class)],
        ]);

        $inviteTeamMemberAction->execute(new InviteTeamMemberData(
            email: $validated['invite_email'],
            role: OrganizationRole::from($validated['invite_role']),
            organizationId: $organizationContext->id(),
            invitedByUserId: auth()->id(),
        ));

        $this->reset(['invite_email']);
        $this->invite_role = OrganizationRole::Rep->value;
        $this->resetPage();

        session()->flash('team_message', __('Invitation sent successfully.'));
    }

    public function updateRole(string $membershipId, string $role, ChangeTeamMemberRoleAction $changeTeamMemberRoleAction): void
    {
        $membership = OrgMember::query()->findOrFail($membershipId);
        $this->authorize('update', $membership);

        $changeTeamMemberRoleAction->execute(
            $membership,
            OrganizationRole::from($role),
            auth()->user(),
        );

        session()->flash('team_message', __('Team member role updated.'));
    }

    public function deactivate(string $membershipId, DeactivateTeamMemberAction $deactivateTeamMemberAction): void
    {
        $membership = OrgMember::query()->findOrFail($membershipId);
        $this->authorize('delete', $membership);

        $deactivateTeamMemberAction->execute($membership, auth()->user());

        session()->flash('team_message', __('Team member deactivated.'));
    }

    #[Layout('layouts.settings', ['heading' => 'Team', 'subheading' => 'Invite colleagues and manage roles.'])]
    public function render(OrganizationContext $organizationContext)
    {
        $members = OrgMember::query()
            ->with(['user', 'inviter'])
            ->where('organization_id', $organizationContext->id())
            ->latest()
            ->paginate(10);

        return view('livewire.settings.team-management', [
            'members' => $members,
            'roles' => OrganizationRole::cases(),
        ]);
    }
}
