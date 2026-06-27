<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to ExportOS')
            ->greeting('Welcome, '.$notifiable->fullName().'!')
            ->line('Your exporter workspace is ready. Start by completing your company profile and inviting your team.')
            ->action('Go to Dashboard', route('dashboard'))
            ->line('Thank you for choosing ExportOS.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'welcome',
            'title' => 'Welcome to ExportOS',
            'body' => 'Your workspace is ready. Explore the dashboard to get started.',
            'url' => route('dashboard'),
        ];
    }
}
