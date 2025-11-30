<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <title>@yield('title', 'Admin')</title>
    @vite('resources/css/app.css')
</head>

<body class="bg-white">
    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar -->
        <aside class="bg-white border-r border-pink-200">
            @include('partials.admin-sidebar')
        </aside>

        <!-- RIGHT SIDE -->
        <div class="flex-1 flex flex-col overflow-hidden">

            <!-- Header -->
            <header class="shrink-0">
                @include('partials.admin-header')
            </header>

            <!-- MAIN CONTENT (scrollable) -->
            <main class="flex-1 overflow-y-auto p-4">
                @yield('content')
            </main>

        </div>
    </div>
</body>

</html>