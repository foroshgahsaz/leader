<?php

namespace App\Notifications;

use App\Models\AiGeneration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AiGenerationFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public AiGeneration $generation,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'ai_generation_failed',
            'title' => $this->generation->type->label().' failed',
            'body' => $this->generation->error_message ?? 'The AI generation could not be completed.',
            'url' => $this->generation->buyer_id
                ? route('discover.leads.show', $this->generation->buyer_id)
                : route('discover.index'),
            'ai_generation_id' => $this->generation->id,
        ];
    }
}
