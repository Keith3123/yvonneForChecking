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
            <h2 class="text-2xl font-bold mb-1">Select Delivery Slot</h2>
            {{-- Updated subtitle: limit is per month, not per day --}}
            <p class="text-gray-500 text-sm mb-4">
                Choose your delivery month and day. Max <strong>20 customers per month</strong>.
            </p>

            <img id="paluwagan-image2" src="" class="rounded-lg w-full h-36 object-cover mb-4">

            <!-- ── MONTH PICKER ───────────────────────────────────── -->
            <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">① Pick a Month</p>

            <div id="months-loading" class="text-center py-4">
                <svg class="animate-spin h-5 w-5 text-pink-500 mx-auto" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <p class="text-gray-400 text-xs mt-1">Loading months...</p>
            </div>

            {{-- Month legend --}}
            <div class="flex flex-wrap gap-3 mb-2 text-xs text-gray-500">
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-400 inline-block"></span> Open
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-yellow-400 inline-block"></span> Almost full
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-300 inline-block"></span> Full (join waitlist)
                </span>
            </div>

            <div id="month-cards-grid"
                 class="hidden grid grid-cols-3 gap-2 mb-4 max-h-48 overflow-y-auto pr-1"></div>

            <!-- ── DAY PICKER ────────────────────────────────────── -->
            <div id="day-picker-section" class="hidden">
                <div class="flex items-center gap-2 mb-2 mt-3">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">② Pick a Day</p>
                    <span id="selected-month-label"
                          class="text-xs font-semibold text-pink-600 bg-pink-50 border border-pink-200
                                 px-2 py-0.5 rounded-full"></span>
                    <span class="text-xs text-gray-400">— your delivery date</span>
                </div>

                <div id="day-loading" class="hidden text-center py-3">
                    <svg class="animate-spin h-4 w-4 text-pink-500 mx-auto" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                </div>

                {{--
                    grid-cols-7 is required for the calendar layout.
                    JS inserts: 7 weekday headers + offset blank cells + day cells.
                --}}
                <div id="day-cards-grid"
                     class="hidden grid grid-cols-7 gap-1 mb-1 max-h-52 overflow-y-auto pr-0.5"></div>

                {{-- Legend rendered by JS (_renderDayGrid appends #day-legend here) --}}
            </div>

            <!-- Hidden inputs -->
            <input type="hidden" id="start-month" value="">
            <input type="hidden" id="start-day"   value="">

            <!-- Waitlist notice (shown when month is full OR day is full) -->
            <div id="waitlist-notice" class="hidden bg-yellow-50 border border-yellow-200 rounded-xl p-3 mb-3 text-sm mt-3">
                <p class="font-semibold text-yellow-800">📋 Waiting list</p>
                <p class="text-yellow-700 text-xs mt-1" id="waitlist-notice-text"></p>
            </div>

            <div class="bg-[#FFF1F0] p-3 rounded-lg mb-4 text-sm text-gray-800 mt-3">
                <p class="font-semibold mb-1">Important Reminders</p>
                <ul class="list-disc list-inside text-gray-700 space-y-1 text-xs">
                    <li>Payments start from the <strong>first available month</strong> the admin opened.</li>
                    <li>Your selected date is your <strong>delivery deadline</strong>.</li>
                    <li>Full payment is required before delivery can be processed.</li>
                    <li>All payments are non-refundable.</li>
                </ul>
            </div>

            <div class="flex justify-end gap-3 mt-4">
                <button id="back-paluwagan"
                        class="border border-gray-300 rounded-lg px-4 py-2 hover:bg-gray-100 transition text-sm">
                    Back
                </button>
                <button id="confirmEnrollmentBtn" disabled
                        class="bg-pink-600 hover:bg-pink-700 disabled:opacity-40 disabled:cursor-not-allowed
                               text-white font-semibold px-4 py-2 rounded text-sm transition">
                    Confirm Subscription
                </button>
            </div>
        </div>

    </div>
</div>