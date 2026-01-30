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
            <button class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition">
                + Add Paluwagan
            </button>
            <button class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                Export Report
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-5 mb-8">

        <div class="border border-pink-200 bg-white p-5 sm:p-6 rounded-xl shadow-sm">
            <p class="text-gray-600 font-semibold text-sm sm:text-base">Active Subscription</p>
            <div class="flex items-center justify-between mt-2">
                <p class="text-2xl sm:text-3xl font-bold">0</p>
                <span class="text-blue-600 text-xl sm:text-2xl">📄</span>
            </div>
            <p class="text-gray-400 text-xs mt-2">Total active subscriptions</p>
        </div>

        <div class="border border-pink-200 bg-white p-5 sm:p-6 rounded-xl shadow-sm">
            <p class="text-gray-600 font-semibold text-sm sm:text-base">Collected Revenue</p>
            <div class="flex items-center justify-between mt-2">
                <p class="text-2xl sm:text-3xl font-bold">0</p>
                <span class="text-green-600 text-xl sm:text-2xl">✔</span>
            </div>
            <p class="text-gray-400 text-xs mt-2">Total revenue collected</p>
        </div>

        <div class="border border-pink-200 bg-white p-5 sm:p-6 rounded-xl shadow-sm">
            <p class="text-gray-600 font-semibold text-sm sm:text-base">Expected Revenue</p>
            <div class="flex items-center justify-between mt-2">
                <p class="text-2xl sm:text-3xl font-bold">₱0</p>
                <span class="text-purple-600 text-xl sm:text-2xl">📅</span>
            </div>
            <p class="text-gray-400 text-xs mt-2">Revenue expected for the month</p>
        </div>

        <div class="border border-pink-200 bg-white p-5 sm:p-6 rounded-xl shadow-sm">
            <p class="text-gray-600 font-semibold text-sm sm:text-base">Late Payments</p>
            <div class="flex items-center justify-between mt-2">
                <p class="text-2xl sm:text-3xl font-bold">0</p>
                <span class="text-red-600 text-xl sm:text-2xl">⚠️</span>
            </div>
            <p class="text-gray-400 text-xs mt-2">Late payments this month</p>
        </div>

    </div>

    {{-- Search + Filters --}}
    <div class="border rounded-xl border-pink-200 p-5 bg-white shadow-sm">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            {{-- Search Bar --}}
            <div class="flex items-center border border-gray-300 rounded-lg px-3 py-2 w-full md:w-1/2">
                <span class="text-gray-400 mr-2 text-sm sm:text-base">🔍</span>
                <input 
                    type="text" 
                    class="w-full outline-none text-sm sm:text-base" 
                    placeholder="Search by package name or ID..."
                >
            </div>

            {{-- Filters --}}
            <div class="flex gap-2 flex-wrap">
                <button class="px-4 py-2 bg-black text-white rounded-lg text-sm sm:text-base transition hover:bg-pink-100">
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
                <button class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                    Refresh
                </button>
                <button class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
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
