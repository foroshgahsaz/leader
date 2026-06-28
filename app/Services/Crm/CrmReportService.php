<?php

namespace App\Services\Crm;

use App\Enums\CrmMeetingStatus;
use App\Enums\TaskStatus;
use App\Models\Buyer;
use App\Models\CrmActivity;
use App\Models\CrmMeeting;
use App\Models\Deal;
use App\Models\PipelineStage;
use App\Models\Task;
use App\Support\OrganizationContext;
use Illuminate\Support\Collection;

class CrmReportService
{
    public function __construct(
        protected OrganizationContext $organizationContext,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function dashboard(): array
    {
        $organizationId = $this->organizationContext->id();

        $companiesTotal = Buyer::query()->count();
        $companiesWithDeals = Deal::query()->count();

        $pipelineValue = Deal::query()
            ->whereHas('stage', fn ($q) => $q->where('is_closed', false))
            ->sum('estimated_value');

        $wonValue = Deal::query()
            ->whereHas('stage', fn ($q) => $q->where('is_won', true))
            ->sum('estimated_value');

        $tasksOpen = Task::query()
            ->whereIn('status', [TaskStatus::Pending, TaskStatus::Snoozed])
            ->count();

        $tasksOverdue = Task::query()
            ->where('status', TaskStatus::Pending)
            ->where('due_at', '<', now())
            ->count();

        $meetingsUpcoming = CrmMeeting::query()
            ->where('status', CrmMeetingStatus::Scheduled)
            ->where('starts_at', '>=', now())
            ->where('starts_at', '<=', now()->addDays(7))
            ->count();

        $activitiesThisWeek = CrmActivity::query()
            ->where('occurred_at', '>=', now()->startOfWeek())
            ->count();

        return [
            'companies_total' => $companiesTotal,
            'companies_with_deals' => $companiesWithDeals,
            'pipeline_value' => (float) $pipelineValue,
            'won_value' => (float) $wonValue,
            'tasks_open' => $tasksOpen,
            'tasks_overdue' => $tasksOverdue,
            'meetings_upcoming' => $meetingsUpcoming,
            'activities_this_week' => $activitiesThisWeek,
            'pipeline_by_stage' => $this->pipelineByStage(),
            'companies_by_country' => $this->companiesByCountry(),
            'activity_by_type' => $this->activityByType(),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function pipelineByStage(): Collection
    {
        return PipelineStage::query()
            ->withCount('deals')
            ->withSum('deals', 'estimated_value')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PipelineStage $stage) => [
                'key' => $stage->key,
                'label' => $stage->localizedLabel(),
                'deals_count' => $stage->deals_count,
                'value' => (float) ($stage->deals_sum_estimated_value ?? 0),
                'color' => $stage->color,
            ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function companiesByCountry(): Collection
    {
        return Buyer::query()
            ->selectRaw('country_code, COUNT(*) as total')
            ->groupBy('country_code')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'country_code' => $row->country_code,
                'total' => (int) $row->total,
            ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function activityByType(): Collection
    {
        return CrmActivity::query()
            ->selectRaw('activity_type, COUNT(*) as total')
            ->where('occurred_at', '>=', now()->subDays(30))
            ->groupBy('activity_type')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'activity_type' => $row->activity_type,
                'total' => (int) $row->total,
            ]);
    }
}
