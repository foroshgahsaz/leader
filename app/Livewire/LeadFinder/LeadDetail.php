<?php

namespace App\Livewire\LeadFinder;

use App\Actions\LeadFinder\AddBuyerTagAction;
use App\Actions\LeadFinder\CreateBuyerTaskAction;
use App\Actions\LeadFinder\RemoveBuyerTagAction;
use App\Actions\LeadFinder\SaveBuyerNoteAction;
use App\Actions\LeadFinder\ToggleFavoriteAction;
use App\Actions\LeadFinder\UpdateBuyerStatusAction;
use App\Data\LeadFinder\CreateBuyerNoteData;
use App\Data\LeadFinder\CreateBuyerTaskData;
use App\Data\LeadFinder\UpdateBuyerStatusData;
use App\Enums\BuyerStatus;
use App\Enums\TaskPriority;
use App\Models\ActivityLog;
use App\Models\Buyer;
use App\Models\EntityHistory;
use Livewire\Component;

class LeadDetail extends Component
{
    public Buyer $buyer;

    public string $activeTab = 'overview';

    public string $newStatus = '';

    public string $statusReason = '';

    public string $noteBody = '';

    public bool $notePinned = false;

    public string $newTag = '';

    public string $taskTitle = '';

    public string $taskDescription = '';

    public string $taskDueAt = '';

    public string $taskPriority = 'medium';

    public function mount(Buyer $buyer): void
    {
        $this->authorize('view', $buyer);

        $this->buyer = $buyer->load([
            'globalBuyer',
            'currentScore',
            'summaries' => fn ($query) => $query->latest('generated_at')->limit(1),
            'notes' => fn ($query) => $query->with('author')->latest(),
            'tags',
            'tasks' => fn ($query) => $query->with(['assignee', 'creator'])->latest(),
            'owner',
        ]);

        $this->newStatus = $buyer->status->value;
        $this->taskDueAt = now()->addDays(3)->format('Y-m-d');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function updateStatus(UpdateBuyerStatusAction $updateBuyerStatusAction): void
    {
        $this->authorize('update', $this->buyer);

        $this->validate([
            'newStatus' => ['required', 'in:'.implode(',', array_column(BuyerStatus::cases(), 'value'))],
            'statusReason' => ['nullable', 'string', 'max:500'],
        ]);

        $this->buyer = $updateBuyerStatusAction->execute(
            $this->buyer->id,
            new UpdateBuyerStatusData(
                status: BuyerStatus::from($this->newStatus),
                reason: $this->statusReason !== '' ? $this->statusReason : null,
            ),
            auth()->user(),
        )->load(['globalBuyer', 'currentScore', 'tags']);

        $this->statusReason = '';
        session()->flash('status', __('Status updated.'));
    }

    public function toggleFavorite(ToggleFavoriteAction $toggleFavoriteAction): void
    {
        $this->authorize('update', $this->buyer);

        $this->buyer = $toggleFavoriteAction->execute($this->buyer->id, auth()->user());
    }

    public function addNote(SaveBuyerNoteAction $saveBuyerNoteAction): void
    {
        $this->authorize('update', $this->buyer);

        $this->validate([
            'noteBody' => ['required', 'string', 'max:5000'],
        ]);

        $saveBuyerNoteAction->execute(
            $this->buyer->id,
            new CreateBuyerNoteData($this->noteBody, $this->notePinned),
            auth()->user(),
        );

        $this->reset(['noteBody', 'notePinned']);
        $this->buyer->load(['notes' => fn ($query) => $query->with('author')->latest()]);
        session()->flash('status', __('Note added.'));
    }

    public function addTag(AddBuyerTagAction $addBuyerTagAction): void
    {
        $this->authorize('update', $this->buyer);

        $this->validate([
            'newTag' => ['required', 'string', 'max:50'],
        ]);

        $addBuyerTagAction->execute($this->buyer->id, $this->newTag, auth()->user());

        $this->reset('newTag');
        $this->buyer->load('tags');
    }

    public function removeTag(string $tag, RemoveBuyerTagAction $removeBuyerTagAction): void
    {
        $this->authorize('update', $this->buyer);

        $removeBuyerTagAction->execute($this->buyer->id, $tag, auth()->user());
        $this->buyer->load('tags');
    }

    public function createTask(CreateBuyerTaskAction $createBuyerTaskAction): void
    {
        $this->authorize('update', $this->buyer);

        $this->validate([
            'taskTitle' => ['required', 'string', 'max:255'],
            'taskDescription' => ['nullable', 'string', 'max:2000'],
            'taskDueAt' => ['required', 'date'],
            'taskPriority' => ['required', 'in:'.implode(',', array_column(TaskPriority::cases(), 'value'))],
        ]);

        $createBuyerTaskAction->execute(
            $this->buyer->id,
            new CreateBuyerTaskData(
                title: $this->taskTitle,
                description: $this->taskDescription !== '' ? $this->taskDescription : null,
                dueAt: \Carbon\Carbon::parse($this->taskDueAt),
                priority: TaskPriority::from($this->taskPriority),
            ),
            auth()->user(),
        );

        $this->reset(['taskTitle', 'taskDescription']);
        $this->taskDueAt = now()->addDays(3)->format('Y-m-d');
        $this->buyer->load(['tasks' => fn ($query) => $query->with(['assignee', 'creator'])->latest()]);
        session()->flash('status', __('Task created.'));
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        $summary = $this->buyer->summaries->first();

        $timeline = ActivityLog::query()
            ->with('actor')
            ->where('entity_type', Buyer::class)
            ->where('entity_id', $this->buyer->id)
            ->latest('occurred_at')
            ->limit(50)
            ->get();

        $history = EntityHistory::query()
            ->with('changedByUser')
            ->where('entity_type', Buyer::class)
            ->where('entity_id', $this->buyer->id)
            ->latest('changed_at')
            ->limit(50)
            ->get();

        $location = trim(($this->buyer->city ? $this->buyer->city.', ' : '').$this->buyer->country_code);

        return view('livewire.lead-finder.lead-detail', [
            'summary' => $summary,
            'timeline' => $timeline,
            'history' => $history,
            'statuses' => BuyerStatus::options(),
            'priorities' => TaskPriority::cases(),
        ])->layout('layouts.discover', [
            'heading' => $this->buyer->name,
            'subheading' => $location !== '' ? $location : null,
        ]);
    }
}
