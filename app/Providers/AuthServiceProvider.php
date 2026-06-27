<?php

namespace App\Providers;

use App\Enums\OrganizationRole;
use App\Models\AiGeneration;
use App\Models\Buyer;
use App\Models\BuyerContact;
use App\Models\CrmActivity;
use App\Models\CrmFile;
use App\Models\CrmMeeting;
use App\Models\Deal;
use App\Models\LeadList;
use App\Models\Organization;
use App\Models\OrgMember;
use App\Models\Task;
use App\Models\User;
use App\Policies\AiGenerationPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\ContactPolicy;
use App\Policies\CrmActivityPolicy;
use App\Policies\CrmFilePolicy;
use App\Policies\CrmMeetingPolicy;
use App\Policies\CrmTaskPolicy;
use App\Policies\DealPolicy;
use App\Policies\LeadListPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\OrganizationPolicy;
use App\Policies\OrgMemberPolicy;
use App\Support\OrganizationContext;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OrganizationContext::class);
    }

    public function boot(): void
    {
        Gate::policy(Organization::class, OrganizationPolicy::class);
        Gate::policy(OrgMember::class, OrgMemberPolicy::class);
        Gate::policy(DatabaseNotification::class, NotificationPolicy::class);
        Gate::policy(Buyer::class, CompanyPolicy::class);
        Gate::policy(BuyerContact::class, ContactPolicy::class);
        Gate::policy(Deal::class, DealPolicy::class);
        Gate::policy(Task::class, CrmTaskPolicy::class);
        Gate::policy(CrmActivity::class, CrmActivityPolicy::class);
        Gate::policy(CrmMeeting::class, CrmMeetingPolicy::class);
        Gate::policy(CrmFile::class, CrmFilePolicy::class);
        Gate::policy(LeadList::class, LeadListPolicy::class);
        Gate::policy(AiGeneration::class, AiGenerationPolicy::class);

        Gate::define('view-dashboard', function (User $user): bool {
            return $user->can('dashboard.view');
        });

        Gate::define('crm.view', function (User $user): bool {
            return $user->can('crm.view');
        });

        Gate::define('access-settings', function (User $user): bool {
            return $user->can('settings.view');
        });

        Gate::define('manage-team', function (User $user): bool {
            return $user->can('team.invite') || $user->can('team.manage');
        });

        Gate::define('view-activity-logs', function (User $user): bool {
            return $user->can('activity.view');
        });

        Gate::define('view-audit-logs', function (User $user): bool {
            return $user->can('audit.view');
        });

        Gate::define('is-admin', function (User $user): bool {
            return $user->organizationRole() === OrganizationRole::Admin;
        });

        Gate::define('is-manager-or-admin', function (User $user): bool {
            return in_array($user->organizationRole(), [OrganizationRole::Admin, OrganizationRole::Manager], true);
        });
    }
}
