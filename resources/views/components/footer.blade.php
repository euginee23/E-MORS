<!-- Footer -->
<footer class="bg-zinc-950 text-white py-10 sm:py-12 lg:py-16 px-4 sm:px-6 lg:px-8 mt-auto">
    <div class="max-w-7xl mx-auto">
        <div class="grid sm:grid-cols-2 md:grid-cols-4 gap-6 sm:gap-8 lg:gap-12 mb-6 sm:mb-8 lg:mb-12">
            <div class="sm:col-span-2">
                <div class="flex items-center gap-3 mb-3 sm:mb-4 lg:mb-6">
                    <div class="relative">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-orange-500 to-amber-500 rounded-lg sm:rounded-xl rotate-3"></div>
                        <div class="absolute inset-0 w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-orange-600 to-amber-600 rounded-lg sm:rounded-xl -rotate-3 flex items-center justify-center">
                            <span class="text-white font-black text-xs sm:text-sm">EP</span>
                        </div>
                    </div>
                    <div>
                        <h3 class="font-black text-lg sm:text-xl">E-MORS</h3>
                        <p class="text-[10px] sm:text-xs text-orange-400 font-medium">E-Palengke System</p>
                    </div>
                </div>
                <p class="text-zinc-400 max-w-md leading-relaxed text-xs sm:text-sm">
                    E-Palengke Market Operations and Revenue System — A comprehensive Operations Management System (OMS) empowering public markets with modern technology for efficient operations and transparent revenue management.
                </p>
            </div>
            
            <div>
                <h4 class="font-bold mb-2 sm:mb-3 lg:mb-4 text-sm lg:text-base">Quick Links</h4>
                <ul class="space-y-1.5 sm:space-y-2 lg:space-y-3 text-zinc-400 text-xs sm:text-sm">
                    <li><a href="{{ url('/') }}#about" class="hover:text-orange-400 transition-colors">About</a></li>
                    <li><a href="{{ url('/') }}#modules" class="hover:text-orange-400 transition-colors">Modules</a></li>
                    <li><a href="{{ url('/') }}#benefits" class="hover:text-orange-400 transition-colors">Benefits</a></li>
                </ul>
            </div>
            
            <div>
                <h4 class="font-bold mb-2 sm:mb-3 lg:mb-4 text-sm lg:text-base">Account</h4>
                <ul class="space-y-1.5 sm:space-y-2 lg:space-y-3 text-zinc-400 text-xs sm:text-sm">
                    <li><a href="{{ route('login') }}" class="hover:text-orange-400 transition-colors">Sign In</a></li>
                    @if (Route::has('register'))
                        <li><a href="{{ route('register') }}" class="hover:text-orange-400 transition-colors">Register</a></li>
                    @endif
                </ul>
            </div>
        </div>
        
        <div class="border-t border-zinc-800 pt-5 sm:pt-6 lg:pt-8 flex flex-col md:flex-row justify-between items-center gap-3 sm:gap-4">
            <p class="text-zinc-500 text-xs sm:text-sm">
                CodeHub.Site &copy; {{ date('Y') }} E-MORS. All rights reserved.
            </p>
            <p class="text-zinc-600 text-xs sm:text-sm">
                E-Palengke Market Operations and Revenue System
            </p>
        </div>
    </div>
</footer>
