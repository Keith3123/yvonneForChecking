<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <title>@yield('title', 'Admin')</title>
    @vite('resources/css/app.css')
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="bg-white">
    <div class="flex h-screen overflow-hidden">

        <aside class="bg-white border-r border-pink-200">
            @include('partials.admin-sidebar')
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

    @yield('scripts')
    <div id="loginToast"
     class="fixed top-6 left-1/2 -translate-x-1/2 z-[9999]
            px-6 py-3 rounded-xl shadow-xl text-white font-semibold text-sm hidden">
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const message = @json(session('success'));

    if (message) {
        const t = document.getElementById('loginToast');
        t.textContent = message;
        t.classList.add('bg-green-600');
        t.classList.remove('hidden');

        setTimeout(() => {
            t.classList.add('hidden');
        }, 4000);
    }

});
</script>

</body>
</html>