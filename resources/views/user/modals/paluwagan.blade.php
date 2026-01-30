<div id="paluwagan-modal" class="fixed inset-0 hidden z-50 flex items-center justify-center">
    <!-- Overlay -->
    <div class="modal-overlay absolute inset-0 bg-black/50"></div>

    <!-- Modal content -->
    <div class="bg-white rounded-2xl p-6 max-w-lg w-full relative overflow-y-auto max-h-[90vh] z-10">

        <!-- Close Button -->
        <button id="close-modal-paluwagan" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-2xl">&times;</button>

        <!-- STEP 1 -->
        <div id="paluwagan-step1">
            <h2 id="paluwagan-name" class="text-2xl font-bold mb-1"></h2>
            <img id="paluwagan-image" src="" class="rounded-lg w-full h-60 object-cover mb-5">
            
            <div class="mt-4">
                <h3 class="font-semibold text-gray-800 mb-1">What's Included</h3>
                <ul id="paluwagan-desc" class="list-disc ml-6 text-gray-500 mb-2 space-y-1"></ul>
            </div>

            <div class="bg-[#FFF1F0] p-3 rounded-lg mb-4 text-sm text-gray-800 mt-4">
                <p class="font-semibold mb-1">Paluwagan Details</p>
                <p>Total Package: <span id="paluwagan-total"></span></p>
                <p>Monthly Payment: <span id="paluwagan-monthly"></span></p>
                <p>Duration: <span id="paluwagan-duration"></span></p>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button id="join-paluwagan"
                        class="bg-[#FF1493] hover:bg-[#FF69B4] text-white px-5 py-2 rounded-lg font-semibold transition">
                        Join Paluwagan
                </button>
            </div>
        </div>

        <!-- STEP 2 -->
        <div id="paluwagan-step2" class="hidden">
            <h2 class="text-2xl font-bold mb-4">Paluwagan Enrollment</h2>
            
            <label class="font-semibold text-gray-700 block mb-2">Select Start Month</label>
            <select id="start-month" class="border rounded-md w-full p-2 mb-4">
                @foreach (['January','February','March','April','May','June','July','August','September','October','November','December'] as $month)
                    <option>{{ $month }}</option>
                @endforeach
            </select>

            <img id="paluwagan-image2" src="" class="rounded-lg w-full h-60 object-cover mb-5">

            <div class="bg-[#FFF1F0] p-3 rounded-lg mb-4 text-sm text-gray-800">
                <p class="font-semibold mb-1">Important Reminders</p>
                <ul class="list-disc list-inside text-gray-700 space-y-1">
                    <li>Payments are due on the 15th of each month.</li>
                    <li>5-day extension for late payment, then penalty per day.</li>
                    <li>No cancellation or refund once payment starts.</li>
                    <li>All payments are non-refundable.</li>
                </ul>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button id="back-paluwagan"
                        class="border border-gray-300 rounded-lg px-4 py-2 hover:bg-gray-100 transition">Back</button>
                <button id="confirmEnrollmentBtn" class="bg-pink-600 hover:bg-pink-700 text-white font-semibold px-4 py-2 rounded">
                    Confirm Enrollment
                </button>
            </div>
        </div>

    </div>
</div>
