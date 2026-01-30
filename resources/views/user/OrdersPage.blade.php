@extends('layouts.app')

@section('no-footer')
@endsection

@section('content')
<div class="bg-gradient-to-b from-[#FFF8F5] to-[#FFEDEA] min-h-screen flex justify-center py-12 px-4 overflow-y-auto">
    <div class="w-full max-w-5xl">

        {{-- Header --}}
        <div class="mb-10 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">My Orders</h2>
                <p class="text-gray-600 text-sm mt-1">
                    Track the status of your orders and view order history
                </p>
            </div>
            <a href="{{ route('catalog') }}" 
               class="inline-flex items-center justify-center bg-orange-300 hover:bg-orange-400 text-gray-900 px-5 py-2.5 rounded-lg font-semibold text-sm shadow transition">
                Go to Catalog
            </a>
        </div>

        {{-- Orders List --}}
        <div class="space-y-8">
            @forelse($orders as $order)
                <div class="bg-white rounded-xl shadow-md border border-red-100 p-6 md:p-8 relative">

                    {{-- Order Header --}}
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-6">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800">
                                Order #{{ $order->orderID }}
                            </h2>
                            <p class="text-sm text-gray-500">
                                Placed on {{ $order->orderDate->format('F d, Y \a\t h:i A') }}
                            </p>
                        </div>

                        <span class="text-xs font-semibold px-4 py-1.5 rounded-full
                            {{ $order->status == 'Done' 
                                ? 'bg-green-100 text-green-700 ring-1 ring-green-200' 
                                : ($order->status == 'Cancelled' ? 'bg-red-100 text-red-700 ring-1 ring-red-200' : 'bg-yellow-100 text-yellow-700 ring-1 ring-yellow-200') }}">
                            {{ $order->status }}
                        </span>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="mb-6">
                        <div class="w-full bg-gray-200 h-2 rounded-full overflow-hidden">
                            @php
                                $progress = $order->status == 'Done' ? 100 : 50;
                            @endphp
                            <div class="h-2 bg-gradient-to-r from-green-400 to-green-600 rounded-full transition-all duration-500"
                                 style="width: {{ $progress }}%;"></div>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 mt-2">
                            <span>Order Placed</span>
                            <span>In Progress</span>
                            <span>Delivered</span>
                        </div>
                    </div>

                    {{-- Order Items --}}
                    <div class="space-y-5">
                        @foreach($order->orderItems as $item)
                            <div class="flex gap-4 items-center">

                                {{-- IMAGE --}}
                                @php
                                    $productImageURL = $item->product->imageURL ?? null;
                                @endphp

                                <img src="{{ $productImageURL ? asset($productImageURL) : asset('images/sample_food.jpg') }}"
                                     alt="Food Package"
                                     class="w-24 h-24 object-cover rounded-lg border shadow-sm">

                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-800">
                                        {{ $item->product->name ?? 'Product Name' }}
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Quantity: {{ $item->qty }}
                                    </p>
                                </div>

                                <div class="text-right font-semibold text-gray-800">
                                    ₱{{ $item->subtotal }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Delivery & Payment Info --}}
                    <div class="grid md:grid-cols-2 gap-6 border-t border-gray-200 pt-6 mt-6">
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Delivery Information</h4>
                            <p class="text-sm text-gray-600 mb-1">
                                📅 {{ $order->deliveryDate ? $order->deliveryDate->format('Y-m-d h:i A') : 'Not Set' }}
                            </p>
                            <p class="text-sm text-gray-600">
                                📍 {{ $order->deliveryAddress }}
                            </p>
                        </div>

                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Payment Information</h4>
                            <div class="text-sm text-gray-700 space-y-1">
                                <p>
    Subtotal
    <span class="float-right font-semibold">
        ₱{{ number_format($order->computedSubtotal, 2) }}
    </span>
</p>

<p>
    VAT (12%)
    <span class="float-right font-semibold">
        ₱{{ number_format($order->computedVat, 2) }}
    </span>
</p>

<p class="border-t pt-1 mt-1">
    Total Amount
    <span class="float-right font-bold">
        ₱{{ number_format($order->computedTotal, 2) }}
    </span>
