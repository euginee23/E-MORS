<?php

use App\Enums\PaymentStatus;
use App\Models\Collection;
use App\Models\Stall;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public string $period = 'month';

    public function updatedPeriod(): void
    {
        $this->clearCache();
    }

    #[Computed]
    public function marketId(): ?int
    {
        return Auth::user()->market_id;
    }

    private function periodRange(): array
    {
        return match ($this->period) {
            'today' => [Carbon::today(), Carbon::today()->endOfDay()],
            'week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            'quarter' => [Carbon::now()->firstOfQuarter(), Carbon::now()->lastOfQuarter()->endOfDay()],
            'year' => [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()],
            default => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
        };
    }

    #[Computed]
    public function totalRevenue(): string
    {
        [$start, $end] = $this->periodRange();
        $total = Collection::where('market_id', $this->marketId)
            ->where('status', PaymentStatus::Paid)
            ->whereBetween('payment_date', [$start, $end])
            ->sum('amount');
        return '₱ ' . number_format($total, 0);
    }

    #[Computed]
    public function avgDailyCollection(): string
    {
        [$start, $end] = $this->periodRange();
        $total = Collection::where('market_id', $this->marketId)
            ->where('status', PaymentStatus::Paid)
            ->whereBetween('payment_date', [$start, $end])
            ->sum('amount');

        $days = max(1, $start->diffInDaysFiltered(fn (Carbon $date) => ! $date->isSunday(), min($end, Carbon::today())) ?: 1);
        return '₱ ' . number_format(round($total / $days), 0);
    }

    #[Computed]
    public function collectionEfficiency(): string
    {
        [$start, $end] = $this->periodRange();
        $total = Collection::where('market_id', $this->marketId)
            ->whereBetween('payment_date', [$start, $end])
            ->count();
        $paid = Collection::where('market_id', $this->marketId)
            ->where('status', PaymentStatus::Paid)
            ->whereBetween('payment_date', [$start, $end])
            ->count();
        $rate = $total > 0 ? round(($paid / $total) * 100, 1) : 0;
        return $rate . '%';
    }

    #[Computed]
    public function outstandingBalance(): array
    {
        $amount = Collection::where('market_id', $this->marketId)
            ->whereIn('status', [PaymentStatus::Pending, PaymentStatus::Overdue])
            ->sum('amount');
        $vendorCount = Collection::where('market_id', $this->marketId)
            ->whereIn('status', [PaymentStatus::Pending, PaymentStatus::Overdue])
            ->distinct('vendor_id')
            ->count('vendor_id');
        return [
            'amount' => '₱ ' . number_format($amount, 0),
            'vendors' => $vendorCount,
        ];
    }

    #[Computed]
    public function monthlyTrend(): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $amount = Collection::where('market_id', $this->marketId)
                ->where('status', PaymentStatus::Paid)
                ->whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('amount');
            $months[] = [
                'label' => $date->format('M'),
                'amount' => (float) $amount,
            ];
        }
        $max = max(array_column($months, 'amount')) ?: 1;
        foreach ($months as &$m) {
            $m['pct'] = round(($m['amount'] / $max) * 100);
        }
        return $months;
    }

    #[Computed]
    public function sectionBreakdown(): array
    {
        [$start, $end] = $this->periodRange();
        $sections = Collection::where('collections.market_id', $this->marketId)
            ->where('collections.status', PaymentStatus::Paid)
            ->whereBetween('collections.payment_date', [$start, $end])
            ->join('stalls', 'collections.stall_id', '=', 'stalls.id')
            ->selectRaw('stalls.section, SUM(collections.amount) as total')
            ->groupBy('stalls.section')
            ->orderBy('stalls.section')
            ->get();

        $grandTotal = $sections->sum('total') ?: 1;
        $colors = ['A' => 'emerald', 'B' => 'blue', 'C' => 'purple', 'D' => 'amber'];

        return $sections->map(fn ($s) => [
            'name' => 'Section ' . $s->section,
            'amount' => (float) $s->total,
            'percentage' => round(($s->total / $grandTotal) * 100, 1),
            'color' => $colors[$s->section] ?? 'zinc',
        ])->all();
    }

    #[Computed]
    public function topVendors(): \Illuminate\Support\Collection
    {
        [$start, $end] = $this->periodRange();
        return Vendor::where('vendors.market_id', $this->marketId)
            ->join('collections', 'vendors.id', '=', 'collections.vendor_id')
            ->where('collections.status', PaymentStatus::Paid)
            ->whereBetween('collections.payment_date', [$start, $end])
            ->selectRaw('vendors.id, vendors.contact_name, SUM(collections.amount) as total_paid')
            ->groupBy('vendors.id', 'vendors.contact_name')
            ->orderByDesc('total_paid')
            ->limit(5)
            ->get()
            ->map(function ($vendor, $index) {
                $stall = Stall::where('vendor_id', $vendor->id)->first();
                return [
                    'rank' => $index + 1,
                    'name' => $vendor->contact_name,
                    'stall' => $stall?->stall_number ?? '—',
                    'paid' => '₱ ' . number_format($vendor->total_paid, 0),
                ];
            });
    }

    #[Computed]
    public function overduePayments(): \Illuminate\Support\Collection
    {
        return Collection::where('collections.market_id', $this->marketId)
            ->where('collections.status', PaymentStatus::Overdue)
            ->with(['vendor', 'stall'])
            ->orderBy('payment_date')
            ->limit(10)
            ->get()
            ->map(fn ($c) => [
                'name' => $c->vendor?->contact_name ?? '—',
                'stall' => $c->stall?->stall_number ?? '—',
                'amount' => '₱ ' . number_format($c->amount, 0),
                'days' => Carbon::parse($c->payment_date)->diffInDays(today()) . ' days',
            ]);
    }

    public function export()
    {
        [$start, $end] = $this->periodRange();
        $periodLabel = match ($this->period) {
            'today' => 'Today',
            'week' => 'This_Week',
            'month' => 'This_Month',
            'quarter' => 'This_Quarter',
            'year' => 'This_Year',
            default => 'Report',
        };

        return response()->streamDownload(function () use ($start, $end) {
            $handle = fopen('php://output', 'w');

            // Summary
            fputcsv($handle, ['E-MORS Collection Report']);
            fputcsv($handle, ['Generated', now()->format('M j, Y g:i A')]);
            fputcsv($handle, []);

            // Collection details
            fputcsv($handle, ['Receipt #', 'Date', 'Vendor', 'Stall', 'Section', 'Amount', 'Method', 'Collector', 'Status']);

            Collection::where('market_id', $this->marketId)
                ->whereBetween('payment_date', [$start, $end])
                ->with(['vendor', 'stall', 'collector'])
                ->orderBy('payment_date')
                ->chunk(100, function ($collections) use ($handle) {
                    foreach ($collections as $c) {
                        fputcsv($handle, [
                            $c->receipt_number,
                            $c->payment_date->format('Y-m-d'),
                            $c->vendor?->contact_name ?? '',
                            $c->stall?->stall_number ?? '',
                            $c->stall?->section ?? '',
                            $c->amount,
                            ucfirst($c->payment_method),
                            $c->collector?->name ?? '',
                            $c->status->label(),
                        ]);
                    }
                });

            fclose($handle);
        }, "EMORS_Report_{$periodLabel}_" . now()->format('Ymd') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function clearCache(): void
    {
        unset(
            $this->totalRevenue,
            $this->avgDailyCollection,
            $this->collectionEfficiency,
            $this->outstandingBalance,
            $this->monthlyTrend,
            $this->sectionBreakdown,
            $this->topVendors,
            $this->overduePayments,
        );
    }

    public function render()
    {
        return $this->view()->title(__('Reports & Analytics'));
    }
}; ?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Reports & Analytics') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Real-time dashboards and comprehensive reports for data-driven decisions.') }}</flux:subheading>
            </div>
            <div class="flex gap-2">
                <flux:select wire:model.live="period" class="sm:w-40">
                    <flux:select.option value="today">{{ __('Today') }}</flux:select.option>
                    <flux:select.option value="week">{{ __('This Week') }}</flux:select.option>
                    <flux:select.option value="month">{{ __('This Month') }}</flux:select.option>
                    <flux:select.option value="quarter">{{ __('This Quarter') }}</flux:select.option>
                    <flux:select.option value="year">{{ __('This Year') }}</flux:select.option>
                </flux:select>
                <flux:button icon="arrow-down-tray" variant="outline" wire:click="export">
                    {{ __('Export') }}
                </flux:button>
            </div>
        </div>

        {{-- Revenue Summary Cards --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Total Revenue') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">{{ $this->totalRevenue }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Avg Daily Collection') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">{{ $this->avgDailyCollection }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Collection Efficiency') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-emerald-600">{{ $this->collectionEfficiency }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Outstanding Balance') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-red-600">{{ $this->outstandingBalance['amount'] }}</flux:heading>
                <flux:text class="mt-1 text-xs text-zinc-500">{{ $this->outstandingBalance['vendors'] }} {{ __('vendors with balance') }}</flux:text>
            </div>
        </div>

        {{-- Charts Area --}}
        <div class="grid gap-4 lg:grid-cols-2">
            {{-- Revenue Trend --}}
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="border-b border-orange-100 px-6 py-4 dark:border-neutral-700">
                    <flux:heading size="lg">{{ __('Monthly Revenue Trend') }}</flux:heading>
                </div>
                <div class="p-6">
                    @php $trend = $this->monthlyTrend; @endphp
                    @if(count($trend) > 0 && max(array_column($trend, 'amount')) > 0)
                    <div class="flex h-48 items-end gap-2">
                        @foreach($trend as $m)
                        @php $barH = $m['amount'] > 0 ? max(4, (int) round($m['pct'] * 1.68)) : 0; @endphp
                        <div class="flex flex-1 flex-col items-center gap-1">
                            <div class="w-full rounded-t bg-blue-500/80 transition-all hover:bg-blue-500 cursor-pointer" style="height: {{ $barH }}px" title="₱ {{ number_format($m['amount'], 0) }}"></div>
                            <flux:text class="shrink-0 text-xs text-zinc-500">{{ $m['label'] }}</flux:text>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="flex h-48 items-center justify-center text-zinc-400">
                        {{ __('No revenue data for this period.') }}
                    </div>
                    @endif
                </div>
            </div>

            {{-- Collection by Section --}}
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="border-b border-orange-100 px-6 py-4 dark:border-neutral-700">
                    <flux:heading size="lg">{{ __('Collection by Section') }}</flux:heading>
                </div>
                <div class="p-6">
                    @php $sections = $this->sectionBreakdown; @endphp
                    @if(count($sections) > 0)
                    <div class="space-y-4">
                        @foreach($sections as $section)
                        <div>
                            <div class="mb-1 flex items-center justify-between">
                                <flux:text class="text-sm font-medium">{{ $section['name'] }}</flux:text>
                                <flux:text class="text-sm text-zinc-500">₱ {{ number_format($section['amount']) }} ({{ $section['percentage'] }}%)</flux:text>
                            </div>
                            <div class="h-2.5 w-full overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
                                @php
                                    $barColor = match($section['color']) {
                                        'emerald' => 'bg-emerald-500',
                                        'blue' => 'bg-blue-500',
                                        'purple' => 'bg-purple-500',
                                        'amber' => 'bg-amber-500',
                                        default => 'bg-zinc-500',
                                    };
                                @endphp
                                <div class="h-full rounded-full {{ $barColor }}" style="width: {{ $section['percentage'] }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="flex h-32 items-center justify-center text-zinc-400">
                        {{ __('No section data for this period.') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Top Vendors & Overdue --}}
        <div class="grid gap-4 lg:grid-cols-2">
            {{-- Top Performing Vendors --}}
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="border-b border-orange-100 px-6 py-4 dark:border-neutral-700">
                    <flux:heading size="lg">{{ __('Top Performing Vendors') }}</flux:heading>
                </div>
                <div class="divide-y divide-orange-100 dark:divide-zinc-700">
                    @forelse($this->topVendors as $vendor)
                    <div class="flex items-center gap-4 px-6 py-3">
                        <span class="flex size-8 items-center justify-center rounded-full bg-zinc-100 text-sm font-bold text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">{{ $vendor['rank'] }}</span>
                        <div class="flex-1">
                            <flux:text class="font-medium text-zinc-900 dark:text-zinc-100">{{ $vendor['name'] }}</flux:text>
                            <flux:text class="text-xs text-zinc-500">{{ $vendor['stall'] }}</flux:text>
                        </div>
                        <flux:text class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $vendor['paid'] }}</flux:text>
                    </div>
                    @empty
                    <div class="px-6 py-8 text-center">
                        <flux:text class="text-sm text-zinc-500">{{ __('No vendor data for this period.') }}</flux:text>
                    </div>
                    @endforelse
                </div>
            </div>

            {{-- Overdue Payments --}}
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <div class="border-b border-orange-100 px-6 py-4 dark:border-neutral-700">
                    <flux:heading size="lg">{{ __('Overdue Payments') }}</flux:heading>
                </div>
                <div class="divide-y divide-orange-100 dark:divide-zinc-700">
                    @forelse($this->overduePayments as $item)
                    <div class="flex items-center gap-4 px-6 py-3">
                        <flux:icon.exclamation-triangle class="size-5 text-red-500" />
                        <div class="flex-1">
                            <flux:text class="font-medium text-zinc-900 dark:text-zinc-100">{{ $item['name'] }}</flux:text>
                            <flux:text class="text-xs text-zinc-500">{{ $item['stall'] }} · {{ __('Overdue by :days', ['days' => $item['days']]) }}</flux:text>
                        </div>
                        <flux:text class="font-semibold text-red-600 dark:text-red-400">{{ $item['amount'] }}</flux:text>
                    </div>
                    @empty
                    <div class="px-6 py-8 text-center">
                        <flux:icon.check-circle class="mx-auto size-8 text-emerald-500" />
                        <flux:text class="mt-2 text-sm text-zinc-500">{{ __('No overdue payments') }}</flux:text>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
