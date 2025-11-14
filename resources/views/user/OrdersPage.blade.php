@extends('layouts.app')

@section('no-footer')
@endsection

@section('content')
<div class="bg-[#FFF8F5] min-h-screen flex justify-center py-10 px-4 overflow-y-auto">
    <div class="w-full max-w-5xl">

        {{-- Header --}}
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">My Orders</h2>
                <p class="text-gray-600 text-sm">
                    Track the status of your orders and view order history
                </p>
            </div>
            <a href="{{ route('catalog') }}" 
               class="bg-orange-200 hover:bg-orange-300 px-4 py-2 rounded font-semibold text-sm">
                Go to Catalog
            </a>
        </div>

        {{-- Orders List --}}
        <div class="space-y-6">
            @for($i = 1; $i <= 1; $i++)
            <div class="border border-red-200 bg-white rounded-md shadow-sm p-6 relative mb-6">
                {{-- Order Header --}}
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Order #43030{{ $i }}</h2>
                        <p class="text-sm text-gray-500">Placed on October {{ 10 + $i }}, 2025 at 8:3{{ $i }} PM</p>
                    </div>
                    <span class="text-xs font-semibold {{ $i % 2 == 0 ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }} px-3 py-1 rounded-full">
                        {{ $i % 2 == 0 ? 'DELIVERED' : 'PENDING' }}
                    </span>
                </div>

                {{-- Progress Bar --}}
                <div class="w-full bg-gray-200 h-2 rounded-full mb-3">
                    <div class="h-2 bg-green-500 rounded-full" style="width: {{ $i * 30 }}%;"></div>
                </div>
                <div class="flex justify-between text-xs text-gray-500 mb-6">
                    <span>Order Placed</span>
                    <span>In Progress</span>
                    <span>Delivered</span>
                </div>

                {{-- Order Items --}}
                <div class="flex space-x-4 mb-6">
                    <img src="{{ asset('images/sample_food.jpg') }}" alt="Food Package" class="w-24 h-24 object-cover rounded-md border">
                    <div>
                        <h3 class="font-semibold text-gray-800">Family Food Package</h3>
                        <p class="text-sm text-gray-500">Quantity: {{ $i }}</p>
                        <p class="text-gray-800 font-semibold mt-2">₱{{ 3000 * $i }}</p>
                    </div>
                </div>

                {{-- Delivery & Payment Info --}}
                <div class="grid md:grid-cols-2 gap-6 border-t border-gray-200 pt-4 mb-6">
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Delivery Information</h4>
                        <p class="text-sm text-gray-600 mb-1"><i class="far fa-calendar-alt mr-1"></i> 2025-10-{{ 12+$i }} at 9:00 AM - 11:00 AM</p>
                        <p class="text-sm text-gray-600"><i class="fas fa-map-marker-alt mr-1"></i> Tigatto, Davao City</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Payment Information</h4>
                        <div class="text-sm text-gray-700 space-y-1">
                            <p>Subtotal: <span class="float-right font-semibold">₱{{ 3000 * $i }}</span></p>
                            <p>Downpayment (50%): <span class="float-right text-blue-600 font-medium">₱{{ (3000 * $i)/2 }}</span></p>
                            <p>Remaining Balance: <span class="float-right">₱{{ (3000 * $i)/2 }}</span></p>
                            <p>Payment Method: <span class="float-right font-medium">{{ $i % 2 == 0 ? 'GCASH' : 'COD' }}</span></p>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-3 mt-4">
                    <button class="viewReceiptBtn bg-black text-white text-sm font-semibold px-5 py-2 rounded hover:bg-gray-800 transition">
                        <i class="far fa-file-alt mr-2"></i> View Receipt
                    </button>
                    <button class="cancelOrderBtn border border-red-300 text-red-600 text-sm font-semibold px-5 py-2 rounded hover:bg-red-50 transition">
                        <i class="fas fa-times mr-2"></i> Cancel Order
                    </button>
                </div>
            </div>
            @endfor
        </div>
    </div>
</div>

