<?php

namespace App\Livewire\Crm;

use App\Actions\Crm\CompleteCrmTaskAction;
use App\Actions\Crm\CompleteMeetingAction;
use App\Actions\Crm\CreateContactAction;
use App\Actions\Crm\CreateCrmTaskAction;
use App\Actions\Crm\CreateDealAction;
use App\Actions\Crm\DeleteContactAction;
use App\Actions\Crm\DeleteCrmFileAction;
use App\Actions\Crm\LogCrmActivityAction;
use App\Actions\Crm\SaveCrmNoteAction;
use App\Actions\Crm\ScheduleMeetingAction;
use App\Actions\Crm\SnoozeCrmTaskAction;
use App\Actions\Crm\UpdateCompanyAction;
use App\Actions\Crm\UpdateContactAction;
use App\Actions\Crm\UploadCrmFileAction;
use App\Data\Crm\CreateContactData;
use App\Data\Crm\CreateCrmTaskData;
use App\Data\Crm\CreateDealData;
use App\Data\Crm\LogCrmActivityData;
use App\Data\Crm\ScheduleMeetingData;
use App\Data\Crm\UpdateCompanyData;
use App\Data\Crm\UpdateContactData;
use App\Data\LeadFinder\CreateBuyerNoteData;
use App\Enums\CrmActivityType;
use App\Enums\OrgMemberStatus;
use App\Enums\TaskPriority;
use App\Models\Buyer;
use App\Models\OrgMember;
use App\Models\PipelineStage;
use App\Models\User;
use App\Services\Crm\CrmTimelineService;
use Livewire\Component;
use Livewire\WithFileUploads;

class CompanyShow extends Component
{
    use WithFileUploads;

    public Buyer $company;

    public string $activeTab = 'overview';

    public string $editName = '';

    public string $editCountryCode = '';

    public string $editCity = '';

    public string $editWebsite = '';

    public string $editPhone = '';

    public string $editIndustry = '';

    public string $editDescription = '';

    public string $editOwnerId = '';

    public string $contactFullName = '';

    public string $contactTitle = '';

    public string $contactEmail = '';

    public string $contactPhone = '';

    public bool $contactIsPrimary = false;

    public ?string $editingContactId = null;

    public string $editContactFullName = '';

    public string $editContactTitle = '';

    public string $editContactEmail = '';

    public string $editContactPhone = '';

    public bool $editContactIsPrimary = false;

    public string $activityType = 'call';

    public string $activitySubject = '';

    public string $activityBody = '';

    public string $activityDuration = '';

    public string $taskTitle = '';

    public string $taskDescription = '';

    public string $taskDueAt = '';

    public string $taskPriority = 'medium';

    public string $taskAssigneeId = '';

    public string $meetingTitle = '';

    public string $meetingAgenda = '';

    public string $meetingLocation = '';

    public string $meetingUrl = '';

    public string $meetingStartsAt = '';

    public string $meetingEndsAt = '';

    public string $meetingOutcome = '';

    public string $noteBody = '';

    public bool $notePinned = false;

    public string $dealTitle = '';

    public string $dealValue = '';

    public string $dealCurrency = 'USD';

    public string $dealCloseDate = '';

    public string $dealStageId = '';

    public $uploadFile;

    public string $fileDescription = '';

