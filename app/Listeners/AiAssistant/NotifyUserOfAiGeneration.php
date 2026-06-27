<?php

namespace App\Listeners\AiAssistant;

use App\Events\AiAssistant\AiGenerationCompleted;
use App\Events\AiAssistant\AiGenerationFailed;
use App\Notifications\AiGenerationCompletedNotification;
use App\Notifications\AiGenerationFailedNotification;

class NotifyUserOfAiGeneration
{
    public function handleCompleted(AiGenerationCompleted $event): void
    {
        $event->generation->user->notify(new AiGenerationCompletedNotification($event->generation));
    }

    public function handleFailed(AiGenerationFailed $event): void
    {
        $event->generation->user->notify(new AiGenerationFailedNotification($event->generation));
    }
}
