@extends('layouts.admin')
 
@section('title', 'Inventory')
 
@section('content')
 
@php
    $currentUserID = DB::table('user')
        ->where('username', session('admin_user')['username'])
        ->value('userID');
@endphp
 
<div 
    x-data="{
        showAddModal: false,
        showReceiveModal: false,
        showPullOutModal: false,
        showAddSupplierModal: false,
        showEditSupplierModal: false,
        selectedSupplier: {},
        searchQuery: '',
        filterStatus: 'all',
        filterIngredient: 'all',
        historyTab: 'all',
        perPage: 10,
        ingredientPage: 1,
        txPage: 1,
        supplierPage: 1,
        supplierSearch: '',
        toast: { show: false, message: '', type: 'success' },
 
        receiveForm: {
            receive_type: 'Supplier Delivery',
            supplier: '',
            received_by: {{ $currentUserID ?? 'null' }},
            items: [{ ingredient_id: '', qty: '', unit_cost: '', expiry_date: '' }]
        },
        pullOutForm: {
            pull_type: 'Preparation',
            pulled_by: {{ $currentUserID ?? 'null' }},
            items: [{ ingredient_id: '', qty: '' }]
        },
 
        showToast(message, type = 'success') {
            this.toast = { show: true, message, type };
            setTimeout(() => this.toast.show = false, 3500);
        },
 
        addReceiveRow() {
            this.receiveForm.items.push({ ingredient_id: '', qty: '', unit_cost: '', expiry_date: '' });
        },
        removeReceiveRow(index) {
            if (this.receiveForm.items.length > 1) this.receiveForm.items.splice(index, 1);
        },
        addPullRow() {
            this.pullOutForm.items.push({ ingredient_id: '', qty: '' });
        },
        removePullRow(index) {
            if (this.pullOutForm.items.length > 1) this.pullOutForm.items.splice(index, 1);
        },
 
        resetReceiveForm() {
            this.receiveForm = {
                receive_type: 'Supplier Delivery',
                supplier: '',
                received_by: {{ $currentUserID ?? 'null' }},
                items: [{ ingredient_id: '', qty: '', unit_cost: '', expiry_date: '' }]
            };
        },
        resetPullForm() {
            this.pullOutForm = {
                pull_type: 'Preparation',
                pulled_by: {{ $currentUserID ?? 'null' }},
                items: [{ ingredient_id: '', qty: '' }]
            };
        },
    }"
    @keydown.escape.window="
        showAddModal = false;
        showReceiveModal = false; showPullOutModal = false;
        showAddSupplierModal = false; showEditSupplierModal = false;
    "
    class="px-3 sm:px-6 md:px-10 py-6 md:py-8"
