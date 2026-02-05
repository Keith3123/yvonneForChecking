<aside
  class="group/sidebar bg-white flex flex-col justify-between h-screen
         w-24 hover:w-64 transition-[width] duration-300
         overflow-hidden shadow-sm border-r border-pink-200">

    <div class="p-6">

        <!-- LOGO -->
        <div class="flex items-center mb-10">

            <img src="{{ asset('images/logo.png') }}"
                 class="h-10 w-10 rounded-full mx-auto
                        transition-all duration-300
                        group-hover/sidebar:mx-0" />

            <div
              class="ml-3 opacity-0 max-w-0 overflow-hidden
                     group-hover/sidebar:opacity-100
                     group-hover/sidebar:max-w-xs
                     transition-all duration-300 whitespace-nowrap">
                <h2 class="font-bold text-gray-800 leading-tight">
                    Yvonne’s Admin
                </h2>
                <p class="text-gray-500 text-xs">
                    erika_yvonne@yahoo.com.ph
                </p>
            </div>
        </div>

        <!-- NAVIGATION -->
        <nav class="space-y-1">

            <!-- DASHBOARD -->
            <a href="{{ route('admin.dashboard') }}"
               class="grid grid-cols-[40px_1fr] items-center
                      py-3 rounded-lg
                      text-gray-700 hover:text-pink-500
                      hover:bg-pink-50 transition">

                <i class="fas fa-chart-bar text-pink-500 fa-fw justify-self-center"></i>

                <span
                  class="font-semibold opacity-0 max-w-0 overflow-hidden
                         group-hover/sidebar:opacity-100
                         group-hover/sidebar:max-w-xs
                         transition-all duration-300 whitespace-nowrap">
                    Dashboard
                </span>
            </a>

            <!-- ORDERS -->
            <a href="{{ route('admin.orders') }}"
               class="grid grid-cols-[40px_1fr] items-center
                      py-3 rounded-lg
                      text-gray-700 hover:text-pink-500
                      hover:bg-pink-50 transition">

                <i class="fas fa-shopping-cart text-pink-500 fa-fw justify-self-center"></i>

                <span
                  class="font-semibold opacity-0 max-w-0 overflow-hidden
                         group-hover/sidebar:opacity-100
                         group-hover/sidebar:max-w-xs
                         transition-all duration-300 whitespace-nowrap">
                    Orders
                </span>
            </a>

            <!-- PRODUCTS -->
            <a href="{{ route('admin.products') }}"
               class="grid grid-cols-[40px_1fr] items-center
                      py-3 rounded-lg
                      text-gray-700 hover:text-pink-500
                      hover:bg-pink-50 transition">

                <i class="fas fa-box-open text-pink-500 fa-fw justify-self-center"></i>

                <span
                  class="font-semibold opacity-0 max-w-0 overflow-hidden
                         group-hover/sidebar:opacity-100
                         group-hover/sidebar:max-w-xs
                         transition-all duration-300 whitespace-nowrap">
                    Products
                </span>
            </a>

            <!-- REPORTS -->
            <a href="{{ route('admin.salesreport') }}"
               class="grid grid-cols-[40px_1fr] items-center
                      py-3 rounded-lg
                      text-gray-700 hover:text-pink-500
                      hover:bg-pink-50 transition">

                <i class="fas fa-file-alt text-pink-500 fa-fw justify-self-center"></i>

                <span
                  class="font-semibold opacity-0 max-w-0 overflow-hidden
                         group-hover/sidebar:opacity-100
                         group-hover/sidebar:max-w-xs
                         transition-all duration-300 whitespace-nowrap">
                    Reports
                </span>
            </a>

            <!-- USERS -->
            <a href="{{ route('admin.users') }}"
               class="grid grid-cols-[40px_1fr] items-center
                      py-3 rounded-lg
                      text-gray-700 hover:text-pink-500
                      hover:bg-pink-50 transition">

                <i class="fas fa-users text-pink-500 fa-fw justify-self-center"></i>

                <span
                  class="font-semibold opacity-0 max-w-0 overflow-hidden
                         group-hover/sidebar:opacity-100
                         group-hover/sidebar:max-w-xs
                         transition-all duration-300 whitespace-nowrap">
                    Users
                </span>
            </a>

            <!-- PALUWAGAN -->
            <a href="{{ route('admin.paluwagan') }}"
               class="grid grid-cols-[40px_1fr] items-center
                      py-3 rounded-lg
                      text-gray-700 hover:text-pink-500
                      hover:bg-pink-50 transition">

                <i class="fas fa-wallet text-pink-500 fa-fw justify-self-center"></i>

                <span
                  class="font-semibold opacity-0 max-w-0 overflow-hidden
                         group-hover/sidebar:opacity-100
                         group-hover/sidebar:max-w-xs
                         transition-all duration-300 whitespace-nowrap">
                    Paluwagan
                </span>
            </a>

            <!-- INVENTORY -->
            <a href="{{ route('admin.inventory') }}"
               class="grid grid-cols-[40px_1fr] items-center
                      py-3 rounded-lg
                      text-gray-700 hover:text-pink-500
                      hover:bg-pink-50 transition">

                <i class="fas fa-boxes text-pink-500 fa-fw justify-self-center"></i>

                <span
                  class="font-semibold opacity-0 max-w-0 overflow-hidden
                         group-hover/sidebar:opacity-100
                         group-hover/sidebar:max-w-xs
                         transition-all duration-300 whitespace-nowrap">
                    Inventory
                </span>
            </a>

        </nav>
    </div>

    <!-- LOGOUT -->
    <form action="{{ route('admin.logout') }}" method="POST" class="p-6">
        @csrf
        <button type="submit"
                class="grid grid-cols-[40px_1fr] items-center
                       py-3 rounded-lg w-full
                       text-gray-700 hover:text-pink-500
                       hover:bg-pink-50 transition">

            <i class="fas fa-sign-out-alt text-pink-500 fa-fw justify-self-center"></i>

            <span
              class="font-semibold opacity-0 max-w-0 overflow-hidden
                     group-hover/sidebar:opacity-100
                     group-hover/sidebar:max-w-xs
                     transition-all duration-300 whitespace-nowrap">
                Logout
            </span>
        </button>
    </form>

</aside>