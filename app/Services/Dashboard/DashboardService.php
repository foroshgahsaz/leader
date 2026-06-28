<?php

namespace App\Services\Dashboard;

use App\Enums\AiGenerationStatus;
use App\Enums\AiGenerationType;
use App\Enums\BuyerStatus;
use App\Enums\CrmActivityType;
use App\Enums\ScoreBand;
use App\Enums\TaskStatus;
use App\Models\AiGeneration;
use App\Models\Buyer;
use App\Models\BuyerScore;
use App\Models\CrmActivity;
use App\Models\Deal;
use App\Models\EntityHistory;
use App\Models\PipelineStage;
use App\Models\Task;
use App\Models\User;
use App\Support\OrganizationContext;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(
        protected OrganizationContext $organizationContext,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(User $user): array
    {
        $today = now()->startOfDay();
        $weekStart = now()->startOfWeek();
        $lastWeekStart = now()->subWeek()->startOfWeek();
        $lastWeekEnd = now()->subWeek()->endOfWeek();

        return [
            'kpis' => $this->kpis($user, $today, $weekStart),
            'todays_leads' => $this->todaysLeads($today),
            'top_scored_leads' => $this->topScoredLeads(),
            'tasks_due_today' => $this->tasksDueToday($user),
            'pipeline' => $this->pipelineSummary(),
            'charts' => $this->charts(),
            'performance' => $this->performance($weekStart, $lastWeekStart, $lastWeekEnd),
        ];
    }

    /**
     * @return array<string, int|float>
     */
    protected function kpis(User $user, Carbon $today, Carbon $weekStart): array
    {
        $emailsToday = $this->emailsSentCount($today, now());
        $emailsWeek = $this->emailsSentCount($weekStart, now());
        $repliesToday = $this->repliesCount($today, now());
        $repliesWeek = $this->repliesCount($weekStart, now());

        $scores = BuyerScore::query()->where('is_current', true);

        return [
            'todays_leads' => Buyer::query()->where('created_at', '>=', $today)->count(),
            'leads_this_week' => Buyer::query()->where('created_at', '>=', $weekStart)->count(),
            'avg_ai_score' => (int) round((float) (clone $scores)->avg('score')),
            'scored_leads' => (clone $scores)->count(),
            'high_score_leads' => (clone $scores)->where('score_band', ScoreBand::High)->count(),
            'emails_sent_today' => $emailsToday,
            'emails_sent_week' => $emailsWeek,
            'ai_emails_generated_week' => AiGeneration::query()
                ->where('type', AiGenerationType::Email)
                ->where('status', AiGenerationStatus::Completed)
                ->where('completed_at', '>=', $weekStart)
                ->count(),
            'replies_today' => $repliesToday,
            'replies_week' => $repliesWeek,
            'total_replied_leads' => Buyer::query()->where('status', BuyerStatus::Replied)->count(),
            'tasks_due_today' => Task::query()
                ->whereIn('status', [TaskStatus::Pending, TaskStatus::Snoozed])
                ->whereDate('due_at', $today)
                ->count(),
            'my_tasks_due_today' => Task::query()
                ->where('assigned_to', $user->id)
                ->whereIn('status', [TaskStatus::Pending, TaskStatus::Snoozed])
                ->whereDate('due_at', $today)
                ->count(),
            'tasks_overdue' => Task::query()
                ->where('status', TaskStatus::Pending)
                ->where('due_at', '<', $today)
                ->count(),
            'open_tasks' => Task::query()
                ->whereIn('status', [TaskStatus::Pending, TaskStatus::Snoozed])
                ->count(),
            'open_deals' => Deal::query()
                ->whereHas('stage', fn ($q) => $q->where('is_closed', false))
                ->count(),
            'pipeline_value' => (float) Deal::query()
                ->whereHas('stage', fn ($q) => $q->where('is_closed', false))
                ->sum('estimated_value'),
            'won_value' => (float) Deal::query()
                ->whereHas('stage', fn ($q) => $q->where('is_won', true))
                ->sum('estimated_value'),
        ];
    }

    protected function emailsSentCount(Carbon $from, Carbon $to): int
    {
        return CrmActivity::query()
            ->where('activity_type', CrmActivityType::Email)
            ->whereBetween('occurred_at', [$from, $to])
            ->count();
    }

    protected function repliesCount(Carbon $from, Carbon $to): int
    {
        $fromHistory = EntityHistory::query()
            ->where('entity_type', Buyer::class)
            ->where('field_name', 'status')
            ->where('new_value', BuyerStatus::Replied->value)
            ->whereBetween('changed_at', [$from, $to])
            ->count();

        if ($fromHistory > 0) {
            return $fromHistory;
        }

        return Buyer::query()
            ->where('status', BuyerStatus::Replied)
            ->whereBetween('updated_at', [$from, $to])
            ->count();
    }

    /**
     * @return Collection<int, Buyer>
     */
    protected function todaysLeads(Carbon $today): Collection
    {
        return Buyer::query()
            ->with(['currentScore', 'owner'])
            ->where('created_at', '>=', $today)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }

    /**
     * @return Collection<int, Buyer>
     */
    protected function topScoredLeads(): Collection
    {
        return Buyer::query()
            ->with(['currentScore', 'owner'])
            ->whereHas('currentScore')
            ->join('buyer_scores', function ($join): void {
                $join->on('buyers.id', '=', 'buyer_scores.buyer_id')
                    ->where('buyer_scores.is_current', true);
            })
            ->orderByDesc('buyer_scores.score')
            ->select('buyers.*')
            ->limit(8)
            ->get();
    }

    /**
     * @return Collection<int, Task>
     */
    protected function tasksDueToday(User $user): Collection
    {
        return Task::query()
            ->with(['buyer', 'assignee'])
            ->whereIn('status', [TaskStatus::Pending, TaskStatus::Snoozed])
            ->whereDate('due_at', now()->toDateString())
            ->orderBy('due_at')
            ->limit(10)
            ->get();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function pipelineSummary(): Collection
    {
        return PipelineStage::query()
            ->withCount('deals')
            ->withSum('deals', 'estimated_value')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PipelineStage $stage) => [
                'key' => $stage->key,
                'label' => $stage->localizedLabel(),
                'color' => $stage->color,
                'deals_count' => $stage->deals_count,
                'value' => (float) ($stage->deals_sum_estimated_value ?? 0),
                'is_closed' => $stage->is_closed,
                'is_won' => $stage->is_won,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function charts(): array
    {
        return [
            'leads_trend' => $this->dailySeries(
                Buyer::query(),
                'created_at',
                14,
            ),
            'emails_trend' => $this->dailyActivitySeries(CrmActivityType::Email, 14),
            'replies_trend' => $this->dailyRepliesSeries(14),
            'score_distribution' => $this->scoreDistribution(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function performance(Carbon $weekStart, Carbon $lastWeekStart, Carbon $lastWeekEnd): array
    {
        $contacted = Buyer::query()->where('status', BuyerStatus::Contacted)->count()
            + Buyer::query()->whereIn('status', [BuyerStatus::Replied, BuyerStatus::Qualified])->count();

        $replied = Buyer::query()->whereIn('status', [BuyerStatus::Replied, BuyerStatus::Qualified])->count();

        $wonDeals = Deal::query()->whereHas('stage', fn ($q) => $q->where('is_won', true))->count();
        $lostDeals = Deal::query()->whereHas('stage', fn ($q) => $q->where('is_closed', true)->where('is_won', false))->count();
        $closedDeals = $wonDeals + $lostDeals;

        $leadsThisWeek = Buyer::query()->where('created_at', '>=', $weekStart)->count();
        $leadsLastWeek = Buyer::query()
            ->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
            ->count();

        $emailsThisWeek = $this->emailsSentCount($weekStart, now());
        $emailsLastWeek = $this->emailsSentCount($lastWeekStart, $lastWeekEnd);

        $activitiesThisWeek = CrmActivity::query()->where('occurred_at', '>=', $weekStart)->count();
        $activitiesLastWeek = CrmActivity::query()
            ->whereBetween('occurred_at', [$lastWeekStart, $lastWeekEnd])
            ->count();

        return [
            'reply_rate' => $contacted > 0 ? round(($replied / $contacted) * 100, 1) : 0.0,
            'qualification_rate' => Buyer::query()->count() > 0
                ? round((Buyer::query()->where('status', BuyerStatus::Qualified)->count() / Buyer::query()->count()) * 100, 1)
                : 0.0,
            'win_rate' => $closedDeals > 0 ? round(($wonDeals / $closedDeals) * 100, 1) : 0.0,
            'avg_deal_value' => (float) Deal::query()->avg('estimated_value'),
            'leads_week_change' => $this->percentChange($leadsLastWeek, $leadsThisWeek),
            'emails_week_change' => $this->percentChange($emailsLastWeek, $emailsThisWeek),
            'activities_week_change' => $this->percentChange($activitiesLastWeek, $activitiesThisWeek),
            'won_deals' => $wonDeals,
            'lost_deals' => $lostDeals,
        ];
    }

    /**
     * @return array<int, array{date: string, label: string, value: int}>
     */
    protected function dailySeries($query, string $column, int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();
        $counts = (clone $query)
            ->where($column, '>=', $start)
            ->selectRaw('DATE('.$column.') as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return $this->fillDailySeries($counts, $days);
    }

    /**
     * @return array<int, array{date: string, label: string, value: int}>
     */
    protected function dailyActivitySeries(CrmActivityType $type, int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();
        $counts = CrmActivity::query()
            ->where('activity_type', $type)
            ->where('occurred_at', '>=', $start)
            ->selectRaw('DATE(occurred_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return $this->fillDailySeries($counts, $days);
    }

    /**
     * @return array<int, array{date: string, label: string, value: int}>
     */
    protected function dailyRepliesSeries(int $days): array
    {
        $start = now()->subDays($days - 1)->startOfDay();
        $counts = EntityHistory::query()
            ->where('entity_type', Buyer::class)
            ->where('field_name', 'status')
            ->where('new_value', BuyerStatus::Replied->value)
            ->where('changed_at', '>=', $start)
            ->selectRaw('DATE(changed_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        if ($counts->isEmpty()) {
            $counts = Buyer::query()
                ->where('status', BuyerStatus::Replied)
                ->where('updated_at', '>=', $start)
                ->selectRaw('DATE(updated_at) as day, COUNT(*) as total')
                ->groupBy('day')
                ->pluck('total', 'day');
        }

        return $this->fillDailySeries($counts, $days);
    }

    /**
     * @return array<int, array{band: string, label: string, count: int}>
     */
    protected function scoreDistribution(): array
    {
        $counts = BuyerScore::query()
            ->where('is_current', true)
            ->selectRaw('score_band, COUNT(*) as total')
            ->groupBy('score_band')
            ->pluck('total', 'score_band');

        return collect(ScoreBand::cases())->map(fn (ScoreBand $band) => [
            'band' => $band->value,
            'label' => $band->label(),
            'count' => (int) ($counts[$band->value] ?? 0),
        ])->values()->all();
    }

    /**
     * @param  Collection<string, mixed>  $counts
     * @return array<int, array{date: string, label: string, value: int}>
     */
    protected function fillDailySeries(Collection $counts, int $days): array
    {
        $period = CarbonPeriod::create(now()->subDays($days - 1)->startOfDay(), '1 day', now()->startOfDay());
        $series = [];

        foreach ($period as $date) {
            $key = $date->toDateString();
            $series[] = [
                'date' => $key,
                'label' => $date->format('M j'),
                'value' => (int) ($counts[$key] ?? 0),
            ];
        }

        return $series;
    }

    protected function percentChange(int $previous, int $current): float
    {
        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
