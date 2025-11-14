{{-- 1️⃣ Make Payment Modal --}}
<div id="paymentModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-3xl shadow-lg">
        <h2 class="text-xl font-bold mb-4">Make Payment - Holiday Package</h2>
        <p class="text-sm text-gray-600 mb-4">Choose your payment method and amount</p>

        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Amount</label>
        <input type="text" value="500" class="w-full border border-gray-300 rounded px-3 py-2 mb-2">
        <p class="text-sm text-gray-500 mb-4">Monthly amount: ₱500 • Remaining: ₱5,000</p>

        <div class="mb-3">
            <p class="font-medium text-gray-700 mb-1">Payment Method</p>
            <div class="flex flex-col gap-1">
                <label><input type="radio" name="method" checked> GCash Payment</label>
                <label><input type="radio" name="method"> Cash at store</label>
            </div>
        </div>

        <div class="bg-pink-50 border border-pink-100 p-3 rounded mb-4 text-sm">
            <p><strong>GCash Number:</strong> 09X-XXX-XXXX</p>
            <p><strong>Account Name:</strong> jas</p>
            <p><strong>Amount to Pay:</strong> ₱500</p>
            <label class="block mt-2 text-gray-700">Upload Proof of Payment</label>
            <input type="file" class="w-full border border-gray-300 rounded px-3 py-1 text-sm">
        </div>

        <div class="flex justify-between gap-2">
            <button onclick="toggleModal('paymentModal')" class="border border-gray-300 px-4 py-2 rounded hover:bg-gray-100">Cancel</button>
            <button class="bg-orange-200 hover:bg-orange-300 px-4 py-2 rounded font-semibold">Submit Payment</button>
        </div>

        {{-- Go to Catalog Button --}}
        <div class="mt-4">
            <a href="{{ route('catalog') }}" class="block text-center w-full bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded font-medium">Go to Catalog</a>
        </div>
    </div>
</div>

{{-- 2️⃣ Payment Schedule Modal --}}
<div id="scheduleModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto">
    <div class="bg-white rounded-lg p-6 w-full max-w-3xl shadow-lg">
        <h2 class="text-xl font-bold mb-2">Payment Schedule - Holiday Package</h2>
        <p class="text-sm text-gray-600 mb-4">Your 10-month payment plan overview</p>

        <div class="bg-pink-50 border border-pink-200 rounded p-3 mb-4 flex justify-between text-sm">
            <p><strong>Total Package:</strong> ₱5,000</p>
            <p><strong>Progress:</strong> 1/10 months</p>
            <p><strong>Monthly Payment:</strong> ₱500</p>
        </div>

        <div class="overflow-y-auto max-h-72 border border-gray-200 rounded mb-4">
            <table class="w-full text-sm">
                <thead class="bg-gray-100 sticky top-0">
                    <tr class="text-left text-gray-700">
                        <th class="p-2">Month</th>
                        <th class="p-2">Due Date</th>
                        <th class="p-2">Amount</th>
                        <th class="p-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b">
                        <td class="p-2">Month 1</td>
                        <td class="p-2">Jan 15, 2024</td>
                        <td class="p-2">₱500</td>
                        <td class="p-2"><span class="text-green-600 font-semibold">PAID</span></td>
                    </tr>
                    <tr class="border-b">
                        <td class="p-2">Month 2</td>
                        <td class="p-2">Feb 15, 2024</td>
                        <td class="p-2">₱500</td>
                        <td class="p-2"><span class="text-red-500 font-semibold">OVERDUE</span></td>
                    </tr>
                    <tr class="border-b">
                        <td class="p-2">Month 3</td>
                        <td class="p-2">Mar 15, 2024</td>
                        <td class="p-2">₱500</td>
                        <td class="p-2"><span class="text-red-500 font-semibold">OVERDUE</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="bg-pink-50 border border-pink-200 rounded p-3 text-sm mb-4">
            <p class="font-semibold mb-1">Important Reminders:</p>
            <ul class="list-disc list-inside text-gray-700">
                <li>Due date is every last day of the month</li>
                <li>5 days extension for late payment; after 5 days, penalty per day applies</li>
                <li>Once payment starts, no cancellation or refund</li>
                <li>All payments are non-refundable</li>
            </ul>
        </div>

        <div class="flex justify-between">
            <button onclick="toggleModal('scheduleModal')" class="border border-gray-300 px-4 py-2 rounded hover:bg-gray-100">Close</button>
            <a href="{{ route('catalog') }}" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded font-medium text-center">Go to Catalog</a>
        </div>
    </div>
</div>

{{-- 3️⃣ Cancel Subscription Modal --}}
<div id="cancelModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-3xl shadow-lg">
        <h2 class="text-xl font-bold mb-2">Cancel Order</h2>
        <p class="text-sm text-gray-600 mb-4">Are you sure you want to cancel this order?</p>

        <div class="bg-pink-50 border border-pink-200 rounded p-4 mb-4 text-sm">
            <p class="font-semibold text-red-600 mb-1">⚠ Please Note:</p>
            <p>Cancelling this order may result in a non-refundable downpayment depending on our cancellation policy. Please contact support for more information.</p>
        </div>

        <div class="flex justify-between gap-2">
            <button onclick="toggleModal('cancelModal')" class="border border-gray-300 px-4 py-2 rounded hover:bg-gray-100">Keep Order</button>
            <button class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Yes, Cancel Order</button>
        </div>

        <div class="mt-4">
            <a href="{{ route('catalog') }}" class="block text-center w-full bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded font-medium">Go to Catalog</a>
        </div>
    </div>
</div>
