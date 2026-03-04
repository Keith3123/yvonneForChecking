@extends('layouts.admin')

@section('title', 'Order Management')

@section('content')
<div class="px-4 sm:px-10 py-6">

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Order Management</h1>
            <p class="text-gray-500 mt-1">Manage and track all customer orders</p>
        </div>
    </div>

    <div class="mt-6 bg-white border border-pink-200 rounded-xl p-6 shadow-sm">

        <h2 class="text-xl font-semibold text-gray-700 flex justify-between items-center">
            Orders
            <span class="text-gray-500 text-sm">{{ $orders->count() }} orders found</span>
        </h2>

        {{-- ================= FILTERS ================= --}}
        <div class="mt-4 grid grid-cols-1 md:grid-cols-5 gap-3">

            <input type="text" id="searchInput"
                   placeholder="Search Order ID..."
                   class="border rounded-lg px-3 py-2 text-sm w-full">

            <select id="statusFilter"
                    class="border rounded-lg px-3 py-2 text-sm w-full">
                <option value="">All Status</option>
                <option value="Pending">Pending</option>
                <option value="Confirmed">Confirmed</option>
                <option value="Preparing">Preparing</option>
                <option value="Out for Delivery">Out for Delivery</option>
                <option value="Done">Done</option>
                <option value="Cancelled">Cancelled</option>
            </select>

            <input type="date" id="startDate"
                   class="border rounded-lg px-3 py-2 text-sm w-full">

            <input type="date" id="endDate"
                   class="border rounded-lg px-3 py-2 text-sm w-full">

            <select id="customerFilter"
                    class="border rounded-lg px-3 py-2 text-sm w-full">
                <option value="">All Customers</option>
                @foreach($customers as $customer)
                    <option value="{{ strtolower($customer->firstName . ' ' . $customer->lastName) }}">
                        {{ $customer->firstName }} {{ $customer->lastName }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- ================= BULK ACTIONS ================= --}}
        <div id="bulkActions"
             class="hidden flex gap-6 border rounded-xl p-4 bg-gray-50 mt-4 flex-wrap">

            <button onclick="bulkUpdate('Confirmed')"
                    class="flex flex-col items-center w-20 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-check text-lg"></i>
                <span class="text-xs mt-1">Accept</span>
            </button>

            <button onclick="bulkUpdate('Cancelled')"
                    class="flex flex-col items-center w-20 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                <i class="fas fa-times text-lg"></i>
                <span class="text-xs mt-1">Decline</span>
            </button>

            <button onclick="bulkUpdate('Preparing')"
                    class="flex flex-col items-center w-20 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                <i class="fas fa-utensils text-lg"></i>
                <span class="text-xs mt-1">Preparing</span>
            </button>

            <button onclick="bulkUpdate('Out for Delivery')"
                    class="flex flex-col items-center w-20 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                <i class="fas fa-truck text-lg"></i>
                <span class="text-xs mt-1">Deliver</span>
            </button>

            <button onclick="bulkUpdate('Done')"
                    class="flex flex-col items-center w-20 py-3 bg-green-700 text-white rounded-lg hover:bg-green-800 transition">
                <i class="fas fa-check-circle text-lg"></i>
                <span class="text-xs mt-1">Complete</span>
            </button>
        </div>

        {{-- ================= TABLE --}}
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full border-collapse text-sm">
                <thead>
                    <tr class="border-b text-left text-gray-600 bg-gray-50">
                        <th class="py-3 px-4"><input type="checkbox" id="selectAll"></th>
                        <th class="py-3 px-4">Order ID</th>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Items</th>
                        <th class="py-3 px-4">Delivery Date</th>
                        <th class="py-3 px-4">Payment</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Total</th>
                        <th class="py-3 px-4">View</th>
                    </tr>
                </thead>

                <tbody class="text-gray-700">
                @php
                    $statusClasses = [
                        'Confirmed' => 'bg-green-600 text-white',
                        'Cancelled' => 'bg-red-600 text-white',
                        'Preparing' => 'bg-purple-600 text-white',
                        'Out for Delivery' => 'bg-indigo-600 text-white',
                        'Done' => 'bg-green-700 text-white',
                        'Pending' => 'bg-gray-200 text-gray-700'
                    ];
                @endphp

                @forelse($orders as $order)
                    <tr class="border-b hover:bg-pink-50 transition"
                        data-order-id="{{ $order->orderID }}"
                        data-status="{{ $order->status }}"
                        data-date="{{ $order->orderDate ? $order->orderDate->format('Y-m-d') : '' }}"
                        data-customer="{{ $order->customer ? strtolower($order->customer->firstName . ' ' . $order->customer->lastName) : '' }}">

                        <td class="py-3 px-4">
                            <input type="checkbox" class="orderCheckbox"
                                   value="{{ $order->orderID }}">
                        </td>

                        <td class="py-3 px-4 font-medium">{{ $order->orderID }}</td>

                        <td class="py-3 px-4">
                            {{ $order->orderDate ? $order->orderDate->format('Y-m-d H:i') : 'N/A' }}
                        </td>

                        <td class="py-3 px-4">
                            {{ $order->orderItems->sum('qty') }}
                        </td>

                        <td class="py-3 px-4">
                            {{ $order->deliveryDate ? $order->deliveryDate->format('Y-m-d') : 'N/A' }}
                        </td>

                        <td class="py-3 px-4">
                            {{ $order->paymentStatus }}
                        </td>

                        <td class="py-3 px-4 status">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $statusClasses[$order->status] ?? 'bg-gray-200 text-gray-700' }}">
                                {{ $order->status }}
                            </span>
                        </td>

                        <td class="py-3 px-4 font-semibold">
                            ₱{{ number_format($order->totalAmount, 2) }}
                        </td>

                        <td class="py-3 px-4">
                            <button onclick="viewOrder({{ $order->orderID }})"
                                    class="p-2 rounded-lg hover:bg-gray-100 transition text-pink-500">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="py-10 text-center text-gray-400">
                            No orders found
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ================= MODAL ================= --}}
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
@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {

    const selectAll = document.getElementById('selectAll');
    const bulkActions = document.getElementById('bulkActions');
    const tableBody = document.querySelector('tbody');
    const filters = {
        searchInput: document.getElementById('searchInput'),
        statusFilter: document.getElementById('statusFilter'),
        startDate: document.getElementById('startDate'),
        endDate: document.getElementById('endDate'),
        customerFilter: document.getElementById('customerFilter')
    };

    function toggleBulkActions() {
        const anyChecked = document.querySelectorAll('.orderCheckbox:checked').length > 0;
        bulkActions.classList.toggle('hidden', !anyChecked);
    }

    // Filter logic
    function applyFilters() {
        const searchValue = filters.searchInput.value.toLowerCase();
        const statusValue = filters.statusFilter.value;
        const start = filters.startDate.value;
        const end = filters.endDate.value;
        const customerValue = filters.customerFilter.value;

        document.querySelectorAll('tbody tr').forEach(row => {
            const id = row.dataset.orderId ? row.dataset.orderId.toString().toLowerCase() : '';
            const status = row.dataset.status || '';
            const date = row.dataset.date || '';
            const customer = row.dataset.customer || '';

            let show = true;

            if(searchValue && !id.includes(searchValue)) show = false;
            if(statusValue && status !== statusValue) show = false;

            if(start && end) {
                if(date < start || date > end) show = false;
            } else if(start && date < start) show = false;
            else if(end && date > end) show = false;

            if(customerValue && customer !== customerValue) show = false;

            row.style.display = show ? '' : 'none';
        });

        // When filters change, also deselect all checkboxes and bulk actions
        deselectAll();
    }

    // Deselect all checkboxes and hide bulk actions
    function deselectAll() {
        selectAll.checked = false;
        document.querySelectorAll('.orderCheckbox').forEach(cb => cb.checked = false);
        toggleBulkActions();
    }

    // Event listeners on filters - use input/change for instant filtering
    Object.values(filters).forEach(el => {
        if(el.tagName === 'INPUT') {
            el.addEventListener('input', applyFilters);
        } else {
            el.addEventListener('change', applyFilters);
        }
    });

    // Checkbox toggling logic
    document.addEventListener('change', e => {
        if(e.target.classList.contains('orderCheckbox')) {
            toggleBulkActions();

            // If any checkbox unchecked, uncheck selectAll
            if(!e.target.checked) {
                selectAll.checked = false;
            } else {
                // If all checkboxes checked, check selectAll
                const allCheckboxes = Array.from(document.querySelectorAll('.orderCheckbox'));
                const allChecked = allCheckboxes.every(cb => cb.checked);
                selectAll.checked = allChecked;
            }
        }
    });

    selectAll.addEventListener('change', function() {
        const visibleCheckboxes = Array.from(document.querySelectorAll('.orderCheckbox'))
            .filter(cb => cb.closest('tr').style.display !== 'none');
        visibleCheckboxes.forEach(cb => cb.checked = this.checked);
        toggleBulkActions();
    });

    // Bulk update action
    window.bulkUpdate = function(newStatus) {
        const selected = Array.from(document.querySelectorAll('.orderCheckbox:checked'));
        if(selected.length === 0) return;
        if(!confirm(`Update ${selected.length} orders to ${newStatus}?`)) return;

        selected.forEach(cb => updateStatus(cb.value, newStatus));
        deselectAll();
    };

    function getStatusClass(status) {
        switch(status) {
            case 'Confirmed': return 'bg-green-600 text-white';
            case 'Cancelled': return 'bg-red-600 text-white';
            case 'Preparing': return 'bg-purple-600 text-white';
            case 'Out for Delivery': return 'bg-indigo-600 text-white';
            case 'Done': return 'bg-green-700 text-white';
            default: return 'bg-gray-200 text-gray-700';
        }
    }

    window.updateStatus = function(orderId, newStatus) {
        fetch(`/admin/orders/${orderId}/update-status`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Content-Type": "application/json"
            },
            body: JSON.stringify({status: newStatus})
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                let row = document.querySelector(`tr[data-order-id="${orderId}"]`);
                let statusClass = getStatusClass(newStatus);

                row.querySelector(".status").innerHTML =
                    `<span class="inline-block px-3 py-1 rounded-full text-xs font-semibold ${statusClass}">${newStatus}</span>`;

                row.dataset.status = newStatus;
            }
        });
    };

    window.viewOrder = function(orderID) {
        fetch(`/admin/orders/${orderID}/view`)
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {

                const order = data.order;
                let itemsHtml = '';

                order.order_items.forEach(item => {
                    itemsHtml += `
                        <tr>
                            <td class="border-b py-2 px-4">${item.product.name}</td>
                            <td class="border-b py-2 px-4 text-right">$${parseFloat(item.price).toFixed(2)}</td>
                            <td class="border-b py-2 px-4 text-center">${item.qty}</td>
                            <td class="border-b py-2 px-4 text-right">$${parseFloat(item.subtotal).toFixed(2)}</td>
                        </tr>
                    `;
                });

                document.getElementById('order-content').innerHTML = `
                    <div class="grid grid-cols-3 gap-6 text-sm text-gray-700">

                        <div class="col-span-2 border p-4 rounded-lg bg-white shadow-sm">
                            <h4 class="font-semibold mb-3">Order Items</h4>
                            <table class="w-full table-auto border-collapse">
                                <thead>
                                    <tr class="bg-gray-100">
                                        <th class="text-left py-2 px-4 border-b">Product</th>
                                        <th class="text-right py-2 px-4 border-b">Price</th>
                                        <th class="text-center py-2 px-4 border-b">QTY</th>
                                        <th class="text-right py-2 px-4 border-b">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${itemsHtml}
                                    <tr>
                                        <td colspan="3" class="text-right font-semibold py-2 px-4">Total:</td>
                                        <td class="text-right font-semibold py-2 px-4">$${parseFloat(order.totalAmount).toFixed(2)}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="border p-4 rounded-lg bg-white shadow-sm">
                            <h4 class="font-semibold mb-3">Customer</h4>
                            <p class="font-semibold">${order.customer.firstName} ${order.customer.lastName}</p>
                            <p>${order.customer.address || 'N/A'}</p>
                            <p>${order.customer.phone || 'N/A'}</p>
                            <p>${order.customer.email || 'N/A'}</p>
                        </div>

                        <div class="col-span-3 border p-4 rounded-lg bg-white shadow-sm mt-6">
                            <h4 class="font-semibold mb-3">Order Details</h4>
                            <p><strong>Order Status:</strong> ${order.status}</p>
                            <p><strong>Payment Status:</strong> ${order.paymentStatus}</p>
                            <p><strong>Order Date:</strong> ${new Date(order.orderDate).toLocaleString()}</p>
                            <p><strong>Delivery Date:</strong> ${order.deliveryDate ? new Date(order.deliveryDate).toLocaleDateString() : 'N/A'}</p>
                            <p><strong>Delivery Time:</strong> ${order.deliveryTime ?? 'N/A'}</p>
                        </div>
                    </div>
                `;

                document.getElementById('view-order-modal').classList.remove('hidden');
            }
        });
    };

    window.closeViewModal = function() {
        document.getElementById('view-order-modal').classList.add('hidden');
    };

    // AUTO DESELECT ON OUTSIDE CLICK
    document.addEventListener('click', (e) => {
        const clickedInsideTable = e.target.closest('table');
        const clickedCheckbox = e.target.classList.contains('orderCheckbox') || e.target.id === 'selectAll';

        if (!clickedInsideTable && !clickedCheckbox) {
            deselectAll();
        }
    });

});
</script>
@endsection