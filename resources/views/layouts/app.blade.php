<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', "Yvonne's Cakes & Pastries")</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 text-gray-900">

    {{-- ✅ Global Navbar --}}
    @include('partials.navbar')

    {{-- ✅ Main Page Content --}}
    <main class="min-h-screen">
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
