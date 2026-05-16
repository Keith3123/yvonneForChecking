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
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 whitespace-nowrap">Rows per page</label>
                <select id="ordersPerPage" class="border rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-pink-400 bg-white border-pink-200">
                    <option value="5">5</option>
                    <option value="10" selected>10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                </select>
            </div>
        </h2>

        {{-- ================= FILTERS ================= --}}
        <div class="mt-4 grid grid-cols-1 md:grid-cols-5 gap-3">

            <input type="text" id="searchInput"
                   placeholder="Search Order ID..."
                   class="border rounded-lg px-3 py-2 text-sm w-full border-pink-200 focus:ring-pink-500 focus:outline-none focus:ring-2">

            <select id="statusFilter"
                    class="border rounded-lg px-3 py-2 text-sm w-full border-pink-200 focus:ring-pink-500 focus:outline-none focus:ring-2">
                <option value="">All Status</option>
                <option value="Pending">Pending</option>
                <option value="Confirmed">Confirmed</option>
                <option value="Preparing">Preparing</option>
                <option value="Out for Delivery">Out for Delivery</option>
                <option value="Done">Done</option>
                <option value="Cancelled">Cancelled</option>
            </select>

            <input type="date" id="startDate"
                   class="border rounded-lg px-3 py-2 text-sm w-full border-pink-200 focus:ring-pink-500 focus:outline-none focus:ring-2">

            <input type="date" id="endDate"
                   class="border rounded-lg px-3 py-2 text-sm w-full border-pink-200 focus:ring-pink-500 focus:outline-none focus:ring-2">

            <select id="customerFilter"
                    class="border rounded-lg px-3 py-2 text-sm w-full border-pink-200 focus:ring-pink-500 focus:outline-none focus:ring-2">
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

        {{-- ================= TABLE ================= --}}
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
                        <th class="py-3 px-4">Pay Status</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Total</th>
                        <th class="py-3 px-4">View</th>
                    </tr>
                </thead>

                <tbody class="text-gray-700">
                @php
                    $statusClasses = [
                        'Confirmed'        => 'bg-green-600 text-white',
                        'Cancelled'        => 'bg-red-600 text-white',
                        'Preparing'        => 'bg-purple-600 text-white',
                        'Out for Delivery' => 'bg-indigo-600 text-white',
                        'Done'             => 'bg-green-700 text-white',
                        'Pending'          => 'bg-yellow-200 text-gray-700'
                    ];
                @endphp

                @forelse($orders as $order)
                @php
                    // Downpayment detection (GCash DP + COD remaining balance)
                    $gcashPayment   = $order->payments->firstWhere('paymentType', 'downpayment');
                    $codPayment     = $order->payments->firstWhere('paymentType', 'remaining_balance')
                                ?? $order->payments->firstWhere('method', 'COD');
                    $hasDownpayment = !is_null($gcashPayment);

                    // Statuses for split-payment rows
                    $gcashStatus  = $gcashPayment->status ?? 'pending';
                    $codStatus    = $codPayment->status   ?? 'pending';
                    $canToggleCod = $order->status === 'Done' || $codStatus === 'approved';

                    // Single-payment fallback
                    $payMethod = $order->payment->method ?? 'COD';
                    $payStatus = $order->payment->status ?? 'pending';
                    $isCOD     = $payMethod === 'COD';
                    $canToggle = $order->status === 'Done' || $payStatus === 'approved';

                    $payBadgeClass = match($payStatus) {
                        'approved' => 'bg-green-100 text-green-700 border border-green-300',
                        'rejected' => 'bg-red-100 text-red-700 border border-red-300',
                        default    => 'bg-yellow-100 text-yellow-700 border border-yellow-300',
                    };
                @endphp
                    <tr class="border-b hover:bg-pink-50 transition order-row"
                        data-order-id="{{ $order->orderID }}"
                        data-status="{{ $order->status }}"
                        data-date="{{ $order->orderDate ? $order->orderDate->format('Y-m-d') : '' }}"
                        data-customer="{{ $order->customer ? strtolower($order->customer->firstName . ' ' . $order->customer->lastName) : '' }}"
                        data-order-index="{{ $loop->index }}">

                        <td class="py-3 px-4">
                            <input type="checkbox" class="orderCheckbox" value="{{ $order->orderID }}">
                        </td>

                        <td class="py-3 px-4 font-medium">{{ $order->orderID }}</td>

                        <td class="py-3 px-4">
                            {{ $order->orderDate ? $order->orderDate->format('Y-m-d h:i A') : 'N/A' }}
                        </td>

                        <td class="py-3 px-4">
                            {{ $order->orderItems->sum('qty') }}
                        </td>

                        <td class="py-3 px-4">
                            {{ $order->deliveryDate ? $order->deliveryDate->format('Y-m-d') : 'N/A' }}
                        </td>

                        {{-- Payment Method --}}
                        <td class="py-3 px-4">
                            {{ $hasDownpayment ? 'GCASH + COD' : $payMethod }}
                        </td>
 
                        {{-- Pay Status --}}
                        <td class="py-3 px-4">
                            @if($hasDownpayment)
                                <div class="flex flex-col gap-1">
 
                                    {{-- GCash downpayment — auto-managed, no toggle --}}
                                    <div class="flex items-center gap-1">
                                        <span class="text-xs text-gray-400">DP:</span>
                                        <span class="pay-status-badge inline-block px-2 py-0.5 rounded-full text-xs font-semibold
                                            {{ $gcashStatus === 'approved'
                                                ? 'bg-green-100 text-green-700 border border-green-300'
                                                : 'bg-yellow-100 text-yellow-700 border border-yellow-300' }}">
                                            {{ ucfirst($gcashStatus) }}
                                        </span>
                                        <span class="text-gray-300 text-xs" title="Auto-managed via GCash">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                    </div>
 
                                    {{-- COD remaining balance + toggle --}}
                                    <div class="flex items-center gap-1">
                                        <span class="text-xs text-gray-400">RB:</span>
                                        <span class="pay-status-badge-cod inline-block px-2 py-0.5 rounded-full text-xs font-semibold
                                            {{ $codStatus === 'approved'
                                                ? 'bg-green-100 text-green-700 border border-green-300'
                                                : 'bg-yellow-100 text-yellow-700 border border-yellow-300' }}">
                                            {{ ucfirst($codStatus) }}
                                        </span>
                                        <button
                                            onclick="{{ $canToggleCod ? "togglePayStatus({$order->orderID}, this)" : 'void(0)' }}"
                                            title="{{ $canToggleCod
                                                ? ($codStatus === 'approved' ? 'Mark as Pending' : 'Mark Remaining as Paid')
                                                : 'Order must be Done first' }}"
                                            {{ !$canToggleCod ? 'disabled' : '' }}
                                            class="pay-toggle-btn p-1 rounded-lg transition text-xs
                                                {{ !$canToggleCod
                                                    ? 'text-gray-300 cursor-not-allowed opacity-40'
                                                    : ($codStatus === 'approved'
                                                        ? 'text-yellow-600 hover:bg-yellow-50'
                                                        : 'text-green-600 hover:bg-green-50') }}">
                                            <i class="{{ $codStatus === 'approved' ? 'fas fa-undo' : 'fas fa-check-circle' }}"></i>
                                        </button>
                                    </div>
 
                                </div>
                            @else
                                {{-- Single payment --}}
                                <div class="flex items-center gap-2">
                                    <span class="pay-status-badge inline-block px-2 py-0.5 rounded-full text-xs font-semibold
                                        {{ match($payStatus) {
                                            'approved' => 'bg-green-100 text-green-700 border border-green-300',
                                            'rejected' => 'bg-red-100 text-red-700 border border-red-300',
                                            default    => 'bg-yellow-100 text-yellow-700 border border-yellow-300',
                                        } }}">
                                        {{ ucfirst($payStatus) }}
                                    </span>
 
                                    @if($isCOD)
                                        <button
                                            onclick="{{ $canToggle ? "togglePayStatus({$order->orderID}, this)" : 'void(0)' }}"
                                            title="{{ $canToggle
                                                ? ($payStatus === 'approved' ? 'Mark as Pending' : 'Mark as Paid')
                                                : 'Order must be Done' }}"
                                            {{ !$canToggle ? 'disabled' : '' }}
                                            class="pay-toggle-btn p-1 rounded-lg transition text-xs
                                                {{ !$canToggle
                                                    ? 'text-gray-300 cursor-not-allowed opacity-40'
                                                    : ($payStatus === 'approved'
                                                        ? 'text-yellow-600 hover:bg-yellow-50'
                                                        : 'text-green-600 hover:bg-green-50') }}">
                                            <i class="{{ $payStatus === 'approved' ? 'fas fa-undo' : 'fas fa-check-circle' }}"></i>
                                        </button>
                                    @else
                                        <span class="text-gray-300 text-xs" title="Auto-managed">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </td>
 
                        {{-- Order Status --}}
                        <td class="py-3 px-4 status">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $statusClasses[$order->status] ?? 'bg-gray-200 text-gray-700' }}">
                                {{ $order->status }}
                            </span>
                        </td>
 
                        {{-- Total --}}
                        <td class="py-3 px-4 font-semibold">
                            ₱{{ number_format($order->totalAmount, 2) }}
                        </td>
 
                        {{-- View --}}
                        <td class="py-3 px-4">
                            <button onclick="viewOrder({{ $order->orderID }})"
                                    class="p-2 rounded-lg hover:bg-gray-100 transition text-pink-500">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
 
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="py-10 text-center text-gray-400">
                            No orders found
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
<div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-gray-500">
    <span id="orders-count-label"></span>
    <div id="orders-page-buttons" class="flex flex-wrap gap-1"></div>
