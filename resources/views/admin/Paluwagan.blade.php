@extends('layouts.admin')

@section('title', 'Paluwagan')

@section('content')
<div class="px-4 sm:px-10 py-8">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Paluwagan Management</h1>
            <p class="text-gray-500 -mt-1 mb-2 text-sm sm:text-base">Monitor and manage paluwagan orders</p>
        </div>

        <div class="flex gap-2 flex-wrap">
            <button class="bg-pink-600 hover:bg-pink-700 text-white px-6 py-3 rounded-lg shadow w-full md:w-auto transition">
                + Add Paluwagan
            </button>
            <button class="px-6 py-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                Export Report
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-5 mb-8">

        {{-- Active Subscription --}}
    <div class="bg-gradient-to-br from-pink-50 to-white border border-pink-100 shadow-sm hover:shadow-md transition rounded-xl p-4 md:p-5">
        <div class="flex items-start justify-between">
            <p class="text-gray-600 font-semibold text-sm sm:text-base">
                Active Subscription
            </p>
            <div class="bg-white/80 rounded-full p-2 border border-pink-100">
                <i class="fas fa-file-contract text-pink-500"></i>
            </div>
        </div>

        <h3 class="text-xl sm:text-2xl font-bold mt-4">0</h3>
        <p class="text-gray-400 text-xs mt-1">
            Total active subscriptions
        </p>
    </div>

    {{-- Collected Revenue --}}
    <div class="bg-gradient-to-br from-pink-50 to-white border border-pink-100 shadow-sm hover:shadow-md transition rounded-xl p-4 md:p-5">
        <div class="flex items-start justify-between">
            <p class="text-gray-600 font-semibold text-sm sm:text-base">
                Collected Revenue
            </p>
            <div class="bg-white/80 rounded-full p-2 border border-pink-100">
                <i class="fas fa-circle-check text-pink-500"></i>
            </div>
        </div>

        <h3 class="text-xl sm:text-2xl font-bold mt-4">₱0</h3>
        <p class="text-gray-400 text-xs mt-1">
            Total revenue collected
        </p>
    </div>

    {{-- Expected Revenue --}}
    <div class="bg-gradient-to-br from-pink-50 to-white border border-pink-100 shadow-sm hover:shadow-md transition rounded-xl p-4 md:p-5">
        <div class="flex items-start justify-between">
            <p class="text-gray-600 font-semibold text-sm sm:text-base">
                Expected Revenue
            </p>
            <div class="bg-white/80 rounded-full p-2 border border-pink-100">
                <i class="fas fa-calendar-days text-pink-500"></i>
            </div>
        </div>

        <h3 class="text-xl sm:text-2xl font-bold mt-4">₱0</h3>
        <p class="text-gray-400 text-xs mt-1">
            Revenue expected for the month
        </p>
    </div>

    {{-- Late Payments --}}
    <div class="bg-gradient-to-br from-pink-50 to-white border border-pink-100 shadow-sm hover:shadow-md transition rounded-xl p-4 md:p-5">
        <div class="flex items-start justify-between">
            <p class="text-gray-600 font-semibold text-sm sm:text-base">
                Late Payments
            </p>
            <div class="bg-white/80 rounded-full p-2 border border-pink-100">
                <i class="fas fa-triangle-exclamation text-pink-500"></i>
            </div>
        </div>

        <h3 class="text-xl sm:text-2xl font-bold mt-4">0</h3>
        <p class="text-gray-400 text-xs mt-1">
            Late payments this month
        </p>
    </div>

</div>
    {{-- Search + Filters --}}
    <div class="border rounded-xl border-pink-200 p-5 bg-white shadow-sm">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            {{-- Search Bar --}}
           <div class="relative w-full md:w-1/2">
    <input 
        type="text"
        placeholder="Search by package name or ID..."
        class="w-full border rounded-lg pl-10 p-3 focus:outline-none focus:ring-2 focus:ring-pink-500"
    >
    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-lg"></i>
</div>

            {{-- Filters --}}
            <div class="flex gap-2 flex-wrap">
                <button class="px-4 py-2 bg-pink-600 text-white rounded-lg text-sm sm:text-base transition hover:bg-pink-700">
                    All (0)
                </button>
                <button class="px-4 py-2 border border-pink-300 rounded-lg text-sm sm:text-base hover:bg-pink-100 transition">
                    On Track (0)
                </button>
                <button class="px-4 py-2 border border-pink-300 rounded-lg text-sm sm:text-base hover:bg-pink-100 transition">
                    Late (0)
                </button>
                <button class="px-4 py-2 border border-pink-300 rounded-lg text-sm sm:text-base hover:bg-pink-100 transition">
                    Fully Paid (0)
                </button>
            </div>

        </div>
    </div>

    {{-- Subscription Table --}}
    <div class="border border-pink-200 mt-8 rounded-xl p-6 bg-white shadow-sm">

        <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-4">
            <div>
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Subscriptions</h2>
                <p class="text-gray-500 text-xs sm:text-sm">0 subscription found</p>
            </div>

            <div class="flex gap-2">
                <button class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-pink-50 transition">
                    Refresh
                </button>
                <button class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-pink-50 transition">
                    Export CSV
                </button>
            </div>
        </div>

        {{-- Table (Scrollable on Mobile) --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm sm:text-base min-w-max">
                <thead class="border-b text-gray-600 bg-gray-50">
                    <tr>
                        <th class="py-2 px-3 whitespace-nowrap">Subscription ID</th>
                        <th class="py-2 px-3 whitespace-nowrap">Package</th>
                        <th class="py-2 px-3 whitespace-nowrap">Progress</th>
                        <th class="py-2 px-3 whitespace-nowrap">Monthly</th>
                        <th class="py-2 px-3 whitespace-nowrap">Paid</th>
                        <th class="py-2 px-3 whitespace-nowrap">Remaining</th>
                        <th class="py-2 px-3 whitespace-nowrap">Due Date</th>
                        <th class="py-2 px-3 whitespace-nowrap">Status</th>
                        <th class="py-2 px-3 whitespace-nowrap">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    {{-- Empty State --}}
                    <tr>
                        <td colspan="9" class="py-10 text-center text-gray-400">
                            <div class="flex flex-col items-center">
                                <span class="text-4xl mb-2">📄</span>
                                No subscription found
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

</div>
@endsection
