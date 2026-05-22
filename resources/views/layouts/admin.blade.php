<!-- layouts/admin.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <title>@yield('title', 'Admin')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])  {{-- ✅ app.js loads bootstrap.js → echo.js --}}
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="bg-white">
    <div class="flex h-screen overflow-hidden">

        <aside class="bg-white border-r border-pink-200">
            @include('partials.admin-sidebar')  {{-- ✅ sidebar link stays HERE, inside the layout --}}
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="shrink-0">
                @include('partials.admin-header')
            </header>
            <main class="flex-1 overflow-y-auto p-4">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- ✅ REMOVED the stray <a> tag that was floating outside the main div --}}

    @yield('scripts')

    {{-- Toast --}}
    <div id="loginToast"
         class="fixed top-6 left-1/2 -translate-x-1/2 z-[9999]
                px-6 py-3 rounded-xl shadow-xl text-white font-semibold text-sm hidden">
    </div>

    {{-- Expired Ingredients Notification --}}
    <div id="expiryNotif"
         class="hidden fixed bottom-6 right-6 z-[9998] w-80 bg-white border border-red-200 rounded-2xl shadow-2xl overflow-hidden">
        <div class="bg-red-500 px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2 text-white font-semibold text-sm">
                <i class="fas fa-triangle-exclamation"></i>
                <span>Expired Ingredients</span>
            </div>
            <button onclick="dismissExpiryNotif()"
                    class="text-white/80 hover:text-white text-xl leading-none transition">&times;</button>
        </div>
        <div class="px-4 py-3 max-h-56 overflow-y-auto">
            <p class="text-xs text-gray-500 mb-2">The following ingredients have expired stock:</p>
            <ul id="expiryList" class="space-y-1"></ul>
        </div>
        <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
            <label class="flex items-center gap-2 text-xs text-gray-400 cursor-pointer select-none">
                <input type="checkbox" id="expiryDontShow" class="accent-red-500">
                Don't show again today
            </label>
            <a href="{{ route('admin.inventory') }}"
               class="text-xs font-semibold text-red-500 hover:text-red-700 transition">
                View Inventory →
            </a>
        </div>
    </div>

    {{-- ============================================
         NEW ORDER NOTIFICATION PANEL
    ============================================ --}}
    <div id="admin-order-notif"
         class="hidden fixed top-5 right-5 z-[9999] w-80 bg-white rounded-2xl
                shadow-2xl border-l-4 border-pink-500 overflow-hidden">
        <div class="bg-gradient-to-r from-pink-500 to-rose-500 px-4 py-3
                    flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-bell text-white text-sm animate-bounce"></i>
                </div>
                <span class="text-white font-bold text-sm">New Orders!</span>
            </div>
            <button id="notif-mute-btn" title="Mute sound" onclick="toggleMute()"
                    class="text-white/80 hover:text-white transition text-xs">
                <i id="mute-icon" class="fas fa-volume-up"></i>
            </button>
        </div>
        <div class="px-4 py-4">
            <p class="text-gray-800 font-semibold text-sm" id="notif-main-text">
                You have <span class="text-pink-600 font-bold text-lg">1</span> new order
            </p>
            <p class="text-gray-500 text-xs mt-1">Click to view and manage</p>
            <div id="notif-order-list" class="mt-3 space-y-2 max-h-40 overflow-y-auto"></div>
            <button onclick="goToNewOrders()"
                    class="w-full mt-4 bg-pink-500 hover:bg-pink-600 text-white
                           font-semibold py-2.5 rounded-xl text-sm transition shadow-md
                           flex items-center justify-center gap-2">
                <i class="fas fa-eye"></i> View New Orders
            </button>
        </div>
        <div class="h-1 bg-gradient-to-r from-pink-400 via-rose-400 to-orange-400 animate-pulse"></div>
    </div>

    <style>
    @keyframes slideInRight {
        from { transform: translateX(120%); opacity: 0; }
        to   { transform: translateX(0);    opacity: 1; }
    }
    @keyframes slideOutRight {
        from { transform: translateX(0);    opacity: 1; }
        to   { transform: translateX(120%); opacity: 0; }
    }
    .notif-slide-in  { animation: slideInRight 0.4s ease-out forwards; }
    .notif-slide-out { animation: slideOutRight 0.3s ease-in  forwards; }
    </style>

    {{-- All Echo listener logic is now inside admin-echo.js --}}

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Login toast
        const message = @json(session('success'));
        if (message) {
            const t = document.getElementById('loginToast');
            t.textContent = message;
            t.classList.add('bg-green-600');
            t.classList.remove('hidden');
            setTimeout(() => t.classList.add('hidden'), 4000);
        }

        // Expired ingredients
        const STORAGE_KEY = 'expiryNotifDismissedOn';
        const today       = new Date().toISOString().slice(0, 10);
        const dismissedOn = localStorage.getItem(STORAGE_KEY);
        if (dismissedOn === today) return;

        fetch('{{ route("admin.inventory.expired") }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data.length) return;
            const list  = document.getElementById('expiryList');
            const notif = document.getElementById('expiryNotif');
            data.forEach(item => {
                const li = document.createElement('li');
                li.className = 'flex items-center justify-between text-xs py-1 border-b border-gray-50 last:border-0';
                li.innerHTML = `
                    <span class="font-medium text-gray-700">${item.name}</span>
                    <span class="text-red-400 whitespace-nowrap ml-2">Expired ${item.expiry_date}</span>
                `;
                list.appendChild(li);
            });
            notif.classList.remove('hidden');
        })
        .catch(() => {});
    });

    function dismissExpiryNotif() {
        document.getElementById('expiryNotif').classList.add('hidden');
        if (document.getElementById('expiryDontShow').checked) {
            localStorage.setItem('expiryNotifDismissedOn', new Date().toISOString().slice(0, 10));
        }
    }
    </script>

    {{-- ✅ This loads bootstrap.js → echo.js (sets window.Echo) + admin-echo.js (sets up listener) --}}
    @vite('resources/js/admin-echo.js')

</body>
</html>