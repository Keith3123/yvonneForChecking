@extends('layouts.admin')

@section('title', 'Order Management')

@section('content')
<div class="px-4 sm:px-10 py-6">

    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Order Management</h1>
            <p class="text-gray-500 mt-1">Manage and track all customer orders</p>
        </div>
        {{-- RELOAD PAGE BUTTON --}}
        <button onclick="location.reload()" class="text-pink-500 hover:text-pink-700">
            <i class="fas fa-arrows-rotate fa-xl"></i>
        </button>
    </div>

    <div class="mt-6 bg-white border border-pink-200 rounded-xl p-6 shadow-sm">

        <h2 class="text-xl font-semibold text-gray-700 flex justify-between items-center">
            Orders
            <span class="text-gray-500 text-sm">{{ $orders->count() }} orders found</span>
        </h2>

        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full border-collapse text-sm">
                <thead>
                    <tr class="border-b text-left text-gray-600 bg-gray-50">
                        <th class="py-3 px-4">Order ID</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Items</th>
                        <th class="py-3 px-4">Delivery Date</th>
                        <th class="py-3 px-4">Payment</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Total</th>
                        <th class="py-3 px-4">Actions</th>
                    </tr>
                </thead>

                <tbody class="text-gray-700">
                    @forelse($orders as $order)
                    <tr class="border-b hover:bg-pink-50 transition" data-order-id="{{ $order->orderID }}">
                        <td class="py-3 px-4 font-medium text-gray-800">{{ $order->orderID }}</td>
                        <td class="py-3 px-4">{{ $order->orderDate->format('Y-m-d H:i') }}</td>
                        <td class="py-3 px-4">{{ $order->orderItems->sum('qty') }}</td>
                        <td class="py-3 px-4">{{ $order->deliveryDate ? $order->deliveryDate->format('Y-m-d') : 'N/A' }}</td>
                        <td class="py-3 px-4">{{ $order->paymentStatus }}</td>
                        <td class="py-3 px-4 status">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold
                                {{ $order->status == 'Pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ $order->status == 'In Progress' ? 'bg-blue-100 text-blue-700' : '' }}
                                {{ $order->status == 'Completed' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $order->status == 'Declined' ? 'bg-red-100 text-red-700' : '' }}">
                                {{ $order->status }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-semibold">₱{{ number_format($order->totalAmount, 2) }}</td>

                        <td class="py-3 px-4 flex gap-2">
                            {{-- VIEW --}}
                            <button onclick="viewOrder({{ $order->orderID }})"
                                    class="p-2 rounded-lg hover:bg-gray-100 transition text-pink-500">
                                <i class="fas fa-eye"></i>
                            </button>

                            {{-- ACCEPT --}}
                            <button onclick="acceptOrder({{ $order->orderID }})"
                                    class="p-2 rounded-lg hover:bg-gray-100 transition text-green-600">
                                <i class="fas fa-check"></i>
                            </button>

                            {{-- CANCEL --}}
                            <button onclick="showCancelConfirmation({{ $order->orderID }})"
                                    class="p-2 rounded-lg hover:bg-gray-100 transition text-red-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-10 text-center text-gray-400">
                            <div class="flex flex-col items-center">
                                <span class="text-4xl mb-2">📄</span>
                                No orders found
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </div>

</div>

{{-- VIEW ORDER MODAL --}}
<div id="view-order-modal"
     class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">

    <div class="bg-white rounded-2xl p-6 max-w-3xl w-full relative max-h-[90vh] overflow-y-auto shadow-xl">

        <button onclick="closeViewModal()"
                class="absolute top-3 right-4 text-gray-500 text-2xl hover:text-black">
            &times;
        </button>

        <h3 class="text-xl font-semibold mb-4">Order Details</h3>

        <div id="order-content"></div>

    </div>
</div>


{{-- CANCEL CONFIRMATION MODAL --}}
<div id="cancel-order-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
    <div class="bg-white rounded-2xl p-6 max-w-md w-full relative shadow-xl">
        <h3 class="text-xl font-semibold mb-3">Decline Order</h3>
        <p>Are you sure you want to decline this order?</p>
        <div class="flex justify-end gap-3 mt-4">
            <button id="confirm-cancel-btn" class="bg-red-600 text-white px-4 py-2 rounded">Yes, Decline</button>
            <button onclick="closeCancelModal()" class="bg-gray-200 px-4 py-2 rounded">No</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {
    let cancelOrderID = null;

    // -------------------------
    // VIEW ORDER
    // -------------------------
    window.viewOrder = function(orderId) {
        fetch(`/admin/orders/${orderId}/view`)
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    let order = data.order;
                    let html = `
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
                                <div>
                                    <p class="text-xs text-gray-500">Order ID</p>
                                    <p class="font-semibold">#${order.orderID}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Customer</p>
                                    <p class="font-semibold">
                                        ${order.customer.firstName} ${order.customer.lastName}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Order Date</p>
                                    <p class="font-semibold">${order.orderDate}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Delivery Date</p>
                                    <p class="font-semibold">
                                        ${order.deliveryDate ? order.deliveryDate.substring(0,10) : 'N/A'}
                                    </p>
                                </div>
                            </div>
                        </div>
                    `;
                    document.getElementById("order-content").innerHTML = html;
                    document.getElementById("view-order-modal").classList.remove("hidden");
                }
            });
    }

    window.closeViewModal = function() {
        document.getElementById("view-order-modal").classList.add("hidden");
    }

    // -------------------------
    // ACCEPT ORDER
    // -------------------------
    window.acceptOrder = function(orderId) {
        fetch(`/admin/orders/${orderId}/accept`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Content-Type": "application/json"
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                document.querySelector(`tr[data-order-id="${orderId}"] td.status`).innerText = 'In Progress';
                alert("Order accepted!");
            } else {
                alert(data.message || "Failed to accept the order.");
            }
        });
    }

    // -------------------------
    // DECLINE ORDER
    // -------------------------
    window.showCancelConfirmation = function(orderId) {
        cancelOrderID = orderId;
        document.getElementById("cancel-order-modal").classList.remove("hidden");
    }

    window.closeCancelModal = function() {
        document.getElementById("cancel-order-modal").classList.add("hidden");
    }

    document.getElementById("confirm-cancel-btn").onclick = function() {
        fetch(`/admin/orders/${cancelOrderID}/cancel`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Content-Type": "application/json"
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                document.querySelector(`tr[data-order-id="${cancelOrderID}"] td.status`).innerText = 'Declined';
                closeCancelModal();
                alert("Order declined.");
            } else {
                alert(data.message || "Failed to decline the order.");
            }
        });
    }
});
</script>
@endsection
