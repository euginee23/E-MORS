{{--
    Global Toast Notification Component
    Usage from Livewire:  $this->dispatch('toast', message: 'Saved!', type: 'success')
    Usage from Alpine/JS: $dispatch('toast', { message: 'Done!', type: 'success' })
    Types: success | error | warning | info
--}}
<div
    x-data="{
        toasts: [],
        add(message, type = 'success') {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, message, type });
            setTimeout(() => this.remove(id), 4500);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }"
    x-on:toast.window="add($event.detail.message ?? $event.detail[0], $event.detail.type ?? $event.detail[1] ?? 'success')"
    class="fixed bottom-5 right-5 z-[9999] flex flex-col gap-3 items-end pointer-events-none"
    aria-live="polite"
    aria-atomic="false"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="true"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
            class="pointer-events-auto flex items-start gap-3 rounded-xl border px-4 py-3 shadow-lg backdrop-blur-sm max-w-sm w-full"
            :class="{
                'bg-white border-emerald-200 dark:bg-zinc-900 dark:border-emerald-800': toast.type === 'success',
                'bg-white border-red-200 dark:bg-zinc-900 dark:border-red-800': toast.type === 'error',
                'bg-white border-amber-200 dark:bg-zinc-900 dark:border-amber-800': toast.type === 'warning',
                'bg-white border-blue-200 dark:bg-zinc-900 dark:border-blue-800': toast.type === 'info',
            }"
        >
            {{-- Icon --}}
            <div class="shrink-0 mt-0.5">
                <template x-if="toast.type === 'success'">
                    <svg class="size-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
                <template x-if="toast.type === 'error'">
                    <svg class="size-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
                <template x-if="toast.type === 'warning'">
                    <svg class="size-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </template>
                <template x-if="toast.type === 'info'">
                    <svg class="size-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
            </div>

            {{-- Message --}}
            <p class="flex-1 text-sm font-medium text-zinc-800 dark:text-zinc-100" x-text="toast.message"></p>

            {{-- Close button --}}
            <button
                x-on:click="remove(toast.id)"
                class="shrink-0 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 transition-colors"
                aria-label="Dismiss"
            >
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </template>
</div>
