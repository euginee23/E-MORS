<x-layouts::app :title="__('Assigned Vendors')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Page Header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Assigned Vendors') }}</flux:heading>
                <flux:subheading>{{ __('Vendors assigned to you for collection.') }}</flux:subheading>
            </div>
            <flux:badge color="zinc" size="lg" icon="users">24 vendors</flux:badge>
        </div>

        {{-- Summary Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Total Assigned') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                        <flux:icon.users class="size-5 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">24</flux:heading>
                    <flux:text class="mt-1 text-xs text-zinc-500">across 4 sections</flux:text>
                </div>
            </div>

            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Paid Today') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/20">
                        <flux:icon.check-circle class="size-5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">18</flux:heading>
                    <flux:text class="mt-1 text-xs text-emerald-600 dark:text-emerald-400">75% collected</flux:text>
                </div>
            </div>

            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Pending Today') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/20">
                        <flux:icon.clock class="size-5 text-amber-600 dark:text-amber-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">6</flux:heading>
                    <flux:text class="mt-1 text-xs text-amber-600 dark:text-amber-400">need collection</flux:text>
                </div>
            </div>

            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Overdue') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-red-50 dark:bg-red-900/20">
                        <flux:icon.exclamation-triangle class="size-5 text-red-600 dark:text-red-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">2</flux:heading>
                    <flux:text class="mt-1 text-xs text-red-600 dark:text-red-400">overdue payments</flux:text>
                </div>
            </div>
        </div>

        {{-- Search --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <flux:input icon="magnifying-glass" placeholder="Search vendors..." />
            </div>
            <flux:select class="sm:w-40">
                <flux:select.option value="">All Sections</flux:select.option>
                <flux:select.option value="a">Section A</flux:select.option>
                <flux:select.option value="b">Section B</flux:select.option>
                <flux:select.option value="c">Section C</flux:select.option>
                <flux:select.option value="d">Section D</flux:select.option>
            </flux:select>
            <flux:select class="sm:w-40">
                <flux:select.option value="">All Status</flux:select.option>
                <flux:select.option value="paid">Paid Today</flux:select.option>
                <flux:select.option value="pending">Pending</flux:select.option>
                <flux:select.option value="overdue">Overdue</flux:select.option>
            </flux:select>
        </div>

        {{-- Vendors Table --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-orange-100 text-left dark:border-zinc-700">
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Vendor') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Stall') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Section') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Daily Rate') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Today\'s Status') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Last Collection') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                        @php
                            $vendors = [
                                ['name' => 'Maria Santos', 'stall' => 'A-12', 'section' => 'A', 'rate' => '₱ 150', 'status' => 'Paid', 'lastCollection' => 'Today, 6:15 AM'],
                                ['name' => 'Pedro Lim', 'stall' => 'A-03', 'section' => 'A', 'rate' => '₱ 150', 'status' => 'Paid', 'lastCollection' => 'Today, 6:22 AM'],
                                ['name' => 'Elena Tan', 'stall' => 'A-07', 'section' => 'A', 'rate' => '₱ 150', 'status' => 'Paid', 'lastCollection' => 'Today, 7:15 AM'],
                                ['name' => 'Roberto Aquino', 'stall' => 'A-15', 'section' => 'A', 'rate' => '₱ 150', 'status' => 'Pending', 'lastCollection' => 'Feb 8, 7:20 AM'],
                                ['name' => 'Juan Cruz', 'stall' => 'B-05', 'section' => 'B', 'rate' => '₱ 200', 'status' => 'Paid', 'lastCollection' => 'Today, 6:48 AM'],
                                ['name' => 'Grace Villanueva', 'stall' => 'B-08', 'section' => 'B', 'rate' => '₱ 200', 'status' => 'Pending', 'lastCollection' => 'Feb 8, 8:10 AM'],
                                ['name' => 'Carlos Mendoza', 'stall' => 'B-12', 'section' => 'B', 'rate' => '₱ 200', 'status' => 'Paid', 'lastCollection' => 'Today, 7:30 AM'],
                                ['name' => 'Mark Fernandez', 'stall' => 'B-14', 'section' => 'B', 'rate' => '₱ 200', 'status' => 'Pending', 'lastCollection' => 'Feb 7, 6:50 AM'],
                                ['name' => 'Ana Reyes', 'stall' => 'C-08', 'section' => 'C', 'rate' => '₱ 175', 'status' => 'Paid', 'lastCollection' => 'Today, 7:02 AM'],
                                ['name' => 'Luisa Bautista', 'stall' => 'C-03', 'section' => 'C', 'rate' => '₱ 175', 'status' => 'Paid', 'lastCollection' => 'Today, 7:45 AM'],
                                ['name' => 'Joy Pascual', 'stall' => 'C-11', 'section' => 'C', 'rate' => '₱ 175', 'status' => 'Pending', 'lastCollection' => 'Feb 8, 7:55 AM'],
                                ['name' => 'Rosa Garcia', 'stall' => 'D-11', 'section' => 'D', 'rate' => '₱ 250', 'status' => 'Paid', 'lastCollection' => 'Today, 6:35 AM'],
                                ['name' => 'Miguel Rivera', 'stall' => 'D-06', 'section' => 'D', 'rate' => '₱ 200', 'status' => 'Paid', 'lastCollection' => 'Today, 8:00 AM'],
                                ['name' => 'Dennis Ramos', 'stall' => 'D-02', 'section' => 'D', 'rate' => '₱ 250', 'status' => 'Overdue', 'lastCollection' => 'Feb 5, 6:40 AM'],
                                ['name' => 'Linda Soriano', 'stall' => 'D-09', 'section' => 'D', 'rate' => '₱ 250', 'status' => 'Overdue', 'lastCollection' => 'Feb 4, 7:15 AM'],
                            ];
                        @endphp
                        @foreach($vendors as $v)
                        <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-gradient-to-br from-orange-400 to-amber-400 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <span class="text-white font-bold text-[10px]">{{ collect(explode(' ', $v['name']))->map(fn($w) => $w[0])->implode('') }}</span>
                                    </div>
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $v['name'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $v['stall'] }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">Section {{ $v['section'] }}</td>
                            <td class="px-6 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $v['rate'] }}</td>
                            <td class="px-6 py-3">
                                @if($v['status'] === 'Paid')
                                    <flux:badge color="lime" size="sm">Paid</flux:badge>
                                @elseif($v['status'] === 'Overdue')
                                    <flux:badge color="red" size="sm">Overdue</flux:badge>
                                @else
                                    <flux:badge color="yellow" size="sm">Pending</flux:badge>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-xs text-zinc-500 dark:text-zinc-400">{{ $v['lastCollection'] }}</td>
                            <td class="px-6 py-3">
                                @if($v['status'] !== 'Paid')
                                <flux:button variant="primary" size="sm" icon="banknotes" :href="route('collector.collect')" wire:navigate>
                                    {{ __('Collect') }}
                                </flux:button>
                                @else
                                <flux:badge color="zinc" size="sm">Done</flux:badge>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts::app>
