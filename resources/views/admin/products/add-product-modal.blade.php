@extends('layouts.admin')

@section('content')
<div class="p-6">

    {{-- PAGE HEADER --}}
    <div class="bg-gradient-to-r from-pink-500 to-pink-600 text-white p-6 rounded-xl mb-6">
        <h1 class="text-3xl font-bold mb-1">Add New Product</h1>
        <p class="opacity-80">Create a new product for the customer catalog</p>
    </div>

    {{-- FORM CARD --}}
    <div class="bg-white p-6 rounded-xl shadow max-w-3xl mx-auto">

        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf

            {{-- NAME --}}
            <label class="font-semibold">Product Name</label>
            <input type="text" name="name"
                class="w-full border p-3 rounded-lg mb-4"
                placeholder="Enter product name" required>

            {{-- CATEGORY --}}
            <label class="font-semibold">Product Category</label>
            <select name="productTypeID" class="w-full border p-3 rounded-lg mb-4" required>
                @foreach ($types as $t)
                    <option value="{{ $t->productTypeID }}">{{ $t->productType }}</option>
                @endforeach
            </select>

            {{-- DESCRIPTION --}}
            <label class="font-semibold">Description</label>
            <textarea name="description" rows="4"
                class="w-full border p-3 rounded-lg mb-4"
                placeholder="Enter description"></textarea>

            {{-- PRICE --}}
            <label class="font-semibold">Price (₱)</label>
            <input type="number" name="price" min="1" step="0.01"
                class="w-full border p-3 rounded-lg mb-4"
                placeholder="0.00" required>

            {{-- STOCK --}}
            <label class="font-semibold">Stock</label>
            <input type="number" name="stock" min="0"
                class="w-full border p-3 rounded-lg mb-4"
                placeholder="0" required>

            {{-- IMAGE --}}
            <label class="font-semibold">Upload Image</label>
            <input type="file" name="imageURL" accept="image/*"
                class="w-full border p-3 rounded-lg mb-4" required>

            {{-- STATUS --}}
            <label class="font-semibold">Status</label>
            <select name="isAvailable" class="w-full border p-3 rounded-lg mb-6">
                <option value="1">Available</option>
                <option value="0">Unavailable</option>
            </select>

            {{-- SUBMIT --}}
            <button class="bg-pink-600 hover:bg-pink-700 text-white font-semibold px-6 py-3 rounded-lg w-full">
                Create Product
            </button>

        </form>

    </div>
</div>
@endsection
