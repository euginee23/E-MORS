<!-- Navbar - Shared across all pages -->
<header class="border-b border-orange-200 dark:border-zinc-800 bg-white/80 dark:bg-zinc-900/80 backdrop-blur-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-20">
            <!-- Logo -->
            <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="flex items-center gap-2 sm:gap-4 group">
                <div class="relative">
                    <div class="w-9 h-9 sm:w-12 sm:h-12 bg-gradient-to-br from-orange-500 via-amber-500 to-yellow-500 rounded-xl sm:rounded-2xl rotate-3 shadow-lg group-hover:rotate-6 transition-transform"></div>
                    <div class="absolute inset-0 w-9 h-9 sm:w-12 sm:h-12 bg-gradient-to-br from-orange-600 via-amber-600 to-yellow-600 rounded-xl sm:rounded-2xl -rotate-3 flex items-center justify-center group-hover:-rotate-6 transition-transform">
                        <span class="text-white font-black text-sm sm:text-lg">EP</span>
                    </div>
                </div>
                <div>
                    <h1 class="text-lg sm:text-2xl font-black text-zinc-900 dark:text-white tracking-tight">E-MORS</h1>
                    <p class="text-[10px] sm:text-xs text-orange-600 dark:text-orange-400 font-medium tracking-widest uppercase">E-Palengke System</p>
                </div>
            </a>

            @auth
                <!-- Authenticated Navigation -->
                <nav class="hidden lg:flex items-center gap-1">
                    <a href="{{ route('dashboard') }}" class="px-3 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        Dashboard
                    </a>

                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('vendors.index') }}" class="px-3 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('vendors.*') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        Vendors
                    </a>
                    <a href="{{ route('stalls.index') }}" class="px-3 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('stalls.*') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        Stalls
                    </a>
                    <a href="{{ route('collections.index') }}" class="px-3 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('collections.*') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        Collections
                    </a>
                    <a href="{{ route('reports.index') }}" class="px-3 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('reports.*') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        Reports
                    </a>
                    <a href="{{ route('users.index') }}" class="px-3 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('users.*') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        Users
                    </a>
                    @elseif(auth()->user()->isCollector())
                    <a href="{{ route('collector.summary') }}" class="px-3 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('collector.summary') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        Daily Summary
                    </a>
                    <a href="{{ route('collector.collect') }}" class="px-3 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('collector.collect') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        Record Collection
                    </a>
                    <a href="{{ route('collector.collections') }}" class="px-3 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('collector.collections') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        My Collections
                    </a>
                    <a href="{{ route('collector.vendors') }}" class="px-3 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('collector.vendors') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        Assigned Vendors
                    </a>
                    @elseif(auth()->user()->isVendor())
                    <a href="{{ route('vendor.stall') }}" class="px-3 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('vendor.stall') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        My Stall
                    </a>
                    <a href="{{ route('vendor.payments') }}" class="px-3 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('vendor.payments') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        My Payments
                    </a>
                    <a href="{{ route('vendor.announcements') }}" class="px-3 py-2 rounded-xl text-sm font-medium transition-colors {{ request()->routeIs('vendor.announcements') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        Announcements
                    </a>
                    @endif
                </nav>

                <!-- User Menu & Mobile Toggle -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Theme Toggle -->
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" class="p-2 rounded-xl text-zinc-500 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer" aria-label="Toggle theme">
                            <!-- Sun icon (shown in dark mode) -->
                            <svg x-cloak x-show="$flux.appearance === 'dark' || ($flux.appearance === 'system' && $flux.dark)" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            <!-- Moon icon (shown in light mode) -->
                            <svg x-cloak x-show="!($flux.appearance === 'dark' || ($flux.appearance === 'system' && $flux.dark))" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                        </button>
                        <div x-cloak x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-1" class="absolute right-0 mt-2 w-40 bg-white dark:bg-zinc-900 border border-orange-100 dark:border-zinc-700 rounded-xl shadow-xl shadow-orange-500/10 py-1 z-50">
                            <button @click="$flux.appearance = 'light'; open = false" class="flex items-center gap-3 w-full px-4 py-2 text-sm transition-colors cursor-pointer" :class="$flux.appearance === 'light' ? 'text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/20' : 'text-zinc-700 dark:text-zinc-300 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                Light
                            </button>
                            <button @click="$flux.appearance = 'dark'; open = false" class="flex items-center gap-3 w-full px-4 py-2 text-sm transition-colors cursor-pointer" :class="$flux.appearance === 'dark' ? 'text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/20' : 'text-zinc-700 dark:text-zinc-300 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                                Dark
                            </button>
                            <button @click="$flux.appearance = 'system'; open = false" class="flex items-center gap-3 w-full px-4 py-2 text-sm transition-colors cursor-pointer" :class="$flux.appearance === 'system' ? 'text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/20' : 'text-zinc-700 dark:text-zinc-300 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                System
                            </button>
                        </div>
                    </div>

                    <!-- User Dropdown (Desktop) -->
                    <div class="relative hidden sm:block" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-orange-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer">
                            <div class="w-8 h-8 bg-gradient-to-br from-orange-500 to-amber-500 rounded-lg flex items-center justify-center">
                                <span class="text-white font-bold text-xs">{{ auth()->user()->initials() }}</span>
                            </div>
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300 hidden md:inline">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4 text-zinc-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </button>

                        <div x-cloak x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-1" class="absolute right-0 mt-2 w-64 bg-white dark:bg-zinc-900 border border-orange-100 dark:border-zinc-700 rounded-2xl shadow-xl shadow-orange-500/10 py-2 z-50">
                            <!-- User Info -->
                            <div class="px-4 py-3 border-b border-orange-100 dark:border-zinc-700">
                                <p class="text-sm font-semibold text-zinc-900 dark:text-white">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ auth()->user()->email }}</p>
                                <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-md bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300">{{ auth()->user()->role?->label() ?? 'User' }}</span>
                            </div>
                            <!-- Menu Items -->
                            <div class="py-1">
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-zinc-700 dark:text-zinc-300 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    Settings
                                </a>
                            </div>
                            <div class="border-t border-orange-100 dark:border-zinc-700 pt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-zinc-700 dark:text-zinc-300 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/10 transition-colors cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
                                        Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Menu Button -->
                    <button
                        id="appMobileMenuBtn"
                        class="lg:hidden p-2 text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 transition-colors"
                        aria-label="Toggle menu"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            @else
                <!-- Guest Navigation -->
                <nav class="hidden md:flex items-center gap-6 lg:gap-8">
                    <a href="{{ url('/') }}#about" class="text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 font-medium transition-colors">About</a>
                    <a href="{{ url('/') }}#modules" class="text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 font-medium transition-colors">Modules</a>
                    <a href="{{ url('/') }}#benefits" class="text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 font-medium transition-colors">Benefits</a>
                </nav>

                <!-- Guest Auth Buttons -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Theme Toggle (Guest) -->
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" class="p-2 rounded-xl text-zinc-500 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800 transition-colors cursor-pointer" aria-label="Toggle theme">
                            <svg x-cloak x-show="$flux.appearance === 'dark' || ($flux.appearance === 'system' && $flux.dark)" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            <svg x-cloak x-show="!($flux.appearance === 'dark' || ($flux.appearance === 'system' && $flux.dark))" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                        </button>
                        <div x-cloak x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-1" class="absolute right-0 mt-2 w-40 bg-white dark:bg-zinc-900 border border-orange-100 dark:border-zinc-700 rounded-xl shadow-xl shadow-orange-500/10 py-1 z-50">
                            <button @click="$flux.appearance = 'light'; open = false" class="flex items-center gap-3 w-full px-4 py-2 text-sm transition-colors cursor-pointer" :class="$flux.appearance === 'light' ? 'text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/20' : 'text-zinc-700 dark:text-zinc-300 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                Light
                            </button>
                            <button @click="$flux.appearance = 'dark'; open = false" class="flex items-center gap-3 w-full px-4 py-2 text-sm transition-colors cursor-pointer" :class="$flux.appearance === 'dark' ? 'text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/20' : 'text-zinc-700 dark:text-zinc-300 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                                Dark
                            </button>
                            <button @click="$flux.appearance = 'system'; open = false" class="flex items-center gap-3 w-full px-4 py-2 text-sm transition-colors cursor-pointer" :class="$flux.appearance === 'system' ? 'text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/20' : 'text-zinc-700 dark:text-zinc-300 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                System
                            </button>
                        </div>
                    </div>

                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex px-4 py-2.5 text-zinc-700 dark:text-zinc-300 hover:text-orange-600 dark:hover:text-orange-400 font-medium transition-colors">
                            Sign In
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="hidden sm:inline-flex px-4 lg:px-6 py-2.5 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white text-sm lg:text-base font-semibold rounded-xl transition-all shadow-lg shadow-orange-500/25 hover:shadow-orange-500/40 hover:-translate-y-0.5">
                                Register
                            </a>
                        @endif
                        <a href="{{ route('login') }}" class="sm:hidden px-3 py-2 text-sm text-zinc-700 dark:text-zinc-300 hover:text-orange-600 font-medium transition-colors">
                            Sign In
                        </a>
                    @endif

                    <!-- Mobile Menu Button (Guest) -->
                    <button
                        id="guestMobileMenuBtn"
                        class="md:hidden p-2 text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 transition-colors"
                        aria-label="Toggle menu"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            @endauth
        </div>

        @auth
            <!-- Mobile Menu (Authenticated) -->
            <div id="appMobileMenu" class="hidden lg:hidden pb-4 border-t border-orange-100 dark:border-zinc-800 mt-2">
                <nav class="flex flex-col gap-1 pt-4">
                    <a href="{{ route('dashboard') }}" class="px-4 py-3 rounded-xl font-medium transition-colors {{ request()->routeIs('dashboard') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        Dashboard
                    </a>

                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('vendors.index') }}" class="px-4 py-3 rounded-xl font-medium transition-colors {{ request()->routeIs('vendors.*') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        Vendors
                    </a>
                    <a href="{{ route('stalls.index') }}" class="px-4 py-3 rounded-xl font-medium transition-colors {{ request()->routeIs('stalls.*') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        Stalls
                    </a>
                    <a href="{{ route('collections.index') }}" class="px-4 py-3 rounded-xl font-medium transition-colors {{ request()->routeIs('collections.*') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        Collections
                    </a>
                    <a href="{{ route('reports.index') }}" class="px-4 py-3 rounded-xl font-medium transition-colors {{ request()->routeIs('reports.*') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        Reports
                    </a>
                    <a href="{{ route('users.index') }}" class="px-4 py-3 rounded-xl font-medium transition-colors {{ request()->routeIs('users.*') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        Users
                    </a>
                    @elseif(auth()->user()->isCollector())
                    <a href="{{ route('collector.summary') }}" class="px-4 py-3 rounded-xl font-medium transition-colors {{ request()->routeIs('collector.summary') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        Daily Summary
                    </a>
                    <a href="{{ route('collector.collect') }}" class="px-4 py-3 rounded-xl font-medium transition-colors {{ request()->routeIs('collector.collect') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        Record Collection
                    </a>
                    <a href="{{ route('collector.collections') }}" class="px-4 py-3 rounded-xl font-medium transition-colors {{ request()->routeIs('collector.collections') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        My Collections
                    </a>
                    <a href="{{ route('collector.vendors') }}" class="px-4 py-3 rounded-xl font-medium transition-colors {{ request()->routeIs('collector.vendors') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        Assigned Vendors
                    </a>
                    @elseif(auth()->user()->isVendor())
                    <a href="{{ route('vendor.stall') }}" class="px-4 py-3 rounded-xl font-medium transition-colors {{ request()->routeIs('vendor.stall') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        My Stall
                    </a>
                    <a href="{{ route('vendor.payments') }}" class="px-4 py-3 rounded-xl font-medium transition-colors {{ request()->routeIs('vendor.payments') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        My Payments
                    </a>
                    <a href="{{ route('vendor.announcements') }}" class="px-4 py-3 rounded-xl font-medium transition-colors {{ request()->routeIs('vendor.announcements') ? 'text-orange-700 dark:text-orange-300 bg-orange-100 dark:bg-orange-900/30' : 'text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800' }}">
                        Announcements
                    </a>
                    @endif
                </nav>
                <div class="flex flex-col gap-2 pt-4 mt-4 border-t border-orange-100 dark:border-zinc-800">
                    <!-- Mobile User Info -->
                    <div class="flex items-center gap-3 px-4 py-2">
                        <div class="w-8 h-8 bg-gradient-to-br from-orange-500 to-amber-500 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-xs">{{ auth()->user()->initials() }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-zinc-900 dark:text-white">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-zinc-500">{{ auth()->user()->role?->label() ?? 'User' }}</p>
                        </div>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="px-4 py-3 text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800 rounded-xl font-medium transition-colors">
                        Settings
                    </a>
                    <!-- Mobile Theme Toggle -->
                    <div class="px-4 py-2" x-data>
                        <p class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-2">Theme</p>
                        <div class="flex gap-1 rounded-xl bg-zinc-100 dark:bg-zinc-800 p-1">
                            <button @click="$flux.appearance = 'light'" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors cursor-pointer" :class="$flux.appearance === 'light' ? 'bg-white dark:bg-zinc-700 text-orange-600 dark:text-orange-400 shadow-sm' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300'">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                Light
                            </button>
                            <button @click="$flux.appearance = 'dark'" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors cursor-pointer" :class="$flux.appearance === 'dark' ? 'bg-white dark:bg-zinc-700 text-orange-600 dark:text-orange-400 shadow-sm' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300'">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                                Dark
                            </button>
                            <button @click="$flux.appearance = 'system'" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors cursor-pointer" :class="$flux.appearance === 'system' ? 'bg-white dark:bg-zinc-700 text-orange-600 dark:text-orange-400 shadow-sm' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300'">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                System
                            </button>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-3 text-zinc-600 dark:text-zinc-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/10 rounded-xl font-medium transition-colors cursor-pointer">
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
        @else
            <!-- Mobile Menu (Guest) -->
            <div id="guestMobileMenu" class="hidden md:hidden pb-4 border-t border-orange-100 dark:border-zinc-800 mt-2">
                <nav class="flex flex-col gap-1 pt-4">
                    <a href="{{ url('/') }}#about" class="px-4 py-3 text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800 rounded-xl font-medium transition-colors">About</a>
                    <a href="{{ url('/') }}#modules" class="px-4 py-3 text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800 rounded-xl font-medium transition-colors">Modules</a>
                    <a href="{{ url('/') }}#benefits" class="px-4 py-3 text-zinc-600 dark:text-zinc-400 hover:text-orange-600 dark:hover:text-orange-400 hover:bg-orange-50 dark:hover:bg-zinc-800 rounded-xl font-medium transition-colors">Benefits</a>
                </nav>
                <!-- Mobile Theme Toggle (Guest) -->
                <div class="px-4 py-2 pt-4 mt-4 border-t border-orange-100 dark:border-zinc-800" x-data>
                    <p class="text-xs font-semibold text-zinc-400 dark:text-zinc-500 uppercase tracking-wider mb-2">Theme</p>
                    <div class="flex gap-1 rounded-xl bg-zinc-100 dark:bg-zinc-800 p-1">
                        <button @click="$flux.appearance = 'light'" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors cursor-pointer" :class="$flux.appearance === 'light' ? 'bg-white dark:bg-zinc-700 text-orange-600 dark:text-orange-400 shadow-sm' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                            Light
                        </button>
                        <button @click="$flux.appearance = 'dark'" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors cursor-pointer" :class="$flux.appearance === 'dark' ? 'bg-white dark:bg-zinc-700 text-orange-600 dark:text-orange-400 shadow-sm' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" /></svg>
                            Dark
                        </button>
                        <button @click="$flux.appearance = 'system'" class="flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors cursor-pointer" :class="$flux.appearance === 'system' ? 'bg-white dark:bg-zinc-700 text-orange-600 dark:text-orange-400 shadow-sm' : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-300'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            System
                        </button>
                    </div>
                </div>
                @guest
                    <div class="flex flex-col gap-2 pt-4 mt-4 border-t border-orange-100 dark:border-zinc-800">
                        <a href="{{ route('login') }}" class="px-4 py-3 text-center text-zinc-700 dark:text-zinc-300 hover:text-orange-600 dark:hover:text-orange-400 font-medium border border-zinc-200 dark:border-zinc-700 rounded-xl transition-colors">
                            Sign In
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-4 py-3 text-center bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-semibold rounded-xl transition-all shadow-lg shadow-orange-500/25">
                                Register Your Market
                            </a>
                        @endif
                    </div>
                @endguest
            </div>
        @endauth
    </div>
</header>

<script>
    function initMobileMenu() {
        const appBtn = document.getElementById('appMobileMenuBtn');
        const appMenu = document.getElementById('appMobileMenu');
        const guestBtn = document.getElementById('guestMobileMenuBtn');
        const guestMenu = document.getElementById('guestMobileMenu');

        if (appBtn && appMenu) {
            appBtn.replaceWith(appBtn.cloneNode(true));
            document.getElementById('appMobileMenuBtn').addEventListener('click', () => appMenu.classList.toggle('hidden'));
        }
        if (guestBtn && guestMenu) {
            guestBtn.replaceWith(guestBtn.cloneNode(true));
            document.getElementById('guestMobileMenuBtn').addEventListener('click', () => guestMenu.classList.toggle('hidden'));
        }
    }
    document.addEventListener('DOMContentLoaded', initMobileMenu);
    document.addEventListener('livewire:navigated', initMobileMenu);
</script>
