<div id="paluwagan-schedule-modal"
    class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex justify-center items-center p-4">

    <div class="bg-white w-full max-w-4xl rounded-lg shadow-lg p-6 relative max-h-[90vh] overflow-y-auto">

        <!-- CLOSE BUTTON -->
        <button onclick="closeScheduleModal()"
            class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>

        <!-- HEADER -->
        <h2 id="sched-package-name" class="text-2xl font-bold mb-1"></h2>
        <!-- <p id="sched-start-month" class="text-gray-600 mb-2"></p> -->

        <!-- PACKAGE SUMMARY -->
<div class="grid grid-cols-2 gap-4 mb-4">
    <div>
        <p class="text-gray-700"><span class="font-semibold">Total Package:</span> ₱<span id="sched-total-package"></span></p>
        <p class="text-gray-700"><span class="font-semibold">Monthly Payment:</span> ₱<span id="sched-monthly-payment"></span></p>
    </div>
    <div>
        <p class="text-gray-700"><span id="sched-start-month" class="font-bold"></span></p>
        <p class="text-gray-700"><span class="font-semibold">Months Paid:</span> <span id="sched-months-paid"></span> / <span id="sched-total-months"></span></p>
        <!-- Progress bar removed -->
    </div>
</div>

 
        <!-- PAYMENT SCHEDULE TABLE -->
        <table class="w-full border text-sm">
            <thead class="bg-gray-200">
                <tr>
                    <th class="p-2">Month</th>
                    <th class="p-2">Due Date</th>
                    <th class="p-2">Amount Due</th>
                    <th class="p-2">Amount Paid</th>
                    <th class="p-2">Status</th>
                </tr>
            </thead>
            <tbody id="schedule-table-body">
                <!-- Rows inserted by AJAX -->
            </tbody>
        </table>

          <div class="bg-[#FFF1F0] p-3 rounded-lg mb-4 text-sm text-gray-800">
    <p class="font-semibold mb-1">Important Reminders</p>
    <ul class="list-disc list-inside text-gray-700 space-y-1">
        <li>Payments are due on the 15th of each month</li>
        <li>5-day extension for late payment, then penalty per day.</li>
        <li>No cancellation or refund once payment starts.</li>
        <li>Package will be delivered after final payment</li>
    </ul>
</div>

        <div class="mt-6 text-right">
            <button onclick="closeScheduleModal()"
                class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded-md">
                Close
            </button>
        </div>
        
    </div>

  

</div>

