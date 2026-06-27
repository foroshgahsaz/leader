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
            ->subject('You have been invited to '.$this->organization->name.' on ExportOS')
            ->greeting('Hello!')
            ->line($this->inviter->fullName().' invited you to join '.$this->organization->name.' as '.$this->role->label().'.')
            ->action('Accept Invitation', url('/login'))
            ->line('Sign in with this email address to access your workspace.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'team_invitation',
            'title' => 'Team invitation',
            'body' => $this->inviter->fullName().' invited you to '.$this->organization->name,
            'organization_id' => $this->organization->id,
            'organization_name' => $this->organization->name,
            'inviter_name' => $this->inviter->fullName(),
            'role' => $this->role->value,
            'url' => route('login'),
        ];
    }
}
