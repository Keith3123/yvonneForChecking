<div id="addModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto shadow-2xl">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">Add New Product</h2>
            <button type="button" id="closeAddModal" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        </div>

        <form id="addProductForm" enctype="multipart/form-data">
            @csrf

            <label class="block text-sm font-medium mb-1">Product Name *</label>
            <input type="text" name="name" required
                   class="w-full border p-2 mb-3 rounded-lg border-pink-200 focus:ring-2 focus:ring-pink-500 focus:outline-none">

            <label class="block text-sm font-medium mb-1">Category *</label>
            <select name="productTypeID" required
                    class="w-full border p-2 mb-3 rounded-lg border-pink-200 focus:ring-2 focus:ring-pink-500 focus:outline-none">
                <option value="">Select...</option>
                @foreach($types as $t)
                    @if(strtolower($t->productType) !== 'paluwagan')
                        <option value="{{ $t->productTypeID }}">{{ $t->productType }}</option>
                    @endif
                @endforeach
            </select>

            <label class="block text-sm font-medium mb-1">Description</label>
            <textarea name="description" rows="2"
                      class="w-full border p-2 mb-3 rounded-lg border-pink-200 focus:ring-2 focus:ring-pink-500 focus:outline-none"></textarea>

            <div class="grid grid-cols-2 gap-3 mb-3">
                <div>
                    <label class="block text-sm font-medium mb-1">Status</label>
                    <select name="isAvailable"
                            class="w-full border p-2 rounded-lg border-pink-200 focus:ring-2 focus:ring-pink-500 focus:outline-none">
                        <option value="1">Available</option>
                        <option value="0">Unavailable</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Promo (%)</label>
                    <input type="number" name="promo" min="0" max="100" step="0.01" placeholder="0"
                           class="w-full border p-2 rounded-lg border-pink-200 focus:ring-2 focus:ring-pink-500 focus:outline-none">
                </div>
            </div>

            <label class="block text-sm font-medium mb-1">Product Image *</label>
            <input type="file" name="imageURL" accept="image/*" required
                   class="w-full mb-4 border p-2 rounded-lg border-pink-200 text-sm">

            <div class="mb-4">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="font-semibold text-sm text-gray-700">Servings & Ingredients</h3>
                    <button type="button" onclick="addServingRow('add')"
                            class="text-xs bg-pink-100 text-pink-700 px-3 py-1 rounded-full hover:bg-pink-200 font-medium">
                        + Add Serving
                    </button>
                </div>
                <div id="addServingsContainer"></div>
            </div>

            <div class="flex justify-end gap-3 pt-3 border-t">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden'); document.getElementById('addModal').classList.remove('flex');"
                        class="px-5 py-2 bg-gray-100 rounded-lg hover:bg-gray-200 font-semibold">Cancel</button>
                <button type="submit"
                        class="px-5 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 font-semibold shadow">
                    Create Product
                </button>
            </div>
        </form>
    </div>
</div>