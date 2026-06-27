<?php

namespace App\Providers;

use App\Events\AiAssistant\AiGenerationCompleted;
use App\Events\AiAssistant\AiGenerationFailed;
use App\Events\AiAssistant\AiGenerationRequested;
use App\Events\Auth\OrganizationRegistered;
use App\Events\Crm\DealStageChanged;
use App\Events\Team\TeamMemberInvited;
use App\Listeners\Crm\EnsurePipelineStagesForOrganization;
use App\Listeners\Crm\LogDealStageChanged;
use App\Listeners\AiAssistant\LogAiGenerationActivity;
use App\Listeners\AiAssistant\NotifyUserOfAiGeneration;
use App\Listeners\AiAssistant\PersistCompanySummaryFromAi;
use App\Listeners\Auth\LogAuthenticationEvents;
use App\Listeners\Auth\LogOrganizationRegistration;
use App\Listeners\Auth\SendWelcomeNotification;
use App\Listeners\Team\LogTeamMemberInvitation;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrganizationRegistered::class => [
            LogOrganizationRegistration::class,
            SendWelcomeNotification::class,
            EnsurePipelineStagesForOrganization::class,
        ],
        DealStageChanged::class => [
            LogDealStageChanged::class,
        ],
        TeamMemberInvited::class => [
            LogTeamMemberInvitation::class,
        ],
        Login::class => [
            [LogAuthenticationEvents::class, 'handleLogin'],
        ],
        Logout::class => [
            [LogAuthenticationEvents::class, 'handleLogout'],
        ],
        AiGenerationRequested::class => [
            [LogAiGenerationActivity::class, 'handleRequested'],
        ],
        AiGenerationCompleted::class => [
            [LogAiGenerationActivity::class, 'handleCompleted'],
            PersistCompanySummaryFromAi::class,
            [NotifyUserOfAiGeneration::class, 'handleCompleted'],
        ],
        AiGenerationFailed::class => [
            [LogAiGenerationActivity::class, 'handleFailed'],
            [NotifyUserOfAiGeneration::class, 'handleFailed'],
        ],
    ];
}
