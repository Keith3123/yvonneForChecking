<!DOCTYPE html>
<html lang="en">
<script src="//unpkg.com/alpinejs" defer></script>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', "Yvonne's Cakes & Pastries")</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 text-gray-900 overflow-y-auto min-h-screen flex flex-col">

    {{-- ✅ Global Navbar --}}
    @include('partials.navbar')

    {{-- ✅ Main Page Content --}}
    <main class="flex-1 w-full">
        @yield('content')
    </main>

    {{-- ✅ Conditional Footer (auto-hides if @section('no-footer') is declared) --}}
    @if (!View::hasSection('no-footer'))
        @include('partials.footer')
    @endif

    {{-- ✅ Scripts pushed from child views --}}
    @stack('scripts')

</body>
</html>
