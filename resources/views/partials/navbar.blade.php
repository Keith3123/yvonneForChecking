<nav class="w-full bg-pink-100 shadow-md px-6 md:px-20 py-4 flex justify-between items-center sticky top-0 z-50">
    <div class="flex items-center space-x-2">
        <a href="{{ route('home') }}" class="flex items-center space-x-2">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-10 w-auto">
            <span class="font-semibold text-2xl text-pink-400">Yvonne's Cakes & Pastries</span>
        </a>
    </div>

    <div class="flex items-center space-x-6">
        @php $user = session('logged_in_user'); @endphp

        @if($user)
            <a href="{{ route('orders.index') }}"
               class="relative text-black hover:text-gray-900 font-medium after:absolute after:left-0 after:-bottom-1 after:w-0 after:h-[2px] after:bg-red-700 after:transition-all after:duration-300 hover:after:w-full">
               My Orders
            </a>

            <a href="{{ route('paluwagan') }}"
               class="relative text-black hover:text-gray-900 font-medium after:absolute after:left-0 after:-bottom-1 after:w-0 after:h-[2px] after:bg-red-700 after:transition-all after:duration-300 hover:after:w-full">
               Paluwagan
            </a>

            <!-- Profile Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <!-- Profile Icon -->
                <button @click="open = !open"
                        class="w-9 h-9 flex items-center justify-center rounded-full border border-gray-300 text-gray-600 hover:text-pink-500 hover:border-pink-400 transition focus:outline-none"
                        aria-label="Profile Menu">
                    <!-- Heroicon: User Outline -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                         stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.75 7.5a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 19.5a8.25 8.25 0 0115 0" />
                    </svg>
                </button>

                <!-- Dropdown Menu (hidden by default) -->
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 transform scale-100"
                    x-transition:leave-end="opacity-0 transform scale-95"
                    @click.away="open = false"
                    class="absolute right-0 mt-2 w-44 bg-white border border-gray-200 rounded-lg shadow-lg z-50 py-1"
                    x-cloak>
                    <a href="{{ route('profile') }}"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition">
                        View Profile
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-pink-100 hover:text-pink-600 transition">
                            Logout
                        </button>
                    </form>
                </div>
            </div>

        @else
            <a href="{{ route('paluwagan') }}"
               class="relative text-black hover:text-gray-900 font-medium after:absolute after:left-0 after:-bottom-1 after:w-0 after:h-[2px] after:bg-red-700 after:transition-all after:duration-300 hover:after:w-full">
               Paluwagan
            </a>

            <a href="{{ route('login') }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-full transition duration-300">
                Login
            </a>
        @endif
    </div>
</nav>
