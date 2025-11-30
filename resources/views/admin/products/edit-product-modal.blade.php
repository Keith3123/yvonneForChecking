
@extends('layouts.admin')

@section('content')
<div class="p-6">

    {{-- PAGE HEADER --}}
    <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-6 rounded-xl mb-6">
        <h1 class="text-3xl font-bold mb-1">Edit Product</h1>
        <p class="opacity-80">Modify the product details</p>
    </div>

    {{-- FORM CARD --}}
    <div class="bg-white p-6 rounded-xl shadow max-w-3xl mx-auto">

        <form method="POST" action="{{ route('admin.products.update', $product->productID) }}"
              enctype="multipart/form-data">

            @csrf

            {{-- NAME --}}
            <label class="font-semibold">Product Name</label>
            <input type="text" name="name" value="{{ $product->name }}"
                class="w-full border p-3 rounded-lg mb-4">

            {{-- CATEGORY --}}
            <label class="font-semibold">Product Category</label>
            <select name="productTypeID" class="w-full border p-3 rounded-lg mb-4">
                @foreach ($types as $t)
                    <option value="{{ $t->productTypeID }}"
                        @selected($product->productTypeID == $t->productTypeID)>
                        {{ $t->productType }}
                    </option>
                @endforeach
            </select>

            {{-- DESCRIPTION --}}
            <label class="font-semibold">Description</label>
            <textarea name="description" rows="4"
                class="w-full border p-3 rounded-lg mb-4">{{ $product->description }}</textarea>

            {{-- PRICE --}}
            <label class="font-semibold">Price (₱)</label>
            <input type="number" name="price" min="1" step="0.01"
                class="w-full border p-3 rounded-lg mb-4"
                value="{{ $product->price }}">

            {{-- STOCK --}}
            <label class="font-semibold">Stock</label>
            <input type="number" name="stock" min="0"
                class="w-full border p-3 rounded-lg mb-4"
                value="{{ $product->stock }}">

            {{-- CURRENT IMAGE --}}
            <label class="font-semibold">Current Image</label>
            <div class="mb-4">
                <img src="{{ asset('storage/' . $product->imageURL) }}"
                     class="w-28 h-28 object-cover rounded-lg shadow">
            </div>

            {{-- NEW IMAGE --}}
            <label class="font-semibold">Change Image</label>
            <input type="file" name="imageURL" accept="image/*"
                class="w-full border p-3 rounded-lg mb-4">

            {{-- STATUS --}}
            <label class="font-semibold">Status</label>
            <select name="isAvailable" class="w-full border p-3 rounded-lg mb-6">
                <option value="1" @selected($product->isAvailable == 1)>Available</option>
                <option value="0" @selected($product->isAvailable == 0)>Unavailable</option>
            </select>

            {{-- SUBMIT --}}
            <button class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-3 rounded-lg w-full">
                Update Product
            </button>

        </form>

    </div>
</div>
@endsection
