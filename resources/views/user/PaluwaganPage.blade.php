@extends('layouts.app')

@section('no-footer')
@endsection

@section('content')
<div class="bg-gradient-to-b from-[#FFF8F5] to-[#FFEDE8] min-h-screen py-12 px-4">
    <div class="max-w-5xl mx-auto">

        {{-- Header --}}
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

        {{-- Features --}}
        @php
            $features = [
                ['icon' => '💸', 'title' => 'Fixed Monthly Payment', 'desc' => 'Pay required amount on every due date'],
                ['icon' => '📅', 'title' => 'Flexible Entry', 'desc' => 'Join at any available month'],
                ['icon' => '✅', 'title' => 'Premium Quality', 'desc' => 'High quality and trusted food'],
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

        {{-- Paluwagan Entries --}}
        @forelse ($entries as $entry)
        @php
            $schedules = $entry->schedules ?? collect();

            $totalPaid = $schedules->sum('amountPaid');
            $totalMonths = $schedules->count();
            $monthsPaid = $schedules->where('status', 'paid')->count();
            $monthsLeft = $totalMonths - $monthsPaid;

            $nextSchedule = $schedules
                ->where('status', '!=', 'paid')
                ->sortBy('dueDate')
                ->first();

            $package = $entry->package;
        @endphp

        <div class="bg-white rounded-xl shadow-md border border-red-100 p-6 mb-8">

            {{-- Header --}}
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="text-green-600">✔</span> {{ $package?->packageName }}
                    </h3>
                    <p class="text-sm text-gray-600">{{ $package?->description ?? '' }}</p>
                    <p class="text-sm text-gray-600 mt-1">
                        Started: {{ \Carbon\Carbon::create()
                        ->month($entry->startMonth)
                        ->year($entry->startYear)
                        ->format('F Y') }} • Monthly:
                        <span class="font-semibold">₱{{ number_format($package?->monthlyPayment ?? 0,2) }}</span>
                    </p>
                </div>

                <span class="text-sm font-semibold px-3 py-1 rounded-full
                    @switch($entry->status)
                        @case('active')
                            bg-green-100 text-green-800 ring-1 ring-green-300
                            @break
                        @case('completed')
                            bg-gray-100 text-gray-800 ring-1 ring-gray-300
                             @break
                            @break
                        @case('cancelled')
                            bg-red-100 text-red-800 ring-1 ring-red-300
                            @break
                    @endswitch">
                    {{ ucfirst($entry->status) }}
                        </span>

            </div>

            {{-- Progress --}}
            <div class="mb-6">
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>{{ $monthsPaid }} of {{ $totalMonths }} months paid</span>
                    <span>₱{{ number_format(($package?->totalAmount ?? 0) - $totalPaid,2) }} remaining</span>
                </div>
                <div class="w-full bg-gray-200 h-2 rounded-full overflow-hidden">
                    <div class="h-2 bg-gradient-to-r from-green-400 to-green-600 rounded-full"
                         style="width: {{ $totalMonths > 0 ? ($monthsPaid/$totalMonths)*100 : 0 }}%"></div>
                </div>
            </div>

            {{-- Metrics --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="border rounded-lg p-4 text-center bg-gray-50">
                    <p class="text-lg font-bold">₱{{ number_format($package?->totalAmount ?? 0,2) }}</p>
                    <p class="text-xs text-gray-500">Package Amount</p>
                </div>
                <div class="border rounded-lg p-4 text-center bg-gray-50">
                    <p class="text-lg font-bold">₱{{ number_format($totalPaid,2) }}</p>
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

            {{-- Next Payment --}}
            @if($nextSchedule)
            <div class="flex items-center gap-2 bg-blue-50 border border-blue-100 rounded-lg p-3 text-sm mb-6">
                📅 <span>Next payment due: <strong>{{ \Carbon\Carbon::parse($nextSchedule->dueDate)->format('M d, Y') }} – ₱{{ number_format($nextSchedule->amountDue,2) }}</strong></span>
            </div>
            @endif

            {{-- Actions --}}
            <div class="flex flex-wrap gap-3">
                @if($entry->status === 'active')
                    <button onclick="openPaymentModal('{{ $entry->paluwaganEntryID }}')"
                        class="bg-black text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-gray-800 transition">
                        Make Payment
                    </button>
                @endif

                <button onclick="openScheduleModal('{{ $entry->paluwaganEntryID }}')"
                        class="border px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-gray-100 transition">
                    View Schedule
                </button>
                 
                @if($entry->status === 'active')
                <button onclick="openCancelModal('{{ $entry->paluwaganEntryID }}')" class="text-red-600 border border-red-300 px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-red-50 transition">
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

{{-- Include Modals --}}
@include('user.modals.paluwaganSchedule')
@include('user.modals.paluwaganPayment')

<div id="cancel-modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 text-center">

        <!-- Icon -->
        <div class="mx-auto w-14 h-14 flex items-center justify-center rounded-full bg-red-100 mb-4">
            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M6 7h12M9 7v10m6-10v10M10 11v6m4-6v6M4 7h16l-1 12H5L4 7z" />
            </svg>
        </div>

        <!-- Title -->
        <h2 class="text-lg font-semibold text-gray-900 mb-2">
            Cancel Order?
        </h2>

        <!-- Message -->
        <p class="text-sm text-gray-500 mb-6">
            Are you sure you want to cancel this order? This action cannot be undone.
        </p>

        <!-- Buttons -->
        <div class="flex gap-3">
            <button onclick="closeCancelModal()"
                class="w-full bg-gray-100 text-gray-700 py-2.5 rounded-lg font-semibold hover:bg-gray-200 transition">
                Cancel
            </button>

            <button id="confirm-cancel-btn"
                class="w-full bg-red-500 text-white py-2.5 rounded-lg font-semibold hover:bg-red-600 transition">
                Yes, Cancel
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
async function openScheduleModal(entryID) {
    const modal = document.getElementById("paluwagan-schedule-modal");
    modal.classList.remove("hidden");

    const response = await fetch(`/paluwagan/schedule/${entryID}`);
    const data = await response.json();

    const entry = data.entry;
    const schedules = data.schedules;

    document.getElementById("sched-package-name").textContent = entry.name;
    document.getElementById("sched-start-month").textContent = "Start: " + entry.startDate;
    document.getElementById("sched-total-package").textContent = parseFloat(entry.totalPackage).toFixed(2);
    document.getElementById("sched-monthly-payment").textContent = parseFloat(entry.monthlyPayment).toFixed(2);

    const tbody = document.getElementById("schedule-table-body");
    tbody.innerHTML = "";

    schedules.forEach((sched, index) => {
        let status = sched.isPaid ? "Paid"
            : (new Date() > new Date(sched.dueDate)) ? "Late"
            : "Pending";

        let statusColor = sched.isPaid ? "text-green-600" : (new Date() > new Date(sched.dueDate)) ? "text-red-600" : "text-yellow-600";

        tbody.innerHTML += `
            <tr class="border-b">
                <td class="p-2 text-center">${sched.monthName}</td>
                <td class="p-2 text-center">${new Date(sched.dueDate).toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' })}</td>
                <td class="p-2 text-center">₱${parseFloat(sched.amountDue).toFixed(2)}</td>
                <td class="p-2 text-center">₱${parseFloat(sched.amountPaid).toFixed(2)}</td>
                <td class="p-2 text-center ${statusColor} font-semibold">${status}</td>
            </tr>
        `;
    });

    document.getElementById("sched-months-paid").textContent = schedules.filter(s => s.isPaid).length;
    document.getElementById("sched-total-months").textContent = schedules.length;
}

async function openPaymentModal(entryID) {
    const modal = document.getElementById("payment-modal");

    if (!modal) {
        console.error("payment-modal missing in DOM");
        return;
    }

    const response = await fetch(`/paluwagan/schedule/${entryID}`);
    const data = await response.json();

    if (!data.schedules || data.schedules.length === 0) {
        alert("No schedule found.");
        return;
    }

    const nextSchedule = data.schedules.find(s => !s.isPaid);

    if (!nextSchedule) {
        alert("All payments are already completed!");
        return;
    }

    modal.classList.remove("hidden");

    document.getElementById("payment-entryID").value = entryID;
    document.getElementById("payment-schedule-due").textContent =
        new Date(nextSchedule.dueDate).toLocaleDateString();

    document.getElementById("payment-amount").textContent =
        `₱${parseFloat(nextSchedule.amountDue).toFixed(2)}`;
}

function closePaymentModal() {
    document.getElementById("payment-modal").classList.add("hidden");
}

function closeScheduleModal() {
    const modal = document.getElementById("paluwagan-schedule-modal");
    if (modal) modal.classList.add("hidden");
}


let cancelEntryID = null;
let isCancelling = false;

function openCancelModal(entryID) {
    cancelEntryID = entryID;
    document.getElementById('cancel-modal').classList.remove('hidden');
    document.getElementById('cancel-modal').classList.add('flex');
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

    try {
        const res = await fetch(`/paluwagan/cancel/${cancelEntryID}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        const data = await res.json();

        if (!data.success) {
            throw new Error(data.message || 'Cancel failed');
        }

        // success feedback
        closeCancelModal();
        showToast('Subscription cancelled successfully', 'success');

        setTimeout(() => location.reload(), 1500);

    } catch (err) {
        console.error(err);
        showToast(err.message || 'Something went wrong', 'error');
    } finally {
        isCancelling = false;
    }
});
</script>

@endpush
@endsection
