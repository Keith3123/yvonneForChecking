{{-- user/modals/paluwagan.blade.php --}}
<div id="paluwagan-modal"
     data-package=""
     class="fixed inset-0 hidden z-50 flex items-center justify-center">

    <!-- Overlay -->
    <div class="modal-overlay absolute inset-0 bg-black/50"></div>

    <!-- Modal content -->
    <div class="bg-white rounded-2xl p-6 max-w-lg w-full relative overflow-y-auto max-h-[90vh] z-10">

        <!-- Close Button -->
        <button id="close-modal-paluwagan"
                class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 text-2xl">&times;</button>

        <!-- STEP 1 -->
        <div id="paluwagan-step1">
            <h2 id="paluwagan-name" class="text-2xl font-bold mb-1"></h2>

            <img id="paluwagan-image"
                 src=""
                 class="rounded-lg w-full h-60 object-cover mb-5">

            <div class="mt-4">
                <h3 class="font-semibold text-gray-800 mb-1">What's Included</h3>
                <ul id="paluwagan-desc"
                    class="list-disc ml-6 text-gray-500 mb-2 space-y-1"></ul>
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
            <h2 class="text-2xl font-bold mb-1">Select Start Month</h2>
            <p class="text-gray-500 text-sm mb-4">Choose an available month to start your Paluwagan.</p>

            <img id="paluwagan-image2"
                 src=""
                 class="rounded-lg w-full h-44 object-cover mb-5">

            <!-- Month loading state -->
            <div id="months-loading" class="text-center py-6">
                <svg class="animate-spin h-6 w-6 text-pink-500 mx-auto" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <p class="text-gray-400 text-sm mt-2">Loading available months...</p>
            </div>

            <!-- Legend -->
            <div id="months-legend" class="hidden flex flex-wrap gap-3 mb-3 text-xs">
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-green-400 inline-block"></span> Available
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-red-300 inline-block"></span> Taken
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-yellow-400 inline-block"></span> You're Waiting
                </span>
            </div>

            <!-- Month grid — filled by JS -->
            <div id="month-cards-grid"
                 class="hidden grid grid-cols-3 gap-2 mb-4 max-h-72 overflow-y-auto pr-1">
                <!-- JS renders cards here -->
            </div>

            <!-- Hidden input to carry selected month value -->
            <input type="hidden" id="start-month" value="">

            <!-- Waitlist notice (shown when user picks a taken month) -->
            <div id="waitlist-notice" class="hidden bg-yellow-50 border border-yellow-200 rounded-xl p-3 mb-4 text-sm">
                <p class="font-semibold text-yellow-800">📋 You'll be added to the waiting list</p>
                <p class="text-yellow-700 text-xs mt-1" id="waitlist-notice-text"></p>
            </div>

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
                        class="border border-gray-300 rounded-lg px-4 py-2 hover:bg-gray-100 transition">
                    Back
                </button>

                <button id="confirmEnrollmentBtn"
                        disabled
                        class="bg-pink-600 hover:bg-pink-700 disabled:opacity-40 disabled:cursor-not-allowed
                               text-white font-semibold px-4 py-2 rounded transition">
                    Confirm Enrollment
                </button>
            </div>
        </div>

    </div>
</div>