<?php

use App\Models\AdminVerificationLog;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public function render()
    {
        $logs = AdminVerificationLog::with(['admin.market', 'performedBy'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return $this->view()->title(__('Verification Audit Log'))->with('logs', $logs);
    }
}; ?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        <div>
            <flux:heading size="xl">{{ __('Verification Audit Log') }}</flux:heading>
            <flux:subheading class="mt-1">{{ __('A record of every admin verification, approval, rejection, activation, and deactivation.') }}</flux:subheading>
        </div>

        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-orange-100 text-left dark:border-zinc-700">
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Admin') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Municipality') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Action') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Reason') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Performed By') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('When') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                        @forelse($logs as $log)
                        <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50" wire:key="log-{{ $log->id }}">
                            <td class="px-6 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $log->admin?->name ?? __('Deleted user') }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $log->admin?->market?->name ?? '—' }}</td>
                            <td class="px-6 py-3">
                                @php
                                    $color = match($log->action) {
                                        'approved' => 'lime',
                                        'rejected' => 'red',
                                        'activated' => 'sky',
                                        'deactivated' => 'zinc',
                                        default => 'zinc',
                                    };
                                @endphp
                                <flux:badge :color="$color" size="sm">{{ ucfirst($log->action) }}</flux:badge>
                            </td>
                            <td class="px-6 py-3 text-zinc-500 dark:text-zinc-400 max-w-64">
                                <span title="{{ $log->reason }}">{{ $log->reason ? Str::limit($log->reason, 60) : '—' }}</span>
                            </td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $log->performedBy?->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-zinc-500 dark:text-zinc-400">{{ $log->created_at->format('M j, Y g:i A') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3 text-zinc-400">
                                    <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-sm">{{ __('No verification actions have been logged yet.') }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-orange-100 px-6 py-3 dark:border-zinc-700">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
