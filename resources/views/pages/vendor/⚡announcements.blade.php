<?php

use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {

    #[Computed]
    public function marketId(): ?int
    {
        return Auth::user()->market_id;
    }

    #[Computed]
    public function announcements()
    {
        return Announcement::where('market_id', $this->marketId)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->with('author')
            ->orderByDesc('published_at')
            ->get();
    }

    #[Computed]
    public function unreadCount(): int
    {
        $readIds = Auth::user()->readAnnouncements()->pluck('announcement_id');
        return $this->announcements->whereNotIn('id', $readIds)->count();
    }

    public function markAsRead(int $announcementId): void
    {
        $announcement = Announcement::where('market_id', $this->marketId)->findOrFail($announcementId);

        $announcement->readers()->syncWithoutDetaching([
            Auth::id() => ['read_at' => now()],
        ]);

        $announcement->readers()->updateExistingPivot(Auth::id(), ['read_at' => now()]);
        unset($this->announcements, $this->unreadCount);
    }

    public function render()
    {
        return $this->view()->title(__('Announcements'));
    }
}; ?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Page Header --}}
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Announcements') }}</flux:heading>
                <flux:subheading>{{ __('Notices and updates from the market administration.') }}</flux:subheading>
            </div>
            @if($this->unreadCount > 0)
            <flux:badge color="amber" size="lg" icon="bell">{{ $this->unreadCount }} unread</flux:badge>
            @endif
        </div>

        {{-- Announcements List --}}
        <div class="space-y-4">
            @forelse($this->announcements as $announcement)
            @php
                $isUnread = ! $announcement->isReadBy(auth()->user());
            @endphp
            <div
                class="rounded-2xl border {{ $isUnread ? 'border-orange-200 dark:border-orange-900/50' : 'border-orange-100 dark:border-zinc-700' }} bg-white/80 backdrop-blur-sm shadow-sm dark:bg-zinc-900/80 transition-colors"
                wire:key="ann-{{ $announcement->id }}"
                @if($isUnread) wire:click="markAsRead({{ $announcement->id }})" class="cursor-pointer" @endif
            >
                <div class="p-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex items-start gap-3">
                            @if($isUnread)
                            <div class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-orange-500 animate-pulse"></div>
                            @else
                            <div class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-zinc-300 dark:bg-zinc-600"></div>
                            @endif
                            <div>
                                <h3 class="font-semibold {{ $isUnread ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-700 dark:text-zinc-300' }}">
                                    {{ $announcement->title }}
                                </h3>
                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    <flux:badge :color="$announcement->category->color()" size="sm">{{ $announcement->category->label() }}</flux:badge>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $announcement->published_at->format('M j, Y') }}</span>
                                    <span class="text-xs text-zinc-400 dark:text-zinc-500">·</span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">By {{ $announcement->author?->name ?? 'Admin' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 ml-6 text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">
                        {{ $announcement->body }}
                    </div>
                </div>
            </div>
            @empty
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-12 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80 text-center">
                <flux:icon.bell-slash class="size-12 text-zinc-300 dark:text-zinc-600 mx-auto" />
                <flux:heading size="lg" class="mt-4">{{ __('No Announcements') }}</flux:heading>
                <flux:subheading class="mt-2">{{ __('There are no announcements from the market administration at this time.') }}</flux:subheading>
            </div>
            @endforelse
        </div>
    </div>
</div>
