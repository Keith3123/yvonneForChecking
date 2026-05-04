@extends('layouts.admin')

@section('title', 'Inventory')

@section('content')
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

        receiveForm: {
            supplier: '',
            received_by: '',
            remarks: '',
            items: [{ ingredient_id: '', qty: '', unit_cost: '', expiry_date: '' }]
        },
        pullOutForm: {
            pull_type: 'Preparation',
            remarks: '',
            items: [{ ingredient_id: '', qty: '' }]
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
            this.receiveForm = { supplier: '', received_by: '', remarks: '', items: [{ ingredient_id: '', qty: '' }] };
        },
        resetPullForm() {
            this.pullOutForm = { pulled_by: '', pull_type: 'Preparation', remarks: '', items: [{ ingredient_id: '', qty: '' }] };
        },
    }"
    class="px-3 sm:px-6 md:px-10 py-6 md:py-8"
>

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-800">Inventory Management</h1>
            <p class="text-gray-500 mt-1 text-xs sm:text-sm md:text-base">Monitor and manage ingredient stock levels</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button
                @click="showReceiveModal = true; resetReceiveForm()"
                class="flex items-center gap-2 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg shadow text-xs sm:text-sm font-medium transition"
            >
                <i class="fas fa-arrow-down"></i>
                <span>Receive Stock</span>
            </button>
            <button
                @click="showPullOutModal = true; resetPullForm()"
                class="flex items-center gap-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg shadow text-xs sm:text-sm font-medium transition"
            >
                <i class="fas fa-arrow-up"></i>
                <span>Pull Out</span>
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 mb-8">

   <div class="bg-gradient-to-br from-pink-50 to-white border border-pink-100 shadow-sm hover:shadow-md transition rounded-xl p-4 md:p-5">
            <div class="flex items-start justify-between">
                <p class="text-gray-600 font-semibold text-xs sm:text-sm">Available Ingredients</p>
                <div class="bg-white/80 rounded-full p-2 border border-pink-100">
                    <i class="fas fa-boxes-stacked text-pink-500"></i>
                </div>
            </div>
            <h3 class="text-xl sm:text-2xl md:text-3xl font-bold mt-4">
                {{ $ingredients->where('currentStock', '>', 0)->count() }}
            </h3>
            <p class="text-gray-400 text-xs mt-1">Ingredients in stock</p>
            </div>

        <div class="bg-gradient-to-br from-pink-50 to-white border border-pink-100 shadow-sm hover:shadow-md transition rounded-xl p-4 md:p-5">
            <div class="flex items-start justify-between">
                <p class="text-gray-600 font-semibold text-xs sm:text-sm">Low Stock</p>
                <div class="bg-white/80 rounded-full p-2 border border-pink-100">
                    <i class="fas fa-triangle-exclamation text-yellow-500"></i>
                </div>
            </div>
            <h3 class="text-xl sm:text-2xl md:text-3xl font-bold mt-4 text-yellow-600">
                {{ $ingredients->filter(fn($i) => $i->currentStock > 0 && $i->currentStock <= $i->minStockLevel)->count() }}
            </h3>
            <p class="text-gray-400 text-xs mt-1">Needs restocking</p>
        </div>

         <div class="bg-gradient-to-br from-pink-50 to-white border border-pink-100 shadow-sm hover:shadow-md transition rounded-xl p-4 md:p-5">
            <div class="flex items-start justify-between">
                <p class="text-gray-600 font-semibold text-xs sm:text-sm">Out of Stock</p>
                <div class="bg-white/80 rounded-full p-2 border border-pink-100">
                    <i class="fas fa-ban text-red-500"></i>
                </div>
            </div>
            <h3 class="text-xl sm:text-2xl md:text-3xl font-bold mt-4 text-red-500">
                {{ $ingredients->where('currentStock', 0)->count() }}
            </h3>
            <p class="text-gray-400 text-xs mt-1">No stock remaining</p>
        </div>

    <div class="bg-gradient-to-br from-green-50 to-white border border-green-100 shadow-sm hover:shadow-md transition rounded-xl p-4 md:p-5">
            <div class="flex items-start justify-between">
                <p class="text-gray-600 font-semibold text-xs sm:text-sm">Total Stock In</p>
                <div class="bg-white/80 rounded-full p-2 border border-green-100">
                    <i class="fas fa-arrow-down text-green-500"></i>
                </div>
            </div>
             <h3 class="text-xl sm:text-2xl md:text-3xl font-bold mt-4 text-green-600">
                {{ $totalStockIn ?? 0 }}
            </h3>
            <p class="text-gray-400 text-xs mt-1">All deliveries</p>
        </div>

        <div class="bg-gradient-to-br from-orange-50 to-white border border-orange-100 shadow-sm hover:shadow-md transition rounded-xl p-4 md:p-5">
            <div class="flex items-start justify-between">
                <p class="text-gray-600 font-semibold text-xs sm:text-sm">Total Stock Out</p>
                <div class="bg-white/80 rounded-full p-2 border border-orange-100">
                    <i class="fas fa-arrow-up text-orange-500"></i>
                </div>
            </div>
            <h3 class="text-xl sm:text-2xl md:text-3xl font-bold mt-4 text-orange-500">
                {{ $totalStockOut ?? 0 }}
            </h3>
            <p class="text-gray-400 text-xs mt-1">All pull-outs</p>
        </div>

    </div>

    {{-- Search --}}
    <div class="w-full border rounded-xl border-pink-200 p-4 md:p-5 mb-4">
         <div class="relative w-full">
            <input 
                type="text"
                x-model="searchQuery"
                placeholder="Search ingredients..."
                class="w-full border rounded-lg pl-10 p-3 focus:outline-none focus:ring-2 focus:ring-pink-500 text-sm md:text-base"
            >
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-lg"></i>
        </div>
    </div>

    {{-- Ingredients Table --}}
    <div class="border border-pink-200 mt-8 rounded-xl p-4 md:p-6 overflow-x-auto">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-3">
            <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Ingredients</h2>
            <button 
                @click="showAddModal = true"
                  class="flex items-center justify-center gap-2 px-4 py-2 bg-pink-500 hover:bg-pink-600 text-white rounded-lg shadow text-xs sm:text-sm font-medium transition"
            >
                <i class="fas fa-plus"></i>
                <span>Add Ingredient</span>
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
                <tr class="border-b hover:bg-pink-50 transition" 
                    x-show="[
                        '{{ strtolower($ingredient->name) }}',
                        '{{ strtolower($ingredient->description) }}',
                        '{{ strtolower(
                            $ingredient->currentStock <= 0 
                                ? 'out of stock' 
                                : ($ingredient->currentStock <= $ingredient->minStockLevel 
                                    ? 'low stock' 
                                    : 'available'
                                )
                        ) }}'
                    ].some(field => field.includes(searchQuery.toLowerCase()))"
                >
                    <td class="py-3 pr-4 font-medium text-gray-800">{{ $ingredient->name }}</td>
                    <td class="py-3 pr-4 text-gray-500">{{ $ingredient->unit ?? '—' }}</td>
                    <td class="py-3 pr-4 text-green-600 font-medium">+{{ $ingredient->totalIn ?? $ingredient->currentStock }}</td>
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
                            <button 
                                class="text-blue-500 hover:text-blue-700 text-xs font-medium" 
                                @click="showEditModal = true; selectedIngredient = {{ $ingredient->toJson() }}"
                            >
                                Edit
                            </button>
                            <form method="POST" action="/admin/inventory/{{ $ingredient->ingredientID }}"
                                onsubmit="return confirm('Are you sure you want to delete {{ $ingredient->name }}?')"
                                class="m-0"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium">
                                    Delete
                                </button>
                            </form>
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

        {{-- Tabs --}}
        <div class="flex gap-1 border-b border-gray-200 mb-4">
            <button 
                @click="historyTab = 'all'"
                :class="historyTab === 'all' ? 'border-b-2 border-pink-500 text-pink-600 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm transition"
            >All</button>
            <button 
                @click="historyTab = 'in'"
                :class="historyTab === 'in' ? 'border-b-2 border-green-500 text-green-600 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm transition"
            >Stock In</button>
            <button 
                @click="historyTab = 'out'"
                :class="historyTab === 'out' ? 'border-b-2 border-orange-500 text-orange-600 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm transition"
            >Stock Out</button>
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
                <tr 
                    class="border-b hover:bg-pink-50 transition"
                    x-show="
                        historyTab === 'all' ||
                        (historyTab === 'in' && '{{ $tx['type'] }}' === 'in') ||
                        (historyTab === 'out' && '{{ $tx['type'] }}' === 'out')
                    "
                >
                    <td class="py-3 pr-4 text-gray-500">{{ $tx['date'] }}</td>
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
    <div x-cloak x-show="showAddModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50 px-4 sm:px-6">
        <div @click.away="showAddModal = false" class="bg-white w-full max-w-xs sm:max-w-md md:max-w-lg rounded-2xl shadow-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Add Ingredient</h2>
                <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
        <p class="text-xs text-gray-400 mb-4">This creates the ingredient master record only. Use <strong>Receive Stock</strong> to add quantities.</p>
            <form method="POST" action="{{ route('inventory.store') }}">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium block mb-1">Name</label>
                        <input type="text" name="name" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium block mb-1">Description</label>
                        <input type="text" name="description" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm" required>
                    </div>
                    <div>
                          <label class="text-sm font-medium block mb-1">Unit (e.g. kg, pcs, L)</label>
                        <input type="text" name="unit" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm" required>
                    </div>
                    <div>
                          <label class="text-sm font-medium block mb-1">Min Stock Level</label>
                        <input type="number" name="min_stock" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm" min="0" required>
                    </div>
                </div>
                 <div class="flex justify-end gap-2 mt-6">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-lg border text-sm">Cancel</button>
                     <button type="submit" class="px-6 py-2 rounded-lg bg-pink-500 hover:bg-pink-600 text-white font-semibold text-sm transition">Add</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Ingredient Modal --}}
    <div x-cloak x-show="showEditModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50 px-4 sm:px-6">
        <div @click.away="showEditModal = false" class="bg-white w-full max-w-xs sm:max-w-md md:max-w-lg rounded-2xl shadow-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Edit Ingredient</h2>
                <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
            <form method="POST" :action="'/admin/inventory/' + selectedIngredient.ingredientID">
                @csrf
                @method('PUT')
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium block mb-1">Name</label>
                        <input type="text" name="name" x-model="selectedIngredient.name" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium block mb-1">Description</label>
                        <input type="text" name="description" x-model="selectedIngredient.description" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm">
                    </div>
                    <div>
                         <label class="text-sm font-medium block mb-1">Unit</label>
                        <input type="text" name="unit" x-model="selectedIngredient.unit" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm">
                    </div>
                    <div>
                         <label class="text-sm font-medium block mb-1">Min Stock Level</label>
                        <input type="number" min="0" name="min_stock" x-model="selectedIngredient.minStockLevel" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm">
                    </div>
                </div>
                 <div class="flex justify-end gap-2 mt-6">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-lg border text-sm">Cancel</button>
                    <button 
                        type="submit" 
                        @click.stop
                        class="px-6 py-2 rounded-lg bg-pink-500 hover:bg-pink-600 text-white font-semibold text-sm transition"
                    >Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Receive Stock Modal --}}
    <div x-cloak x-show="showReceiveModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50 px-4 sm:px-6">
        <div @click.away="showReceiveModal = false" class="bg-white w-full max-w-lg rounded-2xl shadow-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Receive Stock</h2>
                <button @click="showReceiveModal = false" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-4">
                <div>
                    <label class="text-sm font-medium block mb-1">Supplier</label>
                    <select x-model="receiveForm.supplier" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm">
                        <option value="">-- Select --</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->supplierID }}">{{ $supplier->supplierName }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium block mb-1">Received By</label>
                    <input type="text" value="{{ session('admin_user')['username'] }}" class="w-full px-3 py-2 bg-gray-200 rounded-lg text-sm" readonly>
                </div>
            </div>

            <div class="mb-3">
                <label class="text-sm font-medium block mb-2">Ingredients Delivered</label>
                    <template x-for="(item, index) in receiveForm.items" :key="index">
                        <div class="grid grid-cols-2 gap-2 mb-3 p-3 bg-gray-50 rounded-lg">
                            <div class="col-span-2">
                                <label class="text-xs text-gray-500 mb-1 block">Ingredient</label>
                                <select x-model="item.ingredient_id" class="w-full px-3 py-2 bg-white border rounded-lg text-sm">
                                    <option value="">-- Ingredient --</option>
                                    @foreach ($ingredients as $ingredient)
                                        <option value="{{ $ingredient->ingredientID }}">{{ $ingredient->name }} ({{ $ingredient->unit ?? '' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500 mb-1 block">Qty</label>
                                <input type="number" x-model="item.qty" min="0.01" step="0.01" placeholder="0" class="w-full px-3 py-2 bg-white border rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="text-xs text-gray-500 mb-1 block">Unit Cost (₱)</label>
                                <input type="number" x-model="item.unit_cost" min="0" step="0.01" placeholder="0.00" class="w-full px-3 py-2 bg-white border rounded-lg text-sm">
                            </div>
                            <div class="col-span-2">
                                <label class="text-xs text-gray-500 mb-1 block">Expiry Date</label>
                                <input type="date" x-model="item.expiry_date" class="w-full px-3 py-2 bg-white border rounded-lg text-sm">
                            </div>
                            <div class="col-span-2 flex justify-end">
                                <button type="button" @click="removeReceiveRow(index)" class="text-red-400 hover:text-red-600 text-xs" x-show="receiveForm.items.length > 1">Remove</button>
                            </div>
                        </div>
                    </template>
                <button type="button" @click="addReceiveRow()" class="text-sm text-green-600 hover:text-green-800 font-medium mt-1">
                    + Add another ingredient
                </button>
            </div>

            <div>
                <label class="text-sm font-medium block mb-1">Remarks (optional)</label>
                <input type="text" x-model="receiveForm.remarks" placeholder="e.g. Supplier delivery" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm">
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <button type="button" @click="showReceiveModal = false" class="px-4 py-2 rounded-lg border text-sm">Cancel</button>
                <button 
                    type="button" 
                    @click="
                        fetch('{{ route('inventory.receive') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(receiveForm)
                        })
                        .then(r => r.json())
                        .then(d => {
                            if(d.success) { showReceiveModal = false; window.location.reload(); }
                            else { alert(d.message ?? 'Something went wrong.'); }
                        })
                        .catch(err => {
                            alert('Server error. Check console for details.');
                            console.error(err);
                        })
                    "
                    class="px-6 py-2 rounded-lg bg-green-500 hover:bg-green-600 text-white font-semibold text-sm transition"
                >
                    Save Delivery
                </button>
            </div>
        </div>
    </div>

    {{-- Pull Out Modal --}}
    <div x-cloak x-show="showPullOutModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50 px-4 sm:px-6">
        <div @click.away="showPullOutModal = false" class="bg-white w-full max-w-lg rounded-2xl shadow-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Pull Out Stock</h2>
                <button @click="showPullOutModal = false" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-4">
                <div>
                    <label class="text-sm font-medium block mb-1">Pulled By</label>
                    <input type="text" value="{{ session('admin_user')['username'] }}" class="w-full px-3 py-2 bg-gray-200 rounded-lg text-sm" readonly>
                </div>
                <div>
                    <label class="text-sm font-medium block mb-1">Pull Out Type</label>
                    <select x-model="pullOutForm.pull_type" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm">
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
                            <select x-model="item.ingredient_id" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm">
                                <option value="">-- Ingredient --</option>
                                @foreach ($ingredients as $ingredient)
                                    <option value="{{ $ingredient->ingredientID }}">{{ $ingredient->name }} ({{ $ingredient->currentStock }} {{ $ingredient->unit ?? '' }} left)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-24">
                            <input type="number" x-model="item.qty" min="0.01" step="0.01" placeholder="Qty" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm">
                        </div>
                        <button type="button" @click="removePullRow(index)" class="text-red-400 hover:text-red-600 text-lg pb-1" x-show="pullOutForm.items.length > 1">&times;</button>
                    </div>
                </template>
                <button type="button" @click="addPullRow()" class="text-sm text-orange-600 hover:text-orange-800 font-medium mt-1">
                    + Add another ingredient
                </button>
            </div>

            <div>
                <label class="text-sm font-medium block mb-1">Remarks (optional)</label>
                <input type="text" x-model="pullOutForm.remarks" placeholder='e.g. Used for Order #1001' class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm">
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <button type="button" @click="showPullOutModal = false" class="px-4 py-2 rounded-lg border text-sm">Cancel</button>
                <button
                    type="button"
                    @click="
                        fetch('{{ route('inventory.pullout') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify(pullOutForm)
                        }).then(r => r.json()).then(d => {
                            if(d.success) { showPullOutModal = false; window.location.reload(); }
                            else { alert(d.message ?? 'Something went wrong.'); }
                        })
                    "
                    class="px-6 py-2 rounded-lg bg-orange-500 hover:bg-orange-600 text-white font-semibold text-sm transition"
                >
                    Confirm Pull Out
                </button>
            </div>
        </div>
    </div>

    {{-- Supplier Management --}}
    <div class="border border-pink-200 mt-8 rounded-xl p-4 md:p-6 overflow-x-auto">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-3">
            <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Suppliers</h2>
            <button
                @click="showAddSupplierModal = true"
                class="flex items-center justify-center gap-2 px-4 py-2 bg-pink-500 hover:bg-pink-600 text-white rounded-lg shadow text-xs sm:text-sm font-medium transition"
            >
                <i class="fas fa-plus"></i>
                <span>Add Supplier</span>
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
                        <button
                            class="text-blue-500 hover:text-blue-700 text-xs font-medium"
                            @click="showEditSupplierModal = true; selectedSupplier = {{ $supplier->toJson() }}"
                        >
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
    <div x-cloak x-show="showAddSupplierModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50 px-4 sm:px-6">
        <div @click.away="showAddSupplierModal = false" class="bg-white w-full max-w-xs sm:max-w-md rounded-2xl shadow-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Add Supplier</h2>
                <button @click="showAddSupplierModal = false" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
            <form method="POST" action="{{ route('inventory.supplier.store') }}">
                @csrf
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium block mb-1">Supplier Name</label>
                        <input type="text" name="supplierName" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium block mb-1">Phone</label>
                        <input type="text" name="phone" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm" required>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" @click="showAddSupplierModal = false" class="px-4 py-2 rounded-lg border text-sm">Cancel</button>
                    <button type="submit" class="px-6 py-2 rounded-lg bg-pink-500 hover:bg-pink-600 text-white font-semibold text-sm transition">Add</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Supplier Modal --}}
    <div x-cloak x-show="showEditSupplierModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50 px-4 sm:px-6">
        <div @click.away="showEditSupplierModal = false" class="bg-white w-full max-w-xs sm:max-w-md rounded-2xl shadow-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Edit Supplier</h2>
                <button @click="showEditSupplierModal = false" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
            <form method="POST" :action="'/admin/inventory/supplier/' + selectedSupplier.supplierID">
                @csrf
                @method('PUT')
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium block mb-1">Supplier Name</label>
                        <input type="text" name="supplierName" x-model="selectedSupplier.supplierName" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium block mb-1">Phone</label>
                        <input type="text" name="phone" x-model="selectedSupplier.phone" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm" required>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" @click="showEditSupplierModal = false" class="px-4 py-2 rounded-lg border text-sm">Cancel</button>
                    <button type="submit" class="px-6 py-2 rounded-lg bg-pink-500 hover:bg-pink-600 text-white font-semibold text-sm transition">Save</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection