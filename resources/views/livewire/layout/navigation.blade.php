<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate class="text-lg font-bold text-indigo-600">
                        ExportOS
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    @can('viewAny', \App\Models\Buyer::class)
                        <x-nav-link :href="route('discover.index')" :active="request()->routeIs('discover.*')" wire:navigate>
                            {{ __('Discover') }}
                        </x-nav-link>
                    @endcan
                    @can('crm.view')
                        <x-nav-link :href="route('crm.companies.index')" :active="request()->routeIs('crm.*')" wire:navigate>
                            {{ __('CRM') }}
                        </x-nav-link>
                    @endcan
                    @can('access-settings')
                        <x-nav-link :href="route('settings.company')" :active="request()->routeIs('settings.*')" wire:navigate>
                            {{ __('Settings') }}
                        </x-nav-link>
                    @endcan
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:gap-3 sm:ms-6">
                @can('notifications.view')
                    <livewire:notifications.notification-bell />
                @endcan

                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-600 bg-white hover:text-gray-800 focus:outline-none">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold">
                                {{ auth()->user()->initials() }}
                            </span>
                            <span x-data="{{ json_encode(['name' => auth()->user()->fullName()]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></span>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 text-xs text-gray-500">
                            {{ auth()->user()->currentOrganization?->name }}
                        </div>
                        <x-dropdown-link :href="route('profile')" wire:navigate>{{ __('Profile') }}</x-dropdown-link>
                        @can('access-settings')
                            <x-dropdown-link :href="route('settings.company')" wire:navigate>{{ __('Settings') }}</x-dropdown-link>
                        @endcan
                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link>{{ __('Log Out') }}</x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</x-responsive-nav-link>
            @can('viewAny', \App\Models\Buyer::class)
                <x-responsive-nav-link :href="route('discover.index')" :active="request()->routeIs('discover.*')" wire:navigate>{{ __('Discover') }}</x-responsive-nav-link>
            @endcan
            @can('crm.view')
                <x-responsive-nav-link :href="route('crm.companies.index')" :active="request()->routeIs('crm.*')" wire:navigate>{{ __('CRM') }}</x-responsive-nav-link>
            @endcan
            @can('access-settings')
                <x-responsive-nav-link :href="route('settings.company')" :active="request()->routeIs('settings.*')" wire:navigate>{{ __('Settings') }}</x-responsive-nav-link>
            @endcan
        </div>
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ auth()->user()->fullName() }}</div>
                <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
            </div>
            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>{{ __('Profile') }}</x-responsive-nav-link>
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link>{{ __('Log Out') }}</x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>
