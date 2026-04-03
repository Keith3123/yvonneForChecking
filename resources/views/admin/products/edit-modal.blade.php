    <div id="editModal" class="fixed inset-0 hidden z-50 flex items-center justify-center">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-black bg-opacity-40 pointer-events-none"></div>

        <!-- Modal Content -->
        <div class="bg-white rounded-xl p-6 w-full max-w-md z-50 pointer-events-auto">
            <h2 class="text-xl font-bold mb-4">Edit Product</h2>

            <form id="editProductForm" enctype="multipart/form-data" data-action="">
                @csrf
                @method('PUT')

                <label>Product Name</label>
                <input type="text" id="editName" name="name" required class="w-full border p-2 mb-2 rounded border-pink-200 focus:ring-pink-500 focus:outline-none focus:ring-2">

                <label>Product Category</label>
                <select id="editCategory" name="productTypeID" required class="w-full border p-2 mb-2 rounded border-pink-200 focus:ring-pink-500 focus:outline-none focus:ring-2">
                    @foreach($types as $t)
                        @if(strtolower($t->productType) !== 'paluwagan')
                            <option value="{{ $t->productTypeID }}">{{ $t->productType }}</option>
                        @endif
                    @endforeach
                </select>

                {{-- Dynamic Fields --}}

                <div id="editDynamicFields"></div>

                <label>Description</label>
                <textarea id="editDescription" name="description" class="w-full border p-2 mb-2 rounded border-pink-200 focus:ring-pink-500 focus:outline-none focus:ring-2"></textarea>

                <label>Status</label>
                <select id="editStatus" name="isAvailable" class="w-full border p-2 mb-2 rounded border-pink-200 focus:ring-pink-500 focus:outline-none focus:ring-2">
                    <option value="1">Available</option>
                    <option value="0">Unavailable</option>
                </select>

                <label>Image (Optional)</label> 
                <input type="file" id="editImage" name="imageURL" accept="image/*" class="w-full mb-4  border p-2 rounded border-pink-200 focus:ring-pink-500 focus:outline-none focus:ring-2">

                <div class="flex justify-between">
                    <button type="button" id="closeEditModal" class="bg-gray-300 px-4 py-2 rounded">Cancel</button>
                    <button type="submit" class="bg-pink-600 text-white px-4 py-2 rounded">Update</button>
                </div>
            </form>
        </div>
    </div>