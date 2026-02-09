<x-layouts::app :title="__('Stall Allocation')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Stall Allocation') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Visual stall mapping, real-time availability, and assignment management.') }}</flux:subheading>
            </div>
            @if(auth()->user()->isAdmin())
            <flux:button icon="plus" variant="primary">
                {{ __('Add Stall') }}
            </flux:button>
            @endif
        </div>

        {{-- Stats Row --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Total Stalls') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">320</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Occupied') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-emerald-600">295</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Available') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-blue-600">20</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Under Maintenance') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-amber-600">5</flux:heading>
            </div>
        </div>

        {{-- Stall Map Visual --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="border-b border-orange-100 px-6 py-4 dark:border-neutral-700">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Stall Map') }}</flux:heading>
                    <div class="flex items-center gap-4 text-xs">
                        <span class="flex items-center gap-1.5"><span class="inline-block size-3 rounded-sm bg-emerald-500"></span> {{ __('Occupied') }}</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block size-3 rounded-sm bg-blue-500"></span> {{ __('Available') }}</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block size-3 rounded-sm bg-amber-500"></span> {{ __('Maintenance') }}</span>
                    </div>
                </div>
            </div>
            <div class="p-6">
                @php
                    $stallSections = [
                        'A' => ['total' => 20, 'stalls' => array_map(fn($i) => ['no' => "A-" . str_pad($i, 2, '0', STR_PAD_LEFT), 'status' => $i <= 18 ? 'occupied' : ($i == 19 ? 'maintenance' : 'available')], range(1, 20))],
                        'B' => ['total' => 20, 'stalls' => array_map(fn($i) => ['no' => "B-" . str_pad($i, 2, '0', STR_PAD_LEFT), 'status' => $i <= 16 ? 'occupied' : ($i <= 18 ? 'available' : 'occupied')], range(1, 20))],
                        'C' => ['total' => 20, 'stalls' => array_map(fn($i) => ['no' => "C-" . str_pad($i, 2, '0', STR_PAD_LEFT), 'status' => $i <= 17 ? 'occupied' : 'available'], range(1, 20))],
                        'D' => ['total' => 20, 'stalls' => array_map(fn($i) => ['no' => "D-" . str_pad($i, 2, '0', STR_PAD_LEFT), 'status' => $i <= 15 ? 'occupied' : ($i == 16 ? 'maintenance' : ($i <= 18 ? 'available' : 'occupied'))], range(1, 20))],
                    ];
                @endphp
                @foreach($stallSections as $sectionKey => $section)
                <div class="mb-6 last:mb-0">
                    <flux:text class="mb-2 text-sm font-semibold">{{ __('Section :section', ['section' => $sectionKey]) }}</flux:text>
                    <div class="grid grid-cols-10 gap-2 sm:grid-cols-20">
                        @foreach($section['stalls'] as $stall)
                        @php
                            $bgColor = match($stall['status']) {
                                'occupied' => 'bg-emerald-500/80 hover:bg-emerald-500',
                                'available' => 'bg-blue-500/80 hover:bg-blue-500',
                                'maintenance' => 'bg-amber-500/80 hover:bg-amber-500',
                            };
                        @endphp
                        <flux:tooltip :content="$stall['no'] . ' - ' . ucfirst($stall['status'])">
                            <div class="flex aspect-square items-center justify-center rounded {{ $bgColor }} cursor-pointer text-xs font-medium text-white transition-colors">
                                {{ str_replace($sectionKey . '-', '', $stall['no']) }}
                            </div>
                        </flux:tooltip>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Stalls Table --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="border-b border-orange-100 px-6 py-4 dark:border-neutral-700">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Stall Directory') }}</flux:heading>
                    <div class="flex gap-2">
                        <flux:input icon="magnifying-glass" size="sm" placeholder="{{ __('Search stalls...') }}" />
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-orange-100 text-left dark:border-zinc-700">
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Stall No.') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Section') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Size') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Assigned Vendor') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Monthly Rate') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                        @php
                            $stalls = [
                                ['no' => 'A-01', 'section' => 'Section A', 'size' => '3x3m', 'status' => 'Occupied', 'vendor' => 'Maria Santos', 'rate' => '₱ 3,500'],
                                ['no' => 'A-02', 'section' => 'Section A', 'size' => '3x3m', 'status' => 'Occupied', 'vendor' => 'Juan Cruz', 'rate' => '₱ 3,500'],
                                ['no' => 'A-03', 'section' => 'Section A', 'size' => '4x4m', 'status' => 'Occupied', 'vendor' => 'Pedro Lim', 'rate' => '₱ 5,000'],
                                ['no' => 'B-17', 'section' => 'Section B', 'size' => '3x3m', 'status' => 'Available', 'vendor' => '—', 'rate' => '₱ 3,500'],
                                ['no' => 'B-18', 'section' => 'Section B', 'size' => '3x3m', 'status' => 'Available', 'vendor' => '—', 'rate' => '₱ 3,500'],
                                ['no' => 'D-16', 'section' => 'Section D', 'size' => '4x4m', 'status' => 'Maintenance', 'vendor' => '—', 'rate' => '₱ 5,000'],
                            ];
                        @endphp
                        @foreach($stalls as $stall)
                        <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50">
                            <td class="px-6 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $stall['no'] }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $stall['section'] }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $stall['size'] }}</td>
                            <td class="px-6 py-3">
                                @php
                                    $color = match($stall['status']) {
                                        'Occupied' => 'lime',
                                        'Available' => 'sky',
                                        'Maintenance' => 'yellow',
                                        default => 'zinc',
                                    };
                                @endphp
                                <flux:badge :color="$color" size="sm">{{ $stall['status'] }}</flux:badge>
                            </td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $stall['vendor'] }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $stall['rate'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-orange-100 px-6 py-3 dark:border-neutral-700">
                <flux:text class="text-sm text-zinc-500">{{ __('Showing 6 of 320 stalls') }}</flux:text>
            </div>
        </div>
    </div>
</x-layouts::app>
