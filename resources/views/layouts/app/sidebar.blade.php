<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                @if(auth()->user()->isAdmin())
                <flux:sidebar.group :heading="__('Management')" class="grid">
                    <flux:sidebar.item icon="users" :href="route('vendors.index')" :current="request()->routeIs('vendors.*')" wire:navigate>
                        {{ __('Vendors') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="building-storefront" :href="route('stalls.index')" :current="request()->routeIs('stalls.*')" wire:navigate>
                        {{ __('Stalls') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Operations')" class="grid">
                    <flux:sidebar.item icon="banknotes" :href="route('collections.index')" :current="request()->routeIs('collections.*')" wire:navigate>
                        {{ __('Collections') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="chart-bar" :href="route('reports.index')" :current="request()->routeIs('reports.*')" wire:navigate>
                        {{ __('Reports') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Administration')" class="grid">
                    <flux:sidebar.item icon="shield-check" :href="route('users.index')" :current="request()->routeIs('users.*')" wire:navigate>
                        {{ __('Users & Access') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @elseif(auth()->user()->isCollector())
                <flux:sidebar.group :heading="__('Collections')" class="grid">
                    <flux:sidebar.item icon="clipboard-document-list" :href="route('collector.summary')" :current="request()->routeIs('collector.summary')" wire:navigate>
                        {{ __('Daily Summary') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="plus-circle" :href="route('collector.collect')" :current="request()->routeIs('collector.collect')" wire:navigate>
                        {{ __('Record Collection') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="banknotes" :href="route('collector.collections')" :current="request()->routeIs('collector.collections')" wire:navigate>
                        {{ __('My Collections') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Vendors')" class="grid">
                    <flux:sidebar.item icon="users" :href="route('collector.vendors')" :current="request()->routeIs('collector.vendors')" wire:navigate>
                        {{ __('Assigned Vendors') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @elseif(auth()->user()->isVendor())
                <flux:sidebar.group :heading="__('My Stall')" class="grid">
                    <flux:sidebar.item icon="building-storefront" :href="route('vendor.stall')" :current="request()->routeIs('vendor.stall')" wire:navigate>
                        {{ __('Stall Info') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="banknotes" :href="route('vendor.payments')" :current="request()->routeIs('vendor.payments')" wire:navigate>
                        {{ __('My Payments') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>

                <flux:sidebar.group :heading="__('Account')" class="grid">
                    <flux:sidebar.item icon="user-circle" :href="route('vendor.profile')" :current="request()->routeIs('vendor.profile')" wire:navigate>
                        {{ __('Vendor Profile') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="bell" :href="route('vendor.announcements')" :current="request()->routeIs('vendor.announcements')" wire:navigate>
                        {{ __('Announcements') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
                @endif
            </flux:sidebar.nav>

            <flux:spacer />

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>


        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
