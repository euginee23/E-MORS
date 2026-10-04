<?php

/**
 * One stall in depth: its details, every vendor who has held it, and the rent
 * collected against it. Tabs live in the query string so each view can be linked.
 */

use App\Enums\DueStatus;
use App\Enums\PaymentStatus;
use App\Models\Collection;
use App\Models\Stall;
use App\Support\StallDue;
use App\Support\StallDues;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public const TABS = [
        'details' => 'Details',
        'renters' => 'Renters History',
        'collections' => 'Collection History',
    ];

    public Stall $stall;

    #[Url]
    public string $tab = 'details';

    public function mount(Stall $stall): void
    {
        abort_unless($stall->market_id === Auth::user()->market_id, 404);

        $this->stall = $stall->load(['vendor', 'currentAssignment']);

        if (! array_key_exists($this->tab, self::TABS)) {
            $this->tab = 'details';
        }
    }

    public function setTab(string $tab): void
    {
        $this->tab = array_key_exists($tab, self::TABS) ? $tab : 'details';
    }

    /** Stalls sharing this one's section, for the snapshot map. */
    #[Computed]
    public function sectionStalls()
    {
        return Stall::where('market_id', $this->stall->market_id)
            ->where('section', $this->stall->section)
            ->orderBy('stall_number')
            ->get(['id', 'stall_number', 'status']);
    }

    #[Computed]
    public function assignments()
    {
        return $this->stall->assignments()
            ->with('vendor')
            ->orderByRaw('end_date IS NULL DESC')
            ->orderByDesc('start_date')
            ->get();
    }

    #[Computed]
    public function renterStats(): array
    {
        $assignments = $this->assignments;
        $longest = $assignments->max(fn ($a) => $a->durationInMonths()) ?? 0;

        return [
            'total' => $assignments->pluck('vendor_id')->filter()->unique()->count(),
            'longest' => $longest,
        ];
    }

    #[Computed]
    public function collections()
    {
        return Collection::where('stall_id', $this->stall->id)
            ->with(['collector', 'vendor'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate(10, pageName: 'collections');
    }

    /** Paid totals per month for this stall, keyed 'Y-m'. */
    #[Computed]
    public function paidByMonth(): array
    {
        return StallDues::paidByMonth([$this->stall->id])[$this->stall->id] ?? [];
    }

    #[Computed]
    public function collectionStats(): array
    {
        $now = Carbon::now();
        $rate = (float) $this->stall->monthly_rate;

        $paidThisYear = collect($this->paidByMonth)
            ->filter(fn ($bucket, $month) => str_starts_with($month, $now->format('Y')));

        return [
            'year' => (float) $paidThisYear->sum('paid'),
            'month' => (float) ($this->paidByMonth[$now->format('Y-m')]['paid'] ?? 0),
            'partial' => (float) $paidThisYear
                ->filter(fn ($bucket) => StallDues::statusOf($bucket['paid'], $rate) === DueStatus::Partial)
                ->sum('paid'),
            'last' => Collection::where('stall_id', $this->stall->id)
                ->where('status', PaymentStatus::Paid)
                ->max('payment_date'),
        ];
    }

    /** Months of the current tenancy that aren't fully paid yet. */
    #[Computed]
    public function outstanding()
    {
        return StallDues::forStalls([$this->stall])
            ->reject(fn (StallDue $due) => $due->isSettled())
            ->values();
    }

    public function render()
    {
        return $this->view()->title(__('Stall :no', ['no' => $this->stall->stall_number]));
    }
}; ?>

@php
    $stall = $this->stall;
    $current = $stall->currentAssignment;
    $card = 'rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80';
    $tileColor = fn ($status) => match ($status->value) {
        'occupied' => 'bg-emerald-500',
        'available' => 'bg-blue-500',
        'maintenance' => 'bg-amber-500',
        default => 'bg-zinc-400',
    };
@endphp

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Breadcrumb & header --}}
        <div>
            <nav class="flex items-center gap-1.5 text-sm text-zinc-500 dark:text-zinc-400">
                <a href="{{ route('stalls.index') }}" wire:navigate class="hover:text-orange-600 dark:hover:text-orange-400">{{ __('Stalls') }}</a>
                <span>/</span>
                <button type="button" wire:click="setTab('details')" class="hover:text-orange-600 dark:hover:text-orange-400">{{ $stall->stall_number }}</button>
                @if($tab !== 'details')
                <span>/</span>
                <span class="text-zinc-700 dark:text-zinc-300">{{ __($this::TABS[$tab]) }}</span>
                @endif
            </nav>
            @php
                $titles = [
                    'details' => [__('Stall Details'), __(':no in Section :s', ['no' => $stall->stall_number, 's' => $stall->section])],
                    'renters' => [__('Stall Renters History'), __('Past and current vendors assigned to stall :no.', ['no' => $stall->stall_number])],
                    'collections' => [__('Stall Collection History'), $stall->vendor
                        ? __('Rent payments recorded for stall :no, :vendor.', ['no' => $stall->stall_number, 'vendor' => $stall->vendor->contact_name])
                        : __('Rent payments recorded for stall :no.', ['no' => $stall->stall_number])],
                ];
            @endphp
            <flux:heading size="xl" class="mt-2">{{ $titles[$tab][0] }}</flux:heading>
            <flux:subheading class="mt-1">{{ $titles[$tab][1] }}</flux:subheading>
        </div>

        <div class="flex flex-col gap-3 rounded-2xl border border-orange-100 bg-white/80 px-5 py-4 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <flux:heading size="lg">{{ $stall->stall_number }}</flux:heading>
                <flux:badge :color="$stall->status->color()" size="sm">{{ $stall->status->label() }}</flux:badge>
            </div>
            <div class="flex gap-2">
                <flux:button size="sm" variant="outline" icon="pencil-square" :href="route('stalls.index', ['edit' => $stall->id])" wire:navigate>{{ __('Edit Stall') }}</flux:button>
                @unless($stall->vendor_id)
                <flux:button size="sm" variant="primary" icon="user-plus" :href="route('stalls.index', ['edit' => $stall->id])" wire:navigate>{{ __('Assign Vendor') }}</flux:button>
                @endunless
            </div>
        </div>

        {{-- Tab-specific headline stats --}}
        @if($tab === 'details')
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="{{ $card }}">
                <flux:text class="text-sm text-zinc-500">{{ __('Section') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">{{ $stall->section }}</flux:heading>
            </div>
            <div class="{{ $card }}">
                <flux:text class="text-sm text-zinc-500">{{ __('Size') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">{{ $stall->size }}</flux:heading>
            </div>
            <div class="{{ $card }}">
                <flux:text class="text-sm text-zinc-500">{{ __('Monthly Rate') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">₱{{ number_format($stall->monthly_rate, 0) }}</flux:heading>
            </div>
            <div class="{{ $card }}">
                <flux:text class="text-sm text-zinc-500">{{ __('Stall Expiry') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">{{ $stall->rent_expiry?->format('M j, Y') ?? '—' }}</flux:heading>
            </div>
        </div>
        @elseif($tab === 'renters')
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="{{ $card }}">
                <flux:text class="text-sm text-zinc-500">{{ __('Total Renters') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">{{ $this->renterStats['total'] }}</flux:heading>
            </div>
            <div class="{{ $card }}">
                <flux:text class="text-sm text-zinc-500">{{ __('Current Renter') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">{{ $stall->vendor?->contact_name ?? '—' }}</flux:heading>
            </div>
            <div class="{{ $card }}">
                <flux:text class="text-sm text-zinc-500">{{ __('Longest Stay') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">{{ $this->renterStats['longest'] ? trans_choice(':count month|:count months', $this->renterStats['longest']) : '—' }}</flux:heading>
            </div>
        </div>
        @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="{{ $card }}">
                <flux:text class="text-sm text-zinc-500">{{ __('Collected This Year') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-emerald-600">₱{{ number_format($this->collectionStats['year'], 0) }}</flux:heading>
            </div>
            <div class="{{ $card }}">
                <flux:text class="text-sm text-zinc-500">{{ __('This Month') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">₱{{ number_format($this->collectionStats['month'], 0) }}</flux:heading>
            </div>
            <div class="{{ $card }}">
                <flux:text class="text-sm text-zinc-500">{{ __('Partial') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-orange-600">₱{{ number_format($this->collectionStats['partial'], 0) }}</flux:heading>
            </div>
            <div class="{{ $card }}">
                <flux:text class="text-sm text-zinc-500">{{ __('Last Payment') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">{{ $this->collectionStats['last'] ? \Illuminate\Support\Carbon::parse($this->collectionStats['last'])->format('M j, Y') : '—' }}</flux:heading>
            </div>
        </div>
        @endif

        {{-- Tabbed panel --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="flex gap-6 border-b border-orange-100 px-6 dark:border-zinc-700">
                @foreach($this::TABS as $key => $label)
                <button type="button" wire:click="setTab('{{ $key }}')" wire:key="tab-{{ $key }}"
                    @class([
                        '-mb-px border-b-2 py-3 text-sm font-medium transition',
                        'border-orange-500 text-zinc-900 dark:text-zinc-100' => $tab === $key,
                        'border-transparent text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200' => $tab !== $key,
                    ])>
                    {{ __($label) }}
                </button>
                @endforeach
            </div>

            <div class="p-6">
                @if($tab === 'details')
                <div class="grid gap-6 lg:grid-cols-2">
                    {{-- Current renter --}}
                    <div class="rounded-xl border border-zinc-100 p-4 dark:border-zinc-700">
                        <flux:heading size="sm">{{ __('Current Renter') }}</flux:heading>
                        @if($stall->vendor)
                        <div class="mt-3 flex items-center gap-3">
                            <flux:avatar size="sm" :name="$stall->vendor->contact_name" />
                            <div class="min-w-0 flex-1">
                                <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $stall->vendor->contact_name }}</div>
                                <div class="text-xs text-zinc-500">{{ $stall->vendor->business_name ?: '—' }}</div>
                            </div>
                            <flux:badge :color="$stall->rental_status->color()" size="sm">{{ $stall->rental_status->label() }}</flux:badge>
                        </div>
                        <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <dt class="text-xs text-zinc-500">{{ __('Assigned') }}</dt>
                                <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ ($current?->start_date ?? $stall->rent_start)?->format('M j, Y') ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-zinc-500">{{ __('Permit Expiry') }}</dt>
                                <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $stall->vendor->permit_expiry?->format('M j, Y') ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-zinc-500">{{ __('Contact') }}</dt>
                                <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $stall->vendor->contact_phone ?: '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-zinc-500">{{ __('Rent Expiry') }}</dt>
                                <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $stall->rent_expiry?->format('M j, Y') ?? '—' }}</dd>
                            </div>
                        </dl>
                        @else
                        <flux:text class="mt-3 text-sm text-zinc-400">{{ __('No vendor is assigned to this stall.') }}</flux:text>
                        @endif
                    </div>

                    {{-- Section snapshot --}}
                    <div class="rounded-xl border border-zinc-100 p-4 dark:border-zinc-700">
                        <flux:heading size="sm">{{ __('Stall Snapshot') }}</flux:heading>
                        <div class="mt-3 grid gap-1.5" style="grid-template-columns: repeat(auto-fill, minmax(2.5rem, 1fr))">
                            @foreach($this->sectionStalls as $neighbour)
                            <a href="{{ route('stalls.show', $neighbour->id) }}" wire:navigate wire:key="snap-{{ $neighbour->id }}" title="{{ $neighbour->stall_number }} — {{ $neighbour->status->label() }}"
                                @class([
                                    'flex aspect-square items-center justify-center rounded text-[10px] font-medium text-white',
                                    $tileColor($neighbour->status),
                                    'ring-2 ring-offset-2 ring-orange-500 dark:ring-offset-zinc-900' => $neighbour->id === $stall->id,
                                    'opacity-40 hover:opacity-80' => $neighbour->id !== $stall->id,
                                ])>
                                {{ str_replace($stall->section . '-', '', $neighbour->stall_number) }}
                            </a>
                            @endforeach
                        </div>
                        <div class="mt-3 flex flex-wrap items-center gap-4 text-xs text-zinc-500">
                            <span class="flex items-center gap-1.5"><span class="inline-block size-3 rounded-sm bg-emerald-500"></span> {{ __('Occupied') }}</span>
                            <span class="flex items-center gap-1.5"><span class="inline-block size-3 rounded-sm bg-blue-500"></span> {{ __('Available') }}</span>
                            <span class="flex items-center gap-1.5"><span class="inline-block size-3 rounded-sm bg-amber-500"></span> {{ __('Maintenance') }}</span>
                        </div>
                        <flux:text class="mt-2 text-xs">{{ __('Located in Section :s, stall number :n.', ['s' => $stall->section, 'n' => str_replace($stall->section . '-', '', $stall->stall_number)]) }}</flux:text>
                    </div>
                </div>

                <dl class="mt-6 grid max-w-md grid-cols-2 gap-x-6 gap-y-2 text-sm">
                    <dt class="text-zinc-500">{{ __('Stall No.') }}</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $stall->stall_number }}</dd>
                    <dt class="text-zinc-500">{{ __('Section') }}</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ __('Section :s', ['s' => $stall->section]) }}</dd>
                    <dt class="text-zinc-500">{{ __('Size') }}</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $stall->size }}</dd>
                    <dt class="text-zinc-500">{{ __('Status') }}</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $stall->status->label() }}</dd>
                    <dt class="text-zinc-500">{{ __('Monthly Rate') }}</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">₱{{ number_format($stall->monthly_rate, 2) }}</dd>
                    <dt class="text-zinc-500">{{ __('Current Vendor') }}</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ $stall->vendor?->contact_name ?? '—' }}</dd>
                </dl>

                @elseif($tab === 'renters')
                <flux:heading size="sm">{{ __('Assignment Timeline') }}</flux:heading>
                <div class="mt-3 grid gap-6 lg:grid-cols-[1fr_12rem]">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-zinc-100 text-left dark:border-zinc-700">
                                    <th class="py-2 pr-4 font-medium text-zinc-500">{{ __('Vendor') }}</th>
                                    <th class="py-2 pr-4 font-medium text-zinc-500">{{ __('Business') }}</th>
                                    <th class="py-2 pr-4 font-medium text-zinc-500">{{ __('Start') }}</th>
                                    <th class="py-2 pr-4 font-medium text-zinc-500">{{ __('End') }}</th>
                                    <th class="py-2 pr-4 font-medium text-zinc-500">{{ __('Monthly Rate') }}</th>
                                    <th class="py-2 font-medium text-zinc-500">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @forelse($this->assignments as $assignment)
                                <tr wire:key="assignment-{{ $assignment->id }}">
                                    <td class="py-3 pr-4 font-medium text-zinc-900 dark:text-zinc-100">{{ $assignment->vendor?->contact_name ?? __('Removed vendor') }}</td>
                                    <td class="py-3 pr-4 text-zinc-600 dark:text-zinc-300">{{ $assignment->vendor?->business_name ?: '—' }}</td>
                                    <td class="py-3 pr-4 text-zinc-600 dark:text-zinc-300">{{ $assignment->start_date?->format('M j, Y') ?? '—' }}</td>
                                    <td class="py-3 pr-4 text-zinc-600 dark:text-zinc-300">{{ $assignment->end_date?->format('M j, Y') ?? __('Present') }}</td>
                                    <td class="py-3 pr-4 text-zinc-600 dark:text-zinc-300">₱{{ number_format($assignment->monthly_rate, 0) }}</td>
                                    <td class="py-3">
                                        <flux:badge :color="$assignment->isCurrent() ? 'lime' : 'zinc'" size="sm">{{ $assignment->isCurrent() ? __('Current') : __('Ended') }}</flux:badge>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-sm text-zinc-400">{{ __('No vendor has been assigned to this stall yet.') }}</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($this->assignments->isNotEmpty())
                    <ol class="relative ml-2 border-l border-zinc-200 dark:border-zinc-700">
                        @foreach($this->assignments as $assignment)
                        <li class="mb-6 ml-4 last:mb-0" wire:key="timeline-{{ $assignment->id }}">
                            <span @class(['absolute -left-1.5 mt-1 size-3 rounded-full border-2 border-white dark:border-zinc-900', 'bg-orange-500' => $assignment->isCurrent(), 'bg-zinc-400' => ! $assignment->isCurrent()])></span>
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                {{ $assignment->isCurrent() ? __('Current') : ($assignment->start_date?->format('Y') . '–' . $assignment->end_date?->format('Y')) }}
                            </p>
                            <p class="text-xs text-zinc-500">{{ $assignment->vendor?->contact_name ?? '—' }}</p>
                        </li>
                        @endforeach
                    </ol>
                    @endif
                </div>

                @else
                <div class="grid gap-6 lg:grid-cols-[1fr_15rem]">
                    <div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-zinc-100 text-left dark:border-zinc-700">
                                        <th class="py-2 pr-4 font-medium text-zinc-500">{{ __('Date') }}</th>
                                        <th class="py-2 pr-4 font-medium text-zinc-500">{{ __('OR No.') }}</th>
                                        <th class="py-2 pr-4 font-medium text-zinc-500">{{ __('Period') }}</th>
                                        <th class="py-2 pr-4 font-medium text-zinc-500">{{ __('Collector') }}</th>
                                        <th class="py-2 pr-4 text-right font-medium text-zinc-500">{{ __('Amount') }}</th>
                                        <th class="py-2 pl-4 font-medium text-zinc-500">{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                    @forelse($this->collections as $collection)
                                    @php
                                        $monthPaid = $this->paidByMonth[$collection->payment_date->format('Y-m')]['paid'] ?? 0;
                                        $due = $collection->status === \App\Enums\PaymentStatus::Paid
                                            ? \App\Support\StallDues::statusOf($monthPaid, (float) $stall->monthly_rate)
                                            : null;
                                    @endphp
                                    <tr wire:key="stall-collection-{{ $collection->id }}">
                                        <td class="py-3 pr-4 text-zinc-700 dark:text-zinc-300">{{ $collection->payment_date->format('M j, Y') }}</td>
                                        <td class="py-3 pr-4 font-mono text-xs text-zinc-900 dark:text-zinc-100">{{ $collection->receipt_number }}</td>
                                        <td class="py-3 pr-4 text-zinc-700 dark:text-zinc-300">{{ $collection->payment_date->format('F Y') }}</td>
                                        <td class="py-3 pr-4 text-zinc-700 dark:text-zinc-300">{{ $collection->collector?->name ?? '—' }}</td>
                                        <td class="py-3 pr-4 text-right font-medium text-zinc-900 dark:text-zinc-100">₱{{ number_format($collection->amount, 0) }}</td>
                                        <td class="py-3 pl-4">
                                            @if($due)
                                            <flux:badge :color="$due->color()" size="sm">{{ $due === \App\Enums\DueStatus::Partial ? $due->label() : __('Paid') }}</flux:badge>
                                            @else
                                            <flux:badge :color="$collection->status->color()" size="sm">{{ $collection->status->label() }}</flux:badge>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="py-10 text-center text-sm text-zinc-400">{{ __('No payments recorded for this stall.') }}</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $this->collections->links() }}</div>
                    </div>

                    <div class="h-fit rounded-xl border border-zinc-100 p-4 dark:border-zinc-700">
                        <flux:text class="text-sm text-zinc-500">{{ __('Monthly rate') }}</flux:text>
                        <flux:heading size="xl" class="mt-1 text-2xl font-bold">₱{{ number_format($stall->monthly_rate, 0) }}</flux:heading>
                        <div class="mt-3 space-y-1.5">
                            @if($this->outstanding->isNotEmpty())
                            <p class="text-sm font-semibold text-red-600 dark:text-red-400">
                                {{ __('₱:amount outstanding', ['amount' => number_format($this->outstanding->sum(fn ($due) => $due->balance()), 0)]) }}
                            </p>
                            @foreach($this->outstanding->take(3) as $due)
                            <p class="text-sm text-zinc-700 dark:text-zinc-300" wire:key="outstanding-{{ $due->month->format('Y-m') }}">
                                {{ __('Balance due ₱:amount for :month', ['amount' => number_format($due->balance(), 0), 'month' => $due->month->format('F Y')]) }}
                            </p>
                            @endforeach
                            @if($this->outstanding->count() > 3)
                            <p class="text-xs text-zinc-500">{{ trans_choice('+ :count earlier month unpaid|+ :count earlier months unpaid', $this->outstanding->count() - 3) }}</p>
                            @endif
                            @else
                            <p class="text-sm text-emerald-600">{{ $stall->vendor_id ? __('All months are paid up.') : __('No active tenancy.') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
