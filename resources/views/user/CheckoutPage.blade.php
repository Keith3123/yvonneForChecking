@extends('layouts.app')

@section('no-footer')
@endsection

@section('content')
@php $user = session('logged_in_user'); @endphp
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
                        <p class="text-sm text-gray-500 mb-4">Please provide your delivery details (2–3 days advance reservation required)</p>

                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Delivery Date</label>
                                <input type="date" id="deliveryDate" name="deliveryDate" required onkeydown="return false"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none">
                                @error('deliveryDate')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Delivery Time</label>
                                <input type="time" name="deliveryTime" required
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none">
                                <p class="text-[10px] text-gray-400 mt-1">Please select your preferred delivery time.</p>
                            </div>
                        </div>

                        {{-- Address + Phone --}}
                        <div class="grid md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Complete Delivery Address</label>
                                <div class="flex gap-2">
                                    <input type="text" id="deliveryAddress" name="deliveryAddress"
                                        value="{{ old('deliveryAddress', $customer->address ?? '') }}"
                                        placeholder="Enter delivery address"
                                        class="flex-1 border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none">
                                    <button type="button" onclick="openMapModal()"
                                        class="px-4 rounded-md bg-pink-500 text-white hover:bg-pink-600 transition">➜</button>
                                </div>
                                @error('deliveryAddress')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Phone Number</label>
                                <input type="text" name="phone"
                                    value="{{ old('phone', $customer->phone ?? '') }}"
                                    placeholder="Enter phone number"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none">
                            </div>
                        </div>

                        <input type="hidden" name="latitude" id="latitude">
                        <input type="hidden" name="longitude" id="longitude">

                        <div class="mt-4">
                            <label class="block text-sm font-medium mb-1">Message for Admin (Optional)</label>
                            <input type="text" name="remarks" maxlength="200"
                                placeholder="Any special instructions or notes for your order..."
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none">
                            <p class="text-xs text-gray-400 mt-1 text-right">0/200 Characters</p>
                        </div>
                    </div>

                    {{-- Payment Information --}}
                    <div>
                        <h2 class="font-semibold mb-1">Payment Information</h2>
                        <p class="text-sm text-gray-500 mb-4">Choose your preferred payment method</p>

                        <div class="flex gap-6 mb-5">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" id="pay-gcash" name="payment" value="gcash" required>
                                <span>Gcash</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" id="pay-cod" name="payment" value="cod">
                                <span>Cash On Delivery (COD)</span>
                            </label>
                        </div>

                        @error('payment')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror

                        <div id="gcashDetails" class="hidden bg-[#FFF8F8] border border-[#F9B3B0] p-4 rounded-lg text-sm">
                            <p class="font-semibold mb-2">Gcash Payment</p>
                        </div>

                        <p id="codNote"
                            class="mt-2 text-sm text-gray-600 border border-gray-300 rounded p-3 flex items-center gap-2 hidden">
                            <span class="text-red-500 font-semibold">❗</span>
                            <span class="font-semibold">Note:</span>
                            <span>You will pay ₱<span id="orderTotal">0</span> when the order is delivered.</span>
                        </p>
                    </div>
                </form>
            </div>

            {{-- RIGHT: Order Summary --}}
            <div class="bg-white rounded-lg shadow p-6 border border-gray-200 h-fit flex flex-col justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-center bg-[#FF1493] text-white py-2 rounded">Order Summary</h2>

                    <div class="mt-4 text-sm divide-y divide-gray-200">
                        @foreach($cart as $item)
                        <div class="flex justify-between py-2">
                            <span>{{ $item['name'] }} ×{{ $item['quantity'] }}</span>
                            <span>₱{{ number_format($item['price'] * $item['quantity'], 0) }}</span>
                        </div>
                        @endforeach
                    </div>

                    <div class="text-sm mt-3 space-y-2">
                        <div class="flex justify-between"><span>VATable Sales</span><span>₱{{ number_format($subtotal, 2) }}</span></div>
                        <div class="flex justify-between text-gray-600"><span>VAT</span><span>₱{{ number_format($vatAmount, 2) }}</span></div>
                        <div class="flex justify-between text-lg font-bold border-t pt-3 mt-3">
                            <span>Total Amount</span>
                            <span id="summaryTotal">₱{{ number_format($total, 2) }}</span>
                        </div>
                    </div>

                    @if(!$user)
                    <button type="button" id="trigger-login-modal"
                        class="w-full mt-4 bg-[#FF1493] hover:bg-[#FF69B4] text-white font-semibold py-3 rounded-lg shadow-md transition-all duration-200">
                        Place Order
                    </button>
                    @else
                    <button type="button" id="place-order-btn"
                        class="w-full mt-4 bg-[#FF1493] hover:bg-[#FF69B4] text-white font-semibold py-3 rounded-lg shadow-md transition-all duration-200">
                        Place Order
                    </button>
                    @endif
                </div>
            </div>
        </div>

        <p class="text-center text-sm text-pink-400 mt-8">
            🕒 Order ahead of time within a 2–3 days reservation for normal orders
        </p>
    </div>
</div>


