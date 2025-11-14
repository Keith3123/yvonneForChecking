@extends('layouts.app')

@section('no-footer')
@endsection

@section('content')
<div class="bg-[#FFF6F6] min-h-screen py-10">
    <div class="max-w-6xl mx-auto px-6">

        {{-- Back Button --}}
        <a href="{{ route('catalog') }}" class="flex items-center text-gray-700 mb-8 hover:text-[#F69491]">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <span class="text-lg font-medium">Back</span>
        </a>

        {{-- Header --}}
        <h1 class="text-2xl font-bold mb-1">Checkout</h1>
        <p class="text-gray-500 mb-8">Complete your order details</p>

        {{-- Main Section --}}
        <div class="grid lg:grid-cols-3 gap-8">
            {{-- LEFT: Delivery + Payment --}}
            <div class="lg:col-span-2 bg-white rounded-lg shadow p-6 border border-gray-200">

                {{-- Delivery Information --}}
                <div class="mb-8">
                    <h2 class="font-semibold mb-1">Delivery Information</h2>
                    <p class="text-sm text-gray-500 mb-4">Please provide your delivery details (2–3 days advance reservation required)</p>

                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Delivery Date</label>
                            <input type="date"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 outline-none focus:ring-2 focus:ring-[#F9B3B0]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Delivery Time</label>
                            <select class="w-full border border-gray-300 rounded-md px-3 py-2 outline-none focus:ring-2 focus:ring-[#F9B3B0]">
                                <option>9:30 - 11:00</option>
                                <option>1:00 - 3:00</option>
                                <option>3:00 - 5:00</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium mb-1">Complete Delivery Address</label>
                        <input type="text" placeholder="Enter delivery address"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 outline-none focus:ring-2 focus:ring-[#F9B3B0]">
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium mb-1">Message for Admin (Optional)</label>
                        <textarea rows="3" maxlength="200" placeholder="Any special instructions or notes for your order..."
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 outline-none focus:ring-2 focus:ring-[#F9B3B0]"></textarea>
                        <p class="text-xs text-gray-400 mt-1 text-right">0/200 Characters</p>
                    </div>

                    {{-- Note Box --}}
                    <div class="bg-[#FFF0F0] text-sm text-red-500 border border-[#F9B3B0] p-3 mt-5 rounded-md">
                        <strong>Note:</strong> You will pay ₱{{ number_format($downpayment, 0) }} (50% downpayment)
                        when the order is delivered. The remaining balance of ₱{{ number_format($balance, 0) }}
                        will be collected upon delivery.
                    </div>
                </div>

                {{-- Payment Information --}}
                <div>
                    <h2 class="font-semibold mb-1">Payment Information</h2>
                    <p class="text-sm text-gray-500 mb-4">Choose your payment for the 50% downpayment</p>

                    <div class="flex gap-6 mb-5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="payment" value="gcash" checked>
                            <span>Gcash</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="payment" value="cod">
                            <span>Cash On Delivery (COD)</span>
                        </label>
                    </div>

                    {{-- Gcash Details --}}
                    <div class="bg-[#FFF8F8] border border-[#F9B3B0] p-4 rounded-lg text-sm">
                        <p class="font-semibold mb-2">Gcash Payment Details</p>
                        <p>Gcash Number: <span class="font-semibold">09123456789</span></p>
                        <p>Name: <span class="font-semibold">Yvonne’s Cakes and Pastries</span></p>
                        <p>Amount to Pay: <span class="font-semibold">₱{{ number_format($downpayment, 0) }}</span></p>

                        <div class="mt-3">
                            <label class="font-medium block mb-1">Upload Proof of Payment</label>
                            <input type="file"
                                   class="w-full text-sm border border-gray-300 rounded-md px-2 py-2 bg-white outline-none focus:ring-2 focus:ring-[#F9B3B0]">
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT: Order Summary --}}
            <div class="bg-white rounded-lg shadow p-6 border border-gray-200 h-fit">
                <h2 class="text-lg font-semibold text-center bg-[#F9B3B0] text-white py-2 rounded">Order Summary</h2>
                <div class="mt-4 text-sm divide-y divide-gray-200">
                    @foreach($cart as $item)
                        <div class="flex justify-between py-2">
                            <span>{{ $item['name'] }} ×{{ $item['quantity'] }}</span>
                            <span>₱{{ number_format($item['price'] * $item['quantity'], 0) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="text-sm mt-3 space-y-2">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span>₱{{ number_format($subtotal, 0) }}</span>
                    </div>
                    <div class="flex justify-between text-[#F69491]">
                        <span>Downpayment (50%)</span>
                        <span>₱{{ number_format($downpayment, 0) }}</span>
                    </div>
                    <div class="flex justify-between border-t pt-2 font-semibold">
                        <span>Balance (Due on delivery)</span>
                        <span>₱{{ number_format($balance, 0) }}</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold border-t pt-3 mt-3">
                        <span>Total</span>
                        <span>₱{{ number_format($subtotal, 0) }}</span>
                    </div>
                </div>

                {{-- Place Order Button --}}
                <form action="{{ route('checkout.placeOrder') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="w-full mt-6 bg-[#F9B3B0] hover:bg-[#F69491] text-white font-semibold py-3 rounded-lg shadow-md transition-all duration-200">
                        Place Order
                    </button>
                </form>
            </div>
        </div>

        {{-- Note at the bottom --}}
        <p class="text-center text-sm text-pink-400 mt-8">
            🕒 Order ahead of time within a 2–3 days reservation for normal orders
        </p>
    </div>
</div>
@endsection