>
 
    {{-- Toast --}}
    <div
        x-show="toast.show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed top-10 left-1/2 -translate-x-1/2 z-[9999]"
        x-cloak
    >
        <div
            :class="toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'"
            class="text-white px-8 py-4 rounded-full shadow-2xl flex items-center gap-3 border-2 border-white/20"
        >
            <i :class="toast.type === 'success' ? 'fas fa-check-circle' : 'fas fa-circle-xmark'" class="text-xl"></i>
            <span x-text="toast.message" class="font-bold whitespace-nowrap"></span>
        </div>
    </div>
 
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-800">Inventory Management</h1>
            <p class="text-gray-500 mt-1 text-xs sm:text-sm md:text-base">Monitor and manage ingredient stock levels</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button @click="showReceiveModal = true; resetReceiveForm()"
                class="flex items-center gap-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg shadow text-xs sm:text-sm font-medium transition">
                <i class="fas fa-arrow-down"></i><span>Stock In</span>
            </button>
            <button @click="showPullOutModal = true; resetPullForm()"
                class="flex items-center gap-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg shadow text-xs sm:text-sm font-medium transition">
                <i class="fas fa-arrow-up"></i><span>Pull Out</span>
            </button>
        </div>
    </div>
 
    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 mb-8">
        <div class="bg-gradient-to-br from-pink-50 to-white border border-pink-100 shadow-sm hover:shadow-md transition rounded-xl p-4 md:p-5">
            <div class="flex items-start justify-between">
                <p class="text-gray-600 font-semibold text-xs sm:text-sm">Available Ingredients</p>
                <div class="bg-white/80 rounded-full p-2 border border-pink-100"><i class="fas fa-boxes-stacked text-pink-500"></i></div>
            </div>
            <h3 class="text-xl sm:text-2xl md:text-3xl font-bold mt-4">{{ $ingredients->filter(fn($i) => $i->currentStock > $i->minStockLevel)->count() }}</h3>
            <p class="text-gray-400 text-xs mt-1">Ingredients in stock</p>
        </div>
        <div class="bg-gradient-to-br from-pink-50 to-white border border-pink-100 shadow-sm hover:shadow-md transition rounded-xl p-4 md:p-5">
            <div class="flex items-start justify-between">
                <p class="text-gray-600 font-semibold text-xs sm:text-sm">Low Stock</p>
                <div class="bg-white/80 rounded-full p-2 border border-pink-100"><i class="fas fa-triangle-exclamation text-yellow-500"></i></div>
            </div>
            <h3 class="text-xl sm:text-2xl md:text-3xl font-bold mt-4 text-yellow-600">{{ $ingredients->filter(fn($i) => $i->currentStock > 0 && $i->currentStock <= $i->minStockLevel)->count() }}</h3>
            <p class="text-gray-400 text-xs mt-1">Needs restocking</p>
        </div>
        <div class="bg-gradient-to-br from-pink-50 to-white border border-pink-100 shadow-sm hover:shadow-md transition rounded-xl p-4 md:p-5">
            <div class="flex items-start justify-between">
                <p class="text-gray-600 font-semibold text-xs sm:text-sm">Out of Stock</p>
                <div class="bg-white/80 rounded-full p-2 border border-pink-100"><i class="fas fa-ban text-red-500"></i></div>
            </div>
            <h3 class="text-xl sm:text-2xl md:text-3xl font-bold mt-4 text-red-500">{{ $ingredients->where('currentStock', 0)->count() }}</h3>
            <p class="text-gray-400 text-xs mt-1">No stock remaining</p>
        </div>
        <div class="bg-gradient-to-br from-green-50 to-white border border-green-100 shadow-sm hover:shadow-md transition rounded-xl p-4 md:p-5">
            <div class="flex items-start justify-between">
                <p class="text-gray-600 font-semibold text-xs sm:text-sm">Total Stock In</p>
                <div class="bg-white/80 rounded-full p-2 border border-green-100"><i class="fas fa-arrow-down text-green-500"></i></div>
            </div>
            <h3 class="text-xl sm:text-2xl md:text-3xl font-bold mt-4 text-green-600">{{ $totalStockIn ?? 0 }}</h3>
            <p class="text-gray-400 text-xs mt-1">All deliveries</p>
        </div>
        <div class="bg-gradient-to-br from-orange-50 to-white border border-orange-100 shadow-sm hover:shadow-md transition rounded-xl p-4 md:p-5">
            <div class="flex items-start justify-between">
                <p class="text-gray-600 font-semibold text-xs sm:text-sm">Total Stock Out</p>
                <div class="bg-white/80 rounded-full p-2 border border-orange-100"><i class="fas fa-arrow-up text-orange-500"></i></div>
            </div>
            <h3 class="text-xl sm:text-2xl md:text-3xl font-bold mt-4 text-orange-500">{{ $totalStockOut ?? 0 }}</h3>
            <p class="text-gray-400 text-xs mt-1">All pull-outs</p>
        </div>
    </div>
 
    {{-- Search + Status Filter + Expiry Filter --}}
    <div class="w-full border rounded-xl border-pink-200 p-4 md:p-5 mb-4">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <input type="text" x-model="searchQuery" placeholder="Search ingredients..."
                    class="w-full border rounded-lg pl-10 p-3 focus:outline-none focus:ring-2 focus:ring-pink-500 text-sm md:text-base">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-lg"></i>
            </div>
            <select x-model="filterStatus"
                class="border rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 bg-white min-w-[160px]">
                <option value="all">All Statuses</option>
                <option value="available">Available</option>
                <option value="low stock">Low Stock</option>
                <option value="out of stock">Out of Stock</option>
            </select>
            <select id="filterExpiry"
                class="border rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 bg-white min-w-[180px]">
                <option value="all">All Expiry Dates</option>
                <option value="today">Expiring Today</option>
                <option value="7">Expiring in 7 Days</option>
                <option value="30">Expiring in 30 Days</option>
                <option value="none">No Expiry Set</option>
            </select>
        </div>
    </div>
 
    {{-- Ingredients Table --}}
    <div class="border border-pink-200 mt-8 rounded-xl p-4 md:p-6 overflow-x-auto">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-3">
            <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Ingredients</h2>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <label class="text-xs text-gray-500 whitespace-nowrap">Rows per page</label>
                    <select x-model.number="perPage" @change="ingredientPage = 1; txPage = 1"
                        class="border rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-pink-400 bg-white">
                        <option value="5">5</option>
                        <option value="10" selected>10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                </div>
                <button @click="showAddModal = true"
                    class="flex items-center justify-center gap-2 px-4 py-2 bg-pink-500 hover:bg-pink-600 text-white rounded-lg shadow text-xs sm:text-sm font-medium transition">
                    <i class="fas fa-plus"></i><span>Add Ingredient</span>
                </button>
            </div>
        </div>
 
        <p class="text-gray-500 text-xs sm:text-sm mb-4">{{ count($ingredients) }} ingredient(s) found</p>
 
        <table class="min-w-full text-left text-xs sm:text-sm whitespace-nowrap">
    <thead class="border-b text-gray-600">
        <tr>
            <th class="py-2 pr-4">Ingredient</th>
            <th class="py-2 pr-4">Unit</th>
            <th class="py-2 pr-4">Total In</th>
            <th class="py-2 pr-4">Total Used</th>
            <th class="py-2 pr-4">Available</th>
            <th class="py-2 pr-4">Reorder Level</th>
            <th class="py-2 pr-4">Expiry Date</th>
            <th class="py-2">Status</th>
        </tr>
    </thead>
    <tbody id="ingredient-tbody">
        @forelse ($ingredients as $ingredient)
        @php
            $available = $ingredient->totalIn - $ingredient->totalOut;
            $statusKey = $available <= 0 ? 'out of stock'
                : ($available <= $ingredient->minStockLevel ? 'low stock' : 'available');

            $expiryFormatted = $ingredient->nearestExpiry
                ? \Carbon\Carbon::parse($ingredient->nearestExpiry)->format('M d, Y')
                : null;

            // "Soon" only if expiry is 1–7 days from now (strictly future, not today)
            $daysUntilExpiry = $ingredient->nearestExpiry
                ? (int) today()->diffInDays(\Carbon\Carbon::parse($ingredient->nearestExpiry)->startOfDay(), false)
                : null;

            $expiryWarn = $daysUntilExpiry !== null && $daysUntilExpiry >= 1 && $daysUntilExpiry <= 7;
        @endphp
        <tr class="border-b hover:bg-pink-50 transition ingredient-row"
            data-name="{{ strtolower($ingredient->name) }}"
            data-desc="{{ strtolower($ingredient->description ?? '') }}"
            data-status="{{ $statusKey }}"
            data-expiry="{{ $ingredient->nearestExpiry ? \Carbon\Carbon::parse($ingredient->nearestExpiry)->format('Y-m-d') : '' }}"
            data-index="{{ $loop->index }}"
        >
            <td class="py-3 pr-4 font-medium text-gray-800">{{ $ingredient->name }}</td>
            <td class="py-3 pr-4 text-gray-500">{{ $ingredient->unit ?? '—' }}</td>
            <td class="py-3 pr-4 text-green-600 font-medium">+{{ number_format($ingredient->totalIn, 2) }}</td>
            <td class="py-3 pr-4 text-orange-500 font-medium">-{{ number_format($ingredient->totalOut, 2) }}</td>
            <td class="py-3 pr-4 font-bold text-gray-800">{{ number_format(max(0, $available), 2) }}</td>
            <td class="py-3 pr-4 text-gray-500">{{ $ingredient->minStockLevel }}</td>
            <td class="py-3 pr-4">
                @if ($expiryFormatted)
                    <span class="{{ $expiryWarn ? 'text-red-500 font-semibold' : 'text-gray-500' }}">
                        {{ $expiryFormatted }}
                        @if ($expiryWarn)
                            <span class="text-xs bg-red-100 text-red-500 px-1.5 py-0.5 rounded-full ml-1">Soon</span>
                        @endif
                    </span>
                @else
                    <span class="text-gray-300">—</span>
                @endif
            </td>
            <td class="py-3">
                @if ($available <= 0)
                    <span class="bg-red-100 text-red-600 text-xs font-semibold px-2 py-1 rounded-full">Out of Stock</span>
                @elseif ($available <= $ingredient->minStockLevel)
                    <span class="bg-yellow-100 text-yellow-700 text-xs font-semibold px-2 py-1 rounded-full">Low Stock</span>
                @else
                    <span class="bg-green-100 text-green-700 text-xs font-semibold px-2 py-1 rounded-full">Available</span>
                @endif
            </td>
        </tr>
        @empty
        <tr id="ingredient-empty-row">
            <td colspan="8" class="py-10 text-center text-gray-400">
                <div class="flex flex-col items-center gap-2">
                    <i class="fas fa-box-open text-3xl opacity-50"></i>
                    <span>No ingredient found</span>
                </div>
            </td>
        </tr>
        @endforelse
    </tbody>
