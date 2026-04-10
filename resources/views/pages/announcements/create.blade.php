<?php

use App\Enums\AnnouncementCategory;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

new class extends Component {
    public string $formTitle = '';
    public string $formBody = '';
    public string $formCategory = 'general';
    public bool $formPublished = true;

    public function save()
    {
        $this->validate([
            'formTitle' => ['required', 'string', 'max:255'],
            'formBody' => ['required', 'string', 'max:5000'],
            'formCategory' => ['required', Rule::in(array_column(AnnouncementCategory::cases(), 'value'))],
        ]);

        Announcement::create([
            'market_id' => Auth::user()->market_id,
            'author_id' => Auth::id(),
            'title' => $this->formTitle,
            'body' => $this->formBody,
            'category' => $this->formCategory,
            'published_at' => $this->formPublished ? now() : null,
        ]);

        $this->dispatch('toast', message: 'Announcement created successfully.', type: 'success');

        return redirect()->route('announcements.index');
    }

    public function render()
    {
        return $this->view()->title(__('Create Announcement'));
    }
}; ?>

<div>
    <div class="mx-auto flex h-full w-full max-w-3xl flex-1 flex-col gap-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Create Announcement') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Write and publish a market-wide notice for your vendors.') }}</flux:subheading>
            </div>
            <flux:button variant="ghost" icon="arrow-left" :href="route('announcements.index')" wire:navigate>
                {{ __('Back to List') }}
            </flux:button>
        </div>

        <div class="rounded-2xl border border-orange-100 bg-white/80 p-6 shadow-sm backdrop-blur-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <form wire:submit="save" class="space-y-5">
                <flux:input wire:model="formTitle" :label="__('Title')" required />

                <flux:select wire:model="formCategory" :label="__('Category')">
                    @foreach(AnnouncementCategory::cases() as $cat)
                    <flux:select.option :value="$cat->value">{{ $cat->label() }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:textarea wire:model="formBody" :label="__('Message')" rows="7" required />

                <flux:checkbox wire:model="formPublished" :label="__('Publish immediately')" />

                <div class="flex justify-end gap-3 pt-2">
                    <flux:button variant="ghost" :href="route('announcements.index')" wire:navigate>
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button variant="primary" type="submit">
                        {{ __('Create Announcement') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
</div>
