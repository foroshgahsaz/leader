<?php

namespace App\Livewire\Crm;

use App\Enums\OrgMemberStatus;
use App\Enums\TaskStatus;
use App\Models\OrgMember;
use App\Models\Task;
use App\Models\User;
use App\Support\OrganizationContext;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class TaskIndex extends Component
{
    use WithPagination;

    public string $status = '';

    public string $assigneeId = '';

    public bool $overdueOnly = false;

    public function mount(): void
    {
        $this->authorize('viewAny', Task::class);
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedAssigneeId(): void
    {
        $this->resetPage();
    }

    public function updatedOverdueOnly(): void
    {
        $this->resetPage();
    }

    #[Layout('layouts.crm', ['heading' => 'Tasks', 'subheading' => 'Your CRM task queue.'])]
    public function render(OrganizationContext $organizationContext)
    {
        $query = Task::query()
            ->with(['buyer', 'assignee', 'deal'])
            ->whereNotNull('buyer_id')
            ->latest('due_at');

        if ($this->status !== '') {
            $query->where('status', $this->status);
        } else {
            $query->whereIn('status', [TaskStatus::Pending, TaskStatus::Snoozed]);
        }

        if ($this->assigneeId !== '') {
            $query->where('assigned_to', (int) $this->assigneeId);
        }

        if ($this->overdueOnly) {
            $query->where('status', TaskStatus::Pending)
                ->where('due_at', '<', now());
        }

        $organizationId = $organizationContext->id();

        $assignees = User::query()
            ->whereIn('id', OrgMember::query()
                ->where('organization_id', $organizationId)
                ->where('status', OrgMemberStatus::Active)
                ->pluck('user_id'))
            ->orderBy('first_name')
            ->get();

        return view('livewire.crm.task-index', [
            'tasks' => $query->paginate(25),
            'assignees' => $assignees,
            'statuses' => TaskStatus::options(),
        ]);
    }
}
