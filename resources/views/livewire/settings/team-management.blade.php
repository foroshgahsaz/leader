<div class="space-y-8">
    @if (session('team_message'))
        <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('team_message') }}</div>
    @endif

    @can('invite', App\Models\OrgMember::class)
        <section class="border border-gray-200 rounded-lg p-5">
            <h3 class="text-base font-semibold text-gray-900">{{ __('Invite team member') }}</h3>
            <form wire:submit="invite" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div class="md:col-span-1">
                    <x-input-label for="invite_email" :value="__('Email')" />
                    <x-text-input wire:model="invite_email" id="invite_email" type="email" class="mt-1 block w-full" required />
                    <x-input-error class="mt-2" :messages="$errors->get('invite_email')" />
                </div>
                <div>
                    <x-input-label for="invite_role" :value="__('Role')" />
                    <select wire:model="invite_role" id="invite_role" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}">{{ $role->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-primary-button>{{ __('Send invite') }}</x-primary-button>
                </div>
            </form>
        </section>
    @endcan

    <section>
        <h3 class="text-base font-semibold text-gray-900 mb-4">{{ __('Team members') }}</h3>
        <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Member') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Status') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Role') }}</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600">{{ __('Joined') }}</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-600">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($members as $member)
                        <tr wire:key="member-{{ $member->id }}">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $member->user->fullName() }}</div>
                                <div class="text-gray-500">{{ $member->user->email }}</div>
                            </td>
                            <td class="px-4 py-3 capitalize">{{ $member->status->label() }}</td>
                            <td class="px-4 py-3">
                                @php($role = $member->user->organizationRole($member->organization))
                                @can('update', $member)
                                    <select
                                        class="border-gray-300 rounded-md text-sm"
                                        wire:change="updateRole('{{ $member->id }}', $event.target.value)"
                                    >
                                        @foreach ($roles as $roleOption)
                                            <option value="{{ $roleOption->value }}" @selected($role === $roleOption)>{{ $roleOption->label() }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    {{ $role?->label() ?? '—' }}
                                @endcan
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $member->joined_at?->format('M j, Y') ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                @can('delete', $member)
                                    <button type="button" wire:click="deactivate('{{ $member->id }}')" wire:confirm="{{ __('Deactivate this team member?') }}" class="text-red-600 hover:text-red-800 text-sm">
                                        {{ __('Deactivate') }}
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">{{ __('No team members yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $members->links() }}</div>
    </section>
</div>
