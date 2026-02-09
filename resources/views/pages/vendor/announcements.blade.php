<x-layouts::app :title="__('Announcements')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Page Header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Announcements') }}</flux:heading>
                <flux:subheading>{{ __('Notices and updates from the market administration.') }}</flux:subheading>
            </div>
            <flux:badge color="zinc" size="lg" icon="bell">3 unread</flux:badge>
        </div>

        {{-- Announcements List --}}
        <div class="space-y-4">
            @php
                $announcements = [
                    [
                        'title' => 'Market Maintenance Schedule — February 2026',
                        'date' => 'Feb 7, 2026',
                        'category' => 'Maintenance',
                        'categoryColor' => 'yellow',
                        'unread' => true,
                        'message' => 'Please be informed that the market will undergo general cleaning and maintenance on February 15, 2026 (Sunday). The market will be closed from 8:00 PM Saturday to 4:00 AM Monday. All vendors are requested to secure their stalls and remove perishable items. Thank you for your cooperation.',
                        'author' => 'Juan Dela Cruz',
                    ],
                    [
                        'title' => 'Updated Collection Hours Starting March 2026',
                        'date' => 'Feb 5, 2026',
                        'category' => 'Policy Update',
                        'categoryColor' => 'blue',
                        'unread' => true,
                        'message' => 'Effective March 1, 2026, the daily collection schedule will be adjusted. Collectors will visit stalls between 6:00 AM and 10:00 AM. Please ensure your payment is ready during these hours to avoid delays. Late payments will incur a 5% surcharge.',
                        'author' => 'Juan Dela Cruz',
                    ],
                    [
                        'title' => 'Fire Safety Inspection — February 20, 2026',
                        'date' => 'Feb 3, 2026',
                        'category' => 'Safety',
                        'categoryColor' => 'red',
                        'unread' => true,
                        'message' => 'The Bureau of Fire Protection will conduct a routine fire safety inspection on February 20, 2026. All vendors must ensure fire extinguishers are accessible and electrical connections are in proper condition. Non-compliant stalls may face temporary closure.',
                        'author' => 'Juan Dela Cruz',
                    ],
                    [
                        'title' => 'Holiday Schedule — Chinese New Year',
                        'date' => 'Jan 25, 2026',
                        'category' => 'Holiday',
                        'categoryColor' => 'purple',
                        'unread' => false,
                        'message' => 'The market will observe regular hours during the Chinese New Year holiday. However, collection will be suspended on January 29, 2026. Payments for that day will be collected the following business day.',
                        'author' => 'Juan Dela Cruz',
                    ],
                    [
                        'title' => 'Year-End Financial Report Available',
                        'date' => 'Jan 10, 2026',
                        'category' => 'General',
                        'categoryColor' => 'zinc',
                        'unread' => false,
                        'message' => 'The 2025 year-end financial summary for all vendors is now available. You may request a copy of your individual payment summary at the market administration office. Office hours: Monday to Friday, 8:00 AM to 5:00 PM.',
                        'author' => 'Juan Dela Cruz',
                    ],
                ];
            @endphp

            @foreach($announcements as $announcement)
            <div class="rounded-2xl border {{ $announcement['unread'] ? 'border-orange-200 dark:border-orange-900/50' : 'border-orange-100 dark:border-zinc-700' }} bg-white/80 backdrop-blur-sm shadow-sm dark:bg-zinc-900/80 transition-colors">
                <div class="p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex items-start gap-3">
                            @if($announcement['unread'])
                            <div class="mt-1.5 h-2.5 w-2.5 flex-shrink-0 rounded-full bg-orange-500 animate-pulse"></div>
                            @else
                            <div class="mt-1.5 h-2.5 w-2.5 flex-shrink-0 rounded-full bg-zinc-300 dark:bg-zinc-600"></div>
                            @endif
                            <div>
                                <h3 class="font-semibold text-zinc-900 dark:text-zinc-100 {{ $announcement['unread'] ? '' : 'text-zinc-700 dark:text-zinc-300' }}">
                                    {{ $announcement['title'] }}
                                </h3>
                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    <flux:badge :color="$announcement['categoryColor']" size="sm">{{ $announcement['category'] }}</flux:badge>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $announcement['date'] }}</span>
                                    <span class="text-xs text-zinc-400 dark:text-zinc-500">·</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">By {{ $announcement['author'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 ml-6 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        {{ $announcement['message'] }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</x-layouts::app>
