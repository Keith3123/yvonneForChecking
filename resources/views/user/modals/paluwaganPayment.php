<!-- modals/paluwaganPayment.blade.php -->
<div id="payment-modal" 
     class="hidden fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-3">
    
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl flex flex-col" 
         style="max-height: 92vh;">
        
        <!-- {{-- Header --}} -->
        <div class="bg-pink-600 px-5 py-3 flex justify-between items-center rounded-t-2xl flex-shrink-0">
            <h2 class="text-white text-base font-bold">💳 Paluwagan Payment</h2>
            <button onclick="closePaymentModal()" 
                    class="text-white hover:text-pink-200 text-xl font-bold leading-none">✕</button>
        </div>

        <!-- {{-- Scrollable Body --}} -->
        <div class="overflow-y-auto flex-1 p-5 space-y-4" style="min-height: 0;">

            <input type="hidden" id="payment-entryID">

            <!-- {{-- Package --}} -->
            <div class="bg-pink-50 rounded-xl p-3">
                <p class="text-[10px] text-gray-500 uppercase tracking-wide">Package</p>
                <p id="payment-package-name" class="font-bold text-gray-800 text-sm"></p>
            </div>

            <!-- {{-- Upcoming Payments - COMPACT --}} -->
            <div>
                <p class="text-xs font-semibold text-gray-700 mb-2">📅 Upcoming Payments</p>
                <div id="payment-schedules-list" 
                     class="space-y-1.5 max-h-32 overflow-y-auto pr-1">
                    <!-- {{-- Filled by JS --}} -->
                </div>
            </div>

            <!-- {{-- Monthly / Remaining --}} -->
            <div class="grid grid-cols-2 gap-2">
                <div class="bg-gray-50 rounded-xl p-2.5 text-center">
                    <p class="text-[10px] text-gray-500">Monthly</p>
                    <p id="payment-monthly" class="font-bold text-gray-800 text-sm"></p>
                </div>
                <div class="bg-red-50 rounded-xl p-2.5 text-center">
                    <p class="text-[10px] text-gray-500">Remaining</p>
                    <p id="payment-total-remaining" class="font-bold text-red-600 text-sm"></p>
                </div>
            </div>

            <!-- {{-- Amount Input --}} -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                    💰 Amount to Pay
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-bold text-sm">₱</span>
                    <input type="number" 
                           id="payment-amount-input"
                           min="1"
                           step="0.01"
                           placeholder="Enter amount"
                           class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-2.5 
                                  focus:border-pink-400 focus:outline-none font-semibold text-base
                                  transition">
                </div>
                <p id="payment-amount-error" 
                   class="text-red-500 text-xs mt-1 hidden"></p>

                <!-- {{-- Quick buttons --}} -->
                <div class="flex flex-wrap gap-1.5 mt-2" id="quick-amount-btns">
                    <!-- {{-- Filled by JS --}} -->
                </div>
            </div>

        </div>

        <!-- -- Footer Buttons - ALWAYS VISIBLE --}} -->
        <div class="px-5 py-3 flex gap-2 border-t border-gray-100 flex-shrink-0 rounded-b-2xl bg-white">
            <button onclick="closePaymentModal()"
                    class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-600 rounded-xl 
                           hover:bg-gray-200 font-semibold transition text-sm">
                Cancel
            </button>
            <button id="pay-now-btn"
                    onclick="submitPaluwaganPayment()"
                    class="flex-1 px-4 py-2.5 bg-pink-600 text-white rounded-xl 
                           hover:bg-pink-700 font-semibold shadow-lg shadow-pink-200 
                           transition flex items-center justify-center gap-2 text-sm">
                <span id="pay-btn-text">Pay via GCash</span>
                <svg id="pay-btn-spinner" 
                     class="hidden animate-spin h-4 w-4 text-white" 
                     fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" 
                            stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" 
                          d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
            </button>
        </div>

    </div>
</div>