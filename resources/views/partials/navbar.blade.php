<nav class="w-full bg-pink-100 shadow-md px-6 md:px-20 py-4 flex justify-between items-center sticky top-0 z-50">
    <div class="flex items-center space-x-2">
        <a href="{{ route('home') }}" class="flex items-center space-x-2">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-10 w-auto">
            <span class="font-bold text-2xl text-pink-400">Yvonne's Cakes & Pastries</span>
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

            <span class="text-gray-700 font-semibold">Welcome, {{ $user['firstname'] }}</span>

            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-6 rounded-full transition duration-300">
                    Logout
                </button>
            </form>
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
