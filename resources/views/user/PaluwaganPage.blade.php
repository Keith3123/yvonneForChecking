@extends('layouts.app')

@section('no-footer')
@endsection

@section('content')
<div class="bg-gradient-to-b from-[#FFF8F5] to-[#FFEDE8] min-h-screen py-12 px-4">
    <div class="max-w-5xl mx-auto">

        {{-- ============================================ --}}
        {{-- HEADER --}}
        {{-- ============================================ --}}
        <div class="mb-10 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">My Paluwagan</h2>
                <p class="text-gray-600 text-sm">
                    Manage your installment plans and payment schedules
                </p>
            </div>
            <a href="/catalog"
               class="inline-flex items-center justify-center bg-orange-300 hover:bg-orange-400 text-gray-900 px-5 py-2.5 rounded-lg font-semibold text-sm shadow transition">
                Go to Catalog
            </a>
        </div>

        {{-- ============================================ --}}
        {{-- FEATURES BANNER --}}
        {{-- ============================================ --}}
        @php
            $features = [
                ['icon' => '💸', 'title' => 'Fixed Monthly Payment', 'desc' => 'Pay required amount on every due date'],
                ['icon' => '📅', 'title' => 'Flexible Entry',        'desc' => 'Join at any available month'],
                ['icon' => '✅', 'title' => 'Premium Quality',       'desc' => 'High quality and trusted food'],
            ];
        @endphp

        <div class="bg-blue-50 border border-blue-100 rounded-xl p-6 grid md:grid-cols-3 gap-6 mb-10 shadow-sm">
            @foreach ($features as $feature)
                <div class="text-center">
                    <div class="text-2xl mb-2">{{ $feature['icon'] }}</div>
                    <h3 class="font-semibold text-gray-900">{{ $feature['title'] }}</h3>
                    <p class="text-sm text-gray-500">{{ $feature['desc'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- ============================================ --}}
        {{-- PALUWAGAN ENTRIES --}}
        {{-- ============================================ --}}
        @forelse ($entries as $entry)
        @php
            $schedules    = $entry->schedules ?? collect();
            $totalPaid    = $schedules->sum('amountPaid');
            $totalMonths  = $schedules->count();
            $monthsPaid   = $schedules->filter(fn($s) =>
                                (float)$s->amountPaid >= (float)$s->amountDue && (float)$s->amountDue > 0
                            )->count();
            $monthsLeft   = $totalMonths - $monthsPaid;
            $nextSchedule = $schedules
                ->filter(fn($s) => !in_array($s->status, ['paid', 'cancelled'])
                                   && (float)$s->amountPaid < (float)$s->amountDue)
                ->sortBy('dueDate')
                ->first();
            $package      = $entry->package;
            $isReleased   = !is_null($entry->releasedAt);
            $isRelReq     = $entry->status === 'release_requested';
        @endphp

        <div class="bg-white rounded-xl shadow-md border border-red-100 p-6 mb-8">

            {{-- Entry Header --}}
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="text-green-600">✔</span> {{ $package?->packageName }}
                    </h3>
                    @php $descLines = preg_split('/\r\n|\r|\n/', $package?->description ?? ''); @endphp
                    <ul class="text-sm text-gray-600 mt-1 space-y-0.5 list-none">
                        @foreach($descLines as $line)
                            @if(trim($line))
                            <li class="flex items-start gap-1.5">
                                <span class="text-green-500 mt-0.5 flex-shrink-0">•</span>
                                <span>{{ trim($line) }}</span>
                            </li>
                            @endif
                        @endforeach
                    </ul>
                    <p class="text-sm text-gray-600 mt-1">
                        Started:
                        {{ \Carbon\Carbon::create()->month($entry->startMonth)->year($entry->startYear)->format('F Y') }}
                        • Monthly:
                        <span class="font-semibold">₱{{ number_format($package?->monthlyPayment ?? 0, 2) }}</span>
                    </p>
                </div>

                <div class="flex flex-col items-end gap-1">
                    {{-- Main status badge --}}
                    <span class="text-sm font-semibold px-3 py-1 rounded-full
                        @switch($entry->status)
                            @case('active')            bg-green-100  text-green-800  ring-1 ring-green-300  @break
                            @case('release_requested') bg-orange-100 text-orange-800 ring-1 ring-orange-300 @break
                            @case('completed')         bg-gray-100   text-gray-800   ring-1 ring-gray-300   @break
                            @case('cancelled')         bg-red-100    text-red-800    ring-1 ring-red-300    @break
                        @endswitch">
                        @if($isRelReq) ⏳ Release Pending
                        @else {{ ucfirst($entry->status) }}
                        @endif
                    </span>

                    {{-- Released badge --}}
                    @if($isReleased)
                        <span class="text-xs bg-green-100 text-green-700 border border-green-200 px-2 py-0.5 rounded-full font-semibold">
                            📦 Released {{ \Carbon\Carbon::parse($entry->releasedAt)->format('M d, Y') }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Progress bar --}}
            <div class="mb-6">
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>{{ $monthsPaid }} of {{ $totalMonths }} months paid</span>
                    <span>₱{{ number_format(($package?->totalAmount ?? 0) - $totalPaid, 2) }} remaining</span>
                </div>
                <div class="w-full bg-gray-200 h-2 rounded-full overflow-hidden">
                    <div class="h-2 bg-gradient-to-r from-green-400 to-green-600 rounded-full transition-all"
                         style="width: {{ $totalMonths > 0 ? ($monthsPaid / $totalMonths) * 100 : 0 }}%"></div>
                </div>
            </div>

            {{-- Metrics --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="border rounded-lg p-4 text-center bg-gray-50">
                    <p class="text-lg font-bold">₱{{ number_format($package?->totalAmount ?? 0, 2) }}</p>
                    <p class="text-xs text-gray-500">Package Amount</p>
                </div>
                <div class="border rounded-lg p-4 text-center bg-gray-50">
                    <p class="text-lg font-bold">₱{{ number_format($totalPaid, 2) }}</p>
                    <p class="text-xs text-gray-500">Total Paid</p>
                </div>
                <div class="border rounded-lg p-4 text-center bg-gray-50">
                    <p class="text-lg font-bold">{{ $monthsPaid }}</p>
                    <p class="text-xs text-gray-500">Months Paid</p>
                </div>
                <div class="border rounded-lg p-4 text-center bg-gray-50">
                    <p class="text-lg font-bold">{{ $monthsLeft }}</p>
                    <p class="text-xs text-gray-500">Months Left</p>
                </div>
            </div>

            {{-- Next payment alert --}}
            @if($nextSchedule)
            <div class="flex items-center gap-2 bg-blue-50 border border-blue-100 rounded-lg p-3 text-sm mb-6">
                📅 <span>Next payment due:
                    <strong>
                        {{ \Carbon\Carbon::parse($nextSchedule->dueDate)->format('M d, Y') }}
                        – ₱{{ number_format($nextSchedule->amountDue, 2) }}
                    </strong>
                </span>
            </div>
            @endif

            {{-- Release request note --}}
            @if($isRelReq && $entry->releaseNote)
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 text-sm mb-6">
                📝 Your release note: <em class="text-orange-700">"{{ $entry->releaseNote }}"</em>
            </div>
            @endif

            {{-- ===== ACTIONS ===== --}}
            <div class="flex flex-wrap gap-3">

                {{-- Make Payment --}}
                @if(in_array($entry->status, ['active', 'release_requested']))
                    <button onclick="openPaymentModal('{{ $entry->paluwaganEntryID }}')"
                        class="bg-black text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-gray-800 transition">
                        Make Payment
                    </button>
                @endif

                {{-- View Schedule --}}
                <button onclick="openScheduleModal('{{ $entry->paluwaganEntryID }}')"
                        class="border px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-gray-100 transition">
                    View Schedule
                </button>

                {{-- Early Release Request --}}
                @if($entry->status === 'active' && !$isReleased)
                    <button onclick="openReleaseModal('{{ $entry->paluwaganEntryID }}')"
                        class="border border-orange-300 text-orange-600 px-5 py-2.5 rounded-lg text-sm
                               font-semibold hover:bg-orange-50 transition">
                        📦 Request Early Release
                    </button>
                @endif

                {{-- Withdraw Release Request --}}
                @if($isRelReq)
                    <button onclick="openWithdrawModal('{{ $entry->paluwaganEntryID }}')"
                        class="text-gray-500 border border-gray-300 px-5 py-2.5 rounded-lg text-sm
                               font-semibold hover:bg-gray-100 transition">
                        Withdraw Release Request
                    </button>
                @endif

                {{-- Cancel Subscription --}}
                @if($entry->status === 'active' && !$isRelReq && !$isReleased)
                    <button onclick="openCancelModal('{{ $entry->paluwaganEntryID }}')"
                        class="text-red-600 border border-red-300 px-5 py-2.5 rounded-lg text-sm
                            font-semibold hover:bg-red-50 transition">
                        Cancel Subscription
                    </button>
                @endif
            </div>
        </div>

        @empty
        <p class="text-gray-500 text-center py-16">
            You have no active paluwagan orders.
            <a href="/catalog" class="text-orange-500 underline">Go to Catalog</a>
        </p>
        @endforelse

    </div>
</div>

{{-- ============================================ --}}
{{-- SCHEDULE MODAL --}}
{{-- ============================================ --}}
@include('user.modals.paluwaganSchedule')

{{-- ============================================ --}}
{{-- PAYMENT MODAL --}}
{{-- ============================================ --}}
@include('user.modals.paluwaganPayment')

{{-- ============================================ --}}
{{-- CANCEL MODAL --}}
{{-- ============================================ --}}
<div id="cancel-modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 text-center">
        <div class="mx-auto w-14 h-14 flex items-center justify-center rounded-full bg-red-100 mb-4">
            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M6 7h12M9 7v10m6-10v10M10 11v6m4-6v6M4 7h16l-1 12H5L4 7z" />
            </svg>
        </div>
        <h2 class="text-lg font-semibold text-gray-900 mb-2">Cancel Subscription?</h2>
        <p class="text-sm text-gray-500 mb-6">
            Are you sure you want to cancel? This action cannot be undone.
        </p>
        <div class="flex gap-3">
            <button onclick="closeCancelModal()"
                class="w-full bg-gray-100 text-gray-700 py-2.5 rounded-lg font-semibold hover:bg-gray-200 transition">
                Go Back
            </button>
            <button id="confirm-cancel-btn"
                class="w-full bg-red-500 text-white py-2.5 rounded-lg font-semibold hover:bg-red-600 transition
                       flex items-center justify-center gap-2">
                <span id="cancel-btn-text">Yes, Cancel</span>
                <svg id="cancel-spinner" class="hidden animate-spin h-4 w-4 text-white"
                     fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
            </button>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- EARLY RELEASE REQUEST MODAL --}}
{{-- ============================================ --}}
<div id="release-modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center text-xl">📦</div>
            <div>
                <h2 class="text-lg font-bold text-gray-900">Request Early Release</h2>
                <p class="text-xs text-gray-500">Admin will review and process your request</p>
            </div>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-4 text-sm">
            <p class="font-semibold text-yellow-800 mb-1">⚠️ Before you request:</p>
            <ul class="list-disc list-inside text-yellow-700 space-y-1 text-xs">
                <li>Your product will be delivered before full payment</li>
                <li>Monthly payment schedule <strong>continues as normal</strong></li>
                <li>You are still obligated to complete all remaining payments</li>
                <li>Admin will contact you to arrange delivery</li>
            </ul>
        </div>

        <div class="mb-5">
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                Reason / Note <span class="text-gray-400 font-normal">(optional)</span>
            </label>
            <textarea id="release-note" rows="3"
                      placeholder="e.g. I need it for an event on June 20..."
                      class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5
                             focus:border-orange-400 focus:outline-none text-sm resize-none"></textarea>
        </div>

        <div class="flex gap-3">
            <button onclick="closeReleaseModal()"
                class="flex-1 bg-gray-100 text-gray-700 py-2.5 rounded-xl font-semibold
                       hover:bg-gray-200 transition text-sm">
                Cancel
            </button>
            <button id="confirm-release-btn"
                class="flex-1 bg-orange-500 text-white py-2.5 rounded-xl font-semibold
                       hover:bg-orange-600 transition text-sm flex items-center justify-center gap-2">
                <span id="release-btn-text">Submit Request</span>
                <svg id="release-spinner" class="hidden animate-spin h-4 w-4 text-white"
                     fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
            </button>
        </div>
    </div>
