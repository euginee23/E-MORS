{{--
    Global Loading Bar Component
    Automatically shows during any Livewire network request.
    Uses Livewire.hook() API to track request/response lifecycle.
--}}
<div
    x-data="{ loading: false }"
    x-on:livewire-loading-start.window="loading = true"
    x-on:livewire-loading-end.window="loading = false"
    class="fixed top-0 left-0 right-0 z-[10000] pointer-events-none"
    aria-hidden="true"
>
    {{-- Top progress bar --}}
    <div
        x-show="loading"
        x-transition:enter="transition duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="h-0.5 w-full bg-orange-200 dark:bg-orange-900/40"
        style="display:none"
    >
        <div class="h-full bg-orange-500 dark:bg-orange-400 animate-loading-bar rounded-full"></div>
    </div>

    {{-- Subtle full-screen cursor indicator --}}
    <div
        x-show="loading"
        class="fixed inset-0 cursor-wait"
        style="display:none"
    ></div>
</div>

{{-- Floating spinner for longer operations --}}
<div
    x-data="{ loading: false, slow: false, timer: null }"
    x-on:livewire-loading-start.window="loading = true; timer = setTimeout(() => slow = true, 600)"
    x-on:livewire-loading-end.window="loading = false; slow = false; clearTimeout(timer)"
    class="fixed bottom-5 left-5 z-[9998] pointer-events-none"
    aria-hidden="true"
>
    <div
        x-show="loading && slow"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-90"
        class="flex items-center gap-2 rounded-xl border border-orange-100 bg-white/90 backdrop-blur-sm px-3 py-2 shadow-lg dark:border-zinc-700 dark:bg-zinc-900/90"
        style="display:none"
    >
        <svg class="size-4 animate-spin text-orange-500" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        <span class="text-xs font-medium text-zinc-600 dark:text-zinc-400">{{ __('Loading…') }}</span>
    </div>
</div>

{{-- Hook into Livewire's request lifecycle to dispatch loading events --}}
<script>
    document.addEventListener('livewire:init', () => {
        let activeRequests = 0;

        Livewire.hook('request', ({ respond, fail }) => {
            activeRequests++;
            if (activeRequests === 1) {
                window.dispatchEvent(new CustomEvent('livewire-loading-start'));
            }

            respond(() => {
                activeRequests = Math.max(0, activeRequests - 1);
                if (activeRequests === 0) {
                    window.dispatchEvent(new CustomEvent('livewire-loading-end'));
                }
            });

            fail(() => {
                activeRequests = Math.max(0, activeRequests - 1);
                if (activeRequests === 0) {
                    window.dispatchEvent(new CustomEvent('livewire-loading-end'));
                }
            });
        });
    });
</script>
