{{-- modals/paluwaganSchedule.blade.php --}}
<div id="paluwagan-schedule-modal"
     class="fixed inset-0 bg-black/50 hidden z-50 flex justify-center items-center p-4">

    <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl flex flex-col"
         style="max-height: 90vh;">

        {{-- Header --}}
        <div class="bg-pink-600 px-5 py-4 flex justify-between items-center rounded-t-2xl flex-shrink-0">
            <div>
                <h2 id="sched-package-name" class="text-white font-bold text-base"></h2>
                <p id="sched-release-date" class="text-pink-200 text-xs mt-0.5"></p>
            </div>
            <button onclick="closeScheduleModal()"
                    class="text-white hover:text-pink-200 text-xl font-bold leading-none">✕</button>
        </div>

        {{-- Summary strip --}}
        <div class="grid grid-cols-3 divide-x divide-gray-100 bg-gray-50 flex-shrink-0 border-b">
            <div class="p-3 text-center">
                <p class="text-[10px] text-gray-500 uppercase tracking-wide">Total</p>
                <p class="font-bold text-gray-800 text-sm">₱<span id="sched-total-package"></span></p>
            </div>
            <div class="p-3 text-center">
                <p class="text-[10px] text-gray-500 uppercase tracking-wide">Monthly</p>
                <p class="font-bold text-gray-800 text-sm">₱<span id="sched-monthly-payment"></span></p>
            </div>
            <div class="p-3 text-center">
                <p class="text-[10px] text-gray-500 uppercase tracking-wide">Paid</p>
                <p class="font-bold text-gray-800 text-sm">
                    <span id="sched-months-paid">0</span>/<span id="sched-total-months">0</span>
                </p>
            </div>
        </div>

        {{-- Scrollable body --}}
        <div class="overflow-y-auto flex-1 p-4 space-y-2" style="min-height: 0;">

            {{-- Loading --}}
            <div id="sched-loading" class="text-center py-8">
                <svg class="animate-spin h-6 w-6 text-pink-500 mx-auto" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <p class="text-gray-400 text-sm mt-2">Loading schedule...</p>
            </div>

            {{-- Cards --}}
            <div id="sched-card-list" class="hidden space-y-2"></div>

            {{-- Empty --}}
            <div id="sched-empty" class="hidden text-center py-10">
                <i class="fas fa-calendar-times text-gray-300 text-3xl"></i>
                <p class="text-gray-400 mt-2 text-sm">No schedule found for this entry.</p>
            </div>

            {{-- Reminders --}}
            <div id="sched-reminders"
                 class="hidden bg-pink-50 border border-pink-100 rounded-xl p-3 text-sm mt-2">
                <p class="font-semibold mb-1 text-pink-700 text-xs">Important Reminders</p>
                <ul class="list-disc list-inside space-y-0.5 text-xs text-gray-700">
                    <li>Payments are due on the 15th of each month.</li>
                    <li>5-day extension for late payment, then 30 pesos penalty per day.</li>
                    <li>No cancellation or refund once payment starts.</li>
                    <li>Full payment is required before delivery is processed.</li>
                </ul>
            </div>

        </div>

        {{-- Footer --}}
        <div class="px-5 py-3 border-t flex-shrink-0">
            <button onclick="closeScheduleModal()"
                    class="w-full py-2.5 bg-gray-100 text-gray-600 rounded-xl
                           hover:bg-gray-200 font-semibold text-sm">
                Close
            </button>
        </div>

    </div>
</div>