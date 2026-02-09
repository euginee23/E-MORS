<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:header container class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden mr-2" icon="bars-2" inset="left" />

            <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

            <flux:navbar class="-mb-px max-lg:hidden">
                <flux:navbar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:navbar.item>
                <flux:navbar.item icon="users" :href="route('vendors.index')" :current="request()->routeIs('vendors.*')" wire:navigate>
                    {{ __('Vendors') }}
                </flux:navbar.item>
                <flux:navbar.item icon="building-storefront" :href="route('stalls.index')" :current="request()->routeIs('stalls.*')" wire:navigate>
                    {{ __('Stalls') }}
                </flux:navbar.item>
                <flux:navbar.item icon="banknotes" :href="route('collections.index')" :current="request()->routeIs('collections.*')" wire:navigate>
                    {{ __('Collections') }}
                </flux:navbar.item>
                <flux:navbar.item icon="chart-bar" :href="route('reports.index')" :current="request()->routeIs('reports.*')" wire:navigate>
                    {{ __('Reports') }}
                </flux:navbar.item>
                @if(auth()->user()->isAdmin())
                <flux:navbar.item icon="shield-check" :href="route('users.index')" :current="request()->routeIs('users.*')" wire:navigate>
                    {{ __('Users') }}
                </flux:navbar.item>
                @endif
            </flux:navbar>

            <flux:spacer />

            <flux:navbar class="me-1.5 space-x-0.5 rtl:space-x-reverse py-0!">
                <flux:tooltip :content="__('Search')" position="bottom">
                    <flux:navbar.item class="!h-10 [&>div>svg]:size-5" icon="magnifying-glass" href="#" :label="__('Search')" />
                </flux:tooltip>
            </flux:navbar>

            <x-desktop-user-menu />
        </flux:header>

        <!-- Mobile Menu -->
        <flux:sidebar collapsible="mobile" sticky class="lg:hidden border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')">
                    <flux:sidebar.item icon="layout-grid" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard')  }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Management')">
                    <flux:sidebar.item icon="users" :href="route('vendors.index')" :current="request()->routeIs('vendors.*')" wire:navigate>
                        {{ __('Vendors') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="building-storefront" :href="route('stalls.index')" :current="request()->routeIs('stalls.*')" wire:navigate>
                        {{ __('Stalls') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Operations')">
                    <flux:sidebar.item icon="banknotes" :href="route('collections.index')" :current="request()->routeIs('collections.*')" wire:navigate>
                        {{ __('Collections') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="chart-bar" :href="route('reports.index')" :current="request()->routeIs('reports.*')" wire:navigate>
                        {{ __('Reports') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @if(auth()->user()->isAdmin())
                <flux:sidebar.group :heading="__('Administration')">
                    <flux:sidebar.item icon="shield-check" :href="route('users.index')" :current="request()->routeIs('users.*')" wire:navigate>
                        {{ __('Users & Access') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />
        </flux:sidebar>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
