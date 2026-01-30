<!-- HOVER EXPAND SIDEBAR -->
<aside class="group bg-white flex flex-col justify-between h-screen w-24 hover:w-64 transition-all duration-300 overflow-y-auto md:overflow-hidden shadow-sm">

    <div class="p-6">

        <!-- COLLAPSED LOGO -->
        <div class="flex justify-center mb-10 group-hover:hidden">
            <img src="{{ asset('images/logo.png') }}"
                 class="h-10 w-auto object-contain rounded-full">
        </div>

        <!-- EXPANDED LOGO -->
        <div class="hidden group-hover:flex items-center space-x-3 mb-10">
            <img src="{{ asset('images/logo.png') }}" class="h-10 w-13 object-contain rounded-full">
            <div>
                <h2 class="font-bold text-gray-800 leading-tight">Yvonne’s Admin</h2>
                <p class="text-gray-500 text-xs">erika_yvonne@yahoo.com.ph</p>
            </div>
        </div>

        <!-- NAVIGATION -->
        <nav class="space-y-2">

            <a href="{{ route('admin.dashboard') }}"
               class="group flex items-center space-x-3 text-gray-700 hover:text-pink-500 pl-2 py-3 rounded-lg transition
                      hover:bg-pink-50">
                <span class="text-lg">📊</span>
                <span class="hidden group-hover:inline font-semibold">Dashboard</span>
            </a>

            <a href="{{ route('admin.orders') }}"
               class="group flex items-center space-x-3 text-gray-700 hover:text-pink-500 pl-2 py-3 rounded-lg transition
                      hover:bg-pink-50">
                <span class="text-lg">🛒</span>
                <span class="hidden group-hover:inline font-semibold">Orders</span>
            </a>

            <a href="{{ route('admin.products') }}"
                class="group flex items-center space-x-3 text-gray-700 hover:text-pink-500 pl-2 py-3 rounded-lg transition
                       hover:bg-pink-50">
                <span class="text-lg">🧺</span>
                <span class="hidden group-hover:inline font-semibold">Products</span>
            </a>

            <a href="{{ route('admin.salesreport') }}"
               class="group flex items-center space-x-3 text-gray-700 hover:text-pink-500 pl-2 py-3 rounded-lg transition
                      hover:bg-pink-50">
                <span class="text-lg">📄</span>
                <span class="hidden group-hover:inline font-semibold">Reports</span>
            </a>

            <a href="{{ route('admin.users') }}"
               class="group flex items-center space-x-3 text-gray-700 hover:text-pink-500 pl-2 py-3 rounded-lg transition
                      hover:bg-pink-50">
                <span class="text-lg">👥</span>
                <span class="hidden group-hover:inline font-semibold">Users</span>
            </a>

            <a href="{{ route('admin.paluwagan') }}"
               class="group flex items-center space-x-3 text-gray-700 hover:text-pink-500 pl-2 py-3 rounded-lg transition
                      hover:bg-pink-50">
                <span class="text-lg">💰</span>
                <span class="hidden group-hover:inline font-semibold">Paluwagan</span>
            </a>

            <a href="{{ route('admin.inventory') }}"
               class="group flex items-center space-x-3 text-gray-700 hover:text-pink-500 pl-2 py-3 rounded-lg transition
                      hover:bg-pink-50">
                <span class="text-lg">📦</span>
                <span class="hidden group-hover:inline font-semibold">Inventory</span>
            </a>

        </nav>
    </div>

    <!-- LOGOUT -->
    <form action="{{ route('admin.logout') }}" method="POST" class="px-6 py-6">
        @csrf
        <button type="submit"
                class="group flex items-center space-x-3 text-gray-700 hover:text-pink-500 pl-3 py-3 rounded-lg transition
                       hover:bg-pink-50 w-full">
            <span class="text-lg">🚪</span>
            <span class="hidden group-hover:inline font-semibold">Logout</span>
        </button>
    </form>

</aside>