</div>

{{-- ================= VIEW ORDER MODAL ================= --}}
<div id="view-order-modal"
     class="fixed inset-0 bg-black bg-opacity-50 hidden flex justify-center items-center z-50">
    <div class="bg-white rounded-2xl p-6 max-w-3xl w-full relative max-h-[90vh] overflow-y-auto shadow-xl">
        <button onclick="closeViewModal()"
                class="absolute top-3 right-4 text-gray-500 text-2xl hover:text-black">
            &times;
        </button>
        <div id="order-content"></div>
    </div>
</div>

{{-- FEEDBACK MESSAGE --}}
<div id="actionMessage"
     class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-30 hidden z-50">
    <div class="px-6 py-4 rounded-xl shadow-lg text-center">
        <p id="actionText" class="text-lg font-semibold text-gray-800"></p>
    </div>
</div>

{{-- CONFIRM ACTION MODAL --}}
<div id="confirmModal"
     class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
    <div class="bg-white rounded-xl p-6 w-96 text-center shadow-xl">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Confirm Action</h3>
        <p id="confirmText" class="text-gray-600 mb-6"></p>
        <div class="flex justify-center gap-4">
            <button id="confirmBtn"
                    class="px-4 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700">
                Confirm
            </button>
            <button onclick="closeConfirmModal()"
                    class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400">
                Cancel
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let confirmCallback = null;

