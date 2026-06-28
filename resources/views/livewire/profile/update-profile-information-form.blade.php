<?php

use App\Actions\Settings\UpdateUserProfileAction;
use App\Data\Settings\UpdateUserProfileData;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $first_name = '';

    public string $last_name = '';

    public string $job_title = '';

    public string $email = '';

    public string $timezone = 'UTC';

    public string $locale = 'fa';

    public function mount(): void
    {
        $user = Auth::user();

        $this->first_name = $user->first_name ?? '';
        $this->last_name = $user->last_name ?? '';
        $this->job_title = $user->job_title ?? '';
        $this->email = $user->email;
        $this->timezone = $user->timezone ?? config('exportos.default_timezone', 'UTC');
        $this->locale = $user->locale ?? config('exportos.default_locale', 'fa');
    }

    public function updateProfileInformation(UpdateUserProfileAction $updateUserProfileAction): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'job_title' => ['nullable', 'string', 'max:150'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'timezone' => ['required', 'string', 'max:64'],
            'locale' => ['required', 'string', 'max:10', Rule::in(\App\Support\Locale::supported())],
        ]);

        $updated = $updateUserProfileAction->execute($user, UpdateUserProfileData::fromArray($validated));

        Session::put('locale', $updated->locale);

        if ($user->email !== $updated->email) {
            $updated->email_verified_at = null;
            $updated->save();
        }

        $this->dispatch('profile-updated', name: $updated->fullName());
    }

    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">{{ __('Profile Information') }}</h2>
        <p class="mt-1 text-sm text-gray-600">{{ __('Update your personal details and contact information.') }}</p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="first_name" :value="__('First name')" />
                <x-text-input wire:model="first_name" id="first_name" class="mt-1 block w-full" type="text" required autocomplete="given-name" />
                <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
            </div>
            <div>
                <x-input-label for="last_name" :value="__('Last name')" />
                <x-text-input wire:model="last_name" id="last_name" class="mt-1 block w-full" type="text" required autocomplete="family-name" />
                <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
            </div>
        </div>

        <div>
            <x-input-label for="job_title" :value="__('Job title')" />
            <x-text-input wire:model="job_title" id="job_title" class="mt-1 block w-full" type="text" autocomplete="organization-title" />
            <x-input-error class="mt-2" :messages="$errors->get('job_title')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="mt-1 block w-full" type="email" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-sm text-gray-800">
                        {{ __('Your email address is unverified.') }}
                        <button type="button" wire:click="sendVerification" class="underline text-sm text-gray-600 hover:text-gray-900">
                            {{ __('Re-send verification email') }}
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 text-sm font-medium text-green-600">{{ __('Verification link sent.') }}</p>
                    @endif
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="timezone" :value="__('Timezone')" />
                <x-text-input wire:model="timezone" id="timezone" class="mt-1 block w-full" type="text" required />
                <x-input-error class="mt-2" :messages="$errors->get('timezone')" />
            </div>
            <div>
                <x-input-label for="locale" :value="__('Language')" />
                <select wire:model="locale" id="locale" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                    @foreach (\App\Support\Locale::options() as $code => $name)
                        <option value="{{ $code }}">{{ $name }}</option>
                    @endforeach
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('locale')" />
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>
            <x-action-message class="me-3" on="profile-updated">{{ __('Saved.') }}</x-action-message>
        </div>
    </form>
</section>
