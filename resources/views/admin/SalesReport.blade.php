@extends('layouts.admin')

@section('title', 'Sales Report')

@section('content')
<main>
    <div class="px-4 md:px-10 py-8">

        <!-- Title -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800">Sales Reporting</h2>
                <p class="text-gray-500 mt-1 text-sm md:text-base">
                    Manage and track all customer orders
                </p>
            </div>

            <div class="flex gap-2">
                <button class="px-4 py-2 bg-white border rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                    Export CSV
                </button>
                <button class="px-4 py-2 bg-white border rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                    Export PDF
                </button>
            </div>
        </div>

        <!-- Report Period -->
        <div class="border rounded-xl p-4 md:p-6 bg-white shadow-sm mb-8">
            <div class="flex flex-col md:flex-row items-start md:items-center gap-4">

                <div class="flex items-center gap-2 text-gray-600">
                    <span class="text-lg">📅</span>
                    <span class="font-medium text-sm md:text-base">Report Period:</span>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button class="px-5 py-2 bg-pink-500 text-white rounded-lg text-sm md:text-base">
                        Daily
                    </button>
                    <button class="px-5 py-2 border border-gray-300 rounded-lg text-sm md:text-base">
                        Weekly
                    </button>
                    <button class="px-5 py-2 border border-gray-300 rounded-lg text-sm md:text-base">
                        Monthly
                    </button>
                </div>

                <div class="ml-auto">
                    <p class="text-xs text-gray-400">Last updated: <span class="text-gray-700">—</span></p>
                </div>

            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-6">

            <div class="p-4 md:p-6 border rounded-xl bg-white shadow-sm">
                <p class="text-xs md:text-sm text-gray-500">Total Orders</p>
                <h3 class="text-2xl md:text-3xl font-bold mt-1">0</h3>
                <p class="text-gray-400 text-xs md:text-sm">Today</p>
            </div>

            <div class="p-4 md:p-6 border rounded-xl bg-white shadow-sm">
                <p class="text-xs md:text-sm text-gray-500">Total Revenue</p>
                <h3 class="text-2xl md:text-3xl font-bold mt-1">₱0</h3>
                <p class="text-gray-400 text-xs md:text-sm">Today</p>
            </div>

            <div class="p-4 md:p-6 border rounded-xl bg-white shadow-sm">
                <p class="text-xs md:text-sm text-gray-500">Completed Orders</p>
                <h3 class="text-2xl md:text-3xl font-bold mt-1">0</h3>
                <p class="text-gray-400 text-xs md:text-sm">Today</p>
            </div>

            <div class="p-4 md:p-6 border rounded-xl bg-white shadow-sm">
                <p class="text-xs md:text-sm text-gray-500">Avg Order Value</p>
                <h3 class="text-2xl md:text-3xl font-bold mt-1">₱0</h3>
                <p class="text-gray-400 text-xs md:text-sm">Today</p>
            </div>

        </div>

        <!-- Tabs Bar -->
        <div class="w-full flex overflow-x-auto md:overflow-hidden bg-gray-100 text-gray-600 font-medium rounded-xl shadow-sm">
            <button class="flex-1 min-w-[150px] p-3 text-center hover:bg-gray-200 text-sm md:text-base border-r border-gray-200">
                Sales Summary
            </button>
            <button class="flex-1 min-w-[150px] p-3 text-center hover:bg-gray-200 text-sm md:text-base border-r border-gray-200">
                By Category
            </button>
            <button class="flex-1 min-w-[150px] p-3 text-center hover:bg-gray-200 text-sm md:text-base border-r border-gray-200">
                By Payment
            </button>
            <button class="flex-1 min-w-[150px] p-3 text-center hover:bg-gray-200 text-sm md:text-base">
                Product Performance
            </button>
        </div>

        <!-- Empty State / Placeholder -->
        <div class="my-6 flex flex-col items-center justify-center text-gray-400 border rounded-xl p-8 bg-white shadow-sm">
            <div class="text-4xl mb-2">📊</div>
            <div class="text-sm md:text-base">
                No data available yet
            </div>
            <p class="text-xs md:text-sm mt-2">
                Once there are orders, your sales report will show insights here.
            </p>
        </div>

    </div>
</main>
@endsection
