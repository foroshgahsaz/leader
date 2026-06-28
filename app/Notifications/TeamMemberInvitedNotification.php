<?php

namespace App\Notifications;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamMemberInvitedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Organization $organization,
        public User $inviter,
        public OrganizationRole $role,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('You have been invited to :organization on ExportOS', ['organization' => $this->organization->name]))
            ->greeting(__('Hello!'))
            ->line(__(':inviter invited you to join :organization as :role.', [
                'inviter' => $this->inviter->fullName(),
                'organization' => $this->organization->name,
                'role' => $this->role->label(),
            ]))
            ->action(__('Accept Invitation'), url('/login'))
            ->line(__('Sign in with this email address to access your workspace.'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'team_invitation',
            'title' => __('Team invitation'),
            'body' => __(':inviter invited you to :organization', [
                'inviter' => $this->inviter->fullName(),
                'organization' => $this->organization->name,
            ]),
            'organization_id' => $this->organization->id,
            'organization_name' => $this->organization->name,
            'inviter_name' => $this->inviter->fullName(),
            'role' => $this->role->value,
            'url' => route('login'),
        ];
    }
}
