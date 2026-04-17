<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', "Yvonne's Cakes & Pastries")</title>

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- ✅ Vite CSS -->
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')

    <!-- Alpine.js (optional, kept as-is) -->
    <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body class="bg-gray-50 text-gray-900 overflow-y-auto min-h-screen flex flex-col">

    {{-- ✅ Navbar --}}
    @include('partials.navbar')

    <div id="toast-container"
     class="fixed top-5 left-1/2 -translate-x-1/2 z-[9999] space-y-3 flex flex-col items-center">
</div>

    {{-- ✅ Main Content --}}
    <main class="flex-1 w-full flex flex-col">
        @yield('content')
    </main>

    {{-- ✅ Conditional Footer --}}
    @if (!View::hasSection('no-footer'))
        @include('partials.footer')
    @endif
    <script>
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');

    if (!container) {
        console.error('Toast container not found');
        return;
    }

    const toast = document.createElement('div');

    let bgColor = 'bg-[#10a345]';
    let iconColor = 'text-[#10a345]';

    if (type === 'error') {
        bgColor = 'bg-red-600';
        iconColor = 'text-red-600';
    }

    toast.className = `
        ${bgColor} text-white px-8 py-3 rounded-full shadow-lg 
        flex items-center gap-3 font-semibold 
        transition-all duration-500 opacity-0 translate-y-[-10px]
    `;

    toast.innerHTML = `
        <div class="flex-shrink-0 w-6 h-6 bg-white rounded-full flex items-center justify-center">
            <svg class="w-4 h-4 ${iconColor}" fill="none" stroke="currentColor" stroke-width="4" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <span class="text-[15px] tracking-wide">${message}</span>
    `;

    container.appendChild(toast);

    // animate in
    setTimeout(() => {
        toast.classList.remove('opacity-0', 'translate-y-[-10px]');
    }, 10);

    // animate out
    setTimeout(() => {
        toast.classList.add('opacity-0', '-translate-y-4');
        setTimeout(() => toast.remove(), 400);
    }, 3000);
}
</script>
    {{-- ✅ Global Scripts --}}
    @stack('scripts')

    {{-- ✅ Page-specific JS --}}
    @yield('scripts')

</body>
</html>
