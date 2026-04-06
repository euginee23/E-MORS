<?php

use App\Enums\PaymentStatus;
use App\Models\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $statusFilter = 'all';
    public string $periodFilter = 'all';

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPeriodFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function vendor()
    {
        return Auth::user()->vendor?->load('stall');
    }

    #[Computed]
    public function stall()
    {
        return $this->vendor?->stall;
    }

    #[Computed]
    public function payments()
    {
        if (! $this->vendor) {
            return collect();
        }

        return Collection::where('vendor_id', $this->vendor->id)
            ->with(['collector', 'stall'])
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->periodFilter !== 'all', fn ($q) => match ($this->periodFilter) {
                'month' => $q->whereMonth('payment_date', now()->month)->whereYear('payment_date', now()->year),
                'last_month' => $q->whereMonth('payment_date', now()->subMonth()->month)->whereYear('payment_date', now()->subMonth()->year),
                'year' => $q->whereYear('payment_date', now()->year),
                default => $q,
            })
            ->orderByDesc('payment_date')
            ->paginate(10);
    }

    #[Computed]
    public function totalPaidYear(): string
    {
        if (! $this->vendor) return '0';
        $total = Collection::where('vendor_id', $this->vendor->id)
            ->where('status', PaymentStatus::Paid)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');
        return number_format($total, 0);
    }

    #[Computed]
    public function totalPaidYearCount(): int
    {
        if (! $this->vendor) return 0;
        return Collection::where('vendor_id', $this->vendor->id)
            ->where('status', PaymentStatus::Paid)
            ->whereYear('payment_date', now()->year)
            ->count();
    }

    #[Computed]
    public function outstandingAmount(): string
    {
        if (! $this->vendor) return '0';
        $total = Collection::where('vendor_id', $this->vendor->id)
            ->whereIn('status', [PaymentStatus::Pending, PaymentStatus::Overdue])
            ->sum('amount');
        return number_format($total, 0);
    }

    #[Computed]
    public function lastPayment()
    {
        if (! $this->vendor) return null;
        return Collection::where('vendor_id', $this->vendor->id)
            ->where('status', PaymentStatus::Paid)
            ->latest('payment_date')
            ->first();
    }

    #[Computed]
    public function paymentStatus(): string
    {
        $outstanding = Collection::where('vendor_id', $this->vendor?->id)
            ->whereIn('status', [PaymentStatus::Pending, PaymentStatus::Overdue])
            ->count();
        return $outstanding > 0 ? 'Has Outstanding' : 'Up to Date';
    }

    #[Computed]
    public function paymentStatusColor(): string
    {
        return $this->paymentStatus === 'Up to Date' ? 'lime' : 'yellow';
    }

    public function render()
    {
        return $this->view()->title(__('My Payments'));
    }
}; ?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Page Header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('My Payments') }}</flux:heading>
                <flux:subheading>{{ __('View your payment history and upcoming dues.') }}</flux:subheading>
            </div>
        </div>

        {{-- Payment Summary Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Monthly Rate') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-amber-50 dark:bg-amber-900/20">
                        <flux:icon.calendar class="size-5 text-amber-600 dark:text-amber-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">₱ {{ number_format($this->stall?->monthly_rate ?? 0, 0) }}</flux:heading>
                    <flux:text class="mt-1 text-xs text-amber-600 dark:text-amber-400">Due: {{ now()->endOfMonth()->format('M j, Y') }}</flux:text>
                </div>
            </div>

            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Payment Status') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/20">
                        <flux:icon.check-circle class="size-5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:badge :color="$this->paymentStatusColor" size="sm">{{ $this->paymentStatus }}</flux:badge>
                    @if($this->lastPayment)
                    <flux:text class="mt-1 text-xs text-zinc-500">Last: {{ $this->lastPayment->payment_date->format('M j, Y') }}</flux:text>
                    @endif
                </div>
            </div>

            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Total Paid (Year)') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/20">
                        <flux:icon.banknotes class="size-5 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">₱ {{ $this->totalPaidYear }}</flux:heading>
                    <flux:text class="mt-1 text-xs text-zinc-500">{{ $this->totalPaidYearCount }} payments this year</flux:text>
                </div>
            </div>

            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="flex items-center justify-between">
                    <flux:text class="text-sm font-medium">{{ __('Outstanding') }}</flux:text>
                    <div class="flex size-10 items-center justify-center rounded-lg bg-red-50 dark:bg-red-900/20">
                        <flux:icon.exclamation-circle class="size-5 text-red-600 dark:text-red-400" />
                    </div>
                </div>
                <div class="mt-3">
                    <flux:heading size="xl" class="text-2xl font-bold">₱ {{ $this->outstandingAmount }}</flux:heading>
                    <flux:text class="mt-1 text-xs {{ $this->outstandingAmount === '0' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $this->outstandingAmount === '0' ? 'Fully settled' : 'Needs attention' }}
                    </flux:text>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-end">
            <flux:select wire:model.live="statusFilter" class="sm:w-40">
                <flux:select.option value="all">{{ __('All Status') }}</flux:select.option>
                <flux:select.option value="paid">{{ __('Paid') }}</flux:select.option>
                <flux:select.option value="pending">{{ __('Pending') }}</flux:select.option>
                <flux:select.option value="overdue">{{ __('Overdue') }}</flux:select.option>
            </flux:select>
            <flux:select wire:model.live="periodFilter" class="sm:w-44">
                <flux:select.option value="all">{{ __('All Time') }}</flux:select.option>
                <flux:select.option value="month">{{ __('This Month') }}</flux:select.option>
                <flux:select.option value="last_month">{{ __('Last Month') }}</flux:select.option>
                <flux:select.option value="year">{{ __('This Year') }}</flux:select.option>
            </flux:select>
        </div>

        {{-- Payment History Table --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="border-b border-orange-100 px-6 py-4 dark:border-zinc-700">
                <flux:heading size="lg">{{ __('Payment History') }}</flux:heading>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-orange-100 text-left dark:border-zinc-700">
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Receipt #') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Date') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Amount') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Method') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Collector') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                        @forelse($this->payments as $payment)
                        <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50" wire:key="pay-{{ $payment->id }}">
                            <td class="px-6 py-3 font-mono text-xs text-zinc-700 dark:text-zinc-300">{{ $payment->receipt_number }}</td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $payment->payment_date->format('M j, Y') }}</td>
                            <td class="px-6 py-3 font-medium text-zinc-900 dark:text-zinc-100">₱ {{ number_format($payment->amount, 0) }}</td>
                            <td class="px-6 py-3">
                                <flux:badge color="zinc" size="sm">{{ ucfirst($payment->payment_method) }}</flux:badge>
                            </td>
                            <td class="px-6 py-3 text-zinc-600 dark:text-zinc-400">{{ $payment->collector?->name ?? '—' }}</td>
                            <td class="px-6 py-3">
                                <flux:badge :color="$payment->status->color()" size="sm">{{ $payment->status->label() }}</flux:badge>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-zinc-500">
                                {{ __('No payments found.') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($this->payments instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <div class="border-t border-orange-100 px-6 py-3 dark:border-neutral-700">
                {{ $this->payments->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
