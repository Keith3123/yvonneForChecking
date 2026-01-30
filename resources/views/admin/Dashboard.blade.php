@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="bg-white min-h-screen m-3 md:m-6 px-2 md:px-4">

  <main class="flex-1">

    <h2 class="text-2xl md:text-3xl font-bold mb-2">Dashboard Overview</h2>
    <p class="text-gray-600 mb-6 md:mb-8">Welcome to your Admin Dashboard</p>

    <!-- CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-10">

      <div class="bg-gradient-to-br from-pink-50 to-white border border-pink-100 p-4 md:p-5 rounded-xl shadow-sm hover:shadow-md transition">
        <div class="flex items-start justify-between">
          <div class="text-gray-600">Total Revenue</div>
          <div class="bg-white/80 rounded-full p-2 border border-pink-100">
            <i class="fas fa-wallet text-pink-500"></i>
          </div>
        </div>
        <h3 class="text-xl md:text-2xl font-bold mt-4">₱{{ number_format($totalRevenue, 2) }}</h3>
        <p class="text-gray-500 text-sm mt-1">From {{ $completedOrders }} completed orders</p>
      </div>

      <div class="bg-gradient-to-br from-pink-50 to-white border border-pink-100 p-4 md:p-5 rounded-xl shadow-sm hover:shadow-md transition">
        <div class="flex items-start justify-between">
          <div class="text-gray-600">Pending Orders</div>
          <div class="bg-white/80 rounded-full p-2 border border-pink-100">
            <i class="fas fa-hourglass-half text-pink-500"></i>
          </div>
        </div>
        <h3 class="text-xl md:text-2xl font-bold mt-4">{{ $pendingOrders }}</h3>
        <p class="text-gray-500 text-sm mt-1">{{ $pendingOrders }} orders in progress</p>
      </div>

      <div class="bg-gradient-to-br from-pink-50 to-white border border-pink-100 p-4 md:p-5 rounded-xl shadow-sm hover:shadow-md transition">
        <div class="flex items-start justify-between">
          <div class="text-gray-600">Active Paluwagan</div>
          <div class="bg-white/80 rounded-full p-2 border border-pink-100">
            <i class="fas fa-hand-holding-usd text-pink-500"></i>
          </div>
        </div>
        <h3 class="text-xl md:text-2xl font-bold mt-4">₱{{ number_format($activePaluwagan, 2) }}</h3>
        <p class="text-gray-500 text-sm mt-1">{{ number_format($collected, 2) }} collected</p>
      </div>

      <div class="bg-gradient-to-br from-pink-50 to-white border border-pink-100 p-4 md:p-5 rounded-xl shadow-sm hover:shadow-md transition">
        <div class="flex items-start justify-between">
          <div class="text-gray-600">Low Stock Items</div>
          <div class="bg-white/80 rounded-full p-2 border border-pink-100">
            <i class="fas fa-box-open text-pink-500"></i>
          </div>
        </div>
        <h3 class="text-xl md:text-2xl font-bold mt-4">{{ $lowStock }}</h3>
        <p class="text-gray-500 text-sm mt-1">Requires attention</p>
      </div>

    </div>

    <!-- RECENT ORDERS + QUICK ACTIONS -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">

      <!-- RECENT ORDERS -->
      <div class="col-span-1 lg:col-span-2 bg-white border border-pink-100 p-6 md:p-8 rounded-xl
                  flex flex-col justify-center items-center text-gray-400 min-h-[250px] shadow-sm">
        <h3 class="text-xl md:text-2xl font-bold text-gray-700 mb-2">Recent Orders</h3>
        <p class="text-gray-500 mb-4">Latest customer orders</p>

        @if($completedOrders > 0 || $pendingOrders > 0)
          <p class="text-gray-600">Recent orders will appear here.</p>
        @else
          <p class="text-gray-500">No orders yet</p>
        @endif
      </div>

      <!-- QUICK ACTIONS -->
      <div class="bg-white border border-pink-100 p-6 md:p-8 rounded-xl shadow-sm">
        <h3 class="text-lg md:text-xl font-bold mb-2">Quick Actions</h3>
        <p class="text-gray-500 mb-3 md:mb-5">Common administrative tasks</p>

        <div class="space-y-3 md:space-y-4">
          <div class="flex justify-between items-center p-3 border border-pink-100 rounded-lg bg-pink-50">
            <span>Pending Orders</span>
            <span class="font-bold">{{ $pendingOrders }}</span>
          </div>
          <div class="flex justify-between items-center p-3 border border-pink-100 rounded-lg bg-pink-50">
            <span>Low Stock Ingredients</span>
            <span class="font-bold">{{ $lowStock }}</span>
          </div>
          <div class="flex justify-between items-center p-3 border border-pink-100 rounded-lg bg-pink-50">
            <span>Total Users</span>
            <span class="font-bold">{{ $totalCustomers }}</span>
          </div>
          <div class="flex justify-between items-center p-3 border border-pink-100 rounded-lg bg-pink-50">
            <span>Total Ingredients</span>
            <span class="font-bold">{{ $totalProducts }}</span>
          </div>
        </div>
      </div>

    </div>

  </main>

</div>
@endsection
