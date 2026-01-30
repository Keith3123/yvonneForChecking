<div id="cupcake-modal" class="fixed inset-0 hidden z-50 flex items-center justify-center">
    <div class="modal-overlay absolute inset-0 bg-black/50"></div>

    <div class="bg-white rounded-2xl p-6 max-w-md w-full relative overflow-y-auto max-h-[90vh] z-10">
        <button id="close-modal-cupcake" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
        <input type="hidden" id="cupcake-id" value="">

        <div id="cupcake-product-info">
            <h2 id="cupcake-name" class="text-2xl font-bold mb-1"></h2>
            <p id="cupcake-desc" class="text-gray-600 mb-4"></p>
            <img id="cupcake-image" class="rounded-lg w-full h-60 object-cover mb-5">
        </div>

        <div id="cupcake-customization" class="hidden mb-4">
            <h3 class="text-xl font-semibold mb-3">Customize Your Cupcake</h3>
            <div class="mb-3">
                <label class="font-semibold text-gray-700 block mb-1">Flavor</label>
                <select id="cupcake-flavor" class="border rounded-md w-full p-2"></select>
            </div>
            <div class="mb-3">
                <label class="font-semibold text-gray-700 block mb-1">Icing Color</label>
                <select id="cupcake-icing" class="border rounded-md w-full p-2"></select>
            </div>
        </div>

        <div class="flex flex-col gap-5">
            <div id="cupcake-message-wrapper">
                <label class="font-semibold text-gray-700">Personalized Message</label>
                <input id="cupcake-message" type="text"
                    class="w-full p-2 border rounded-lg mt-1 text-gray-700"
                    placeholder="Enter your custom message">
                <p class="text-right text-gray-500 text-sm mt-1">
                    <span id="cupcake-message-count">0</span>/100
                </p>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button id="decrease-qty-cupcake" class="bg-gray-200 px-3 py-1 rounded text-lg">−</button>
                    <span id="quantity-cupcake" class="text-lg font-semibold">1</span>
                    <button id="increase-qty-cupcake" class="bg-gray-200 px-3 py-1 rounded text-lg">+</button>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Unit Price: <span id="cupcake-price"></span></p>
                    <p class="text-xl font-bold text-green-700">Total: <span id="cupcake-total"></span></p>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-4">
            <button id="add-to-cart-cupcake" class="bg-[#FF1493] hover:bg-[#F69491] text-white px-5 py-2 rounded-lg font-semibold transition">Add to Cart</button>
        </div>
    </div>
</div>
