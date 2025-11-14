@extends('layouts.app')

@section('no-footer')
@endsection

@section('content')

<div class="flex flex-col bg-[#FFF8F5] min-h-screen">

    {{-- Main area --}}
    <div class="flex flex-1 overflow-hidden">

        {{-- Sidebar: Catalog Categories --}}
        <aside class="w-64 bg-white shadow-md flex flex-col py-6 px-3 sticky top-0 h-screen">

            {{-- Fixed Header --}}
            <div class="flex-shrink-0">
                <h3 class="text-lg font-bold mb-1 px-2 text-gray-800">Browse Menu</h3>
                <p class="text-sm text-gray-500 px-2 mb-4">Select a category</p>
            </div>

            {{-- Define Categories --}}
            @php
                $categories = [
                    ['name' => 'Paluwagan', 'image' => '/images/paluwagan.jpg'],
                    ['name' => 'Food Package', 'image' => '/images/food-package.jpg'],
                    ['name' => 'Food Trays', 'image' => '/images/food-tray.jpg'],
                    ['name' => 'Cake', 'image' => '/images/choco-cake.jpg'],
                    ['name' => 'Cup Cake', 'image' => '/images/vanilla-cupcake.jpg'],
                ];
            @endphp

            {{-- Scrollable Category List --}}
            <div id="category-list" class="flex-1 overflow-y-auto custom-scrollbar pr-1">
                <div class="category-container flex flex-col space-y-4">
                    @foreach ($categories as $category)
                        <button 
                            class="category-btn flex flex-col items-center text-center bg-white rounded-xl shadow-md hover:shadow-lg hover:bg-[#FFEFEA] transition p-3"
                            data-category="{{ strtolower(str_replace(' ', '', $category['name'])) }}">
                            <img src="{{ $category['image'] }}" alt="{{ $category['name'] }}" class="w-20 h-20 rounded-lg object-cover mb-2">
                            <span class="font-semibold text-gray-700 text-sm">{{ $category['name'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </aside>

        {{-- Catalog Section --}}
        <main class="flex-1 overflow-y-auto px-8 py-6 bg-[#FFF8F5]" style="padding-bottom: 100px;" id="catalog-section">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                @foreach ($products as $product)
                    <div class="product-card bg-white rounded-xl shadow-md hover:shadow-lg transition p-4 cursor-pointer"
                        data-category="{{ strtolower(str_replace(' ', '', $product['category'] ?? 'foodpackage')) }}"
                        data-name="{{ $product['name'] }}"
                        data-price="{{ $product['price'] }}"
                        data-image="{{ $product['image'] }}"
                        data-description="{{ $product['description'] }}">
                        
                        <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="rounded-lg mb-4 w-full h-40 object-cover">
                        <h3 class="text-lg font-semibold mb-1">{{ $product['name'] }}</h3>
                        <p class="text-sm text-gray-500 mb-2">{{ $product['description'] }}</p>
                        <p class="text-gray-800 font-semibold">{{ $product['price'] }}</p>
                    </div>
                @endforeach
            </div>
        </main>
    </div>

    {{-- Fixed Cart Section --}}
<div class="fixed bottom-0 left-0 right-0 bg-[#FBD2CF] border-t border-[#F3B9B5] px-8 py-4 flex justify-between items-center shadow-[0_-2px_10px_rgba(0,0,0,0.1)] rounded-t-xl z-50">
    
    {{-- Cart Link --}}
    <a href="{{ route('cart') }}" class="flex items-center gap-2 text-gray-700 font-medium hover:underline">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.6 8h13.2M7 13l1.6-8M10 21a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z" />
        </svg>
        <span>Show Cart</span>
        <span id="cart-count" class="text-sm text-gray-500 ml-2">{{ count(session('cart', [])) }} item(s) added</span>
    </a>

    {{-- Order and Pay Button --}}
            <div class="mt-4 md:mt-0">
                <a href="{{ route('checkout') }}"
                    class="bg-[#F9B3B0] hover:bg-[#F69491] text-white px-6 py-3 rounded-lg font-semibold shadow-md transition-all duration-200">
                    Order and Pay
                </a>
            </div>
        </div>


{{-- Popup Modal for Food Trays --}}
<div id="foodtray-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full relative">
        <button id="close-modal" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-2xl">&times;</button>

        <img id="modal-image" src="" class="rounded-lg w-full h-56 object-cover mb-4">
        <h2 id="modal-name" class="text-xl font-bold mb-2"></h2>
        <p id="modal-description" class="text-gray-600 mb-4"></p>

        <div class="flex flex-col gap-2 mb-4">
            <label class="font-semibold text-gray-700">Size</label>
            <select class="border rounded-md p-2">
                <option>Select size</option>
                <option>Small</option>
                <option>Medium</option>
                <option>Large</option>
            </select>
        </div>

        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <button id="decrease-qty" class="bg-gray-200 px-3 py-1 rounded text-lg">−</button>
                <span id="quantity" class="text-lg font-semibold">1</span>
                <button id="increase-qty" class="bg-gray-200 px-3 py-1 rounded text-lg">+</button>
            </div>
            <div>
                <p class="text-sm text-gray-500">Unit Price: <span id="modal-price"></span></p>
                <p class="text-xl font-bold text-green-700">Total: <span id="modal-total"></span></p>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <button id="cancel-modal" class="border border-gray-300 rounded-lg px-4 py-2 hover:bg-gray-100 transition">Cancel</button>
            <button class="bg-[#F9B3B0] hover:bg-[#F69491] text-white px-5 py-2 rounded-lg font-semibold transition">Add to Cart</button>
        </div>
    </div>
</div>

{{-- Popup Modal for Food Package --}}
<div id="foodpackage-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-2xl p-6 max-w-lg w-full relative">
        <button id="close-modal-package" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-2xl">&times;</button>

        <h2 id="package-name" class="text-2xl font-bold mb-1"></h2>
        <p id="package-desc" class="text-gray-600 mb-4"></p>

        <img id="package-image" src="" class="rounded-lg w-full h-60 object-cover mb-5">

        <div class="bg-[#FFF1F0] p-3 rounded-lg mb-4 text-sm text-gray-800">
            <p class="font-semibold mb-1">Package Includes:</p>
            <p id="package-includes">1 small tray menudo, 1 medium tray grilled pork, 2×1.25 L coke</p>
        </div>

        <div class="mb-4">
            <label class="font-semibold text-gray-700 block mb-1">Quantity</label>
            <div class="flex items-center gap-3">
                <button id="decrease-qty-package" class="bg-gray-200 px-3 py-1 rounded text-lg">−</button>
                <span id="quantity-package" class="text-lg font-semibold">1</span>
                <button id="increase-qty-package" class="bg-gray-200 px-3 py-1 rounded text-lg">+</button>
            </div>
        </div>

        <div class="bg-[#E9FFF0] rounded-lg p-3 mb-5">
            <p class="text-sm text-gray-500">Unit Price: <span id="package-price"></span></p>
            <p class="text-xl font-bold text-green-700">Total: <span id="package-total"></span></p>
        </div>

        <div class="flex justify-end gap-3">
            <button id="cancel-modal-package" class="border border-gray-300 rounded-lg px-4 py-2 hover:bg-gray-100 transition">Cancel</button>
            <button 
                class="add-to-cart-btn bg-[#F9B3B0] hover:bg-[#F69491] text-white px-5 py-2 rounded-lg font-semibold transition"
                data-id="1"
                data-name="Chocolate Cake"
                data-price="1200"
                data-quantity="1"
                data-image="/images/choco-cake.jpg"
            >
                Add to Cart
            </button>
        </div>
    </div>
</div>

{{-- Popup Modal for Cake --}}
<div id="cake-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full relative overflow-y-auto max-h-[90vh]">
        <button id="close-modal-cake" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-2xl">&times;</button>

        <h2 id="cake-name" class="text-2xl font-bold mb-1">Chocolate Cake</h2>
        <p id="cake-desc" class="text-gray-600 mb-5">Sweet that melts in your tongue</p>

        <img id="cake-image" src="" class="rounded-lg w-full h-60 object-cover mb-5">

        <p class="font-semibold text-gray-700 mb-3">Personalized birthday cake with your own choice of design</p>

        {{-- Size --}}
        <div class="mb-3">
            <label class="font-semibold text-gray-700 block mb-1">Size</label>
            <select id="cake-size" class="border rounded-md w-full p-2">
                <option>Select size</option>
                <option>6 inches</option>
                <option>8 inches</option>
                <option>10 inches</option>
            </select>
        </div>

        {{-- Flavor --}}
        <div class="mb-3">
            <label class="font-semibold text-gray-700 block mb-1">Flavor</label>
            <select id="cake-flavor" class="border rounded-md w-full p-2">
                <option>Select flavor</option>
                <option>Chocolate</option>
                <option>Mocha</option>
                <option>Vanilla</option>
                <option>Red Velvet</option>
            </select>
        </div>

        {{-- Shape --}}
        <div class="mb-3">
            <label class="font-semibold text-gray-700 block mb-1">Shape</label>
            <select id="cake-shape" class="border rounded-md w-full p-2">
                <option>Select shape</option>
                <option>Round</option>
                <option>Square</option>
                <option>Heart</option>
            </select>
        </div>

        {{-- Icing Color --}}
        <div class="mb-3">
            <label class="font-semibold text-gray-700 block mb-1">Icing Color</label>
            <select id="cake-icing" class="border rounded-md w-full p-2">
                <option>Select icing color</option>
                <option>White</option>
                <option>Pink</option>
                <option>Blue</option>
                <option>Chocolate</option>
            </select>
        </div>

        {{-- Personalized Message --}}
        <div class="mb-4">
            <label class="font-semibold text-gray-700 block mb-1">Personalized Message</label>
            <textarea id="cake-message" maxlength="50" class="border rounded-md w-full p-2 h-20" placeholder="Enter your message (max 50 chars)"></textarea>
            <p class="text-xs text-gray-400 text-right mt-1"><span id="message-count">0</span>/50 characters</p>
        </div>

        {{-- Quantity & Total --}}
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <button id="decrease-qty-cake" class="bg-gray-200 px-3 py-1 rounded text-lg">−</button>
                <span id="quantity-cake" class="text-lg font-semibold">1</span>
                <button id="increase-qty-cake" class="bg-gray-200 px-3 py-1 rounded text-lg">+</button>
            </div>
            <div>
                <p class="text-sm text-gray-500">Unit Price: <span id="cake-price">₱ 0.00</span></p>
                <p class="text-xl font-bold text-green-700">Total: <span id="cake-total">₱ 0.00</span></p>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <button id="cancel-modal-cake" class="border border-gray-300 rounded-lg px-4 py-2 hover:bg-gray-100 transition">Cancel</button>
            <button 
                class="add-to-cart-btn bg-[#F9B3B0] hover:bg-[#F69491] text-white px-5 py-2 rounded-lg font-semibold transition"
                data-id="1"
                data-name="Chocolate Cake"
                data-price="1200"
                data-quantity="1"
                data-image="/images/choco-cake.jpg"
            >
                Add to Cart
            </button>
        </div>
    </div>
</div>

{{-- Popup Modal for Cup Cake --}}
<div id="cupcake-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full relative overflow-y-auto max-h-[90vh]">
        <button id="close-modal-cupcake" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-2xl">&times;</button>

        <h2 id="cupcake-name" class="text-2xl font-bold mb-1">Vanilla Cupcake</h2>
        <p id="cupcake-desc" class="text-gray-600 mb-5">Soft and sweet cupcake perfect for any occasion</p>

        <img id="cupcake-image" src="" class="rounded-lg w-full h-60 object-cover mb-5">

        <p class="font-semibold text-gray-700 mb-3">Personalized cupcake with your own choice of flavor and icing color</p>

        {{-- Flavor --}}
        <div class="mb-3">
            <label class="font-semibold text-gray-700 block mb-1">Flavor</label>
            <select id="cupcake-flavor" class="border rounded-md w-full p-2">
                <option>Select flavor</option>
                <option>Chocolate</option>
                <option>Vanilla</option>
                <option>Red Velvet</option>
                <option>Mocha</option>
            </select>
        </div>

        {{-- Icing Color --}}
        <div class="mb-4">
            <label class="font-semibold text-gray-700 block mb-1">Icing Color</label>
            <select id="cupcake-icing" class="border rounded-md w-full p-2">
                <option>Select icing color</option>
                <option>White</option>
                <option>Pink</option>
                <option>Blue</option>
                <option>Chocolate</option>
            </select>
        </div>

        {{-- Quantity & Total --}}
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <button id="decrease-qty-cupcake" class="bg-gray-200 px-3 py-1 rounded text-lg">−</button>
                <span id="quantity-cupcake" class="text-lg font-semibold">1</span>
                <button id="increase-qty-cupcake" class="bg-gray-200 px-3 py-1 rounded text-lg">+</button>
            </div>
            <div>
                <p class="text-sm text-gray-500">Unit Price: <span id="cupcake-price">₱ 0.00</span></p>
                <p class="text-xl font-bold text-green-700">Total: <span id="cupcake-total">₱ 0.00</span></p>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <button id="cancel-modal-cupcake" class="border border-gray-300 rounded-lg px-4 py-2 hover:bg-gray-100 transition">Cancel</button>
            <button 
                class="add-to-cart-btn bg-[#F9B3B0] hover:bg-[#F69491] text-white px-5 py-2 rounded-lg font-semibold transition"
                data-id="1"
                data-name="Chocolate Cake"
                data-price="1200"
                data-quantity="1"
                data-image="/images/choco-cake.jpg"
            >
                Add to Cart
            </button>
        </div>
    </div>
</div>



{{-- Popup Modal for Paluwagan --}}
<div id="paluwagan-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
  <div class="bg-white rounded-2xl p-6 max-w-lg w-full relative overflow-y-auto max-h-[90vh]">
    <button id="close-modal-paluwagan" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-2xl">&times;</button>

    {{-- STEP 1: Paluwagan Details --}}
    <div id="paluwagan-step1">
      <h2 id="paluwagan-name" class="text-2xl font-bold mb-1">Holiday Package</h2>
      <p id="paluwagan-desc" class="text-gray-600 mb-4">Crispy and juicy chicken</p>

      <img id="paluwagan-image" src="" class="rounded-lg w-full h-60 object-cover mb-5">

      <div class="bg-[#FFF1F0] p-3 rounded-lg mb-4 text-sm text-gray-800">
        <p class="font-semibold mb-1">What's Included</p>
        <ul class="list-disc list-inside text-gray-700 space-y-1" id="paluwagan-includes">
          <li>Holiday roasted chicken</li>
          <li>Christmas ham</li>
          <li>Special holiday cake</li>
        </ul>
      </div>

      <div class="bg-[#FFF1F0] p-3 rounded-lg mb-4 text-sm text-gray-800">
        <p class="font-semibold mb-1">Paluwagan Details</p>
        <p>Total Package: <span id="paluwagan-total">₱10,000</span></p>
        <p>Monthly Payment: <span id="paluwagan-monthly">₱1,000</span></p>
        <p>Duration: 10 months</p>
      </div>

      <div class="flex justify-end gap-3 mt-6">
        <button id="cancel-paluwagan" class="border border-gray-300 rounded-lg px-4 py-2 hover:bg-gray-100 transition">Cancel</button>
        <button id="join-paluwagan" class="bg-[#F9B3B0] hover:bg-[#F69491] text-white px-5 py-2 rounded-lg font-semibold transition">Join Paluwagan</button>
      </div>
    </div>

    {{-- STEP 2: Paluwagan Enrollment --}}
    <div id="paluwagan-step2" class="hidden">
      <h2 class="text-2xl font-bold mb-4">Paluwagan Enrollment</h2>

      <label class="font-semibold text-gray-700 block mb-2">Select Start Month</label>
      <select id="start-month" class="border rounded-md w-full p-2 mb-4">
        <option>January</option>
        <option>February</option>
        <option>March</option>
        <option>April</option>
        <option>May</option>
        <option>June</option>
        <option>July</option>
        <option>August</option>
        <option>September</option>
        <option>October</option>
        <option>November</option>
        <option>December</option>
      </select>

      <img id="paluwagan-image2" src="" class="rounded-lg w-full h-60 object-cover mb-5">

      <div class="bg-[#FFF1F0] p-3 rounded-lg mb-4 text-sm text-gray-800">
        <p class="font-semibold mb-1">Important Reminders</p>
        <ul class="list-disc list-inside text-gray-700 space-y-1">
          <li>Due date is every last day of the month.</li>
          <li>5-day extension for late payment, then penalty per day.</li>
          <li>No cancellation or refund once payment starts.</li>
          <li>All payments are non-refundable.</li>
        </ul>
      </div>

      <div class="flex justify-end gap-3 mt-6">
        <button id="back-paluwagan" class="border border-gray-300 rounded-lg px-4 py-2 hover:bg-gray-100 transition">Back</button>
        <button id="confirm-paluwagan" class="bg-[#F9B3B0] hover:bg-[#F69491] text-white px-5 py-2 rounded-lg font-semibold transition">Confirm Enrollment</button>
      </div>
    </div>
  </div>
</div>



@vite(['resources/js/catalogfilter.js'])
@endsection

