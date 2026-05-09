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
        showEditModal: false,
        showReceiveModal: false,
        showPullOutModal: false,
        showAddSupplierModal: false,
        showEditSupplierModal: false,
        selectedSupplier: {},
        selectedIngredient: {},
        searchQuery: '',
        historyTab: 'all',
        toast: { show: false, message: '', type: 'success' },

        receiveForm: {
            supplier: '',
            received_by: {{ $currentUserID ?? 'null' }},
            remarks: '',
            items: [{ ingredient_id: '', qty: '', unit_cost: '', expiry_date: '' }]
        },
        pullOutForm: {
            pull_type: 'Preparation',
            pulled_by: {{ $currentUserID ?? 'null' }},
            remarks: '',
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
                supplier: '',
                received_by: {{ $currentUserID ?? 'null' }},
                remarks: '',
                items: [{ ingredient_id: '', qty: '', unit_cost: '', expiry_date: '' }]
            };
        },
        resetPullForm() {
            this.pullOutForm = {
                pull_type: 'Preparation',
                pulled_by: {{ $currentUserID ?? 'null' }},
                remarks: '',
                items: [{ ingredient_id: '', qty: '' }]
            };
        },

        async saveIngredient() {
            const id = this.selectedIngredient.ingredientID;
            const res = await fetch(`/admin/inventory/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-HTTP-Method-Override': 'PUT'
                },
                body: JSON.stringify({
                    name:        this.selectedIngredient.name,
                    description: this.selectedIngredient.description,
                    unit:        this.selectedIngredient.unit,
                    min_stock:   this.selectedIngredient.minStockLevel,
                })
            });
            const d = await res.json();
            if (d.success) {
                this.showEditModal = false;
                this.showToast('Ingredient updated!');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                this.showToast(d.message ?? 'Something went wrong.', 'error');
            }
        },

        async deleteIngredient(id, name) {
            if (!confirm(`Delete ${name}? This cannot be undone.`)) return;
            const res = await fetch(`/admin/inventory/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-HTTP-Method-Override': 'DELETE'
                }
            });
            const d = await res.json();
            if (d.success) {
                this.showToast('Ingredient deleted.');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                this.showToast(d.message ?? 'Could not delete ingredient.', 'error');
            }
        }
    }"
    @keydown.escape.window="
        showAddModal = false; showEditModal = false;
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
                <i class="fas fa-arrow-down"></i><span>Receive Stock</span>
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

    {{-- Search --}}
    <div class="w-full border rounded-xl border-pink-200 p-4 md:p-5 mb-4">
        <div class="relative w-full">
            <input type="text" x-model="searchQuery" placeholder="Search ingredients..."
                class="w-full border rounded-lg pl-10 p-3 focus:outline-none focus:ring-2 focus:ring-pink-500 text-sm md:text-base">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-lg"></i>
        </div>
    </div>

    {{-- Ingredients Table --}}
    <div class="border border-pink-200 mt-8 rounded-xl p-4 md:p-6 overflow-x-auto">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-3">
            <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Ingredients</h2>
            <button @click="showAddModal = true"
                class="flex items-center justify-center gap-2 px-4 py-2 bg-pink-500 hover:bg-pink-600 text-white rounded-lg shadow text-xs sm:text-sm font-medium transition">
                <i class="fas fa-plus"></i><span>Add Ingredient</span>
            </button>
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
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($ingredients as $ingredient)
                @php
                    $statusKey = $ingredient->currentStock <= 0 ? 'out of stock'
                        : ($ingredient->currentStock <= $ingredient->minStockLevel ? 'low stock' : 'available');
                @endphp
                <tr class="border-b hover:bg-pink-50 transition"
                    x-show="
                        '{{ strtolower($ingredient->name) }}'.includes(searchQuery.toLowerCase()) ||
                        '{{ strtolower($ingredient->description ?? '') }}'.includes(searchQuery.toLowerCase()) ||
                        '{{ $statusKey }}'.includes(searchQuery.toLowerCase())
                    "
                >
                    <td class="py-3 pr-4 font-medium text-gray-800">{{ $ingredient->name }}</td>
                    <td class="py-3 pr-4 text-gray-500">{{ $ingredient->unit ?? '—' }}</td>
                    <td class="py-3 pr-4 text-green-600 font-medium">+{{ $ingredient->totalIn ?? 0 }}</td>
                    <td class="py-3 pr-4 text-orange-500 font-medium">-{{ $ingredient->totalOut ?? 0 }}</td>
                    <td class="py-3 pr-4 font-bold text-gray-800">{{ $ingredient->currentStock }}</td>
                    <td class="py-3 pr-4 text-gray-500">{{ $ingredient->minStockLevel }}</td>
                    <td class="py-3 pr-4">
                        @if ($ingredient->currentStock <= 0)
                            <span class="bg-red-100 text-red-600 text-xs font-semibold px-2 py-1 rounded-full">Out of Stock</span>
                        @elseif ($ingredient->currentStock <= $ingredient->minStockLevel)
                            <span class="bg-yellow-100 text-yellow-700 text-xs font-semibold px-2 py-1 rounded-full">Low Stock</span>
                        @else
                            <span class="bg-green-100 text-green-700 text-xs font-semibold px-2 py-1 rounded-full">Available</span>
                        @endif
                    </td>
                    <td class="py-3">
                        <div class="flex items-center gap-3">
                            <button class="text-blue-500 hover:text-blue-700 text-xs font-medium transition"
                                @click="showEditModal = true; selectedIngredient = {{ $ingredient->toJson() }}">
                                Edit
                            </button>
                            <button class="text-red-400 hover:text-red-600 text-xs font-medium transition"
                                @click="deleteIngredient({{ $ingredient->ingredientID }}, '{{ addslashes($ingredient->name) }}')">
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
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
    </div>

    {{-- Transaction History --}}
    <div class="border border-pink-200 mt-8 rounded-xl p-4 md:p-6 overflow-x-auto">
        <h2 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Transaction History</h2>
        <div class="flex gap-1 border-b border-gray-200 mb-4">
            <button @click="historyTab = 'all'"
                :class="historyTab === 'all' ? 'border-b-2 border-pink-500 text-pink-600 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm transition">All</button>
            <button @click="historyTab = 'in'"
                :class="historyTab === 'in' ? 'border-b-2 border-green-500 text-green-600 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm transition">Stock In</button>
            <button @click="historyTab = 'out'"
                :class="historyTab === 'out' ? 'border-b-2 border-orange-500 text-orange-600 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm transition">Stock Out</button>
        </div>
        <table class="min-w-full text-left text-xs sm:text-sm whitespace-nowrap">
            <thead class="border-b text-gray-600">
                <tr>
                    <th class="py-2 pr-4">Date</th>
                    <th class="py-2 pr-4">Type</th>
                    <th class="py-2 pr-4">Ingredient</th>
                    <th class="py-2 pr-4">Qty</th>
                    <th class="py-2 pr-4">By</th>
                    <th class="py-2">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($transactions ?? [] as $tx)
                <tr class="border-b hover:bg-pink-50 transition"
                    data-tx-type="{{ $tx['type'] }}"
                    x-show="historyTab === 'all' || $el.dataset.txType === historyTab"
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
                <tr>
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
                        <label class="text-sm font-medium block mb-1">Description</label>
                        <input type="text" name="description" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-400" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium block mb-1">Unit (e.g. kg, pcs, L)</label>
                        <input type="text" name="unit" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-400" required>
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

    {{-- Edit Ingredient Modal --}}
    <div x-cloak x-show="showEditModal"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50 px-4 sm:px-6"
         @click.self="showEditModal = false">
        <div class="bg-white w-full max-w-xs sm:max-w-md md:max-w-lg rounded-2xl shadow-lg p-6"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Edit Ingredient</h2>
                <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium block mb-1">Name</label>
                    <input type="text" x-model="selectedIngredient.name"
                        class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-400" required>
                </div>
                <div>
                    <label class="text-sm font-medium block mb-1">Description</label>
                    <input type="text" x-model="selectedIngredient.description"
                        class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-400" required>
                </div>
                <div>
                    <label class="text-sm font-medium block mb-1">Unit (e.g. kg, pcs, L)</label>
                    <input type="text" x-model="selectedIngredient.unit"
                        class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-400" required>
                </div>
                <div>
                    <label class="text-sm font-medium block mb-1">Min Stock Level</label>
                    <input type="number" min="0" step="0.01" x-model="selectedIngredient.minStockLevel"
                        class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-pink-400" required>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-lg border text-sm hover:bg-gray-50 transition">Cancel</button>
                <button type="button" @click="saveIngredient()"
                    class="px-6 py-2 rounded-lg bg-pink-500 hover:bg-pink-600 text-white font-semibold text-sm transition">Save Changes</button>
            </div>
        </div>
    </div>

    {{-- Receive Stock Modal --}}
    <div x-cloak x-show="showReceiveModal"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50 px-4 sm:px-6"
        @click.self="showReceiveModal = false">
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-lg p-6 max-h-[90vh] overflow-y-auto"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Receive Stock</h2>
                <button @click="showReceiveModal = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-4">
                <div>
                    <label class="text-sm font-medium block mb-1">Supplier</label>
                    <select x-model="receiveForm.supplier"
                        class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                        <option value="">-- Select --</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->supplierID }}">{{ $supplier->supplierName }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium block mb-1">Received By</label>
                    <input type="text" value="{{ session('admin_user')['username'] }}"
                        class="w-full px-3 py-2 bg-gray-200 rounded-lg text-sm text-gray-600 cursor-not-allowed" readonly>
                </div>
            </div>

            <div class="mb-3">
                <label class="text-sm font-medium block mb-2">Ingredients Delivered</label>
                <template x-for="(item, index) in receiveForm.items" :key="index">
                    <div class="grid grid-cols-2 gap-2 mb-3 p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="col-span-2">
                            <label class="text-xs text-gray-500 mb-1 block">Ingredient</label>
                            <select x-model="item.ingredient_id"
                                class="w-full px-3 py-2 bg-white border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                                <option value="">-- Ingredient --</option>
                                @foreach ($ingredients as $ingredient)
                                    <option value="{{ $ingredient->ingredientID }}">{{ $ingredient->name }} ({{ $ingredient->unit ?? '' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Qty</label>
                            <input type="number" x-model="item.qty" min="0.01" step="0.01" placeholder="0"
                                class="w-full px-3 py-2 bg-white border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Unit Cost (₱)</label>
                            <input type="number" x-model="item.unit_cost" min="0" step="0.01" placeholder="0.00"
                                class="w-full px-3 py-2 bg-white border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                        </div>
                        <div class="col-span-2">
                            <label class="text-xs text-gray-500 mb-1 block">Expiry Date</label>
                            <input type="date" x-model="item.expiry_date"
                                class="w-full px-3 py-2 bg-white border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                        </div>
                        <div class="col-span-2 flex justify-end">
                            <button type="button" @click="removeReceiveRow(index)"
                                class="text-red-400 hover:text-red-600 text-xs transition"
                                x-show="receiveForm.items.length > 1">
                                <i class="fas fa-trash-alt mr-1"></i>Remove
                            </button>
                        </div>
                    </div>
                </template>
                <button type="button" @click="addReceiveRow()"
                    class="text-sm text-green-600 hover:text-green-800 font-medium mt-1 transition">
                    + Add another ingredient
                </button>
            </div>

            <div>
                <label class="text-sm font-medium block mb-1">Remarks (optional)</label>
                <input type="text" x-model="receiveForm.remarks" placeholder="e.g. Supplier delivery"
                    class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
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
                        <option>Manual Adjustment</option>
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

            <div>
                <label class="text-sm font-medium block mb-1">Remarks (optional)</label>
                <input type="text" x-model="pullOutForm.remarks" placeholder="e.g. Used for Order #1001"
                    class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
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
            <button @click="showAddSupplierModal = true"
                class="flex items-center justify-center gap-2 px-4 py-2 bg-pink-500 hover:bg-pink-600 text-white rounded-lg shadow text-xs sm:text-sm font-medium transition">
                <i class="fas fa-plus"></i><span>Add Supplier</span>
            </button>
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
            <tbody>
                @forelse ($suppliers as $supplier)
                <tr class="border-b hover:bg-pink-50 transition">
                    <td class="py-3 pr-4 text-gray-400">{{ $loop->iteration }}</td>
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
                <tr>
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
@endsection