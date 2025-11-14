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

        {{-- Simulate dynamic paluwagan orders --}}
        @php
            $orders = [
                [
                    'name' => 'Holiday Package',
                    'desc' => 'Crispy and juicy chicken',
                    'start' => 'Month 12',
                    'monthly' => 500,
                    'status' => 'On Track',
                    'months_paid' => 0,
                    'total_months' => 10,
                    'package_amount' => 5000,
                    'total_paid' => 0,
                    'next_payment' => '11/11/2025',
                ],
                [
                    'name' => 'Snack Box',
                    'desc' => 'Delicious assorted snacks',
                    'start' => 'Month 5',
                    'monthly' => 300,
                    'status' => 'Overdue',
                    'months_paid' => 2,
                    'total_months' => 6,
                    'package_amount' => 1800,
                    'total_paid' => 600,
                    'next_payment' => '11/20/2025',
                ],
                // Add more simulated orders here if needed
            ];
        @endphp

        @forelse ($orders as $order)
        <div class="border border-red-200 bg-white rounded-md shadow-sm p-6 relative mb-6">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <h3 class="font-bold text-lg text-gray-900 flex items-center gap-2">
                        <span class="text-green-600">✔</span> {{ $order['name'] }}
                    </h3>
                    <p class="text-sm text-gray-600">{{ $order['desc'] }}</p>
                    <p class="text-sm text-gray-600 mt-1">
                        <span class="font-semibold">Started:</span> {{ $order['start'] }} • Monthly: <span class="font-semibold">₱{{ $order['monthly'] }}</span>
                    </p>
                </div>
                <span class="bg-green-100 text-green-600 text-xs px-3 py-1 rounded-full font-medium">{{ $order['status'] }}</span>
            </div>

            <p class="text-sm text-gray-500 mb-2">{{ $order['months_paid'] }} of {{ $order['total_months'] }} months paid</p>
            <div class="w-full bg-gray-200 h-2 rounded-full mb-3">
                <div class="h-2 bg-green-500 rounded-full" style="width: {{ ($order['months_paid'] / $order['total_months']) * 100 }}%"></div>
            </div>
            <p class="text-right text-sm text-gray-500 mb-6">
                ₱{{ $order['package_amount'] - $order['total_paid'] }} remaining
            </p>

            <div class="grid grid-cols-4 gap-4 mb-4">
                <div class="border border-pink-200 rounded-md p-3 text-center">
                    <p class="text-lg font-bold text-gray-900">₱{{ $order['package_amount'] }}</p>
                    <p class="text-sm text-gray-600">Package Amount</p>
                </div>
                <div class="border border-pink-200 rounded-md p-3 text-center">
                    <p class="text-lg font-bold text-gray-900">₱{{ $order['total_paid'] }}</p>
                    <p class="text-sm text-gray-600">Total Paid</p>
                </div>
                <div class="border border-pink-200 rounded-md p-3 text-center">
                    <p class="text-lg font-bold text-gray-900">{{ $order['months_paid'] }}</p>
                    <p class="text-sm text-gray-600">Months Paid</p>
                </div>
                <div class="border border-pink-200 rounded-md p-3 text-center">
                    <p class="text-lg font-bold text-gray-900">{{ $order['total_months'] - $order['months_paid'] }}</p>
                    <p class="text-sm text-gray-600">Months Left</p>
                </div>
            </div>

            <div class="bg-blue-50 text-sm text-gray-700 px-3 py-2 rounded-md flex items-center gap-2 mb-4">
                <span class="text-blue-600">📅</span>
                <span>Next payment due: <strong>{{ $order['next_payment'] }}</strong> - <strong>₱{{ $order['monthly'] }}</strong></span>
            </div>

            <div class="flex gap-3 mt-4">
                <button onclick="toggleModal('paymentModal')" class="bg-black text-white text-sm font-semibold px-5 py-2 rounded hover:bg-gray-800 transition">
                    Make Payment
                </button>
                <button onclick="toggleModal('scheduleModal')" class="border border-gray-300 text-gray-700 text-sm font-semibold px-5 py-2 rounded hover:bg-gray-100 transition">
                    View Schedule
                </button>
                <button onclick="toggleModal('cancelModal')" class="border border-red-300 text-red-600 text-sm font-semibold px-5 py-2 rounded hover:bg-red-50 transition">
                    Cancel Subscription
                </button>
            </div>
        </div>
        @empty
            <p class="text-gray-500 text-center py-10">You have no active paluwagan orders. <a href="/catalog" class="text-orange-500 underline">Go to Catalog</a> to place an order.</p>
        @endforelse

    </div>
</div>

@include('partials.components.paluwagan-modals')

@push('scripts')
<script>
    function toggleModal(id) {
        const modal = document.getElementById(id);
        modal.classList.toggle('hidden');
    }
</script>
@endpush
@endsection