    public function mount(Buyer $company): void
    {
        $this->authorize('view', $company);

        $this->company = $company->load([
            'owner',
            'deal.stage',
            'deal.owner',
            'contacts',
            'crmActivities' => fn ($q) => $q->with('logger')->latest('occurred_at'),
            'tasks' => fn ($q) => $q->with(['assignee', 'creator'])->latest('due_at'),
            'crmMeetings' => fn ($q) => $q->with('organizer')->latest('starts_at'),
            'crmFiles' => fn ($q) => $q->with('uploader')->latest(),
            'notes' => fn ($q) => $q->with('author')->latest(),
        ]);

        $this->fillEditForm();
        $this->taskDueAt = now()->addDays(3)->format('Y-m-d');
        $this->meetingStartsAt = now()->addDay()->format('Y-m-d\TH:i');
        $this->meetingEndsAt = now()->addDay()->addHour()->format('Y-m-d\TH:i');
        $this->taskAssigneeId = (string) auth()->id();
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    protected function fillEditForm(): void
    {
        $this->editName = $this->company->name;
        $this->editCountryCode = $this->company->country_code;
        $this->editCity = $this->company->city ?? '';
        $this->editWebsite = $this->company->website ?? '';
        $this->editPhone = $this->company->phone ?? '';
        $this->editIndustry = $this->company->industry ?? '';
        $this->editDescription = $this->company->description ?? '';
        $this->editOwnerId = (string) ($this->company->owner_id ?? '');
    }

    protected function reloadCompany(array $relations = []): void
    {
        $default = [
            'owner',
            'deal.stage',
            'deal.owner',
            'contacts',
            'crmActivities' => fn ($q) => $q->with('logger')->latest('occurred_at'),
            'tasks' => fn ($q) => $q->with(['assignee', 'creator'])->latest('due_at'),
            'crmMeetings' => fn ($q) => $q->with('organizer')->latest('starts_at'),
            'crmFiles' => fn ($q) => $q->with('uploader')->latest(),
            'notes' => fn ($q) => $q->with('author')->latest(),
        ];

        $this->company->load($relations ?: $default);
    }

    public function updateCompany(UpdateCompanyAction $updateCompanyAction): void
    {
        $this->authorize('update', $this->company);

        $this->validate([
            'editName' => ['required', 'string', 'max:255'],
            'editCountryCode' => ['required', 'string', 'size:2'],
            'editCity' => ['nullable', 'string', 'max:100'],
            'editWebsite' => ['nullable', 'url', 'max:255'],
            'editPhone' => ['nullable', 'string', 'max:50'],
            'editIndustry' => ['nullable', 'string', 'max:100'],
            'editDescription' => ['nullable', 'string', 'max:2000'],
            'editOwnerId' => ['nullable', 'integer'],
        ]);

        $this->company = $updateCompanyAction->execute(
            $this->company->id,
            new UpdateCompanyData(
                name: $this->editName,
                countryCode: $this->editCountryCode,
                city: $this->editCity !== '' ? $this->editCity : null,
                website: $this->editWebsite !== '' ? $this->editWebsite : null,
                phone: $this->editPhone !== '' ? $this->editPhone : null,
                industry: $this->editIndustry !== '' ? $this->editIndustry : null,
                description: $this->editDescription !== '' ? $this->editDescription : null,
                ownerId: $this->editOwnerId !== '' ? (int) $this->editOwnerId : null,
            ),
            auth()->user(),
        )->load(['owner', 'deal.stage']);

        session()->flash('status', __('Company updated.'));
    }

    public function createContact(CreateContactAction $createContactAction): void
    {
        $this->authorize('create', \App\Models\BuyerContact::class);

        $this->validate([
            'contactFullName' => ['required', 'string', 'max:255'],
            'contactTitle' => ['nullable', 'string', 'max:100'],
            'contactEmail' => ['nullable', 'email', 'max:255'],
            'contactPhone' => ['nullable', 'string', 'max:50'],
        ]);

        $createContactAction->execute(
            new CreateContactData(
                buyerId: $this->company->id,
                fullName: $this->contactFullName,
                title: $this->contactTitle !== '' ? $this->contactTitle : null,
                email: $this->contactEmail !== '' ? $this->contactEmail : null,
                phone: $this->contactPhone !== '' ? $this->contactPhone : null,
                isPrimary: $this->contactIsPrimary,
            ),
            auth()->user(),
        );

        $this->reset(['contactFullName', 'contactTitle', 'contactEmail', 'contactPhone', 'contactIsPrimary']);
        $this->reloadCompany(['contacts']);
        session()->flash('status', __('Contact added.'));
    }

    public function startEditContact(string $contactId): void
    {
        $contact = $this->company->contacts->firstWhere('id', $contactId);
        if (! $contact) {
            return;
        }

        $this->authorize('update', $contact);

        $this->editingContactId = $contactId;
        $this->editContactFullName = $contact->full_name;
        $this->editContactTitle = $contact->title ?? '';
        $this->editContactEmail = $contact->email ?? '';
        $this->editContactPhone = $contact->phone ?? '';
        $this->editContactIsPrimary = $contact->is_primary;
    }

    public function updateContact(UpdateContactAction $updateContactAction): void
    {
        $contact = $this->company->contacts->firstWhere('id', $this->editingContactId);
        if (! $contact) {
            return;
        }

        $this->authorize('update', $contact);

        $this->validate([
            'editContactFullName' => ['required', 'string', 'max:255'],
            'editContactTitle' => ['nullable', 'string', 'max:100'],
            'editContactEmail' => ['nullable', 'email', 'max:255'],
            'editContactPhone' => ['nullable', 'string', 'max:50'],
        ]);

        $updateContactAction->execute(
            $contact->id,
            new UpdateContactData(
                fullName: $this->editContactFullName,
                title: $this->editContactTitle !== '' ? $this->editContactTitle : null,
                email: $this->editContactEmail !== '' ? $this->editContactEmail : null,
                phone: $this->editContactPhone !== '' ? $this->editContactPhone : null,
                isPrimary: $this->editContactIsPrimary,
            ),
            auth()->user(),
        );

        $this->editingContactId = null;
        $this->reloadCompany(['contacts']);
        session()->flash('status', __('Contact updated.'));
    }

    public function deleteContact(string $contactId, DeleteContactAction $deleteContactAction): void
    {
        $contact = $this->company->contacts->firstWhere('id', $contactId);
        if (! $contact) {
            return;
        }

        $this->authorize('delete', $contact);

        $deleteContactAction->execute($contactId, auth()->user());
        $this->reloadCompany(['contacts']);
        session()->flash('status', __('Contact deleted.'));
    }

    public function logActivity(LogCrmActivityAction $logCrmActivityAction): void
    {
        $this->authorize('create', \App\Models\CrmActivity::class);

        $this->validate([
            'activityType' => ['required', 'in:'.implode(',', array_column(CrmActivityType::cases(), 'value'))],
            'activitySubject' => ['required', 'string', 'max:255'],
            'activityBody' => ['nullable', 'string', 'max:5000'],
            'activityDuration' => ['nullable', 'integer', 'min:1', 'max:480'],
        ]);

        $logCrmActivityAction->execute(
            new LogCrmActivityData(
                buyerId: $this->company->id,
                activityType: CrmActivityType::from($this->activityType),
                subject: $this->activitySubject,
                body: $this->activityBody !== '' ? $this->activityBody : null,
                durationMinutes: $this->activityDuration !== '' ? (int) $this->activityDuration : null,
                dealId: $this->company->deal?->id,
            ),
            auth()->user(),
        );

        $this->reset(['activitySubject', 'activityBody', 'activityDuration']);
        $this->reloadCompany(['crmActivities']);
        session()->flash('status', __('Activity logged.'));
    }

    public function createTask(CreateCrmTaskAction $createCrmTaskAction): void
    {
        $this->authorize('create', \App\Models\Task::class);

        $this->validate([
            'taskTitle' => ['required', 'string', 'max:255'],
            'taskDescription' => ['nullable', 'string', 'max:2000'],
            'taskDueAt' => ['required', 'date'],
            'taskPriority' => ['required', 'in:'.implode(',', array_column(TaskPriority::cases(), 'value'))],
            'taskAssigneeId' => ['required', 'integer'],
        ]);

        $createCrmTaskAction->execute(
            new CreateCrmTaskData(
                title: $this->taskTitle,
                dueAt: \Carbon\Carbon::parse($this->taskDueAt),
                buyerId: $this->company->id,
                dealId: $this->company->deal?->id,
                description: $this->taskDescription !== '' ? $this->taskDescription : null,
                priority: $this->taskPriority,
                assignedToUserId: (int) $this->taskAssigneeId,
            ),
            auth()->user(),
        );

        $this->reset(['taskTitle', 'taskDescription']);
        $this->taskDueAt = now()->addDays(3)->format('Y-m-d');
        $this->reloadCompany(['tasks']);
        session()->flash('status', __('Task created.'));
    }

    public function completeTask(string $taskId, CompleteCrmTaskAction $completeCrmTaskAction): void
    {
        $task = $this->company->tasks->firstWhere('id', $taskId);
        if (! $task) {
            return;
        }

        $this->authorize('complete', $task);

        $completeCrmTaskAction->execute($taskId, auth()->user());
        $this->reloadCompany(['tasks']);
        session()->flash('status', __('Task completed.'));
    }

    public function snoozeTask(string $taskId, SnoozeCrmTaskAction $snoozeCrmTaskAction): void
    {
        $task = $this->company->tasks->firstWhere('id', $taskId);
        if (! $task) {
            return;
        }

        $this->authorize('update', $task);

        $snoozeCrmTaskAction->execute($taskId, now()->addDay(), auth()->user());
        $this->reloadCompany(['tasks']);
        session()->flash('status', __('Task snoozed.'));
    }

    public function scheduleMeeting(ScheduleMeetingAction $scheduleMeetingAction): void
    {
        $this->authorize('create', \App\Models\CrmMeeting::class);

        $this->validate([
            'meetingTitle' => ['required', 'string', 'max:255'],
            'meetingAgenda' => ['nullable', 'string', 'max:2000'],
            'meetingLocation' => ['nullable', 'string', 'max:255'],
            'meetingUrl' => ['nullable', 'url', 'max:255'],
            'meetingStartsAt' => ['required', 'date'],
            'meetingEndsAt' => ['required', 'date', 'after:meetingStartsAt'],
        ]);

        $scheduleMeetingAction->execute(
            new ScheduleMeetingData(
                buyerId: $this->company->id,
                title: $this->meetingTitle,
                startsAt: \Carbon\Carbon::parse($this->meetingStartsAt),
                endsAt: \Carbon\Carbon::parse($this->meetingEndsAt),
                agenda: $this->meetingAgenda !== '' ? $this->meetingAgenda : null,
                location: $this->meetingLocation !== '' ? $this->meetingLocation : null,
                meetingUrl: $this->meetingUrl !== '' ? $this->meetingUrl : null,
                dealId: $this->company->deal?->id,
            ),
            auth()->user(),
        );

        $this->reset(['meetingTitle', 'meetingAgenda', 'meetingLocation', 'meetingUrl']);
        $this->reloadCompany(['crmMeetings']);
        session()->flash('status', __('Meeting scheduled.'));
    }

    public function completeMeeting(string $meetingId, CompleteMeetingAction $completeMeetingAction): void
    {
        $meeting = $this->company->crmMeetings->firstWhere('id', $meetingId);
        if (! $meeting) {
            return;
        }

        $this->authorize('update', $meeting);

        $completeMeetingAction->execute(
            $meetingId,
            $this->meetingOutcome !== '' ? $this->meetingOutcome : null,
            auth()->user(),
        );

        $this->meetingOutcome = '';
        $this->reloadCompany(['crmMeetings']);
        session()->flash('status', __('Meeting completed.'));
    }

    public function saveNote(SaveCrmNoteAction $saveCrmNoteAction): void
    {
        $this->authorize('update', $this->company);

        $this->validate([
            'noteBody' => ['required', 'string', 'max:5000'],
        ]);

        $saveCrmNoteAction->execute(
            $this->company->id,
            new CreateBuyerNoteData($this->noteBody, $this->notePinned),
            auth()->user(),
            $this->company->deal?->id,
        );

        $this->reset(['noteBody', 'notePinned']);
        $this->reloadCompany(['notes']);
        session()->flash('status', __('Note saved.'));
    }

    public function createDeal(CreateDealAction $createDealAction): void
    {
        $this->authorize('create', \App\Models\Deal::class);

        $this->validate([
            'dealTitle' => ['required', 'string', 'max:255'],
            'dealValue' => ['nullable', 'numeric', 'min:0'],
            'dealCurrency' => ['required', 'string', 'size:3'],
            'dealCloseDate' => ['nullable', 'date'],
            'dealStageId' => ['nullable', 'uuid'],
        ]);

        try {
            $this->company->load('deal');

            $deal = $createDealAction->execute(
                new CreateDealData(
                    buyerId: $this->company->id,
                    title: $this->dealTitle,
                    stageId: $this->dealStageId !== '' ? $this->dealStageId : null,
                    estimatedValue: $this->dealValue !== '' ? (float) $this->dealValue : null,
                    currencyCode: $this->dealCurrency,
                    expectedCloseDate: $this->dealCloseDate !== '' ? $this->dealCloseDate : null,
                ),
                auth()->user(),
            );

            $this->reset(['dealTitle', 'dealValue', 'dealCloseDate', 'dealStageId']);
            $this->reloadCompany(['deal.stage', 'deal.owner']);
            session()->flash('status', __('Deal created.'));
        } catch (\DomainException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function uploadFile(UploadCrmFileAction $uploadCrmFileAction): void
    {
        $this->authorize('upload', \App\Models\CrmFile::class);

        $this->validate([
            'uploadFile' => ['required', 'file', 'max:10240'],
            'fileDescription' => ['nullable', 'string', 'max:500'],
        ]);

        $uploadCrmFileAction->execute(
            $this->uploadFile,
            $this->company->id,
            auth()->user(),
            $this->company->deal?->id,
            $this->fileDescription !== '' ? $this->fileDescription : null,
        );

        $this->reset(['uploadFile', 'fileDescription']);
        $this->reloadCompany(['crmFiles']);
        session()->flash('status', __('File uploaded.'));
    }

    public function deleteFile(string $fileId, DeleteCrmFileAction $deleteCrmFileAction): void
    {
        $file = $this->company->crmFiles->firstWhere('id', $fileId);
        if (! $file) {
            return;
        }

        $this->authorize('delete', $file);

        $deleteCrmFileAction->execute($fileId, auth()->user());
        $this->reloadCompany(['crmFiles']);
        session()->flash('status', __('File deleted.'));
    }

    public function render(CrmTimelineService $crmTimelineService)
    {
        $location = trim(($this->company->city ? $this->company->city.', ' : '').$this->company->country_code);

        $owners = User::query()
            ->whereIn('id', OrgMember::query()
                ->where('organization_id', $this->company->organization_id)
                ->where('status', OrgMemberStatus::Active)
                ->pluck('user_id'))
            ->orderBy('first_name')
            ->get();

        $stages = PipelineStage::query()->orderBy('sort_order')->get();

        return view('livewire.crm.company-show', [
            'timeline' => $crmTimelineService->forBuyer($this->company),
            'owners' => $owners,
            'stages' => $stages,
            'activityTypes' => CrmActivityType::options(),
            'priorities' => TaskPriority::cases(),
        ])->layout('layouts.crm', [
            'heading' => $this->company->name,
            'subheading' => $location !== '' ? $location : null,
        ]);
    }
}
