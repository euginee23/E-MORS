<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="antialiased">
        {{ $slot }}
        @fluxScripts

        {{-- Global UI: loading bar, confirm modal, toast --}}
        <x-loading-bar />
        <x-confirm-modal />
        <x-toast />
    </body>
</html>