</p>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-wrap gap-3 mt-6">
                        <!-- VIEW RECEIPT -->
                        <button onclick="openReceiptModal('{{ $order->orderID }}')" class="bg-gray-900 text-white text-sm font-semibold px-6 py-2.5 rounded-lg hover:bg-gray-800 shadow transition">
                            <i class="far fa-file-alt mr-2"></i> View Receipt
                        </button>

                        <!-- CANCEL ORDER -->
                        <button onclick="openCancelModal('{{ $order->orderID }}')" class="border border-red-300 text-red-600 text-sm font-semibold px-6 py-2.5 rounded-lg hover:bg-red-50 transition">
                            <i class="fas fa-times mr-2"></i> Cancel Order
                        </button>
                    </div>

                </div>

                {{-- RECEIPT MODAL (Hidden per order) --}}
                <div id="receiptModal-{{ $order->orderID }}"
                     class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center">
                    <div class="bg-white rounded-xl w-full max-w-md p-6 h-[85vh] overflow-y-auto">

                        {{-- Close Button --}}
                        <div class="flex justify-end">
                            <button onclick="closeReceiptModal('{{ $order->orderID }}')" class="text-xl font-bold">&times;</button>
                        </div>

                        {{-- Receipt Header --}}
                        <div class="text-center mb-5">
                            <div class="text-xl font-bold">Yvonne's Cakes & Pastries</div>
                            <div class="text-sm text-gray-500">Along Bacaca Road</div>
                            <div class="text-sm text-gray-500">Davao City</div>
                            <div class="text-sm text-gray-500">Phone: 0912-345-6789</div>
                            <div class="border-t mt-4 pt-4">
                                <div class="text-sm font-semibold">RECEIPT</div>
                                <div class="text-xs text-gray-500">Order #{{ $order->orderID }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $order->orderDate->format('F d, Y \a\t h:i A') }}
                                </div>
                            </div>
                        </div>

                        {{-- Order Info --}}
                        <div class="mt-4 mb-4">
                            <div class="flex justify-between text-sm">
                                <span>Status:</span>
                                <span class="font-semibold text-right">{{ $order->status }}</span>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span>Payment:</span>
                                <span class="font-semibold text-right">
                                    {{ $order->paymentMethod ?? ($order->paymentMode ?? 'COD / GCash') }}
                                </span>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span>Delivery:</span>
                                <span class="font-semibold text-right">
                                    {{ $order->deliveryDate ? $order->deliveryDate->format('Y-m-d h:i A') : 'Not Set' }}
                                </span>
                            </div>

                            <div class="flex justify-between text-sm">
                                <span>Address:</span>
                                <span class="font-semibold text-right text-xs">
                                    {{ $order->deliveryAddress }}
                                </span>
                            </div>
                        </div>

                        <hr class="my-3">

                        {{-- Items --}}
                        <div class="text-xs">
                            <div class="flex font-semibold text-xs">
    <span class="w-1/2">Item</span>
    <span class="w-1/4 justify">Qty</span>
    <span class="w-1/4 text-right">Total</span>
</div>
                            <div class="mt-2 space-y-2">
                                @foreach($order->orderItems as $item)
                                    <div class="flex text-xs">
    <span class="w-1/2 truncate">{{ $item->product->name }}</span>
    <span class="w-1/4 justify">{{ $item->qty }}</span>
    <span class="w-1/4 text-right">₱{{ number_format($item->subtotal, 2) }}</span>
</div>

                                @endforeach
                            </div>
                        </div>

                        <hr class="my-3">

                        {{-- Totals --}}
                        <div class="text-sm">
    <div class="flex justify-between">
        <span>Subtotal</span>
        <span class="font-semibold">
            ₱{{ number_format($order->computedSubtotal, 2) }}
        </span>
    </div>

    <div class="flex justify-between">
        <span>VAT (12%)</span>
        <span class="font-semibold">
            ₱{{ number_format($order->computedVat, 2) }}
        </span>
    </div>

    <div class="flex justify-between mt-2 border-t pt-2">
        <span class="font-semibold">Grand Total</span>
        <span class="font-bold text-lg">
            ₱{{ number_format($order->computedTotal, 2) }}
        </span>
    </div>
</div>


                        <div class="text-center mt-6 text-xs text-gray-500">
                            Thank you for your order!
                            <div>Visit again soon.</div>
                        </div>
                    </div>
                </div>

            @empty
                <p class="text-gray-500 text-center py-20">
                    You have not placed any orders yet.
                </p>
            @endforelse
        </div>
    </div>
</div>

{{-- CANCEL CONFIRMATION MODAL --}}
<div id="cancelModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl w-full max-w-md p-6">
        <h2 class="font-semibold text-lg mb-3">Cancel Order</h2>
        <p class="text-sm text-gray-600 mb-4">
            Are you sure you want to cancel this order?
        </p>

        <div class="flex justify-end gap-3">
            <button onclick="closeCancelModal()" class="px-4 py-2 rounded-lg border">No</button>
            <button id="confirmCancelBtn" class="px-4 py-2 rounded-lg bg-red-600 text-white">Yes, Cancel</button>
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
    let orderToCancel = null;

    // Receipt Modal
    function openReceiptModal(orderID) {
        document.getElementById(`receiptModal-${orderID}`).classList.remove('hidden');
    }

    function closeReceiptModal(orderID) {
        document.getElementById(`receiptModal-${orderID}`).classList.add('hidden');
    }

    // Cancel Modal
    function openCancelModal(orderID) {
        orderToCancel = orderID;
        document.getElementById('cancelModal').classList.remove('hidden');
    }

    function closeCancelModal() {
        orderToCancel = null;
        document.getElementById('cancelModal').classList.add('hidden');
    }

    document.getElementById('confirmCancelBtn').addEventListener('click', () => {
        if (!orderToCancel) return;

        fetch(`/orders/cancel/${orderToCancel}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.status === 'success') {
                location.reload();
            }
        });

        closeCancelModal();
    });
</script>
@endsection