function showMessage(message, status = null) {
    const box  = document.getElementById('actionMessage');
    const text = document.getElementById('actionText');
    let bg = 'bg-gray-700';
    if (status === 'Confirmed')        bg = 'bg-green-600';
    if (status === 'Cancelled')        bg = 'bg-red-600';
    if (status === 'Preparing')        bg = 'bg-purple-600';
    if (status === 'Out for Delivery') bg = 'bg-indigo-600';
    if (status === 'Done')             bg = 'bg-green-700';
    if (status === 'pay_approved')     bg = 'bg-green-600';
    if (status === 'pay_pending')      bg = 'bg-yellow-500';
    text.innerHTML = `<span class="px-6 py-3 rounded-xl text-white font-semibold ${bg}">${message}</span>`;
    box.classList.remove('hidden');
    setTimeout(() => box.classList.add('hidden'), 3000);
}

function showConfirm(message, callback) {
    document.getElementById('confirmText').innerText = message;
    document.getElementById('confirmModal').classList.remove('hidden');
    confirmCallback = callback;
}

function closeConfirmModal() {
    document.getElementById('confirmModal').classList.add('hidden');
}

document.getElementById('confirmBtn').addEventListener('click', function () {
    if (confirmCallback) confirmCallback();
    closeConfirmModal();
});

