<div id="payment-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white w-full max-w-md rounded-lg p-6 shadow-lg">

        <h2 class="text-lg font-bold mb-4">Make Payment</h2>

        <input type="hidden" id="payment-entryID">

        <div class="mb-3">
            <p class="text-sm text-gray-500">Due Date</p>
            <p id="payment-schedule-due" class="font-semibold"></p>
        </div>

        <div class="mb-4">
            <p class="text-sm text-gray-500">Amount</p>
            <p id="payment-amount" class="font-semibold"></p>
        </div>

        <div class="flex justify-end gap-2">
            <button onclick="closePaymentModal()"
                    class="px-4 py-2 bg-gray-200 rounded">
                Cancel
            </button>

            <button class="px-4 py-2 bg-black text-white rounded">
                Pay Now
            </button>
        </div>

    </div>
</div>