@extends('layouts.admin')

@section('content')
<div class="p-6">

    {{-- PAGE HEADER --}}
    <div class="bg-gradient-to-r from-pink-500 to-pink-600 text-white p-6 rounded-xl mb-6">
        <h1 class="text-3xl font-bold mb-1">Product Management</h1>
        <p class="opacity-80">View and manage the products displayed in the customer catalog</p>
    </div>

    {{-- STATS CARDS --}}
    <div class="grid grid-cols-5 gap-4 mb-8">

        <div class="bg-white shadow rounded-xl p-4 text-center">
            <h3 class="text-gray-500 text-sm">Total Products</h3>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
        </div>

        <div class="bg-white shadow rounded-xl p-4 text-center">
            <h3 class="text-gray-500 text-sm">Cakes</h3>
            <p class="text-2xl font-bold text-pink-600">{{ $stats['cakes'] }}</p>
        </div>

        <div class="bg-white shadow rounded-xl p-4 text-center">
            <h3 class="text-gray-500 text-sm">Cupcakes</h3>
            <p class="text-2xl font-bold text-purple-600">{{ $stats['cupcakes'] }}</p>
        </div>

        <div class="bg-white shadow rounded-xl p-4 text-center">
            <h3 class="text-gray-500 text-sm">Food Items</h3>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['food_items'] }}</p>
        </div>

        <div class="bg-white shadow rounded-xl p-4 text-center">
            <h3 class="text-gray-500 text-sm">Paluwagan</h3>
            <p class="text-2xl font-bold text-green-600">{{ $stats['paluwagan'] }}</p>
        </div>

    </div>

    {{-- SEARCH + ADD PRODUCT --}}
    <div class="flex justify-between items-center mb-6">

        {{-- SEARCH BAR --}}
        <div class="relative w-1/2">
            <input id="searchInput" type="text"
                class="w-full border rounded-lg pl-10 p-2"
                placeholder="Search for products...">
            <span class="absolute left-3 top-2.5 text-gray-400 text-xl">🔍</span>
        </div>

        {{-- ADD PRODUCT BUTTON --}}
        <a href="{{ route('admin.products.create') }}"
            class="bg-pink-600 hover:bg-pink-700 text-white px-6 py-3 rounded-lg shadow">
            + Add Product
        </a>

    </div>

    {{-- FILTER BUTTONS --}}
    <div class="flex gap-4 mb-5">

        <button class="categoryBtn active px-5 py-2 rounded-full bg-pink-600 text-white"
                data-category="">
            All
        </button>

        @foreach ($types as $t)
        <button class="categoryBtn px-5 py-2 rounded-full border"
                data-category="{{ $t->productTypeID }}">
            {{ $t->productType }}
        </button>
        @endforeach

    </div>

    {{-- PRODUCT TABLE --}}
    <div class="bg-white p-6 rounded-xl shadow overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b">
                    <th class="p-3">Name</th>
                    <th class="p-3">Category</th>
                    <th class="p-3">Price</th>
                    <th class="p-3">Stock</th>
                    <th class="p-3">Options</th>
                    <th class="p-3 text-center">Actions</th>
                </tr>
            </thead>

            <tbody id="productTable">
                @include('admin.products.product-table')
            </tbody>
        </table>
    </div>

</div>

{{-- AJAX SCRIPT --}}
<script>
document.addEventListener("DOMContentLoaded", () => {

    const searchInput = document.getElementById('searchInput');
    const buttons     = document.querySelectorAll('.categoryBtn');
    const tableBody   = document.getElementById('productTable');

    function fetchProducts() {
        const search = searchInput.value;
        const activeBtn = document.querySelector('.categoryBtn.active');
        const category = activeBtn ? activeBtn.dataset.category : '';

        fetch(`/admin/products/ajax/fetch?search=${search}&category=${category}`)
            .then(res => res.json())
            .then(data => tableBody.innerHTML = data.html);
    }

    searchInput.addEventListener('input', fetchProducts);

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            buttons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            fetchProducts();
        });
    });

});
</script>

@endsection