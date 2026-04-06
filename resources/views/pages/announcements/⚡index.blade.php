<?php

use App\Enums\AnnouncementCategory;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $categoryFilter = 'all';

    // Create/Edit form
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $formTitle = '';
    public string $formBody = '';
    public string $formCategory = 'general';
    public bool $formPublished = true;



    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function marketId(): ?int
    {
        return Auth::user()->market_id;
    }

    #[Computed]
    public function announcements()
    {
        return Announcement::where('market_id', $this->marketId)
            ->with('author')
            ->when($this->search, fn ($q) => $q->where('title', 'like', '%' . $this->search . '%'))
            ->when($this->categoryFilter !== 'all', fn ($q) => $q->where('category', $this->categoryFilter))
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    #[Computed]
    public function totalCount(): int
    {
        return Announcement::where('market_id', $this->marketId)->count();
    }

    #[Computed]
    public function publishedCount(): int
    {
        return Announcement::where('market_id', $this->marketId)->whereNotNull('published_at')->count();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $announcement = Announcement::where('market_id', $this->marketId)->findOrFail($id);
        $this->editingId = $announcement->id;
        $this->formTitle = $announcement->title;
        $this->formBody = $announcement->body;
        $this->formCategory = $announcement->category->value;
        $this->formPublished = $announcement->published_at !== null;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'formTitle' => ['required', 'string', 'max:255'],
            'formBody' => ['required', 'string', 'max:5000'],
            'formCategory' => ['required', Rule::in(array_column(AnnouncementCategory::cases(), 'value'))],
        ]);

        $data = [
            'market_id' => $this->marketId,
            'author_id' => Auth::id(),
            'title' => $this->formTitle,
            'body' => $this->formBody,
            'category' => $this->formCategory,
            'published_at' => $this->formPublished ? now() : null,
        ];

        if ($this->editingId) {
            $announcement = Announcement::where('market_id', $this->marketId)->findOrFail($this->editingId);
            // Keep original published_at if already published and still published
            if ($announcement->published_at && $this->formPublished) {
                unset($data['published_at']);
            }
            $announcement->update($data);
            $this->dispatch('toast', message: 'Announcement updated successfully.', type: 'success');
        } else {
            Announcement::create($data);
            $this->dispatch('toast', message: 'Announcement created successfully.', type: 'success');
        }

        $this->showModal = false;
        $this->resetForm();
        $this->clearCache();
    }

    public function deleteAnnouncement(int $id): void
    {
        Announcement::where('market_id', $this->marketId)->findOrFail($id)->delete();
        $this->dispatch('toast', message: 'Announcement deleted successfully.', type: 'success');
        $this->clearCache();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->formTitle = '';
        $this->formBody = '';
        $this->formCategory = 'general';
        $this->formPublished = true;
        $this->resetValidation();
    }

    private function clearCache(): void
    {
        unset($this->announcements, $this->totalCount, $this->publishedCount);
    }

    public function render()
    {
        return $this->view()->title(__('Announcements'));
    }
}; ?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Flash Messages --}}
        @if(session('message'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                {{ session('message') }}
            </div>
        @endif

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Announcements') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Create and manage market-wide announcements for vendors.') }}</flux:subheading>
            </div>
            <flux:button icon="plus" variant="primary" wire:click="openCreateModal">
                {{ __('New Announcement') }}
            </flux:button>
        </div>

        {{-- Stats Row --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Total Announcements') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">{{ $this->totalCount }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Published') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-emerald-600">{{ $this->publishedCount }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Drafts') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-amber-600">{{ $this->totalCount - $this->publishedCount }}</flux:heading>
            </div>
        </div>

        {{-- Search & Filter --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Search announcements...') }}" />
            </div>
            <flux:select wire:model.live="categoryFilter" class="sm:w-44">
                <flux:select.option value="all">{{ __('All Categories') }}</flux:select.option>
                @foreach(AnnouncementCategory::cases() as $cat)
                <flux:select.option :value="$cat->value">{{ $cat->label() }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        {{-- Table --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-orange-100 text-left dark:border-zinc-700">
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Title') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Category') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Author') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Published') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                        @forelse($this->announcements as $announcement)
                        <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50" wire:key="ann-{{ $announcement->id }}">
                            <td class="px-6 py-3">
                                <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ Str::limit($announcement->title, 50) }}</span>
                            </td>
                            <td class="px-6 py-3">
                                <flux:badge :color="$announcement->category->color()" size="sm">{{ $announcement->category->label() }}</flux:badge>
                            </td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $announcement->author?->name ?? '—' }}</td>
                            <td class="px-6 py-3">
                                @if($announcement->published_at)
                                <flux:badge color="lime" size="sm">{{ $announcement->published_at->format('M j, Y') }}</flux:badge>
                                @else
                                <flux:badge color="zinc" size="sm">Draft</flux:badge>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item icon="pencil-square" wire:click="openEditModal({{ $announcement->id }})">{{ __('Edit') }}</flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger" x-on:click="$dispatch('open-confirm', { title: 'Delete Announcement', message: 'Are you sure you want to delete this announcement?', confirm: 'Delete', variant: 'danger', onConfirm: () => $wire.deleteAnnouncement({{ $announcement->id }}) })">{{ __('Delete') }}</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-zinc-500">
                                {{ __('No announcements found.') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-orange-100 px-6 py-3 dark:border-neutral-700">
                {{ $this->announcements->links() }}
            </div>
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? __('Edit Announcement') : __('New Announcement') }}</flux:heading>
                <flux:subheading>{{ $editingId ? __('Update announcement details.') : __('Create a new announcement for vendors.') }}</flux:subheading>
            </div>

            <form wire:submit="save" class="space-y-4">
                <flux:input wire:model="formTitle" :label="__('Title')" required />

                <flux:select wire:model="formCategory" :label="__('Category')">
                    @foreach(AnnouncementCategory::cases() as $cat)
                    <flux:select.option :value="$cat->value">{{ $cat->label() }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:textarea wire:model="formBody" :label="__('Message')" rows="5" required />

                <div class="flex items-center gap-2">
                    <flux:checkbox wire:model="formPublished" :label="__('Publish immediately')" />
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <flux:button variant="ghost" wire:click="$set('showModal', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="primary" type="submit">{{ $editingId ? __('Update') : __('Create') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>


</div>
