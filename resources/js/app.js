// Suppress benign ResizeObserver browser notification that is not a real error.
// It is triggered by Flux/Livewire UI framework internals and carries no actionable information.
const _windowError = window.onerror;
window.onerror = function (message, ...args) {
    if (typeof message === 'string' && message.includes('ResizeObserver loop')) {
        return true; // suppress — prevents it from reaching the _boost browser-log forwarder
    }
    return _windowError ? _windowError(message, ...args) : false;
};
