<?php

namespace App\Livewire\Settings;

use App\Actions\Settings\UpdateUserPreferencesAction;
use App\Models\UserPreference;
use App\Support\OrganizationContext;
use Livewire\Attributes\Layout;
use Livewire\Component;

class PreferencesForm extends Component
{
    public string $theme = 'system';

    public string $density = 'comfortable';

    public bool $email_notifications = true;

    public bool $task_reminders = true;

    public bool $marketing_emails = false;

    public string $start_page = 'dashboard';

    public function mount(OrganizationContext $organizationContext): void
    {
        $this->authorize('settings.view');

        $preferences = UserPreference::query()
            ->where('user_id', auth()->id())
            ->where('organization_id', $organizationContext->id())
            ->first();

        $values = array_merge(UserPreference::defaults(), $preferences?->preferences ?? []);

        $this->theme = $values['theme'];
        $this->density = $values['density'];
        $this->email_notifications = (bool) $values['email_notifications'];
        $this->task_reminders = (bool) $values['task_reminders'];
        $this->marketing_emails = (bool) $values['marketing_emails'];
        $this->start_page = $values['start_page'];
    }

    public function save(UpdateUserPreferencesAction $updateUserPreferencesAction): void
    {
        $this->authorize('settings.update');

        $validated = $this->validate([
            'theme' => ['required', 'in:system,light,dark'],
            'density' => ['required', 'in:comfortable,compact'],
            'email_notifications' => ['boolean'],
            'task_reminders' => ['boolean'],
            'marketing_emails' => ['boolean'],
            'start_page' => ['required', 'in:dashboard,tasks,discover'],
        ]);

        $updateUserPreferencesAction->execute(auth()->user(), $validated);

        $this->dispatch('saved');
    }

    #[Layout('layouts.settings', ['heading' => 'Preferences', 'subheading' => 'Customize your ExportOS experience.'])]
    public function render()
    {
        return view('livewire.settings.preferences-form');
    }
}
