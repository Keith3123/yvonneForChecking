<!-- HOVER EXPAND SIDEBAR -->
<aside class="group bg-white flex flex-col justify-between h-screen w-24 hover:w-64 transition-all duration-300 overflow-y-auto md:overflow-hidden">

    <div class="p-6">

        <!-- COLLAPSED LOGO -->
        <div class="flex justify-center mb-10 group-hover:hidden">
            <img src="{{ asset('images/logo.png') }}" 
                 class="h-10 w-auto object-contain rounded-full">
        </div>

        <!-- EXPANDED LOGO -->
        <div class="hidden group-hover:flex items-center space-x-3 mb-10 px-0.1">
            <img src="{{ asset('images/logo.png') }}" class="h-10 w-13 object-contain rounded-full">
            <div>
                <h2 class="font-bold text-gray-800 leading-tight">Yvonne’s Admin</h2>
                <p class="text-gray-500 text-xs">erika_yvonne@yahoo.com.ph</p>
            </div>
        </div>

        <!-- NAVIGATION -->
        <nav class="space-y-10">

            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center space-x-3 text-gray-700 hover:text-pink-500 pl-2">
                <span class="text-lg">📊</span>
                <span class="hidden group-hover:inline">Dashboard</span>
            </a>

            <a href="{{ route('admin.orders') }}"
               class="flex items-center space-x-3 text-gray-700 hover:text-pink-500 pl-2">
                <span class="text-lg">🛒</span>
                <span class="hidden group-hover:inline">Orders</span>
            </a>

            <a href="{{ route('admin.products') }}"
                class="flex items-center space-x-3 text-gray-700 hover:text-pink-500 pl-2">
                <span>🧺</span>
                <span class="hidden group-hover:inline">Products</span>
            </a>

            <a href="{{ route('admin.salesreport') }}"
               class="flex items-center space-x-3 text-gray-700 hover:text-pink-500 pl-2">
                <span class="text-lg">📄</span>
                <span class="hidden group-hover:inline">Reports</span>
            </a>

            <a href="{{ route('admin.users') }}"
               class="flex items-center space-x-3 text-gray-700 hover:text-pink-500 pl-2">
                <span class="text-lg">👥</span>
                <span class="hidden group-hover:inline">Users</span>
            </a>

            <a href="{{ route('admin.paluwagan') }}"
               class="flex items-center space-x-3 text-gray-700 hover:text-pink-500 pl-2">
                <span class="text-lg">💰</span>
                <span class="hidden group-hover:inline">Paluwagan</span>
            </a>

            <a href="{{ route('admin.inventory') }}"
               class="flex items-center space-x-3 text-gray-700 hover:text-pink-500 pl-2">
                <span class="text-lg">📦</span>
                <span class="hidden group-hover:inline">Inventory</span>
            </a>

        </nav>
    </div>

    <!-- LOGOUT -->
    <form class="px-6 py-6">
        <button class="flex items-center space-x-3 text-gray-700 hover:text-pink-500 pl-3">
            <span class="text-lg">🚪</span>
            <span class="hidden group-hover:inline">Logout</span>
        </button>
    </form>

</aside>