</table>
 
        {{-- Ingredient Pagination --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-4 text-xs text-gray-500" id="ingredient-pagination">
            <span id="ingredient-count-label"></span>
            <div class="flex items-center gap-1" id="ingredient-page-buttons"></div>
        </div>
    </div>
 
    {{-- Transaction History --}}
    <div class="border border-pink-200 mt-8 rounded-xl p-4 md:p-6 overflow-x-auto">
        <h2 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Transaction History</h2>
 
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div class="flex gap-1 border-b border-gray-200 sm:border-none">
                <button @click="historyTab = 'all'; txPage = 1"
                    :class="historyTab === 'all' ? 'border-b-2 border-pink-500 text-pink-600 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-2 text-sm transition">All</button>
                <button @click="historyTab = 'in'; txPage = 1"
                    :class="historyTab === 'in' ? 'border-b-2 border-green-500 text-green-600 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-2 text-sm transition">Stock In</button>
                <button @click="historyTab = 'out'; txPage = 1"
                    :class="historyTab === 'out' ? 'border-b-2 border-orange-500 text-orange-600 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-2 text-sm transition">Stock Out</button>
            </div>
            <select x-model="filterIngredient" @change="txPage = 1"
                class="border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-pink-400 bg-white min-w-[180px]">
                <option value="all">All Ingredients</option>
                @foreach ($ingredients as $ingredient)
                    <option value="{{ strtolower($ingredient->name) }}">{{ $ingredient->name }}</option>
                @endforeach
            </select>
        </div>
 
        <table class="min-w-full text-left text-xs sm:text-sm whitespace-nowrap">
            <thead class="border-b text-gray-600">
                <tr>
                    <th class="py-2 pr-4">Date</th>
                    <th class="py-2 pr-4">Type</th>
                    <th class="py-2 pr-4">Ingredient</th>
                    <th class="py-2 pr-4">Qty</th>
                    <th class="py-2 pr-4">By</th>
                    <th class="py-2">Details</th>
                </tr>
            </thead>
            <tbody id="tx-tbody">
                @forelse ($transactions ?? [] as $tx)
                <tr class="border-b hover:bg-pink-50 transition tx-row"
                    data-tx-type="{{ $tx['type'] }}"
                    data-tx-ingredient="{{ strtolower($tx['ingredient']) }}"
                    data-tx-index="{{ $loop->index }}"
                >
                    <td class="py-3 pr-4 text-gray-500">{{ \Carbon\Carbon::parse($tx['date'])->format('M d, Y h:i A') }}</td>
                    <td class="py-3 pr-4">
                        @if ($tx['type'] === 'in')
                            <span class="bg-green-100 text-green-700 text-xs font-semibold px-2 py-1 rounded-full">Stock In</span>
                        @else
                            <span class="bg-orange-100 text-orange-700 text-xs font-semibold px-2 py-1 rounded-full">Pull Out</span>
                        @endif
                    </td>
                    <td class="py-3 pr-4 font-medium text-gray-800">{{ $tx['ingredient'] }}</td>
                    <td class="py-3 pr-4 font-bold {{ $tx['type'] === 'in' ? 'text-green-600' : 'text-orange-500' }}">
                        {{ $tx['type'] === 'in' ? '+' : '-' }}{{ $tx['qty'] }}
                    </td>
                    <td class="py-3 pr-4 text-gray-600">{{ $tx['by'] }}</td>
                    <td class="py-3 text-gray-500">{{ $tx['remarks'] ?? '—' }}</td>
                </tr>
                @empty
                <tr id="tx-empty-row">
                    <td colspan="6" class="py-10 text-center text-gray-400">
                        <div class="flex flex-col items-center gap-2">
                            <i class="fas fa-clock-rotate-left text-3xl opacity-50"></i>
                            <span>No transactions yet</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
 
        {{-- Transaction Pagination --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-4 text-xs text-gray-500" id="tx-pagination">
            <span id="tx-count-label"></span>
            <div class="flex items-center gap-1" id="tx-page-buttons"></div>
        </div>
    </div>
 
    {{-- ===================== MODALS ===================== --}}
 
    {{-- Add Ingredient Modal --}}
    <div x-cloak x-show="showAddModal"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50 px-4 sm:px-6"
         @click.self="showAddModal = false">
        <div class="bg-white w-full max-w-xs sm:max-w-md md:max-w-lg rounded-2xl shadow-lg p-6"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Add Ingredient</h2>
                <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>
            <p class="text-xs text-gray-400 mb-4">Creates the ingredient master record only. Use <strong>Receive Stock</strong> to add quantities.</p>
            <form method="POST" action="{{ route('inventory.store') }}">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium block mb-1">Name</label>
                        <input type="text" name="name" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-400" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium block mb-1">Unit of Measure</label>
                        <select name="unit" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-400" required>
                            <option value="" disabled selected>-- Select unit --</option>
                            <option value="kg">kg — Kilogram</option>
                            <option value="g">g — Gram</option>
                            <option value="L">L — Liter</option>
                            <option value="ml">ml — Milliliter</option>
                            <option value="pcs">pcs — Pieces</option>
                            <option value="cans">cans — Cans</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium block mb-1">Min Stock Level</label>
                        <input type="number" name="min_stock" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-400" min="0" step="0.01" required>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50 transition">Cancel</button>
                    <button type="submit" class="px-6 py-2 rounded-lg bg-pink-500 hover:bg-pink-600 text-white font-semibold text-sm transition">Add</button>
                </div>
            </form>
        </div>
    </div>
 
    {{-- Receive Stock Modal --}}
    <div x-cloak x-show="showReceiveModal"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50 px-4 sm:px-6"
        @click.self="showReceiveModal = false">
        <div class="bg-white w-full max-w-3xl rounded-2xl shadow-lg p-6 max-h-[90vh] overflow-y-auto"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Receive Stock</h2>
                <button @click="showReceiveModal = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>
 
            <div class="grid grid-cols-2 gap-3 mb-5">
    <div>
        <label class="text-sm font-medium block mb-1">Receive Type</label>
        <select x-model="receiveForm.receive_type"
            class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
            <option value="Supplier Delivery">Supplier Delivery</option>
            <option value="Manual Adjustment">Manual Adjustment</option>
        </select>
    </div>
    <div>
        <label class="text-sm font-medium block mb-1">Received By</label>
        <input type="text" value="{{ session('admin_user')['username'] }}"
            class="w-full px-3 py-2 bg-gray-200 rounded-lg text-sm text-gray-600 cursor-not-allowed" readonly>
    </div>
</div>

{{-- Supplier row — hidden when Manual Adjustment --}}
<div x-show="receiveForm.receive_type === 'Supplier Delivery'" x-transition class="mb-5">
    <label class="text-sm font-medium block mb-1">Supplier</label>
    <select x-model="receiveForm.supplier"
        class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
        <option value="">-- Select --</option>
        @foreach ($suppliers as $supplier)
            <option value="{{ $supplier->supplierID }}">{{ $supplier->supplierName }}</option>
        @endforeach
    </select>
</div>
 
            <div class="mb-4">
                <label class="text-sm font-medium block mb-2">Ingredients Delivered</label>
 
                {{-- Column headers --}}
                <div class="grid grid-cols-[2fr_1fr_1fr_1fr_auto] gap-2 mb-1 px-1">
                    <span class="text-xs text-gray-400 font-medium">Ingredient</span>
                    <span class="text-xs text-gray-400 font-medium">Qty
                        <span x-show="receiveForm.receive_type === 'Manual Adjustment'" 
          class="text-orange-400">(+ add / - remove)</span>
                    </span>  
                    <span class="text-xs text-gray-400 font-medium">Unit Cost (₱)</span>
                    <span class="text-xs text-gray-400 font-medium">Expiry Date</span>
                    <span></span>
                </div>
 
                <template x-for="(item, index) in receiveForm.items" :key="index">
                    <div class="grid grid-cols-[2fr_1fr_1fr_1fr_auto] gap-2 mb-2 items-center">
                        <select x-model="item.ingredient_id"
                            class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                            <option value="">-- Ingredient --</option>
                            @foreach ($ingredients as $ingredient)
                                <option value="{{ $ingredient->ingredientID }}">
                                    {{ $ingredient->name }} — {{ number_format($ingredient->currentStock, 2) }} {{ $ingredient->unit ?? '' }} left
                                </option>
                            @endforeach
                        </select>
                        <input type="number" x-model="item.qty" 
    :min="receiveForm.receive_type === 'Manual Adjustment' ? null : '0.01'"
    step="0.01" placeholder="0"
    class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                        <input type="number" x-model="item.unit_cost" min="0" step="0.01" placeholder="0.00"
                            class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                        <input type="date" x-model="item.expiry_date"
                            class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                        <button type="button" @click="removeReceiveRow(index)"
                            class="text-red-400 hover:text-red-600 text-lg leading-none pb-0.5 transition"
                            x-show="receiveForm.items.length > 1">&times;</button>
                    </div>
                </template>
 
                <button type="button" @click="addReceiveRow()"
                    class="text-sm text-green-600 hover:text-green-800 font-medium mt-1 transition">
                    + Add another ingredient
                </button>
            </div>
 
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" @click="showReceiveModal = false"
                    class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50 transition">Cancel</button>
                <button type="button"
                    @click="
                        fetch('{{ route('inventory.receive') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(receiveForm)
                        })
                        .then(r => r.json())
                        .then(d => {
                            if (d.success) {
                                showReceiveModal = false;
                                showToast('Stock received successfully!');
                                setTimeout(() => window.location.reload(), 1000);
                            } else {
                                showToast(d.message ?? 'Something went wrong.', 'error');
                            }
                        })
                        .catch(err => { showToast('Server error.', 'error'); console.error(err); })
                    "
                    class="px-6 py-2 rounded-lg bg-green-500 hover:bg-green-600 text-white font-semibold text-sm transition">
                    Save Delivery
                </button>
            </div>
        </div>
    </div>
 
    {{-- Pull Out Modal --}}
    <div x-cloak x-show="showPullOutModal"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50 px-4 sm:px-6"
        @click.self="showPullOutModal = false">
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-lg p-6 max-h-[90vh] overflow-y-auto"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Pull Out Stock</h2>
                <button @click="showPullOutModal = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>
 
            <div class="grid grid-cols-2 gap-3 mb-4">
                <div>
                    <label class="text-sm font-medium block mb-1">Pulled By</label>
                    <input type="text" value="{{ session('admin_user')['username'] }}"
                        class="w-full px-3 py-2 bg-gray-200 rounded-lg text-sm text-gray-600 cursor-not-allowed" readonly>
                </div>
                <div>
                    <label class="text-sm font-medium block mb-1">Pull Out Type</label>
                    <select x-model="pullOutForm.pull_type"
                        class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                        <option>Preparation</option>
                        <option>Damage</option>
                        <option>Expired</option>
                        <option>Loss</option>
                    </select>
                </div>
            </div>
 
            <div class="mb-3">
                <label class="text-sm font-medium block mb-2">Ingredients to Pull</label>
                <template x-for="(item, index) in pullOutForm.items" :key="index">
                    <div class="flex gap-2 mb-2 items-end">
                        <div class="flex-1">
                            <select x-model="item.ingredient_id"
                                class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                                <option value="">-- Ingredient --</option>
                                @foreach ($ingredients as $ingredient)
                                    <option value="{{ $ingredient->ingredientID }}">
                                        {{ $ingredient->name }} ({{ $ingredient->currentStock }} {{ $ingredient->unit ?? '' }} left)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-24">
                            <input type="number" x-model="item.qty" min="0.01" step="0.01" placeholder="Qty"
                                class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                        </div>
                        <button type="button" @click="removePullRow(index)"
                            class="text-red-400 hover:text-red-600 text-lg pb-1 transition"
                            x-show="pullOutForm.items.length > 1">&times;</button>
                    </div>
                </template>
                <button type="button" @click="addPullRow()"
                    class="text-sm text-orange-600 hover:text-orange-800 font-medium mt-1 transition">
                    + Add another ingredient
                </button>
            </div>
 
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" @click="showPullOutModal = false"
                    class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50 transition">Cancel</button>
                <button type="button"
                    @click="
                        fetch('{{ route('inventory.pullout') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(pullOutForm)
                        })
                        .then(r => r.json())
                        .then(d => {
                            if (d.success) {
                                showPullOutModal = false;
                                showToast('Pull out recorded successfully!');
                                setTimeout(() => window.location.reload(), 1000);
                            } else {
                                showToast(d.message ?? 'Something went wrong.', 'error');
                            }
                        })
                        .catch(err => { showToast('Server error.', 'error'); console.error(err); })
                    "
                    class="px-6 py-2 rounded-lg bg-orange-500 hover:bg-orange-600 text-white font-semibold text-sm transition">
                    Confirm Pull Out
                </button>
            </div>
        </div>
    </div>
 
    {{-- Supplier Management --}}
    <div class="border border-pink-200 mt-8 rounded-xl p-4 md:p-6 overflow-x-auto">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-3">
            <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Suppliers</h2>
            <div class="flex items-center gap-3">
                <div class="relative">
                    <input type="text" x-model="supplierSearch" @input="supplierPage = 1"
                        placeholder="Search suppliers..."
                        class="border rounded-lg pl-9 pr-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-pink-400 bg-white w-44">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                </div>
                <button @click="showAddSupplierModal = true"
                    class="flex items-center justify-center gap-2 px-4 py-2 bg-pink-500 hover:bg-pink-600 text-white rounded-lg shadow text-xs sm:text-sm font-medium transition">
                    <i class="fas fa-plus"></i><span>Add Supplier</span>
                </button>
            </div>
        </div>
 
        <table class="min-w-full text-left text-xs sm:text-sm whitespace-nowrap">
            <thead class="border-b text-gray-600">
                <tr>
                    <th class="py-2 pr-4">#</th>
                    <th class="py-2 pr-4">Supplier Name</th>
                    <th class="py-2 pr-4">Phone</th>
                    <th class="py-2">Actions</th>
                </tr>
            </thead>
            <tbody id="supplier-tbody">
                @forelse ($suppliers as $supplier)
                <tr class="border-b hover:bg-pink-50 transition supplier-row"
                    data-supplier-name="{{ strtolower($supplier->supplierName) }}"
                    data-supplier-phone="{{ strtolower($supplier->phone) }}"
                    data-supplier-index="{{ $loop->index }}"
                >
                    <td class="py-3 pr-4 text-gray-400 supplier-num"></td>
                    <td class="py-3 pr-4 font-medium text-gray-800">{{ $supplier->supplierName }}</td>
                    <td class="py-3 pr-4 text-gray-500">{{ $supplier->phone }}</td>
                    <td class="py-3">
                        <button class="text-blue-500 hover:text-blue-700 text-xs font-medium transition"
                            @click="showEditSupplierModal = true; selectedSupplier = {{ $supplier->toJson() }}">
                            Edit
                        </button>
                    </td>
                </tr>
                @empty
                <tr id="supplier-empty-row">
                    <td colspan="4" class="py-10 text-center text-gray-400">
                        <div class="flex flex-col items-center gap-2">
                            <i class="fas fa-truck text-3xl opacity-50"></i>
                            <span>No suppliers yet</span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
 
        {{-- Supplier Pagination --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-4 text-xs text-gray-500" id="supplier-pagination">
            <span id="supplier-count-label"></span>
            <div class="flex items-center gap-1" id="supplier-page-buttons"></div>
        </div>
    </div>
 
    {{-- Add Supplier Modal --}}
    <div x-cloak x-show="showAddSupplierModal"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50 px-4 sm:px-6"
         @click.self="showAddSupplierModal = false">
        <div class="bg-white w-full max-w-xs sm:max-w-md rounded-2xl shadow-lg p-6"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Add Supplier</h2>
                <button @click="showAddSupplierModal = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>
            <form method="POST" action="{{ route('inventory.supplier.store') }}">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium block mb-1">Supplier Name</label>
                        <input type="text" name="supplierName"
                            class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-400" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium block mb-1">Phone</label>
                        <input type="text" name="phone"
                            class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-400" required>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" @click="showAddSupplierModal = false" class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50 transition">Cancel</button>
                    <button type="submit" class="px-6 py-2 rounded-lg bg-pink-500 hover:bg-pink-600 text-white font-semibold text-sm transition">Add</button>
                </div>
            </form>
        </div>
    </div>
 
    {{-- Edit Supplier Modal --}}
    <div x-cloak x-show="showEditSupplierModal"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50 px-4 sm:px-6"
         @click.self="showEditSupplierModal = false">
        <div class="bg-white w-full max-w-xs sm:max-w-md rounded-2xl shadow-lg p-6"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Edit Supplier</h2>
                <button @click="showEditSupplierModal = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>
            <form method="POST" :action="'/admin/inventory/supplier/' + selectedSupplier.supplierID">
                @csrf
                @method('PUT')
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium block mb-1">Supplier Name</label>
                        <input type="text" name="supplierName" x-model="selectedSupplier.supplierName"
                            class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-400" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium block mb-1">Phone</label>
                        <input type="text" name="phone" x-model="selectedSupplier.phone"
                            class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-400" required>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" @click="showEditSupplierModal = false" class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50 transition">Cancel</button>
                    <button type="submit" class="px-6 py-2 rounded-lg bg-pink-500 hover:bg-pink-600 text-white font-semibold text-sm transition">Save</button>
                </div>
            </form>
        </div>
    </div>
 
</div>
 
<script>
document.addEventListener('DOMContentLoaded', () => {

    const state = {
        ingredientPage: 1,
        txPage:         1,
        supplierPage:   1,
    };

    const btnClass = (active) =>
        `px-2.5 py-1 rounded-lg border text-xs font-medium transition ${
            active
            ? 'bg-pink-500 text-white border-pink-500'
            : 'bg-white text-gray-600 border-gray-200 hover:border-pink-300'
        }`;

    const getSearchQuery    = () => (document.querySelector('input[x-model="searchQuery"]')?.value     ?? '').toLowerCase();
    const getFilterStatus   = () =>  document.querySelector('select[x-model="filterStatus"]')?.value   ?? 'all';
    const getFilterExpiry   = () =>  document.getElementById('filterExpiry')?.value                    ?? 'all';
    const getFilterIng      = () => (document.querySelector('select[x-model="filterIngredient"]')?.value ?? 'all').toLowerCase();
    const getPerPage        = () => parseInt(document.querySelector('select[x-model="perPage"]')?.value ?? document.querySelector('select[x-model\\.number="perPage"]')?.value ?? 10);
    const getSupplierSearch = () => (document.querySelector('input[x-model="supplierSearch"]')?.value  ?? '').toLowerCase();

    // Today's date string for comparison e.g. "2026-05-20"
    const todayStr = new Date().toLocaleDateString('en-CA'); // gives "2026-05-20" in local time

    function expiryMatches(expiryStr, filter) {
        if (filter === 'all') return true;
        if (filter === 'none') return !expiryStr;
        if (!expiryStr) return false;

        // Compare as plain date strings (both are Y-m-d)
        const today   = new Date(todayStr + 'T00:00:00');
        const expiry  = new Date(expiryStr + 'T00:00:00');
        const diffDay = Math.round((expiry - today) / (1000 * 60 * 60 * 24));

        if (filter === 'today') return diffDay === 0;
        if (filter === '7')     return diffDay >= 0 && diffDay <= 7;
        if (filter === '30')    return diffDay >= 0 && diffDay <= 30;
        return true;
    }

    let historyTab = 'all';

    document.querySelectorAll('button[\\@click*="historyTab"]').forEach(btn => {
        const match = btn.getAttribute('@click')?.match(/historyTab\s*=\s*'(\w+)'/);
        if (match) {
            btn.addEventListener('click', () => {
                historyTab   = match[1];
                state.txPage = 1;
                renderTransactions();
            });
        }
    });

    document.querySelector('select[x-model="filterIngredient"]')?.addEventListener('change', () => {
        state.txPage = 1;
        renderTransactions();
    });

    function makePagination(container, page, totalPages, onPageChange) {
        container.innerHTML = '';
        if (totalPages <= 1) return;

        const makeBtn = (label, target, active, disabled) => {
            const btn       = document.createElement('button');
            btn.textContent = label;
            btn.className   = btnClass(active);
            btn.disabled    = disabled;
            if (disabled) btn.style.opacity = '0.4';
            btn.addEventListener('click', () => onPageChange(target));
            return btn;
        };

        container.appendChild(makeBtn('‹', page - 1, false, page === 1));

        for (let p = 1; p <= totalPages; p++) {
            if (totalPages > 7 && Math.abs(p - page) > 2 && p !== 1 && p !== totalPages) {
                if (p === page - 3 || p === page + 3) {
                    const dots = document.createElement('span');
                    dots.textContent = '…';
                    dots.style.padding = '0 4px';
                    container.appendChild(dots);
                }
                continue;
            }
            container.appendChild(makeBtn(p, p, p === page, false));
        }

        container.appendChild(makeBtn('›', page + 1, false, page === totalPages));
    }

    // ── Ingredients ──
    const ingredientRows     = [...document.querySelectorAll('.ingredient-row')];
    const ingredientEmptyRow = document.getElementById('ingredient-empty-row');
    const ingredientCountLbl = document.getElementById('ingredient-count-label');
    const ingredientPageBtns = document.getElementById('ingredient-page-buttons');

    function renderIngredients() {
        const sq = getSearchQuery();
        const fs = getFilterStatus();
        const fe = getFilterExpiry();
        const pp = getPerPage();

        const visible = ingredientRows.filter(row => {
            const name   = row.dataset.name   || '';
            const desc   = row.dataset.desc   || '';
            const status = row.dataset.status || '';
            const expiry = row.dataset.expiry || '';
            const matchSearch = !sq || name.includes(sq) || desc.includes(sq) || status.includes(sq);
            const matchStatus = fs === 'all' || fs === status;
            const matchExpiry = expiryMatches(expiry, fe);
            return matchSearch && matchStatus && matchExpiry;
        });

        const total      = visible.length;
        const totalPages = Math.max(1, Math.ceil(total / pp));
        state.ingredientPage = Math.min(state.ingredientPage, totalPages);
        const page  = state.ingredientPage;
        const start = (page - 1) * pp;
        const end   = start + pp;

        ingredientRows.forEach(row => { row.style.display = 'none'; });
        visible.slice(start, end).forEach(row => { row.style.display = ''; });

        if (ingredientEmptyRow) {
            ingredientEmptyRow.style.display = total === 0 ? '' : 'none';
        }

        const from = total === 0 ? 0 : start + 1;
        const to   = Math.min(end, total);
        if (ingredientCountLbl) {
            ingredientCountLbl.textContent = total === 0
                ? 'No ingredients match'
                : `Showing ${from}–${to} of ${total} ingredient(s)`;
        }

        makePagination(ingredientPageBtns, page, totalPages, (target) => {
            state.ingredientPage = target;
            renderIngredients();
        });
    }

    // ── Transactions ──
    const txRows     = [...document.querySelectorAll('.tx-row')];
    const txEmptyRow = document.getElementById('tx-empty-row');
    const txCountLbl = document.getElementById('tx-count-label');
    const txPageBtns = document.getElementById('tx-page-buttons');

    function renderTransactions() {
        const fi = getFilterIng();
        const pp = getPerPage();

        const visible = txRows.filter(row => {
            const type = row.dataset.txType       || '';
            const ing  = row.dataset.txIngredient || '';
            const matchTab = historyTab === 'all' || type === historyTab;
            const matchIng = fi === 'all' || ing === fi;
            return matchTab && matchIng;
        });

        const total      = visible.length;
        const totalPages = Math.max(1, Math.ceil(total / pp));
        state.txPage = Math.min(state.txPage, totalPages);
        const page  = state.txPage;
        const start = (page - 1) * pp;
        const end   = start + pp;

        txRows.forEach(row => { row.style.display = 'none'; });
        visible.slice(start, end).forEach(row => { row.style.display = ''; });

        if (txEmptyRow) {
            txEmptyRow.style.display = total === 0 ? '' : 'none';
        }

        const from = total === 0 ? 0 : start + 1;
        const to   = Math.min(end, total);
        if (txCountLbl) {
            txCountLbl.textContent = total === 0
                ? 'No transactions match'
                : `Showing ${from}–${to} of ${total} transaction(s)`;
        }

        makePagination(txPageBtns, page, totalPages, (target) => {
            state.txPage = target;
            renderTransactions();
        });
    }

    // ── Suppliers ──
    const supplierRows     = [...document.querySelectorAll('.supplier-row')];
    const supplierEmptyRow = document.getElementById('supplier-empty-row');
    const supplierCountLbl = document.getElementById('supplier-count-label');
    const supplierPageBtns = document.getElementById('supplier-page-buttons');

    function renderSuppliers() {
        const sq = getSupplierSearch();
        const pp = getPerPage();

        const visible = supplierRows.filter(row => {
            const name  = row.dataset.supplierName  || '';
            const phone = row.dataset.supplierPhone || '';
            return !sq || name.includes(sq) || phone.includes(sq);
        });

        const total      = visible.length;
        const totalPages = Math.max(1, Math.ceil(total / pp));
        state.supplierPage = Math.min(state.supplierPage, totalPages);
        const page  = state.supplierPage;
        const start = (page - 1) * pp;
        const end   = start + pp;

        supplierRows.forEach(row => { row.style.display = 'none'; });
        let num = start + 1;
        visible.slice(start, end).forEach(row => {
            row.style.display = '';
            const numCell = row.querySelector('.supplier-num');
            if (numCell) numCell.textContent = num++;
        });

        if (supplierEmptyRow) {
            supplierEmptyRow.style.display = total === 0 ? '' : 'none';
        }

        const from = total === 0 ? 0 : start + 1;
        const to   = Math.min(end, total);
        if (supplierCountLbl) {
            supplierCountLbl.textContent = total === 0
                ? 'No suppliers match'
                : `Showing ${from}–${to} of ${total} supplier(s)`;
        }

        makePagination(supplierPageBtns, page, totalPages, (target) => {
            state.supplierPage = target;
            renderSuppliers();
        });
    }

    // ── Event listeners ──
    document.querySelector('input[x-model="searchQuery"]')?.addEventListener('input', () => {
        state.ingredientPage = 1;
        renderIngredients();
    });
    document.querySelector('select[x-model="filterStatus"]')?.addEventListener('change', () => {
        state.ingredientPage = 1;
        renderIngredients();
    });
    // ✅ New expiry filter listener
    document.getElementById('filterExpiry')?.addEventListener('change', () => {
        state.ingredientPage = 1;
        renderIngredients();
    });
    document.querySelector('input[x-model="supplierSearch"]')?.addEventListener('input', () => {
        state.supplierPage = 1;
        renderSuppliers();
    });

    const perPageEl = document.querySelector('select[x-model="perPage"]')
               ?? document.querySelector('select[x-model\\.number="perPage"]');
    perPageEl?.addEventListener('change', () => {
        state.ingredientPage = 1;
        state.txPage         = 1;
        state.supplierPage   = 1;
        renderIngredients();
        renderTransactions();
        renderSuppliers();
    });

    // ── Initial render ──
    renderIngredients();
    renderTransactions();
    renderSuppliers();
});
</script>
@endsection