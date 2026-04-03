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
        <nav class="space-y-1 relative">
            @php
                $user = session('admin_user') ?? ['username'=>'guest','roleID'=>0];
                $links = [
                    ['route'=>'admin.dashboard', 'label'=>'Dashboard', 'icon'=>'fas fa-chart-bar', 'roles'=>[1]],
                    ['route'=>'admin.orders', 'label'=>'Orders', 'icon'=>'fas fa-shopping-cart', 'roles'=>[3]],
                    ['route'=>'admin.products', 'label'=>'Products', 'icon'=>'fas fa-box-open', 'roles'=>[6]],
                    ['route'=>'admin.salesreport', 'label'=>'Reports', 'icon'=>'fas fa-file-alt', 'roles'=>[5]],
                    ['route'=>'admin.users', 'label'=>'Users', 'icon'=>'fas fa-users', 'roles'=>[1]],
                    ['route'=>'admin.paluwagan', 'label'=>'Paluwagan', 'icon'=>'fas fa-wallet', 'roles'=>[4]],
                    ['route'=>'admin.inventory', 'label'=>'Inventory', 'icon'=>'fas fa-boxes', 'roles'=>[2]],
                ];
            @endphp

            @foreach($links as $link)
                @php
                    $enabled = $user['username'] === 'masteradmin' || in_array($user['roleID'], $link['roles']);
                @endphp

                <div class="relative group tooltip-wrapper">
                    <a href="{{ $enabled ? route($link['route']) : '#' }}"
                       class="grid grid-cols-[40px_1fr] items-center py-3 rounded-lg
                              text-gray-700 hover:text-pink-500 hover:bg-pink-50 transition
                              {{ $enabled ? '' : 'pointer-events-none opacity-50 cursor-not-allowed' }}">
                        <i class="{{ $link['icon'] }} text-pink-500 fa-fw justify-self-center"></i>
                        <span
                          class="font-semibold opacity-0 max-w-0 overflow-hidden
                                 group-hover/sidebar:opacity-100
                                 group-hover/sidebar:max-w-xs
                                 transition-all duration-300 whitespace-nowrap">
                            {{ $link['label'] }}
                        </span>
                    </a>

                    @unless($enabled)
                        <span class="access-denied-tooltip absolute px-2 py-1 rounded bg-gray-800 text-white text-xs
                                     opacity-0 pointer-events-none z-50">
                            {{ $link['label'] }} - Access Denied
                        </span>
                    @endunless
                </div>
            @endforeach
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

<!-- TOOLTIP FOLLOW SCRIPT -->
<script>
    document.querySelectorAll('.tooltip-wrapper').forEach(wrapper => {
        const tooltip = wrapper.querySelector('.access-denied-tooltip');
        if (!tooltip) return;

        wrapper.addEventListener('mousemove', e => {
            tooltip.style.left = (e.pageX + 10) + 'px';
            tooltip.style.top = (e.pageY + 10) + 'px';
            tooltip.style.opacity = 1;
        });

        wrapper.addEventListener('mouseleave', () => {
            tooltip.style.opacity = 0;
        });
    });
</script>

<style>
    /* optional smooth fade for tooltip */
    .access-denied-tooltip {
        transition: opacity 0.2s ease, transform 0.2s ease;
        transform: translate(0,0);
        white-space: nowrap;
    }
</style>