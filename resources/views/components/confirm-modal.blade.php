{{--
    Global Confirm Modal Component
    Usage from Alpine/Livewire views:
        x-on:click="$dispatch('open-confirm', {
            title: 'Delete Item',
            message: 'This action cannot be undone.',
            confirm: 'Delete',
            variant: 'danger',
            onConfirm: () => $wire.deleteItem(id)
        })"
    Variants: danger | primary | warning
--}}
<div
    x-data="{
        show: false,
        title: '',
        message: '',
        confirmLabel: 'Confirm',
        variant: 'danger',
        onConfirm: null,
        open(data) {
            this.title       = data.title       ?? 'Are you sure?';
            this.message     = data.message     ?? 'This action cannot be undone.';
            this.confirmLabel= data.confirm     ?? 'Confirm';
            this.variant     = data.variant     ?? 'danger';
            this.onConfirm   = data.onConfirm   ?? null;
            this.show = true;
        },
        confirm() {
            if (this.onConfirm) this.onConfirm();
            this.show = false;
        },
        cancel() {
            this.show = false;
        }
    }"
    x-on:open-confirm.window="open($event.detail)"
    x-on:keydown.escape.window="cancel()"
>
    {{-- Backdrop --}}
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[9990] bg-black/40 backdrop-blur-sm"
        x-on:click="cancel()"
        style="display:none"
        aria-hidden="true"
    ></div>

    {{-- Dialog --}}
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 z-[9991] flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="'confirm-title'"
        style="display:none"
    >
        <div class="w-full max-w-sm rounded-2xl border border-orange-100 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900">
            <div class="p-6 space-y-4">
                {{-- Icon + Title --}}
                <div class="flex items-start gap-4">
                    <div
                        class="shrink-0 flex items-center justify-center size-10 rounded-full"
                        :class="{
                            'bg-red-100 dark:bg-red-900/30': variant === 'danger',
                            'bg-amber-100 dark:bg-amber-900/30': variant === 'warning',
                            'bg-blue-100 dark:bg-blue-900/30': variant === 'primary' || variant === 'info',
                        }"
                    >
                        <template x-if="variant === 'danger'">
                            <svg class="size-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </template>
                        <template x-if="variant === 'warning'">
                            <svg class="size-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </template>
                        <template x-if="variant === 'primary' || variant === 'info'">
                            <svg class="size-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 id="confirm-title" class="text-base font-semibold text-zinc-900 dark:text-zinc-100" x-text="title"></h3>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400" x-text="message"></p>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex justify-end gap-3 pt-2">
                    <button
                        type="button"
                        x-on:click="cancel()"
                        class="inline-flex items-center justify-center rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-700 shadow-sm hover:bg-zinc-50 transition-colors dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700"
                    >
                        {{ __('Cancel') }}
                    </button>
                    <button
                        type="button"
                        x-on:click="confirm()"
                        class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2"
                        :class="{
                            'bg-red-600 hover:bg-red-700 focus:ring-red-500': variant === 'danger',
                            'bg-amber-500 hover:bg-amber-600 focus:ring-amber-400': variant === 'warning',
                            'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500': variant === 'primary' || variant === 'info',
                        }"
                        x-text="confirmLabel"
                    ></button>
                </div>
            </div>
        </div>
    </div>
</div>
