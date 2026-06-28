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
        $buyerName = $this->generation->buyer?->name ?? __('your request');

        return [
            'type' => 'ai_generation_completed',
            'title' => __(':type ready', ['type' => $this->generation->type->label()]),
            'body' => __('AI :type for :buyer is ready to review.', [
                'type' => $this->generation->type->label(),
                'buyer' => $buyerName,
            ]),
            'url' => $this->generation->buyer_id
                ? route('discover.leads.show', $this->generation->buyer_id)
                : route('discover.index'),
            'ai_generation_id' => $this->generation->id,
        ];
    }
}