</div>

{{-- ============================================ --}}
{{-- WITHDRAW RELEASE REQUEST MODAL --}}
{{-- ============================================ --}}
<div id="withdraw-release-modal"
     class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 text-center">
        <div class="mx-auto w-14 h-14 flex items-center justify-center rounded-full bg-yellow-100 mb-4">
            <span class="text-2xl">📦</span>
        </div>
        <h2 class="text-lg font-semibold text-gray-900 mb-2">Withdraw Release Request?</h2>
        <p class="text-sm text-gray-500 mb-6">
            Your early release request will be cancelled. You can submit a new request anytime.
        </p>
        <div class="flex gap-3">
            <button onclick="closeWithdrawModal()"
                class="w-full bg-gray-100 text-gray-700 py-2.5 rounded-xl font-semibold
                       hover:bg-gray-200 transition text-sm">
                Keep Request
            </button>
            <button id="confirm-withdraw-btn"
                class="w-full bg-yellow-500 text-white py-2.5 rounded-xl font-semibold
                       hover:bg-yellow-600 transition text-sm flex items-center justify-center gap-2">
                <span id="withdraw-btn-text">Yes, Withdraw</span>
                <svg id="withdraw-spinner" class="hidden animate-spin h-4 w-4 text-white"
                     fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
// =============================================
// SCHEDULE MODAL
// =============================================
async function openScheduleModal(entryID) {
    const modal = document.getElementById('paluwagan-schedule-modal');
    modal.classList.remove('hidden');

    try {
        const response = await fetch(`/paluwagan/schedule/${entryID}`);
        const data     = await response.json();
        const entry    = data.entry;
        const schedules = data.schedules;

        document.getElementById('sched-package-name').textContent   = entry.name;
        document.getElementById('sched-start-month').textContent     = 'Start: ' + entry.startDate;
        document.getElementById('sched-total-package').textContent   = parseFloat(entry.totalPackage).toFixed(2);
        document.getElementById('sched-monthly-payment').textContent = parseFloat(entry.monthlyPayment).toFixed(2);

        const tbody = document.getElementById('schedule-table-body');
        tbody.innerHTML = '';

        schedules.forEach(sched => {
            const isLate       = new Date() > new Date(sched.dueDate);
            const status       = sched.isPaid ? 'Paid' : (isLate ? 'Late' : 'Pending');
            const statusColor  = sched.isPaid ? 'text-green-600' : (isLate ? 'text-red-600' : 'text-yellow-600');

            tbody.innerHTML += `
                <tr class="border-b">
                    <td class="p-2 text-center">${sched.monthName}</td>
                    <td class="p-2 text-center">${new Date(sched.dueDate).toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' })}</td>
                    <td class="p-2 text-center">₱${parseFloat(sched.amountDue).toFixed(2)}</td>
                    <td class="p-2 text-center">₱${parseFloat(sched.amountPaid).toFixed(2)}</td>
                    <td class="p-2 text-center ${statusColor} font-semibold">${status}</td>
                </tr>`;
        });

        document.getElementById('sched-months-paid').textContent  = schedules.filter(s => s.isPaid).length;
        document.getElementById('sched-total-months').textContent = schedules.length;

    } catch (err) {
        console.error('Schedule load error:', err);
    }
}