// ── COD Pay Status Toggle ──
window.togglePayStatus = function (orderID, btn) {
    const row = btn.closest('tr');

    // ✅ For downpayment rows, target the COD badge (pay-status-badge-cod)
    // For regular COD rows, target pay-status-badge
    const badge = row.querySelector('.pay-status-badge-cod') 
               ?? row.querySelector('.pay-status-badge');

    const isApproved = badge.textContent.trim().toLowerCase() === 'approved';
    const action = isApproved ? 'mark as Pending' : 'mark as Paid';

    showConfirm(`Order #${orderID}: ${action}?`, () => {
        fetch(`/admin/orders/${orderID}/update-payment-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(res => res.json())
        .then(data => {
            if (data.status !== 'success') {
                showMessage(data.message || 'Error');
                return;
            }

            const newStatus = data.new_status;
            const isPaid    = newStatus === 'approved';

            badge.textContent = isPaid ? 'Approved' : 'Pending';
            badge.className = badge.className.replace(
                /bg-\w+-100 text-\w+-\d+ border border-\w+-\d+/,
                isPaid
                    ? 'bg-green-100 text-green-700 border border-green-300'
                    : 'bg-yellow-100 text-yellow-700 border border-yellow-300'
            );

            btn.title = isPaid ? 'Mark as Pending' : 'Mark Remaining as Paid';
            btn.className = 'pay-toggle-btn p-1 rounded-lg transition text-xs ' +
                (isPaid ? 'text-yellow-600 hover:bg-yellow-50' : 'text-green-600 hover:bg-green-50');
            btn.innerHTML = `<i class="${isPaid ? 'fas fa-undo' : 'fas fa-check-circle'}"></i>`;

            showMessage(
                `Order #${orderID} remaining balance ${isPaid ? 'approved' : 'set to pending'}`,
                isPaid ? 'pay_approved' : 'pay_pending'
            );
        })
        .catch(() => showMessage('Network error.'));
    });
};

