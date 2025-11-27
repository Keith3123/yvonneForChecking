@extends('layouts.app')

@section('no-footer')
@endsection

@section('content')
<div class="bg-[#FFF8F5] min-h-screen flex justify-center py-10 px-4 overflow-y-auto">
    <div class="w-full max-w-5xl">

        {{-- Header --}}
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">My Orders</h2>
                <p class="text-gray-600 text-sm">
                    Track the status of your orders and view order history
                </p>
            </div>
            <a href="{{ route('catalog') }}" 
               class="bg-orange-200 hover:bg-orange-300 px-4 py-2 rounded font-semibold text-sm">
                Go to Catalog
            </a>
        </div>

        {{-- Orders List --}}
        <div class="space-y-6">
            @forelse($orders as $order)
                <div class="border border-red-200 bg-white rounded-md shadow-sm p-6 relative mb-6">

                    {{-- Order Header --}}
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800">Order #{{ $order->orderID }}</h2>
                            <p class="text-sm text-gray-500">
                                Placed on {{ $order->orderDate->format('F d, Y \a\t h:i A') }}
                            </p>
                        </div>
                        <span class="text-xs font-semibold 
                                     {{ $order->status == 'DELIVERED' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }} 
                                     px-3 py-1 rounded-full">
                            {{ $order->status }}
                        </span>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="w-full bg-gray-200 h-2 rounded-full mb-3">
                        @php
                            $progress = $order->status == 'DELIVERED' ? 100 : 50;
                        @endphp
                        <div class="h-2 bg-green-500 rounded-full" style="width: {{ $progress }}%;"></div>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 mb-6">
                        <span>Order Placed</span>
                        <span>In Progress</span>
                        <span>Delivered</span>
                    </div>

                    {{-- Order Items --}}
                    @foreach($order->orderItems as $item)
                        <div class="flex space-x-4 mb-6">
                            <img src="{{ asset('images/sample_food.jpg') }}" alt="Food Package" class="w-24 h-24 object-cover rounded-md border">
                            <div>
                                <h3 class="font-semibold text-gray-800">{{ $item->product->name ?? 'Product Name' }}</h3>
                                <p class="text-sm text-gray-500">Quantity: {{ $item->qty }}</p>
                                <p class="text-gray-800 font-semibold mt-2">₱{{ $item->subtotal }}</p>
                            </div>
                        </div>
                    @endforeach

                    {{-- Delivery & Payment Info --}}
                    <div class="grid md:grid-cols-2 gap-6 border-t border-gray-200 pt-4 mb-6">
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Delivery Information</h4>
                            <p class="text-sm text-gray-600 mb-1">
                                <i class="far fa-calendar-alt mr-1"></i> {{ $order->deliveryDate ? $order->deliveryDate->format('Y-m-d h:i A') : 'Not Set' }}
                            </p>
                            <p class="text-sm text-gray-600"><i class="fas fa-map-marker-alt mr-1"></i> {{ $order->deliveryAddress }}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Payment Information</h4>
                            <div class="text-sm text-gray-700 space-y-1">
                                <p>Subtotal: <span class="float-right font-semibold">₱{{ $order->totalAmount }}</span></p>
                                <p>Downpayment (50%): <span class="float-right text-blue-600 font-medium">₱{{ $order->totalAmount / 2 }}</span></p>
                                <p>Remaining Balance: <span class="float-right">₱{{ $order->totalAmount / 2 }}</span></p>
                                <p>Payment Method: <span class="float-right font-medium">{{ $order->paymentStatus == 'PENDING' ? 'COD/GCASH' : $order->paymentStatus }}</span></p>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex gap-3 mt-4">
                        <button class="viewReceiptBtn bg-black text-white text-sm font-semibold px-5 py-2 rounded hover:bg-gray-800 transition">
                            <i class="far fa-file-alt mr-2"></i> View Receipt
                        </button>
                        <button class="cancelOrderBtn border border-red-300 text-red-600 text-sm font-semibold px-5 py-2 rounded hover:bg-red-50 transition">
                            <i class="fas fa-times mr-2"></i> Cancel Order
                        </button>
                    </div>

                </div>
            @empty
                <p class="text-gray-500 text-center">You have not placed any orders yet.</p>
            @endforelse
        </div>
    </div>
</div>


@endsection
