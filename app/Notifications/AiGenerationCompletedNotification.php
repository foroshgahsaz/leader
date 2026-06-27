<?php

namespace App\Notifications;

use App\Models\AiGeneration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AiGenerationCompletedNotification extends Notification implements ShouldQueue
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
        $buyerName = $this->generation->buyer?->name ?? 'your request';

        return [
            'type' => 'ai_generation_completed',
            'title' => $this->generation->type->label().' ready',
            'body' => "AI {$this->generation->type->label()} for {$buyerName} is ready to review.",
            'url' => $this->generation->buyer_id
                ? route('discover.leads.show', $this->generation->buyer_id)
                : route('discover.index'),
            'ai_generation_id' => $this->generation->id,
        ];
    }
}
