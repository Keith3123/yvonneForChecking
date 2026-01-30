<div id="foodpackage-modal" class="fixed inset-0 hidden z-50 flex items-center justify-center">
    <div class="modal-overlay absolute inset-0 bg-black/50"></div>

    <div class="bg-white rounded-2xl p-6 max-w-lg w-full relative overflow-y-auto max-h-[90vh] z-10">
        <button id="close-modal-foodpackage" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        <input type="hidden" id="foodpackage-id" value="">

        <h2 id="foodpackage-name" class="text-2xl font-bold mb-2"></h2>
        <p id="foodpackage-desc" class="text-gray-600 mb-4"></p>
        <img id="foodpackage-image" class="rounded-lg w-full h-60 object-cover mb-5">

        <div class="bg-[#FFF1F0] p-3 rounded-lg mb-4 text-sm text-gray-800">
            <p class="font-semibold mb-1">Package Includes:</p>
            <ul id="foodpackage-includes" class="list-disc ml-6 text-gray-700"></ul>
        </div>

        <div class="mb-4">
            <label class="font-semibold text-gray-700 block mb-1">Quantity</label>
            <div class="flex items-center gap-3">
                <button id="decrease-qty-foodpackage" class="bg-gray-200 px-3 py-1 rounded text-lg">−</button>
                <span id="quantity-foodpackage" class="text-lg font-semibold">1</span>
                <button id="increase-qty-foodpackage" class="bg-gray-200 px-3 py-1 rounded text-lg">+</button>
            </div>
        </div>

        <div class="bg-[#E9FFF0] p-3 rounded-lg mb-5">
            <p class="text-sm text-gray-500">Unit Price: <span id="foodpackage-price"></span></p>
            <p class="text-xl font-bold text-green-700">Total: <span id="foodpackage-total"></span></p>
        </div>

        <div class="flex justify-end gap-3">
            <button id="add-to-cart-foodpackage" class="bg-[#FF1493] hover:bg-[#FF69B4] text-white px-5 py-2 rounded-lg font-semibold transition">Add to Cart</button>
        </div>
    </div>
</div>
