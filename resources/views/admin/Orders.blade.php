@extends('layouts.admin')

@section('title', 'Order Management')

@section('content')
<div class="px-4 sm:px-10 py-6">

    {{-- Page Title --}}
    <h1 class="text-3xl sm:text-4xl font-bold text-gray-800">Order Management</h1>
    <p class="text-gray-500 mt-1">Manage and track all customer orders</p>

    {{-- Filters --}}
    <div class="mt-6 bg-white border border-pink-200 rounded-xl p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

            {{-- Search Bar --}}
            <div class="relative w-full lg:w-1/3">
                <input
                    type="text"
                    placeholder="Search by order ID"
                    class="w-full border border-pink-200 rounded-lg py-2 pl-10 pr-3 
                           focus:ring-pink-300 focus:outline-none text-sm sm:text-base"
                >
                <span class="absolute left-3 top-2.5 text-gray-400 text-sm sm:text-base">
                    🔍
                </span>
            </div>

            {{-- Status Buttons --}}
            <div class="flex flex-wrap gap-2">
                <button class="px-4 py-2 bg-black text-white rounded-lg text-sm sm:text-base">All (0)</button>
                <button class="px-4 py-2 border border-pink-300 rounded-lg text-gray-700 text-sm sm:text-base">Pending (0)</button>
                <button class="px-4 py-2 border border-pink-300 rounded-lg text-gray-700 text-sm sm:text-base">Accepted (0)</button>
                <button class="px-4 py-2 border border-pink-300 rounded-lg text-gray-700 text-sm sm:text-base">In Progress (0)</button>
                <button class="px-4 py-2 border border-pink-300 rounded-lg text-gray-700 text-sm sm:text-base">Done (0)</button>
            </div>
        </div>
    </div>

    {{-- Orders Table --}}
    <div class="mt-6 bg-white border border-pink-200 rounded-xl p-6">
        <h2 class="text-lg sm:text-xl font-semibold text-gray-700">Orders</h2>
        <p class="text-gray-500 text-sm sm:text-base">{{$orders->count() }} orders found</p>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full border-collapse text-sm sm:text-base">
                <thead>
                    <tr class="border-b text-left text-gray-600">
                        <th class="py-3 px-4 whitespace-nowrap">Order ID</th>
                        <th class="py-3 px-4 whitespace-nowrap">Name</th>
                        <th class="py-3 px-4 whitespace-nowrap">Date</th>
                        <th class="py-3 px-4 whitespace-nowrap">Quantity</th>
                        <th class="py-3 px-4 whitespace-nowrap">Delivery Date</th>
                        <th class="py-3 px-4 whitespace-nowrap">Payment</th>
                        <th class="py-3 px-4 whitespace-nowrap">Status</th>
                        <th class="py-3 px-4 whitespace-nowrap">Total</th>
                        <th class="py-3 px-4 whitespace-nowrap">Actions</th>
                    </tr>
                </thead>

               <tbody class="text-gray-700">
    @forelse($orders as $order)
        <tr class="border-b">
            <td class="py-3 px-4">{{ $order->orderID }}</td>
            <td class="py-3 px-4">{{ $order->customer ? $order->customer->firstName . ' ' .($order->customer->mi ? $order->customer->mi . '. ' : '') .$order->customer->lastName: 'Unknown'}}</td>
            <td class="py-3 px-4">{{ $order->orderDate->format('Y-m-d H:i') }}</td>
            <td class="py-3 px-4">{{ $order->orderItems->sum('qty') }}</td>
            <td class="py-3 px-4">{{ $order->deliveryDate ? $order->deliveryDate->format('Y-m-d') : 'N/A' }}</td>
            <td class="py-3 px-4">{{ $order->paymentStatus }}</td>
            <td class="py-3 px-4">{{ $order->status }}</td>
            <td class="py-3 px-4">₱{{ number_format($order->totalAmount, 2) }}</td>
            <td class="py-3 px-4 flex gap-2">
                <a href="#" class="text-blue-600 hover:underline">View</a>
                <a href="#" class="text-red-600 hover:underline">Cancel</a>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="8" class="py-10 text-center text-gray-400">
                <div class="flex flex-col items-center">
                    <span class="text-4xl mb-2">📄</span>
                    No orders found
                </div>
            </td>
        </tr>
    @endforelse
</tbody>
            </table>
        </div>
    </div>

</div>
@endsection