{{-- MAP MODAL --}}
<div id="mapModal" class="fixed inset-0 bg-black/40 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-xl w-full max-w-3xl p-5">
        <div class="flex justify-between items-center mb-3">
            <h2 class="font-semibold text-lg">Pin delivery location</h2>
            <button onclick="closeMapModal()" class="text-xl">&times;</button>
        </div>

        <p class="text-sm text-gray-500 mb-2">
            Pinpoint your exact location for accurate delivery
        </p>

        <input id="mapAddress" class="w-full border rounded px-3 py-2 mb-3 focus:ring-2 focus:ring-pink-300 outline-none">

        <div id="map" class="h-80 rounded-md mb-4"></div>

        <div class="flex justify-end">
            <button onclick="savePinnedLocation()"
                class="bg-pink-500 text-white px-5 py-2 rounded-md hover:bg-pink-600">
                Save changes
            </button>
        </div>
    </div>
</div>


{{-- Leaflet --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
    let map, marker;

    function openMapModal() {
        document.getElementById('mapModal').classList.remove('hidden');

        setTimeout(() => {
            const addressText = document.getElementById('deliveryAddress').value;
            const defaultLatLng = [7.1907, 125.4553];

            map = L.map('map').setView(defaultLatLng, 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            marker = L.marker(defaultLatLng, { draggable: true }).addTo(map);

            if (addressText) {
                fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(addressText)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.length > 0) {
                            const lat = parseFloat(data[0].lat);
                            const lon = parseFloat(data[0].lon);
                            marker.setLatLng([lat, lon]);
                            map.setView([lat, lon], 16);
                            reverseGeocode(lat, lon);
                        }
                    });
            } else {
                reverseGeocode(defaultLatLng[0], defaultLatLng[1]);
            }

            marker.on('dragend', function(e) {
                const pos = marker.getLatLng();
                reverseGeocode(pos.lat, pos.lng);
            });

            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                reverseGeocode(e.latlng.lat, e.latlng.lng);
            });

        }, 200);
    }

    function closeMapModal() {
        document.getElementById('mapModal').classList.add('hidden');
        if (map) map.remove();
    }

    function reverseGeocode(lat, lng) {
        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
            .then(res => res.json())
            .then(data => {
                if (data && data.display_name) {
                    document.getElementById('mapAddress').value = data.display_name;
                }
            });
    }

    function savePinnedLocation() {
        const pos = marker.getLatLng();

        document.getElementById('deliveryAddress').value = document.getElementById('mapAddress').value;
        document.getElementById('latitude').value = pos.lat;
        document.getElementById('longitude').value = pos.lng;


        closeMapModal();
    }

    // ✅ UPDATED AUTH MODAL
    function openAuthModal() {
        document.getElementById('authModal').classList.remove('hidden');

        showLogin();

        // ✅ auto focus username
        setTimeout(() => {
            const username = document.querySelector('#authModal input[name="username"]');
            if (username) username.focus();
        }, 200);
    }

    function closeAuthModal() {
        document.getElementById('authModal').classList.add('hidden');
    }

    function showLogin() {
        document.getElementById('loginSection').classList.remove('hidden');
        document.getElementById('registerSection').classList.add('hidden');

        document.getElementById('loginTab').classList.add('bg-pink-100','text-pink-600');
        document.getElementById('registerTab').classList.remove('bg-pink-100','text-pink-600');
    }

    function showRegister() {
        document.getElementById('registerSection').classList.remove('hidden');
        document.getElementById('loginSection').classList.add('hidden');

        document.getElementById('registerTab').classList.add('bg-pink-100','text-pink-600');
        document.getElementById('loginTab').classList.remove('bg-pink-100','text-pink-600');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const dateInput = document.querySelector('input[name="deliveryDate"]');
    if (dateInput) {
        const today = new Date();
        
        // Add 3 days (Reservation Rule)
        // If today is March 25, min will be March 28
        const minDate = new Date(today);
        minDate.setDate(today.getDate() + 3);
        
        // Format to YYYY-MM-DD
        const yyyy = minDate.getFullYear();
        const mm = String(minDate.getMonth() + 1).padStart(2, '0');
        const dd = String(minDate.getDate()).padStart(2, '0');
        
        const minDateString = `${yyyy}-${mm}-${dd}`;
        
        // Apply restriction and hover message
        dateInput.setAttribute('min', minDateString);
        dateInput.setAttribute('title', 'Please choose a date at least 3 days in advance. Past dates are disabled.');
    }
        const btn = document.getElementById('trigger-login-modal');

        // ✅ safe trigger
        if (btn) {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                openAuthModal();
            });
        }

        // ✅ click outside = close modal
        const modal = document.getElementById('authModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeAuthModal();
                }
            });
        }

        // ✅ ESC key close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeAuthModal();
            }
        });
    });
</script>
<script>
    window.checkoutRoutes = {
        paymongo: "{{ route('checkout.paymongo') }}",
        placeOrder: "{{ route('checkout.placeOrder') }}",
        success: "{{ route('checkout.payment.success') }}"
    };
</script>
{{-- Include Vite JS --}}
@vite('resources/js/payment.js')

@include('partials.auth-modal')
@endsection