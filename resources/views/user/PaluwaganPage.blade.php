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

        {{-- Orders --}}
        @forelse ($orders as $order)
        <div class="bg-white rounded-xl shadow-md border border-red-100 p-6 mb-8">

            {{-- Header --}}
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <span class="text-green-600">✔</span> {{ $order->name }}
                    </h3>
                    <p class="text-sm text-gray-600">{{ $order->desc }}</p>
                    <p class="text-sm text-gray-600 mt-1">
                        Started: {{ $order->startDate ?? 'N/A' }} • Monthly:
                        <span class="font-semibold">₱{{ $order->monthlyPayment }}</span>
                    </p>
                </div>

                <span class="bg-green-100 text-green-700 text-xs px-4 py-1.5 rounded-full font-semibold">
                    {{ $order->status }}
                </span>
            </div>

            {{-- Progress --}}
            <div class="mb-6">
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>0 of 10 months paid</span>
                    <span>₱5,000 remaining</span>
                </div>
                <div class="w-full bg-gray-200 h-2 rounded-full overflow-hidden">
                    <div class="h-2 bg-gradient-to-r from-green-400 to-green-600 rounded-full w-[10%]"></div>
                </div>
            </div>

            {{-- Metrics --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="border rounded-lg p-4 text-center bg-gray-50">
                    <p class="text-lg font-bold">₱5,000</p>
                    <p class="text-xs text-gray-500">Package Amount</p>
                </div>
                <div class="border rounded-lg p-4 text-center bg-gray-50">
                    <p class="text-lg font-bold">₱0</p>
                    <p class="text-xs text-gray-500">Total Paid</p>
                </div>
                <div class="border rounded-lg p-4 text-center bg-gray-50">
                    <p class="text-lg font-bold">0</p>
                    <p class="text-xs text-gray-500">Months Paid</p>
                </div>
                <div class="border rounded-lg p-4 text-center bg-gray-50">
                    <p class="text-lg font-bold">10</p>
                    <p class="text-xs text-gray-500">Months Left</p>
                </div>
            </div>

            {{-- Next Payment --}}
            <div class="flex items-center gap-2 bg-blue-50 border border-blue-100 rounded-lg p-3 text-sm mb-6">
                📅 <span>Next payment due: <strong>11/11/2025 – ₱500</strong></span>
            </div>

            {{-- Actions --}}
            <div class="flex flex-wrap gap-3">
                <button class="bg-black text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-gray-800 transition">
                    Make Payment
                </button>

                <button onclick="openScheduleModal('{{ $order->entryID }}')"
                    class="border px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-gray-100 transition">
                    View Schedule
                </button>

                <button class="text-red-600 border border-red-300 px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-red-50 transition">
                    Cancel Subscription
                </button>
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


{{-- Include Schedule Modal --}}
@include('user.modals.paluwaganSchedule')

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
    document.getElementById("sched-start-month").textContent = "Start Month: " + entry.startDate;
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


function closeScheduleModal() {
    document.getElementById("paluwagan-schedule-modal").classList.add("hidden");
}
</script>
@endpush
@endsection