<nav class="w-full bg-pink-100/90 backdrop-blur-md shadow-md px-6 md:px-20 py-4 sticky top-0 z-50">

    <div class="flex justify-between items-center">
        <!-- LOGO -->
        <a href="{{ route('home') }}" class="flex items-center space-x-3 group">
            <img src="{{ asset('images/logo.png') }}" alt="Logo"
                 class="h-10 w-auto transition-transform duration-300 group-hover:scale-105">
            <span class="font-semibold text-xl md:text-2xl text-pink-400 tracking-tight">
                Yvonne's Cakes & Pastries
            </span>
        </a>

        <!-- MOBILE MENU BUTTON -->
        <button id="menu-toggle"
            class="md:hidden text-pink-500 text-3xl transition-transform hover:scale-110 active:scale-95">
            ☰
        </button>

        <!-- DESKTOP LINKS -->
        <div class="hidden md:flex items-center space-x-8">
            @php $user = session('logged_in_user'); @endphp

            <!-- NAV LINK -->
            <a href="{{ route('paluwagan') }}"
               class="relative font-medium text-gray-800 transition-all duration-300
                      hover:text-pink-500 hover:-translate-y-0.5
                      after:absolute after:left-1/2 after:-bottom-1
                      after:h-[2px] after:w-0 after:bg-pink-500 after:transition-all
                      after:-translate-x-1/2 hover:after:w-full">
                Paluwagan
            </a>

            @if($user)
                <a href="{{ route('orders.index') }}"
                   class="relative font-medium text-gray-800 transition-all duration-300
                          hover:text-pink-500 hover:-translate-y-0.5
                          after:absolute after:left-1/2 after:-bottom-1
                          after:h-[2px] after:w-0 after:bg-pink-500 after:transition-all
                          after:-translate-x-1/2 hover:after:w-full">
                    My Orders
                </a>

                <!-- PROFILE DROPDOWN -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                        class="w-10 h-10 flex items-center justify-center rounded-full
                               border border-gray-300 bg-white
                               transition-all duration-300
                               hover:border-pink-400 hover:text-pink-500
                               hover:shadow-md hover:-translate-y-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15.75 7.5a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 19.5a8.25 8.25 0 0115 0" />
                        </svg>
                    </button>

                    <!-- DROPDOWN MENU -->
                    <div x-show="open"
                        @click.away="open=false"
                        x-transition
                        x-cloak
                        class="absolute right-0 mt-3 w-44 bg-white border border-gray-200
                               rounded-xl shadow-lg overflow-hidden z-50">

                        <a href="{{ route('profile') }}"
                           class="block px-4 py-2 text-sm transition
                                  hover:bg-pink-50 hover:text-pink-600">
                            View Profile
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full text-left px-4 py-2 text-sm transition
                                       hover:bg-pink-50 hover:text-pink-600">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <!-- LOGIN BUTTON -->
                <a href="{{ route('login') }}"
                   class="bg-pink-500 text-white font-semibold
                          px-6 py-2 rounded-xl
                          transition-all duration-300
                          hover:bg-pink-600 hover:shadow-lg hover:-translate-y-0.5
                          active:scale-95">
                    Login
                </a>
            @endif
        </div>
    </div>

    <!-- MOBILE MENU -->
<div id="mobile-menu"
     class="md:hidden hidden mt-6 mx-4
            bg-white rounded-2xl shadow-xl
            divide-y divide-gray-100 overflow-hidden">

    <!-- MENU LINKS -->
    <div class="flex flex-col text-center">
        <a href="{{ route('paluwagan') }}"
           class="py-4 font-medium text-gray-800
                  transition-all duration-200
                  hover:bg-pink-50 hover:text-pink-500
                  hover:tracking-wide">
            Paluwagan
        </a>

        @if($user)
            <a href="{{ route('orders.index') }}"
               class="py-4 font-medium text-gray-800
                      transition-all duration-200
                      hover:bg-pink-50 hover:text-pink-500
                      hover:tracking-wide">
                My Orders
            </a>

            <a href="{{ route('profile') }}"
               class="py-4 font-medium text-gray-800
                      transition-all duration-200
                      hover:bg-pink-50 hover:text-pink-500
                      hover:tracking-wide">
                Profile
            </a>
        @endif
    </div>

    <!-- LOGOUT / LOGIN -->
    <div class="p-4 text-center">
        @if($user)
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    class="w-full py-3 rounded-xl font-semibold
                           text-red-500 border border-red-200
                           transition-all duration-200
                           hover:bg-red-50 hover:text-red-600">
                    Logout
                </button>
            </form>
        @else
            <a href="{{ route('login') }}"
               class="block w-full py-3 rounded-xl
                      bg-pink-500 text-white font-semibold
                      transition hover:bg-pink-600">
                Login
            </a>
        @endif
    </div>
</div>
</nav>


<script>
document.getElementById("menu-toggle").onclick = () => {
    document.getElementById("mobile-menu").classList.toggle("hidden");
};
</script>
