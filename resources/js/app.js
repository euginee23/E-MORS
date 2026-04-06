// Suppress benign ResizeObserver browser notification that is not a real error.
// It is triggered by Flux/Livewire UI framework internals and carries no actionable information.
// We intercept both window.onerror and the capture-phase error event (used by laravel/boost).
const _windowError = window.onerror;
window.onerror = function (message, ...args) {
    if (typeof message === 'string' && message.includes('ResizeObserver loop')) {
        return true;
    }
    return _windowError ? _windowError(message, ...args) : false;
};

window.addEventListener(
    'error',
    function (event) {
        if (event.message && event.message.includes('ResizeObserver loop')) {
            event.stopImmediatePropagation();
        }
    },
    true // capture phase — runs before laravel/boost listener
);