function closeScheduleModal() {
    const modal = document.getElementById('paluwagan-schedule-modal');
    if (modal) modal.classList.add('hidden');
}

// =============================================
// CANCEL MODAL
// =============================================
let cancelEntryID = null;
let isCancelling  = false;

function openCancelModal(entryID) {
    cancelEntryID = entryID;
    const modal   = document.getElementById('cancel-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeCancelModal() {
    const modal = document.getElementById('cancel-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    cancelEntryID = null;
}

document.getElementById('confirm-cancel-btn').addEventListener('click', async function () {
    if (!cancelEntryID || isCancelling) return;
    isCancelling = true;

    const btnText = document.getElementById('cancel-btn-text');
    const spinner = document.getElementById('cancel-spinner');
    btnText.textContent = 'Cancelling...';
    spinner.classList.remove('hidden');

    try {
        const res  = await fetch(`/paluwagan/cancel/${cancelEntryID}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Cancel failed');

        closeCancelModal();
        if (typeof showToast === 'function') showToast('Subscription cancelled successfully', 'success');
        setTimeout(() => location.reload(), 1500);

    } catch (err) {
        console.error(err);
        if (typeof showToast === 'function') showToast(err.message || 'Something went wrong', 'error');
        else alert(err.message || 'Something went wrong');
    } finally {
        isCancelling        = false;
        btnText.textContent = 'Yes, Cancel';
        spinner.classList.add('hidden');
    }
});

// =============================================
// PAYMENT MODAL (GCASH)
// =============================================
let currentSchedules      = [];
let currentMonthlyPayment = 0;
let currentTotalRemaining = 0;

function openPaymentModal(entryID) {
    const modal = document.getElementById('payment-modal');
    if (!modal) { console.error('payment-modal missing'); return; }

    document.getElementById('payment-entryID').value = entryID;
    document.getElementById('payment-schedules-list').innerHTML =
        '<p class="text-gray-400 text-sm text-center py-4">Loading...</p>';

    const btn = document.getElementById('pay-now-btn');
    btn.disabled = false;
    document.getElementById('pay-btn-text').textContent = 'Pay via GCash';
    document.getElementById('pay-btn-spinner').classList.add('hidden');

    modal.classList.remove('hidden');

    fetch(`/paluwagan/schedule/${entryID}`)
        .then(res => res.json())
        .then(data => {
            if (data.status !== 'OK') {
                document.getElementById('payment-schedules-list').innerHTML =
                    '<p class="text-red-500 text-sm text-center py-4">No schedule found</p>';
                return;
            }

            const entry     = data.entry;
            const schedules = data.schedules;

            currentMonthlyPayment = parseFloat(entry.monthlyPayment) || 0;

            document.getElementById('payment-package-name').textContent = entry.name;
            document.getElementById('payment-monthly').textContent =
                '₱' + currentMonthlyPayment.toLocaleString('en-PH', { minimumFractionDigits: 2 });

            currentSchedules      = schedules.filter(s => !s.isPaid);
            currentTotalRemaining = currentSchedules.reduce((sum, s) =>
                sum + (parseFloat(s.amountDue) - parseFloat(s.amountPaid)), 0);

            document.getElementById('payment-total-remaining').textContent =
                '₱' + currentTotalRemaining.toLocaleString('en-PH', { minimumFractionDigits: 2 });

            const listEl = document.getElementById('payment-schedules-list');

            if (currentSchedules.length === 0) {
                listEl.innerHTML = `
                    <div class="text-center py-4 text-green-600">
                        <p class="text-sm font-semibold">✅ All payments complete!</p>
                    </div>`;
                document.getElementById('pay-now-btn').disabled = true;
                return;
            }

            listEl.innerHTML = currentSchedules.slice(0, 5).map((s, i) => {
                const remaining = parseFloat(s.amountDue) - parseFloat(s.amountPaid);
                const isLate    = s.status === 'late';
                const dueDate   = new Date(s.dueDate).toLocaleDateString('en-PH', {
                    month: 'short', day: 'numeric', year: 'numeric'
                });

                return `
                    <div class="flex items-center justify-between p-3 rounded-xl border
                        ${isLate ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200'}">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full text-xs font-bold flex items-center
                                         justify-center text-white
                                         ${isLate ? 'bg-red-500' : 'bg-pink-500'}">
                                ${i + 1}
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-gray-700">${s.monthName}</p>
                                <p class="text-xs text-gray-400">Due: ${dueDate}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold ${isLate ? 'text-red-600' : 'text-gray-800'}">
                                ₱${remaining.toLocaleString('en-PH', { minimumFractionDigits: 2 })}
                            </p>
                            ${isLate ? '<span class="text-xs text-red-500 font-medium">LATE</span>' : ''}
                        </div>
                    </div>`;
            }).join('');

            if (currentSchedules.length > 5) {
                listEl.innerHTML += `
                    <p class="text-center text-xs text-gray-400 py-1">
                        ...and ${currentSchedules.length - 5} more payments
                    </p>`;
            }

            // Quick amount buttons
            const quickBtns = document.getElementById('quick-amount-btns');
            const options   = [
                { label: '1 Month',  amount: currentMonthlyPayment },
                { label: '3 Months', amount: currentMonthlyPayment * 3 },
                { label: 'All',      amount: currentTotalRemaining },
            ].filter(o => o.amount <= currentTotalRemaining && o.amount > 0);

            const seen = new Set();
            quickBtns.innerHTML = options
                .filter(o => {
                    const key = o.amount.toFixed(2);
                    if (seen.has(key)) return false;
                    seen.add(key);
                    return true;
                })
                .map(o => `
                    <button type="button" onclick="setQuickAmount(${o.amount})"
                            class="flex-1 px-3 py-2 text-xs font-semibold border-2 border-pink-200
                                   text-pink-600 rounded-lg hover:bg-pink-50 transition">
                        ${o.label}<br>
                        <span class="text-gray-500">₱${o.amount.toLocaleString('en-PH', { minimumFractionDigits: 2 })}</span>
                    </button>`)
                .join('');

            document.getElementById('payment-amount-input').value = currentMonthlyPayment.toFixed(2);
        })
        .catch(err => {
            console.error('Schedule fetch error:', err);
            document.getElementById('payment-schedules-list').innerHTML =
                '<p class="text-red-500 text-sm text-center py-4">Failed to load schedule</p>';
        });
}

function setQuickAmount(amount) {
    document.getElementById('payment-amount-input').value = amount.toFixed(2);
    document.getElementById('payment-amount-error').classList.add('hidden');
}

function closePaymentModal() {
    document.getElementById('payment-modal').classList.add('hidden');
    document.getElementById('payment-amount-input').value = '';
    document.getElementById('payment-amount-error').classList.add('hidden');
    currentSchedules = [];
}

function submitPaluwaganPayment() {
    const entryID = document.getElementById('payment-entryID').value;
    const amount  = parseFloat(document.getElementById('payment-amount-input').value);
    const errEl   = document.getElementById('payment-amount-error');
    const btn     = document.getElementById('pay-now-btn');
    const btnText = document.getElementById('pay-btn-text');
    const spinner = document.getElementById('pay-btn-spinner');

    if (!amount || amount <= 0) {
        errEl.textContent = 'Please enter a valid amount.';
        errEl.classList.remove('hidden');
        return;
    }
    if (amount > currentTotalRemaining) {
        errEl.textContent = `Amount exceeds total remaining ₱${currentTotalRemaining.toFixed(2)}`;
        errEl.classList.remove('hidden');
        return;
    }

    errEl.classList.add('hidden');
    btn.disabled        = true;
    btnText.textContent = 'Processing...';
    spinner.classList.remove('hidden');

    fetch('/paluwagan/pay-gcash', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ entryID: parseInt(entryID), amount }),
    })
    .then(async res => {
        const text = await res.text();
        try { return JSON.parse(text); }
        catch (e) { throw new Error('Server error - check console'); }
    })
    .then(data => {
        if (data.checkout_url) {
            window.location.href = data.checkout_url;
        } else {
            errEl.textContent = data.error || 'Payment failed. Try again.';
            errEl.classList.remove('hidden');
            btn.disabled        = false;
            btnText.textContent = 'Pay via GCash';
            spinner.classList.add('hidden');
        }
    })
    .catch(err => {
        console.error(err);
        errEl.textContent = err.message || 'Server error. Please try again.';
        errEl.classList.remove('hidden');
        btn.disabled        = false;
        btnText.textContent = 'Pay via GCash';
        spinner.classList.add('hidden');
    });
}

// =============================================
// EARLY RELEASE MODAL
// =============================================
let releaseEntryID      = null;
let isSubmittingRelease = false;

function openReleaseModal(entryID) {
    releaseEntryID = entryID;
    document.getElementById('release-note').value = '';
    const modal = document.getElementById('release-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeReleaseModal() {
    const modal = document.getElementById('release-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    releaseEntryID = null;
}

document.getElementById('confirm-release-btn').addEventListener('click', async function () {
    if (!releaseEntryID || isSubmittingRelease) return;
    isSubmittingRelease = true;

    const btnText = document.getElementById('release-btn-text');
    const spinner = document.getElementById('release-spinner');
    btnText.textContent = 'Submitting...';
    spinner.classList.remove('hidden');

    try {
        const note = document.getElementById('release-note').value.trim();
        const res  = await fetch(`/paluwagan/entry/${releaseEntryID}/request-release`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ note })
        });

        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Request failed');

        closeReleaseModal();
        if (typeof showToast === 'function') showToast('Release request submitted! Admin will review shortly.', 'success');
        setTimeout(() => location.reload(), 1500);

    } catch (err) {
        console.error(err);
        if (typeof showToast === 'function') showToast(err.message || 'Something went wrong', 'error');
        else alert(err.message);
    } finally {
        isSubmittingRelease = false;
        btnText.textContent = 'Submit Request';
        spinner.classList.add('hidden');
    }
});

// =============================================
// WITHDRAW RELEASE REQUEST MODAL
// =============================================
let withdrawEntryID = null;
let isWithdrawing   = false;

// ✅ Replaces the old cancelReleaseRequest() that used browser confirm()
function openWithdrawModal(entryID) {
    withdrawEntryID = entryID;
    const modal     = document.getElementById('withdraw-release-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

// Keep old name as alias so existing blade calls still work
function cancelReleaseRequest(entryID) {
    openWithdrawModal(entryID);
}

function closeWithdrawModal() {
    const modal = document.getElementById('withdraw-release-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    withdrawEntryID = null;
}

document.getElementById('confirm-withdraw-btn').addEventListener('click', async function () {
    if (!withdrawEntryID || isWithdrawing) return;
    isWithdrawing = true;

    const btnText = document.getElementById('withdraw-btn-text');
    const spinner = document.getElementById('withdraw-spinner');
    btnText.textContent = 'Withdrawing...';
    spinner.classList.remove('hidden');

    try {
        const res  = await fetch(`/paluwagan/entry/${withdrawEntryID}/cancel-release-request`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);

        closeWithdrawModal();
        if (typeof showToast === 'function') showToast('Release request withdrawn.', 'success');
        setTimeout(() => location.reload(), 1200);

    } catch (err) {
        console.error(err);
        if (typeof showToast === 'function') showToast(err.message || 'Something went wrong', 'error');
        else alert(err.message);
    } finally {
        isWithdrawing       = false;
        btnText.textContent = 'Yes, Withdraw';
        spinner.classList.add('hidden');
    }
});
</script>
@endpush
@endsection