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

    {{-- ✅ Main Content --}}
    <main class="flex-1 w-full flex flex-col">
        @yield('content')
    </main>

    {{-- ✅ Conditional Footer --}}
    @if (!View::hasSection('no-footer'))
        @include('partials.footer')
    @endif

    {{-- ✅ Global Scripts --}}
    @stack('scripts')

    {{-- ✅ Page-specific JS --}}
    @yield('scripts')

</body>
</html>
