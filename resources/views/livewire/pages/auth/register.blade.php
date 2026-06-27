<?php

use App\Actions\Auth\RegisterOrganizationAction;
use App\Data\Auth\RegisterOrganizationData;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $company_name = '';

    public string $country_code = 'US';

    public string $website = '';

    public function register(RegisterOrganizationAction $registerOrganizationAction): void
    {
        $validated = $this->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'company_name' => ['required', 'string', 'max:255'],
            'country_code' => ['required', 'string', 'size:2'],
            'website' => ['nullable', 'url', 'max:500'],
        ]);

        $user = $registerOrganizationAction->execute(RegisterOrganizationData::fromArray($validated));

        event(new Registered($user));

        Auth::login($user);

        Session::regenerate();

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">{{ __('Create your ExportOS account') }}</h1>
        <p class="mt-1 text-sm text-gray-600">{{ __('Start finding international buyers in minutes.') }}</p>
    </div>

    <form wire:submit="register" class="space-y-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="first_name" :value="__('First name')" />
                <x-text-input wire:model="first_name" id="first_name" class="block mt-1 w-full" type="text" required autofocus autocomplete="given-name" />
                <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="last_name" :value="__('Last name')" />
                <x-text-input wire:model="last_name" id="last_name" class="block mt-1 w-full" type="text" required autocomplete="family-name" />
                <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="company_name" :value="__('Company name')" />
            <x-text-input wire:model="company_name" id="company_name" class="block mt-1 w-full" type="text" required autocomplete="organization" />
            <x-input-error :messages="$errors->get('company_name')" class="mt-2" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label for="country_code" :value="__('Country')" />
                <x-text-input wire:model="country_code" id="country_code" class="block mt-1 w-full uppercase" type="text" maxlength="2" required placeholder="US" />
                <x-input-error :messages="$errors->get('country_code')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="website" :value="__('Website (optional)')" />
                <x-text-input wire:model="website" id="website" class="block mt-1 w-full" type="url" placeholder="https://example.com" />
                <x-input-error :messages="$errors->get('website')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="email" :value="__('Work email')" />
            <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input wire:model="password" id="password" class="block mt-1 w-full" type="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm password')" />
            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full" type="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between pt-2">
            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>
            <x-primary-button>{{ __('Create account') }}</x-primary-button>
        </div>
    </form>
</div>
