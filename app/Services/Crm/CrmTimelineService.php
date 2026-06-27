<?php

namespace App\Services\Crm;

use App\Enums\CrmTimelineEntryType;
use App\Models\ActivityLog;
use App\Models\Buyer;
use App\Models\BuyerNote;
use App\Models\CrmActivity;
use App\Models\CrmFile;
use App\Models\CrmMeeting;
use App\Models\DealStageHistory;
use App\Models\Task;
use Illuminate\Support\Collection;

class CrmTimelineService
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function forBuyer(Buyer $buyer, int $limit = 100): Collection
    {
        $entries = collect();

        ActivityLog::query()
            ->with('actor')
            ->where('buyer_id', $buyer->id)
            ->latest('occurred_at')
            ->limit($limit)
            ->get()
            ->each(function (ActivityLog $log) use ($entries): void {
                $entries->push([
                    'type' => CrmTimelineEntryType::System,
                    'occurred_at' => $log->occurred_at,
                    'title' => $log->action->label(),
                    'summary' => $log->summary,
                    'actor' => $log->actor?->fullName(),
                ]);
            });

        CrmActivity::query()
            ->with('logger')
            ->where('buyer_id', $buyer->id)
            ->latest('occurred_at')
            ->limit($limit)
            ->get()
            ->each(function (CrmActivity $activity) use ($entries): void {
                $entries->push([
                    'type' => CrmTimelineEntryType::CrmActivity,
                    'occurred_at' => $activity->occurred_at,
                    'title' => $activity->activity_type->label(),
                    'summary' => $activity->subject,
                    'body' => $activity->body,
                    'actor' => $activity->logger?->fullName(),
                ]);
            });

        BuyerNote::query()
            ->with('author')
            ->where('buyer_id', $buyer->id)
            ->latest()
            ->limit($limit)
            ->get()
            ->each(function (BuyerNote $note) use ($entries): void {
                $entries->push([
                    'type' => CrmTimelineEntryType::Note,
                    'occurred_at' => $note->created_at,
                    'title' => 'Note',
                    'summary' => $note->body,
                    'actor' => $note->author?->fullName(),
                ]);
            });

        Task::query()
            ->with('creator')
            ->where('buyer_id', $buyer->id)
            ->latest()
            ->limit($limit)
            ->get()
            ->each(function (Task $task) use ($entries): void {
                $entries->push([
                    'type' => CrmTimelineEntryType::Task,
                    'occurred_at' => $task->created_at,
                    'title' => 'Task: '.$task->title,
                    'summary' => $task->description,
                    'actor' => $task->creator?->fullName(),
                    'meta' => ['status' => $task->status->value],
                ]);
            });

        CrmMeeting::query()
            ->with('organizer')
            ->where('buyer_id', $buyer->id)
            ->latest('starts_at')
            ->limit($limit)
            ->get()
            ->each(function (CrmMeeting $meeting) use ($entries): void {
                $entries->push([
                    'type' => CrmTimelineEntryType::Meeting,
                    'occurred_at' => $meeting->starts_at,
                    'title' => 'Meeting: '.$meeting->title,
                    'summary' => $meeting->agenda,
                    'actor' => $meeting->organizer?->fullName(),
                    'meta' => ['status' => $meeting->status->value],
                ]);
            });

        CrmFile::query()
            ->with('uploader')
            ->where('buyer_id', $buyer->id)
            ->latest()
            ->limit($limit)
            ->get()
            ->each(function (CrmFile $file) use ($entries): void {
                $entries->push([
                    'type' => CrmTimelineEntryType::File,
                    'occurred_at' => $file->created_at,
                    'title' => 'File uploaded',
                    'summary' => $file->original_name,
                    'actor' => $file->uploader?->fullName(),
                ]);
            });

        if ($buyer->deal) {
            DealStageHistory::query()
                ->with(['changedByUser', 'fromStage', 'toStage'])
                ->where('deal_id', $buyer->deal->id)
                ->latest('changed_at')
                ->limit($limit)
                ->get()
                ->each(function (DealStageHistory $history) use ($entries): void {
                    $entries->push([
                        'type' => CrmTimelineEntryType::StageChange,
                        'occurred_at' => $history->changed_at,
                        'title' => 'Stage changed',
                        'summary' => ($history->fromStage?->label ?? 'Start').' → '.$history->toStage->label,
                        'actor' => $history->changedByUser?->fullName(),
                    ]);
                });
        }

        return $entries
            ->sortByDesc(fn (array $entry) => $entry['occurred_at'])
            ->values()
            ->take($limit);
    }
}
