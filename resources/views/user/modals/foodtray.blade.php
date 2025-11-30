<div id="foodtray-modal"
     class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">

    <div class="bg-white rounded-2xl p-6  max-w-md w-full relative overflow-y-auto max-h-[90vh]">

        <button id="close-modal-foodtray"
                class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-2xl">&times;</button>

        <h2 id="foodtray-name" class="text-xl font-bold mb-2"></h2>
        <img id="foodtray-image" class="rounded-lg w-full h-56 object-cover mb-5">

        <!-- What's Included -->
        <div class="mb-4">
            <h3 class="font-semibold text-gray-800 mb-1"></h3>
            <ul id="foodtray-includes" class="list-disc ml-6 text-gray-700">
                <!-- Populated dynamically via JS -->
            </ul>
        </div>

        <!-- Size Selector -->
        <div class="flex flex-col gap-2 mb-4">
            <label class="font-semibold text-gray-700">Size</label>
            <select id="foodtray-size" class="border rounded-md p-2"></select>
        </div>

        <!-- Quantity & Price -->
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-3">
                <button id="decrease-qty-foodtray" class="bg-gray-200 px-3 py-1 rounded text-lg">−</button>
                <span id="quantity-foodtray" class="text-lg font-semibold">1</span>
                <button id="increase-qty-foodtray" class="bg-gray-200 px-3 py-1 rounded text-lg">+</button>
            </div>

            <div>
                <p class="text-sm text-gray-500">Unit Price: <span id="foodtray-price"></span></p>
                <p class="text-xl font-bold text-green-700">Total: <span id="foodtray-total"></span></p>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
            <button id="add-to-cart-foodtray"
                    class="bg-[#FF1493] hover:bg-[#FF69B4] text-white px-5 py-2 rounded-lg font-semibold transition">
                    Add to Cart</button>
        </div>

    </div>
</div>