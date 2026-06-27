<form wire:submit="save" class="space-y-6 max-w-2xl">
    <div>
        <x-input-label for="theme" :value="__('Theme')" />
        <select wire:model="theme" id="theme" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="system">{{ __('System') }}</option>
            <option value="light">{{ __('Light') }}</option>
            <option value="dark">{{ __('Dark') }}</option>
        </select>
    </div>

    <div>
        <x-input-label for="density" :value="__('Density')" />
        <select wire:model="density" id="density" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="comfortable">{{ __('Comfortable') }}</option>
            <option value="compact">{{ __('Compact') }}</option>
        </select>
    </div>

    <div>
        <x-input-label for="start_page" :value="__('Start page')" />
        <select wire:model="start_page" id="start_page" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="dashboard">{{ __('Dashboard') }}</option>
            <option value="tasks">{{ __('Tasks') }}</option>
            <option value="discover">{{ __('Discover') }}</option>
        </select>
    </div>

    <div class="space-y-3">
        <label class="flex items-center gap-3">
            <input type="checkbox" wire:model="email_notifications" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <span class="text-sm text-gray-700">{{ __('Email notifications') }}</span>
        </label>
        <label class="flex items-center gap-3">
            <input type="checkbox" wire:model="task_reminders" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <span class="text-sm text-gray-700">{{ __('Task reminders') }}</span>
        </label>
        <label class="flex items-center gap-3">
            <input type="checkbox" wire:model="marketing_emails" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
            <span class="text-sm text-gray-700">{{ __('Product updates and tips') }}</span>
        </label>
    </div>

    <div class="flex items-center gap-4">
        <x-primary-button>{{ __('Save preferences') }}</x-primary-button>
        <x-action-message on="saved">{{ __('Saved.') }}</x-action-message>
    </div>
</form>