{{-- ✅ View Receipt Modal (same style as your provided image) --}}
<div id="viewReceiptModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full max-h-[90vh] overflow-y-auto p-6 text-sm">
        <div class="flex justify-between items-center border-b pb-3 mb-4">
            <h2 class="text-lg font-bold text-gray-800">Order Receipt</h2>
            <button id="closeReceiptModal" class="text-gray-500 hover:text-gray-700 text-2xl font-bold leading-none">&times;</button>
        </div>

        <div class="space-y-3">
            <div>
                <p class="text-gray-600"><strong>Order Number:</strong> #72168422</p>
                <p class="text-gray-600"><strong>Order Date:</strong> October 12, 2025 at 08:29 PM</p>
                <p class="text-gray-600"><strong>Status:</strong> Pending</p>
                <p class="text-gray-600"><strong>Payment Method:</strong> GCASH</p>
            </div>

            <div>
                <h4 class="font-semibold text-gray-800 mt-4 mb-1">Customer Information</h4>
                <p><strong>Name:</strong> jas</p>
                <p><strong>Contact:</strong> 12121212</p>
                <p><strong>Email:</strong> jajs@gmail.com</p>
            </div>

            <div>
                <h4 class="font-semibold text-gray-800 mt-4 mb-1">Delivery Information</h4>
                <p><strong>Delivery Date & Time:</strong> 2025-10-15 at 11:00 AM - 1:00 PM</p>
                <p><strong>Delivery Address:</strong> rtrwet</p>
            </div>

            <div>
                <h4 class="font-semibold text-gray-800 mt-4 mb-1">Order Items</h4>
                <div class="flex justify-between">
                    <span>Fried Chicken Food Tray</span>
                    <span>₱800</span>
                </div>
                <p class="text-gray-500 text-xs mt-1">Quantity: 1</p>
            </div>

            <div class="border-t pt-3">
                <h4 class="font-semibold text-gray-800 mb-1">Payment Summary</h4>
                <div class="flex justify-between text-gray-700">
                    <span>Subtotal:</span> <span>₱800</span>
                </div>
                <div class="flex justify-between text-gray-700">
                    <span>Downpayment (50%):</span> <span>₱400</span>
                </div>
                <div class="flex justify-between font-semibold text-gray-800">
                    <span>Total Amount:</span> <span>₱800</span>
                </div>
            </div>

            <p class="text-center text-gray-500 text-xs mt-4 italic">
                Thank you for your order! This is a computer-generated receipt.
            </p>
        </div>
    </div>
</div>

{{-- ✅ Cancel Order Confirmation Modal --}}
<div id="cancelOrderModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-lg max-w-sm w-full p-6">
        <h3 class="text-lg font-semibold mb-4">Cancel Order Confirmation</h3>
        <p class="mb-6 text-gray-700">Are you sure you want to cancel this order? This action cannot be undone.</p>
        <div class="flex justify-end gap-3">
            <button id="cancelCancelBtn" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100 transition">
                No, Keep Order
            </button>
            <button class="confirmCancelBtn px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition">
                Yes, Cancel Order
            </button>
        </div>
    </div>
</div>

{{-- ✅ JS for modals --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const viewBtns = document.querySelectorAll('.viewReceiptBtn');
    const cancelBtns = document.querySelectorAll('.cancelOrderBtn');
    const viewReceiptModal = document.getElementById('viewReceiptModal');
    const cancelOrderModal = document.getElementById('cancelOrderModal');
    const closeReceiptModal = document.getElementById('closeReceiptModal');
    const cancelCancelBtn = document.getElementById('cancelCancelBtn');

    // Open / Close receipt modal
    viewBtns.forEach(btn => btn.addEventListener('click', () => viewReceiptModal.classList.remove('hidden')));
    closeReceiptModal.addEventListener('click', () => viewReceiptModal.classList.add('hidden'));

    // Open / Close cancel modal
    cancelBtns.forEach(btn => btn.addEventListener('click', () => cancelOrderModal.classList.remove('hidden')));
    cancelCancelBtn.addEventListener('click', () => cancelOrderModal.classList.add('hidden'));

    // Close when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === viewReceiptModal) viewReceiptModal.classList.add('hidden');
        if (e.target === cancelOrderModal) cancelOrderModal.classList.add('hidden');
    });
});
</script>
@endsection
