@extends('layouts.app')

@section('no-footer')
@endsection

@section('content')
<div class="flex min-h-screen bg-[#FFF8F5]">

    {{-- MAIN AREA --}}
    <main class="flex-1 overflow-y-auto">

        {{-- CATEGORY FILTER --}}
        <div class="bg-white px-4 sm:px-6 border-b sticky top-0 z-40 shadow-sm">
            <nav
                id="category-nav"
                class="flex gap-6 overflow-x-auto no-scrollbar whitespace-nowrap py-4 ml-12"
            >

                @php
                    $categories = [
                        'all' => 'All',
                        'paluwagan' => 'Paluwagan',
                        'foodpackage' => 'Food Package',
                        'foodtray' => 'Food Tray',
                        'cake' => 'Cake',
                        'cupcake' => 'Cupcake',
                    ];
                @endphp

                @foreach ($categories as $key => $label)
                    <button
                        type="button"
                        data-category="{{ $key }}"
                        class="
                            category-btn
                            relative pb-2
                            text-sm sm:text-base font-medium
                            text-gray-600
                            cursor-pointer
                            transition-colors duration-200
                            hover:text-pink-500
                            focus:outline-none
                            after:content-['']
                            after:absolute
                            after:left-1/2
                            after:bottom-0
                            after:h-[2px]
                            after:w-full
                            after:bg-pink-500
                            after:origin-center
                            after:scale-x-0
                            after:-translate-x-1/2
                            after:transition-transform
                            after:duration-300
                            hover:after:scale-x-100
                        "
                    >
                        {{ $label }}
                    </button>
                @endforeach

            </nav>
        </div>

        {{-- PRODUCT GRID --}}
        <div
            id="product-grid"
            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4
                   gap-5 mt-6 px-4 sm:px-6 ml-12 mb-10"
        >
            @foreach ($products as $product)
                <div
                    class="product-card bg-white rounded-lg shadow
                        transition-all duration-300
                        hover:-translate-y-2 hover:shadow-2xl
                        cursor-pointer overflow-hidden"
                    data-id="{{ $product['id'] }}"
                    data-category="{{ strtolower(preg_replace('/[^a-z]/', '', $product['productType'])) }}"
                    data-name="{{ $product['name'] }}"
                    data-description="{{ $product['description'] }}"
                    data-image="{{ asset($product['imageURL']) }}"
                    data-servings='@json($product['servings'])'
                    data-price="{{ $product['servings'][0]['price'] ?? 0 }}"
                >
                    <img
                        src="{{ asset($product['imageURL']) }}"
                        class="w-full h-40 object-cover transition-transform duration-500 hover:scale-105"
                    >

                    <div class="p-4">
                        <h3 class="font-semibold text-lg text-center">
                            {{ $product['name'] }}
                        </h3>
                        <p class="text-pink-600 font-semibold text-center">
                            ₱ {{ number_format($product['servings'][0]['price'] ?? 0, 2) }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- MOBILE CART BUTTON --}}
        @php
            $cartEmpty = count(session('cart', [])) === 0;
        @endphp

        <button
            id="mobile-cart-btn"
            class="fixed bottom-5 right-5 z-50
                   w-14 h-14 rounded-full shadow-lg
                   flex items-center justify-center lg:hidden
                   bg-pink-600 text-white hover:bg-pink-700 cursor-pointer transition"
        >
            <i class="fas fa-shopping-cart text-xl"></i>
        </button>
    </main>

    {{-- CART SIDEBAR (DESKTOP ONLY) --}}
    <aside
        class="hidden lg:flex w-80 bg-white border-l p-6 flex-col sticky top-0 h-screen"
    >
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="font-semibold">Shopping Cart</h3>
                <p class="text-xs text-gray-500">Review your items</p>
            </div>
        </div>

        <div id="cart-sidebar" class="flex-1 overflow-y-auto">
            @include('partials.cart-sidebar')
        </div>
    </aside>
</div>

{{-- MOBILE CART MODAL (CENTER POPUP) --}}
<div
    id="mobile-cart-modal"
    class="fixed inset-0 bg-black/50 z-50 hidden lg:hidden flex items-center justify-center"
>
    <div
        id="mobile-cart-popup"
        class="bg-white w-[90%] max-w-sm
               rounded-2xl shadow-xl
               max-h-[80vh]
               flex flex-col
               transform scale-95 opacity-0
               transition-all duration-300"
    >
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="font-semibold text-lg">Your Cart</h3>
            <button id="close-mobile-cart" class="text-2xl text-gray-500">
                &times;
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4">
            @include('partials.cart-sidebar')
        </div>
    </div>
</div>

{{-- MODALS --}}
@include('user.modals.foodtray')
@include('user.modals.foodpackage')
@include('user.modals.cake')
@include('user.modals.cupcake')
@include('user.modals.paluwagan')

<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- SHAKE ANIMATION --}}
<style>
@keyframes shake {
    0% { transform: translateX(0); }
    20% { transform: translateX(-4px); }
    40% { transform: translateX(4px); }
    60% { transform: translateX(-4px); }
    80% { transform: translateX(4px); }
    100% { transform: translateX(0); }
}
.shake {
    animation: shake 0.4s;
}
</style>

{{-- MOBILE CART LOGIC --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const cartBtn = document.getElementById('mobile-cart-btn');
    const modal = document.getElementById('mobile-cart-modal');
    const popup = document.getElementById('mobile-cart-popup');
    const closeBtn = document.getElementById('close-mobile-cart');

    if (!cartBtn) return;

    cartBtn.addEventListener('click', () => {
        modal.classList.remove('hidden');
        setTimeout(() => {
            popup.classList.remove('scale-95', 'opacity-0');
            popup.classList.add('scale-100', 'opacity-100');
        }, 10);
    });

    closeBtn.addEventListener('click', () => {
        popup.classList.add('scale-95', 'opacity-0');
        setTimeout(() => modal.classList.add('hidden'), 200);
    });
});
</script>

@vite(['resources/js/catalog.js'])
@endsection
