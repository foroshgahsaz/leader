<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $heading ?? __('Settings') }}</h2>
        @isset($subheading)
            <p class="mt-1 text-sm text-gray-600">{{ $subheading }}</p>
        @endisset
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-8">
                <aside class="lg:w-64 shrink-0">
                    @include('settings.partials.navigation')
                </aside>
                <div class="flex-1 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
