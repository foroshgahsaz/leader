@php
    $links = [
        ['route' => 'settings.company', 'label' => __('Company'), 'permission' => 'company.view'],
        ['route' => 'settings.team', 'label' => __('Team'), 'permission' => 'team.view'],
        ['route' => 'settings.notifications', 'label' => __('Notifications'), 'permission' => 'notifications.view'],
        ['route' => 'settings.activity', 'label' => __('Activity Log'), 'permission' => 'activity.view'],
        ['route' => 'settings.preferences', 'label' => __('Preferences'), 'permission' => 'settings.view'],
        ['route' => 'profile', 'label' => __('My Profile'), 'permission' => null],
    ];
@endphp

<nav class="bg-white shadow-sm sm:rounded-lg p-4 space-y-1">
    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 px-3 pb-2">{{ __('Settings') }}</p>
    @foreach ($links as $link)
        @if (! $link['permission'] || auth()->user()->can($link['permission']))
            <a href="{{ route($link['route']) }}"
               wire:navigate
               @class([
                   'block rounded-md px-3 py-2 text-sm font-medium transition',
                   'bg-indigo-50 text-indigo-700' => request()->routeIs($link['route']),
                   'text-gray-700 hover:bg-gray-50' => ! request()->routeIs($link['route']),
               ])>
                {{ $link['label'] }}
            </a>
        @endif
    @endforeach
</nav>
