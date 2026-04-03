<div id="addModal" class="fixed inset-0 bg-black bg-opacity-40 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h2 class="text-xl font-bold mb-4">Add Product</h2>

        <form id="addProductForm" enctype="multipart/form-data">
            @csrf

            <label>Product Name</label>
            <input type="text" name="name" required class="w-full border p-2 mb-2 rounded border-pink-200 focus:ring-pink-500 focus:outline-none focus:ring-2">

            <label>Product Category</label>
            <select id="addCategory" name="productTypeID" required class="w-full border p-2 mb-2 rounded border-pink-200 focus:ring-pink-500 focus:outline-none focus:ring-2">
                @foreach($types as $t)
                    @if(strtolower($t->productType) !== 'paluwagan')
                        <option value="{{ $t->productTypeID }}">{{ $t->productType }}</option>
                    @endif
                @endforeach
            </select>


            {{-- Dynamic Fields --}}
            <div id="dynamicFields"></div>

            <label>Description</label>
            <textarea name="description" class="w-full border p-2 mb-2 rounded border-pink-200 focus:ring-pink-500 focus:outline-none focus:ring-2"></textarea>

            <label>Status</label>
            <select name="isAvailable" class="w-full border p-2 mb-2 rounded border-pink-200 focus:ring-pink-500 focus:outline-none focus:ring-2">
                <option value="1">Available</option>
                <option value="0">Unavailable</option>
            </select>

            <label>Image</label>
            <input type="file" name="imageURL" accept="image/*" class="w-full mb-4  border p-2 rounded border-pink-200 focus:ring-pink-500 focus:outline-none focus:ring-2" required>

            <div class="flex justify-between">
                <button type="button" id="closeAddModal" class="bg-gray-300 px-4 py-2 rounded">Cancel</button>
                <button type="submit" class="bg-pink-600 text-white px-4 py-2 rounded">Create</button>
            </div>
        </form>
    </div>
</div>