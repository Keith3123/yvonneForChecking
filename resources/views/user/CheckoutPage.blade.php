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

            <div class="grid lg:grid-cols-3 gap-8">

                {{-- LEFT: Delivery & Payment --}}
                <div class="lg:col-span-2 bg-white rounded-lg shadow p-6 border border-gray-200">
                    <form id="checkoutForm" action="{{ route('checkout.placeOrder') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- Delivery Information --}}
                        <div class="mb-8">
                            <h2 class="font-semibold mb-1">Delivery Information</h2>
                            <p class="text-sm text-gray-500 mb-4">
                                Please provide your delivery details (2–3 days advance reservation required)
                            </p>

                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Delivery Date</label>
                                    <input type="date" name="deliveryDate" class="w-full border border-gray-300 rounded-md px-3 py-2 outline-none focus:ring-2 focus:ring-[#F9B3B0]">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Delivery Time</label>
                                    <select name="deliveryTime" class="w-full border border-gray-300 rounded-md px-3 py-2 outline-none focus:ring-2 focus:ring-[#F9B3B0]">
                                        <option>9:30 - 11:00</option>
                                        <option>1:00 - 3:00</option>
                                        <option>3:00 - 5:00</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium mb-1">Complete Delivery Address</label>
                                <input type="text" name="deliveryAddress" placeholder="Enter delivery address" class="w-full border border-gray-300 rounded-md px-3 py-2 outline-none focus:ring-2 focus:ring-[#F9B3B0]">
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium mb-1">Message for Admin (Optional)</label>
                                <input type="text" name="remarks" maxlength="200" placeholder="Any special instructions or notes for your order..." class="w-full border border-gray-300 rounded-md px-3 py-2 outline-none focus:ring-2 focus:ring-[#F9B3B0]">
                                <p class="text-xs text-gray-400 mt-1 text-right">0/200 Characters</p>
                            </div>
                        </div>

                        {{-- Payment Information --}}
                        <div>
                            <h2 class="font-semibold mb-1">Payment Information</h2>
                            <p class="text-sm text-gray-500 mb-4">Choose your preferred payment method</p>

                            <div class="flex gap-6 mb-5">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" id="pay-gcash" name="payment" value="gcash">
                                    <span>Gcash</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" id="pay-cod" name="payment" value="cod">
                                    <span>Cash On Delivery (COD)</span>
                                </label>
                            </div>

                            {{-- Gcash Details --}}
                            <div id="gcashDetails" class="hidden bg-[#FFF8F8] border border-[#F9B3B0] p-4 rounded-lg text-sm">
                                <p class="font-semibold mb-2">Gcash Payment Details</p>
                                <p>Gcash Number: <span class="font-semibold">09123456789</span></p>
                                <p>Name: <span class="font-semibold">Yvonne’s Cakes and Pastries</span></p>

                                <div class="mt-3">
                                    <label class="font-medium block mb-1">Upload Proof of Payment</label>
                                    <input type="file" name="paymentProof" class="w-full text-sm border border-gray-300 rounded-md px-2 py-2 bg-white outline-none focus:ring-2 focus:ring-[#F9B3B0]">
                                </div>
                            </div>

                            {{-- COD Note --}}
                            <p id="codNote" class="mt-2 text-sm text-gray-600 border border-gray-300 rounded p-3 flex items-center gap-2 hidden">
                                <span class="text-red-500 font-semibold">❗</span>
                                <span class="font-semibold">Note:</span>
                                <span>You will pay ₱<span id="orderTotal">0</span> when the order is delivered.</span>
                            </p>
                        </div>

                    {{-- PLACE ORDER BUTTON inside Order Summary --}}
                    <button type="submit" id="place-order-btn" class="w-full mt-4 bg-[#FF1493] hover:bg-[#FF69B4] text-white font-semibold py-3 rounded-lg shadow-md transition-all duration-200">
                        Place Order
                    </button>

                    </form>
                </div>

                {{-- RIGHT: Order Summary --}}
                <div class="bg-white rounded-lg shadow p-6 border border-gray-200 h-fit flex flex-col justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-center bg-[#FF69B4] text-white py-2 rounded">Order Summary</h2>

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
                                <span>₱<span id="summarySubtotal">{{ number_format($subtotal, 0) }}</span></span>
                            </div>

                            <div class="flex justify-between text-lg font-bold border-t pt-3 mt-3">
                                <span>Total</span>
                                <span>₱<span id="summaryTotal">{{ number_format($subtotal, 0) }}</span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <p class="text-center text-sm text-pink-400 mt-8">
                🕒 Order ahead of time within a 2–3 days reservation for normal orders
            </p>
        </div>
    </div>

    {{-- Include Vite JS --}}
    @vite('resources/js/payment.js')
    @endsection