document.addEventListener('DOMContentLoaded', () => {

    const selectAll   = document.getElementById('selectAll');
    const bulkActions = document.getElementById('bulkActions');
    const perPageSel  = document.getElementById('ordersPerPage');
    const countLabel  = document.getElementById('orders-count-label');
    const pageButtons = document.getElementById('orders-page-buttons');
    const allRows     = [...document.querySelectorAll('.order-row')];
    const emptyRow    = document.querySelector('tbody tr:not(.order-row)');

    const filters = {
        searchInput:    document.getElementById('searchInput'),
        statusFilter:   document.getElementById('statusFilter'),
        startDate:      document.getElementById('startDate'),
        endDate:        document.getElementById('endDate'),
        customerFilter: document.getElementById('customerFilter'),
    };

    let currentPage = 1;
    let perPage     = parseInt(perPageSel.value);

    const btnClass = (active) =>
        `px-2.5 py-1 rounded-lg border text-xs font-medium transition ${
            active
            ? 'bg-pink-500 text-white border-pink-500'
            : 'bg-white text-gray-600 border-gray-200 hover:border-pink-300'
        }`;

    function getVisible() {
        const sq      = filters.searchInput.value.toLowerCase();
        const statusV = filters.statusFilter.value;
        const start   = filters.startDate.value;
        const end     = filters.endDate.value;
        const custV   = filters.customerFilter.value;

        return allRows.filter(row => {
            const id       = (row.dataset.orderId || '').toString().toLowerCase();
            const status   = row.dataset.status   || '';
            const date     = row.dataset.date      || '';
            const customer = row.dataset.customer  || '';
            let show = true;
            if (sq      && !id.includes(sq))   show = false;
            if (statusV && status !== statusV)  show = false;
            if (custV   && customer !== custV)  show = false;
            if (start   && date < start)        show = false;
            if (end     && date > end)          show = false;
            return show;
        });
    }

    function render() {
        const visible    = getVisible();
        const total      = visible.length;
        const totalPages = Math.max(1, Math.ceil(total / perPage));
        currentPage      = Math.min(currentPage, totalPages);
        const start      = (currentPage - 1) * perPage;
        const end        = start + perPage;

        allRows.forEach(row => row.style.display = 'none');
        visible.slice(start, end).forEach(row => row.style.display = '');

        if (emptyRow) emptyRow.style.display = total === 0 ? '' : 'none';

        const from = total === 0 ? 0 : start + 1;
        const to   = Math.min(end, total);
        countLabel.textContent = total === 0
            ? 'No orders match your filters'
            : `Showing ${from}–${to} of ${total} order(s)`;

        pageButtons.innerHTML = '';
        if (totalPages <= 1) return;

        const makeBtn = (label, target, active, disabled) => {
            const btn = document.createElement('button');
            btn.textContent = label;
            btn.className   = btnClass(active);
            btn.disabled    = disabled;
            if (disabled) btn.style.opacity = '0.4';
            btn.addEventListener('click', () => { currentPage = target; render(); deselectAll(); });
            return btn;
        };

        pageButtons.appendChild(makeBtn('‹', currentPage - 1, false, currentPage === 1));
        for (let p = 1; p <= totalPages; p++) {
            if (totalPages > 7 && Math.abs(p - currentPage) > 2 && p !== 1 && p !== totalPages) {
                if (p === currentPage - 3 || p === currentPage + 3) {
                    const dots = document.createElement('span');
                    dots.textContent = '…';
                    dots.style.padding = '0 4px';
                    pageButtons.appendChild(dots);
                }
                continue;
            }
            pageButtons.appendChild(makeBtn(p, p, p === currentPage, false));
        }
        pageButtons.appendChild(makeBtn('›', currentPage + 1, false, currentPage === totalPages));
    }

    function applyFilters() { currentPage = 1; render(); deselectAll(); }

    function deselectAll() {
        selectAll.checked = false;
        document.querySelectorAll('.orderCheckbox').forEach(cb => cb.checked = false);
        toggleBulkActions();
    }

    function toggleBulkActions() {
        const anyChecked = document.querySelectorAll('.orderCheckbox:checked').length > 0;
        bulkActions.classList.toggle('hidden', !anyChecked);
    }

    Object.values(filters).forEach(el => {
        el.addEventListener(el.tagName === 'INPUT' ? 'input' : 'change', applyFilters);
    });

    perPageSel.addEventListener('change', function () {
        perPage = parseInt(this.value); currentPage = 1; render(); deselectAll();
    });

    document.addEventListener('change', e => {
        if (e.target.classList.contains('orderCheckbox')) {
            toggleBulkActions();
            if (!e.target.checked) {
                selectAll.checked = false;
            } else {
                const allCbs = [...document.querySelectorAll('.orderCheckbox')]
                    .filter(cb => cb.closest('tr').style.display !== 'none');
                selectAll.checked = allCbs.every(cb => cb.checked);
            }
        }
    });

    selectAll.addEventListener('change', function () {
        const visibleCbs = [...document.querySelectorAll('.orderCheckbox')]
            .filter(cb => cb.closest('tr').style.display !== 'none');
        visibleCbs.forEach(cb => cb.checked = this.checked);
        toggleBulkActions();
    });

    window.bulkUpdate = function (newStatus) {
        const selected = [...document.querySelectorAll('.orderCheckbox:checked')];
        if (!selected.length) return;
        showConfirm(`Update ${selected.length} order(s) to "${newStatus}"?`, () => {
            selected.forEach(cb => updateStatus(cb.value, newStatus));
            showMessage(`${selected.length} order(s) updated to ${newStatus}`, newStatus);
            deselectAll();
        });
    };

    function getStatusClass(status) {
        switch (status) {
            case 'Confirmed':        return 'bg-green-400 text-white';
            case 'Cancelled':        return 'bg-red-600 text-white';
            case 'Preparing':        return 'bg-purple-600 text-white';
            case 'Out for Delivery': return 'bg-indigo-600 text-white';
            case 'Done':             return 'bg-green-700 text-white';
            default:                 return 'bg-gray-200 text-gray-700';
        }
    }

    // ── Helper: refresh pay toggle button based on current order status ──
function refreshPayToggleState(row, orderStatus) {
    const btn = row.querySelector('.pay-toggle-btn');
    if (!btn) return; // non-COD rows have no toggle btn

    const badge      = row.querySelector('.pay-status-badge');
    const isApproved = badge.textContent.trim().toLowerCase() === 'approved';
    const canToggle  = orderStatus === 'Done' || isApproved;

    if (canToggle) {
        btn.disabled  = false;
        btn.title     = isApproved ? 'Mark as Pending' : 'Mark as Paid';
        btn.className = 'pay-toggle-btn p-1 rounded-lg transition text-xs ' +
            (isApproved ? 'text-yellow-600 hover:bg-yellow-50' : 'text-green-600 hover:bg-green-50');
        btn.style.cursor = '';
        const orderId = parseInt(row.dataset.orderId);
        btn.onclick = function () { togglePayStatus(orderId, btn); };
    } else {
        btn.disabled  = true;
        btn.title     = 'Order must be Done to approve payment';
        btn.className = 'pay-toggle-btn p-1 rounded-lg transition text-xs text-gray-300 cursor-not-allowed opacity-40';
        btn.style.cursor = 'not-allowed';
        btn.onclick = null;
    }
}

window.updateStatus = function (orderId, newStatus) {
    fetch(`/admin/orders/${orderId}/update-status`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            const row = document.querySelector(`tr[data-order-id="${orderId}"]`);

            row.querySelector('.status').innerHTML =
                `<span class="inline-block px-3 py-1 rounded-full text-xs font-semibold ${getStatusClass(newStatus)}">${newStatus}</span>`;
            row.dataset.status = newStatus;

            if (data.new_pay_status) {
                const isPaid = data.new_pay_status === 'approved';

                // ✅ Targets both regular COD and GCASH+COD remaining balance
                const badge = row.querySelector('.pay-status-badge-cod')
                           ?? row.querySelector('.pay-status-badge');
                const btn   = row.querySelector('.pay-toggle-btn');

                if (badge) {
                    badge.textContent = isPaid ? 'Approved' : 'Pending';
                    badge.className = badge.className.replace(
                        /bg-\w+-100 text-\w+-\d+ border border-\w+-\d+/,
                        isPaid
                            ? 'bg-green-100 text-green-700 border border-green-300'
                            : 'bg-yellow-100 text-yellow-700 border border-yellow-300'
                    );
                }

                if (btn) {
                    btn.disabled  = false;
                    btn.title     = isPaid ? 'Mark as Pending' : 'Mark as Paid';
                    btn.className = 'pay-toggle-btn p-1 rounded-lg transition text-xs ' +
                        (isPaid ? 'text-yellow-600 hover:bg-yellow-50' : 'text-green-600 hover:bg-green-50');
                    btn.innerHTML = `<i class="${isPaid ? 'fas fa-undo' : 'fas fa-check-circle'}"></i>`;
                }
            }

            showMessage(`Order #${orderId} updated to ${newStatus}`, newStatus);
        }
    });
};

