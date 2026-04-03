<header class="bg-white border-b border-pink-200 px-4 py-4
                flex flex-col sm:flex-row sm:justify-between sm:items-center
                text-center sm:text-left gap-2">

    <!-- Logo / Title -->
    <h1 class="text-xl sm:text-2xl font-serif text-pink-500">
        Yvonne's Cakes & Pastries
    </h1>

    <!-- User Greeting -->
    <div class="text-gray-700 text-sm sm:text-base">
        Welcome, 
        <span class="font-semibold text-pink-500">
            {{ session('admin_user')['username'] ?? 'Guest' }}
        </span>
    </div>
</header>