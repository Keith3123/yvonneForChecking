@extends('layouts.app')

@section('no-footer')
@endsection

@section('content')
<div class="bg-[#FFF8F5] min-h-screen flex justify-center py-10 px-4 overflow-y-auto">
    <div class="w-full max-w-5xl">

        {{-- Header --}}
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">My Paluwagan</h2>
                <p class="text-gray-600 text-sm">
                    Manage your installment plans and payment schedules
                </p>
            </div>
            <a href="/catalog" class="bg-orange-200 hover:bg-orange-300 px-4 py-2 rounded font-semibold text-sm">
                Go to Catalog
            </a>
        </div>

        {{-- Features --}}
        @php
            $features = [
                ['icon' => '💸', 'title' => 'Fixed Monthly Payment', 'desc' => 'Pay required amount on every due date', 'color' => 'text-green-600'],
                ['icon' => '📅', 'title' => 'Flexible Entry', 'desc' => 'Join at any month available in paluwagan', 'color' => 'text-purple-600'],
                ['icon' => '✅', 'title' => 'Premium Quality', 'desc' => 'High quality and trusted food', 'color' => 'text-green-700'],
            ];
        @endphp

        <div class="bg-blue-50 border border-blue-100 rounded-md shadow-sm p-6 grid md:grid-cols-3 gap-6 mb-8">
            @foreach ($features as $feature)
                <div class="text-center">
                    <div class="{{ $feature['color'] }} text-xl mb-2">{{ $feature['icon'] }}</div>
                    <h3 class="font-semibold text-gray-900">{{ $feature['title'] }}</h3>
                    <p class="text-sm text-gray-500">{{ $feature['desc'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Orders --}}
        @forelse ($orders as $order)
        <div class="border border-red-200 bg-white rounded-md shadow-sm p-6 relative mb-6">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <h3 class="font-bold text-lg text-gray-900 flex items-center gap-2">
                        <span class="text-green-600">✔</span> {{ $order->name }}
                    </h3>

                    <p class="text-sm text-gray-600">{{ $order->desc }}</p>

                    <p class="text-sm text-gray-600 mt-1">
                        <span class="font-semibold">Started:</span>
                        {{ $order->startDate ?? 'N/A' }} • Monthly:
                        <span class="font-semibold">₱{{ $order->monthlyPayment }}</span>
                    </p>

                    <span class="bg-green-100 text-green-600 text-xs px-3 py-1 rounded-full font-medium">
                        {{ $order->status }}
                    </span>
                </div>
            </div>


             <button class="bg-pink-500 hover:bg-pink-300 text-white px-3 py-1 rounded text-sm">
            Make Payment
            </button>


            <button onclick="openScheduleModal('{{ $order->entryID }}')"
                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm mt-3">
                View Schedule
            </button>

            <button class="bg-red-500 hover:bg-pink-300 text-white px-3 py-1 rounded text-sm">
            Cancel Subscription
        </button>
        
        </div>
        @empty
        <p class="text-gray-500 text-center py-10">
            You have no active paluwagan orders. 
            <a href="/catalog" class="text-orange-500 underline">Go to Catalog</a> to place an order.
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