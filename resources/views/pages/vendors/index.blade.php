<x-layouts::app :title="__('Vendor Management')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Vendor Management') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Manage vendor registrations, permits, and renewals.') }}</flux:subheading>
            </div>
            @if(auth()->user()->isAdmin())
            <flux:button icon="plus" variant="primary">
                {{ __('Add Vendor') }}
            </flux:button>
            @endif
        </div>

        {{-- Stats Row --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Total Vendors') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">248</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Active') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-emerald-600">231</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Pending Renewal') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-amber-600">12</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Expired') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-red-600">5</flux:heading>
            </div>
        </div>

        {{-- Search & Filter Bar --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <flux:input icon="magnifying-glass" placeholder="{{ __('Search vendors by name or stall...') }}" />
            </div>
            <flux:select class="sm:w-40" placeholder="{{ __('Status') }}">
                <flux:select.option value="all">{{ __('All Status') }}</flux:select.option>
                <flux:select.option value="active">{{ __('Active') }}</flux:select.option>
                <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                <flux:select.option value="expired">{{ __('Expired') }}</flux:select.option>
            </flux:select>
        </div>

        {{-- Vendors Table --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-orange-100 text-left dark:border-zinc-700">
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Name') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Stall') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Section') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Permit Status') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Permit Expiry') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                        @php
                            $vendors = [
                                ['name' => 'Maria Santos', 'stall' => 'A-12', 'section' => 'Section A', 'status' => 'Active', 'expiry' => 'Dec 31, 2026'],
                                ['name' => 'Juan Cruz', 'stall' => 'B-05', 'section' => 'Section B', 'status' => 'Active', 'expiry' => 'Nov 15, 2026'],
                                ['name' => 'Ana Reyes', 'stall' => 'C-08', 'section' => 'Section C', 'status' => 'Pending', 'expiry' => 'Mar 01, 2026'],
                                ['name' => 'Pedro Lim', 'stall' => 'A-03', 'section' => 'Section A', 'status' => 'Active', 'expiry' => 'Aug 22, 2026'],
                                ['name' => 'Rosa Garcia', 'stall' => 'D-11', 'section' => 'Section D', 'status' => 'Expired', 'expiry' => 'Jan 15, 2026'],
                                ['name' => 'Carlos Tan', 'stall' => 'B-09', 'section' => 'Section B', 'status' => 'Active', 'expiry' => 'Oct 08, 2026'],
                                ['name' => 'Elena Dela Cruz', 'stall' => 'C-15', 'section' => 'Section C', 'status' => 'Active', 'expiry' => 'Jul 20, 2026'],
                                ['name' => 'Roberto Villanueva', 'stall' => 'A-07', 'section' => 'Section A', 'status' => 'Pending', 'expiry' => 'Feb 28, 2026'],
                            ];
                        @endphp
                        @foreach($vendors as $vendor)
                        <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="sm" :name="$vendor['name']" />
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $vendor['name'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $vendor['stall'] }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $vendor['section'] }}</td>
                            <td class="px-6 py-3">
                                @php
                                    $color = match($vendor['status']) {
                                        'Active' => 'lime',
                                        'Pending' => 'yellow',
                                        'Expired' => 'red',
                                        default => 'zinc',
                                    };
                                @endphp
                                <flux:badge :color="$color" size="sm">{{ $vendor['status'] }}</flux:badge>
                            </td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $vendor['expiry'] }}</td>
                            <td class="px-6 py-3">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item icon="eye">{{ __('View') }}</flux:menu.item>
                                        @if(auth()->user()->isAdmin())
                                        <flux:menu.item icon="pencil-square">{{ __('Edit') }}</flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger">{{ __('Delete') }}</flux:menu.item>
                                        @endif
                                    </flux:menu>
                                </flux:dropdown>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- Pagination placeholder --}}
            <div class="border-t border-orange-100 px-6 py-3 dark:border-neutral-700">
                <flux:text class="text-sm text-zinc-500">{{ __('Showing 1-8 of 248 vendors') }}</flux:text>
            </div>
        </div>
    </div>
</x-layouts::app>
