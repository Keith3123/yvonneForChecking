@extends('layouts.app')

@section('no-footer')
@endsection

@section('content')

<div class="flex flex-col bg-[#FFF8F5] min-h-screen">

    {{-- Main area --}}
    <div class="flex flex-1 overflow-hidden">

        {{-- Sidebar: Catalog Categories --}}
        <aside 
            class="w-64 bg-white shadow-md flex flex-col py-6 px-3 sticky top-0 h-screen">


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

        {{-- Catalog Section (scrollable vertically) --}}
        <main class="flex-1 overflow-y-auto px-8 py-6 bg-[#FFF8F5]" style="padding-bottom: 100px;" id="catalog-section">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                @foreach ($products as $product)
                    <div class="product-card bg-white rounded-xl shadow-md hover:shadow-lg transition p-4"
                        data-category="{{ strtolower(str_replace(' ', '', $product['category'] ?? 'foodpackage')) }}">
                        <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}" class="rounded-lg mb-4 w-full h-40 object-cover">
                        <h3 class="text-lg font-semibold mb-1">{{ $product['name'] }}</h3>
                        <p class="text-sm text-gray-500 mb-2">Good for 4–6 pax</p>
                        <div class="bg-[#FFF1F0] p-2 rounded-md text-sm text-gray-700 mb-3">
                            <p><strong>Package Includes:</strong></p>
                            <p>1 small tray menudo, 1 medium tray grilled pork, 2×1.25 L coke</p>
                        </div>
                        <p class="text-gray-800 font-semibold mb-1">{{ $product['price'] }}</p>
                        <p class="text-xs text-gray-500 mb-3">Food Package</p>
                        <button class="w-full bg-[#F9B3B0] hover:bg-[#F69491] text-white font-semibold py-2 rounded-lg">
                            Order Now
                        </button>
                    </div>
                @endforeach
            </div>
        </main>
    </div>

    {{-- Fixed Cart Section at Bottom --}}
    <div class="fixed bottom-0 left-0 right-0 bg-[#FBD2CF] border-t border-[#F3B9B5] px-8 py-4 flex justify-between items-center shadow-[0_-2px_10px_rgba(0,0,0,0.1)] rounded-t-xl z-50">
        <div class="flex items-center gap-2 text-gray-700 font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13l-1.6 8h13.2M7 13l1.6-8M10 21a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z" />
            </svg>
            <span>Show Cart</span>
            <span id="cart-count" class="text-sm text-gray-500 ml-2">0 item added</span>
        </div>
        <button class="bg-gradient-to-r from-[#F9B3B0] to-[#F69491] hover:from-[#F69491] hover:to-[#F56E69] text-white font-semibold px-6 py-2 rounded-lg shadow-sm transition">
            Order and Pay
        </button>
    </div>
</div>

@vite(['resources/js/catalogfilter.js'])
@endsection
