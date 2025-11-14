@extends('layouts.app')

@section('content')
<div class="bg-[#FFF6F6] min-h-screen py-10">
    <div class="max-w-7xl mx-auto px-6 flex flex-col lg:flex-row gap-6">

        {{-- LEFT: Cart Items --}}
        <div class="flex-1 bg-white rounded-lg shadow p-6">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-xl font-bold">Shopping Cart</h2>
                    <p class="text-gray-500">Review your items</p>
                </div>
                <form method="POST" action="{{ route('cart.clear') }}">
                    @csrf
                    <button class="flex items-center gap-2 text-red-500 hover:text-red-600 border border-red-200 hover:border-red-400 px-3 py-1.5 rounded-md text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Clear Cart
                    </button>
                </form>
            </div>

            @if(count($cart) > 0)
                @foreach($cart as $item)
                    <div class="flex items-center justify-between bg-[#FFF8F8] p-4 mb-4 rounded-lg shadow-sm">
                        <div class="flex items-center gap-4">
                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-24 h-24 object-cover rounded-lg">
                            <div>
                                <h3 class="font-semibold">{{ $item['name'] }}</h3>
                                <p class="text-gray-500 text-sm">Cake</p>
                                <button class="mt-2 bg-[#F9B3B0] hover:bg-[#F69491] text-white text-xs px-3 py-1 rounded">Customize</button>
                            </div>
                        </div>

                        <div class="flex items-center gap-6">
                            {{-- Quantity --}}
                            <form method="POST" action="{{ route('cart.update') }}" class="flex items-center gap-2">
                                @csrf
                                <input type="hidden" name="id" value="{{ $item['id'] }}">
                                <button name="action" value="decrease" class="text-gray-600 bg-gray-200 rounded-full w-6 h-6 flex items-center justify-center hover:bg-gray-300">−</button>
                                <span class="font-medium w-5 text-center">{{ $item['quantity'] }}</span>
                                <button name="action" value="increase" class="text-gray-600 bg-gray-200 rounded-full w-6 h-6 flex items-center justify-center hover:bg-gray-300">+</button>
                            </form>

                            {{-- Price --}}
                            <div class="text-right">
                                <p class="text-gray-700">₱{{ number_format($item['price'], 0) }}</p>
                                <p class="font-semibold text-[#F69491]">₱{{ number_format($item['price'] * $item['quantity'], 0) }}</p>
                            </div>

                            {{-- Delete --}}
                            <form method="POST" action="{{ route('cart.remove') }}">
                                @csrf
                                <input type="hidden" name="id" value="{{ $item['id'] }}">
                                <button class="text-gray-400 hover:text-red-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-gray-500 text-center py-10">Your cart is empty.</p>
            @endif
        </div>

        {{-- RIGHT: Order Summary --}}
        <div class="w-full lg:w-80">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-center bg-[#F9B3B0] text-white py-2 rounded">Order Summary</h3>
                <div class="mt-4 divide-y divide-gray-200 text-sm">
                    @php $total = 0; @endphp
                    @foreach($cart as $item)
                        @php $total += $item['price'] * $item['quantity']; @endphp
                        <div class="flex justify-between py-2">
                            <span>{{ $item['name'] }} (x{{ $item['quantity'] }})</span>
                            <span>₱{{ number_format($item['price'] * $item['quantity'], 0) }}</span>
                        </div>
                    @endforeach
                </div>

                @php
                    $downpayment = $total * 0.5;
                @endphp

                <div class="mt-4 border-t pt-4 text-sm">
                    <div class="flex justify-between">
                        <span>Required Downpayment (50%):</span>
                        <span>₱{{ number_format($downpayment, 0) }}</span>
                    </div>
                    <div class="flex justify-between font-semibold text-lg mt-3">
                        <span>Total:</span>
                        <span class="text-[#F69491]">₱{{ number_format($total, 0) }}</span>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="{{ route('checkout') }}" class="block text-center bg-[#F9B3B0] hover:bg-[#F69491] text-white py-3 rounded-lg font-semibold">Proceed to Checkout</a>
                    <a href="{{ route('catalog') }}" class="block text-center mt-3 border border-gray-300 py-3 rounded-lg font-semibold text-gray-600 hover:bg-gray-100">Continue Ordering</a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