window.viewOrder = function (orderID) {
    fetch(`/admin/orders/${orderID}/view`)
    .then(res => res.json())
    .then(data => {
        if (data.status !== 'success') return;
        const order = data.order;

        // ✅ Payment breakdown (supports multiple payment records)
        const paymentsHtml = (order.payments?.length ? order.payments : (order.payment ? [order.payment] : []))
            .map(p => `
                <div class="flex justify-between text-sm py-1 border-b last:border-0">
                    <span class="text-gray-500 capitalize">
                        ${(p.paymentType ?? 'payment').replace(/_/g,' ')} — ${p.method}
                    </span>
                    <span class="flex items-center gap-2">
                        ₱${parseFloat(p.amount).toFixed(2)}
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                            ${p.status === 'approved'
                                ? 'bg-green-100 text-green-700'
                                : 'bg-yellow-100 text-yellow-700'}">
                            ${p.status}
                        </span>
                    </span>
                </div>
            `).join('');

        let itemsHtml = '';
        order.order_items.forEach(item => {
            let extras = [];
            if (item.size)    extras.push(`<span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full">Size: ${item.size}</span>`);
            if (item.message) extras.push(`<span class="bg-pink-100 text-pink-700 text-xs px-2 py-0.5 rounded-full">📝 "${item.message}"</span>`);
            if (item.customization) {
                const c = typeof item.customization === 'string' ? JSON.parse(item.customization) : item.customization;
                if (c.flavor) extras.push(`<span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-0.5 rounded-full">Flavor: ${c.flavor}</span>`);
                if (c.shape)  extras.push(`<span class="bg-purple-100 text-purple-700 text-xs px-2 py-0.5 rounded-full">Shape: ${c.shape}</span>`);
                if (c.icing)  extras.push(`<span class="bg-orange-100 text-orange-700 text-xs px-2 py-0.5 rounded-full">Icing: ${c.icing}</span>`);
            }
            let includesHtml = '';
            if (item.includes) {
                const inc = typeof item.includes === 'string' ? JSON.parse(item.includes) : item.includes;
                if (Array.isArray(inc) && inc.length) {
                    includesHtml = `<p class="text-xs text-gray-400 mt-1 font-medium">Includes:</p>
                        <ul class="list-disc ml-4 text-xs text-gray-500">${inc.map(i => `<li>${i}</li>`).join('')}</ul>`;
                }
            }
            itemsHtml += `
                <tr>
                    <td class="border-b py-2 px-4">
                        <div class="font-medium">${item.product.name}</div>
                        ${extras.length ? `<div class="flex flex-wrap gap-1 mt-1">${extras.join('')}</div>` : ''}
                        ${includesHtml}
                    </td>
                    <td class="border-b py-2 px-4 text-right">₱${parseFloat(item.price).toFixed(2)}</td>
                    <td class="border-b py-2 px-4 text-center">${item.qty}</td>
                    <td class="border-b py-2 px-4 text-right">₱${parseFloat(item.subtotal).toFixed(2)}</td>
                </tr>`;
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
                                <td class="text-right font-semibold py-2 px-4">₱${parseFloat(order.totalAmount).toFixed(2)}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="border p-4 rounded-lg bg-white shadow-sm">
                    <h4 class="font-semibold mb-3">Customer</h4>
                    <p class="font-semibold">${order.customer.firstName} ${order.customer.lastName}</p>
                    <p>${order.customer.address || 'N/A'}</p>
                    <p>${order.customer.phone   || 'N/A'}</p>
                    <p>${order.customer.email   || 'N/A'}</p>
                </div>
                <div class="col-span-3 border p-4 rounded-lg bg-white shadow-sm mt-6">
                    <h4 class="font-semibold mb-3">Order Details</h4>
                    <p><strong>Order Status:</strong> ${order.status}</p>

                    <div class="mt-2 mb-2">
                        <p class="font-semibold text-sm mb-1">Payment Breakdown:</p>
                        <div class="border rounded-lg px-3 py-2 bg-gray-50">
                            ${paymentsHtml || '<p class="text-xs text-gray-400">No payment records</p>'}
                        </div>
                    </div>

                    <p><strong>Order Date:</strong> ${new Date(order.orderDate).toLocaleString()}</p>
                    <p><strong>Delivery Date:</strong> ${order.deliveryDate ? new Date(order.deliveryDate).toLocaleDateString() : 'N/A'}</p>
                    <p><strong>Delivery Time:</strong> ${order.deliveryTime ? new Date('1970-01-01T' + order.deliveryTime).toLocaleTimeString([], {hour:'2-digit',minute:'2-digit',hour12:true}) : 'N/A'}</p>
                    <p><strong>Delivery Address:</strong> ${order.deliveryAddress ?? 'N/A'}</p>
                    <p><strong>Order Message:</strong> ${order.remarks ?? 'N/A'}</p>
                </div>
            </div>`;
        document.getElementById('view-order-modal').classList.remove('hidden');
    });
};

    window.closeViewModal = function () {
        document.getElementById('view-order-modal').classList.add('hidden');
    };

    document.getElementById('view-order-modal').addEventListener('click', e => {
        if (e.target.id === 'view-order-modal') closeViewModal();
    });
    document.getElementById('confirmModal').addEventListener('click', e => {
        if (e.target.id === 'confirmModal') closeConfirmModal();
    });

    document.addEventListener('click', e => {
        const insideTable = e.target.closest('table');
        const isCheckbox  = e.target.classList.contains('orderCheckbox') || e.target.id === 'selectAll';
        if (!insideTable && !isCheckbox) deselectAll();
    });

    render();
});
</script>
@endsection