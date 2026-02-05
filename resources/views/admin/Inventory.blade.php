@extends('layouts.admin')

@section('title', 'Inventory')

@section('content')
<div 
    x-data="{
        showAddModal: false,
        showEditModal: false,
        selectedIngredient: {},
        searchQuery: '',
    }"
    class="px-3 sm:px-6 md:px-10 py-6 md:py-8"
>

    {{-- Page Header --}}
    <div>
        <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-800">Inventory Management</h1>
        <p class="text-gray-500 mt-1 text-xs sm:text-sm md:text-base">Monitor and manage product stock levels</p>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-3 gap-3 sm:gap-4 md:gap-5 mt-6 mb-8">
        
        {{-- Available Ingredients --}}
    <div class="bg-gradient-to-br from-pink-50 to-white border border-pink-100 shadow-sm hover:shadow-md transition rounded-xl p-4 md:p-5">
        <div class="flex items-start justify-between">
            <p class="text-gray-600 font-semibold text-xs sm:text-sm md:text-base">
                Available Ingredients
            </p>
            <div class="bg-white/80 rounded-full p-2 border border-pink-100">
                <i class="fas fa-boxes-stacked text-pink-500"></i>
            </div>
        </div>

        <h3 class="text-xl sm:text-2xl md:text-3xl font-bold mt-4">
            {{ $ingredients->where('currentStock', '>', 0)->count() }}
        </h3>

        <p class="text-gray-400 text-xs mt-1">
            Ingredients in stock
        </p>
    </div>

    {{-- Low Stock --}}
    <div class="bg-gradient-to-br from-pink-50 to-white border border-pink-100 shadow-sm hover:shadow-md transition rounded-xl p-4 md:p-5">
        <div class="flex items-start justify-between">
            <p class="text-gray-600 font-semibold text-xs sm:text-sm md:text-base">
                Low Stock
            </p>
            <div class="bg-white/80 rounded-full p-2 border border-pink-100">
                <i class="fas fa-triangle-exclamation text-yellow-500"></i>
            </div>
        </div>

        <h3 class="text-xl sm:text-2xl md:text-3xl font-bold mt-4 text-yellow-600">
            {{ $ingredients->filter(fn($i) => $i->currentStock > 0 && $i->currentStock <= $i->minStockLevel)->count() }}
        </h3>

        <p class="text-gray-400 text-xs mt-1">
            Needs restocking
        </p>
    </div>

    {{-- Out of Stock --}}
    <div class="bg-gradient-to-br from-pink-50 to-white border border-pink-100 shadow-sm hover:shadow-md transition rounded-xl p-4 md:p-5">
        <div class="flex items-start justify-between">
            <p class="text-gray-600 font-semibold text-xs sm:text-sm md:text-base">
                Out of Stock
            </p>
            <div class="bg-white/80 rounded-full p-2 border border-pink-100">
                <i class="fas fa-ban text-red-500"></i>
            </div>
        </div>

        <h3 class="text-xl sm:text-2xl md:text-3xl font-bold mt-4 text-red-500">
            {{ $ingredients->where('currentStock', 0)->count() }}
        </h3>

        <p class="text-gray-400 text-xs mt-1">
            No stock remaining
        </p>
    </div>

</div>  

    {{-- Search --}}
    <div class="w-full border rounded-xl border-pink-200 p-4 md:p-5 mb-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
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
    </div>

    {{-- Ingredients Table --}}
    <div class="border border-pink-200 mt-8 rounded-xl p-4 md:p-6 overflow-x-auto">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-3">
            <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Ingredients</h2>

            <button 
                @click="showAddModal = true"
                class="flex items-center justify-center space-x-2 px-4 py-2 bg-pink-500 text-white rounded-lg shadow text-xs sm:text-sm md:text-base"
            >
                <i class="fas fa-plus"></i>
                <span>Add Ingredient</span>
            </button>
        </div>

        <p class="text-gray-500 text-xs sm:text-sm mb-4">{{ count($ingredients) }} ingredient(s) found</p>

        <table class="min-w-full text-left text-xs sm:text-sm whitespace-nowrap">
            <thead class="border-b text-gray-600">
                <tr>
                    <th class="py-2">Ingredient</th>
                    <th class="py-2">Total In</th>
                    <th class="py-2">Total Used</th>
                    <th class="py-2">Available</th>
                    <th class="py-2">Reorder</th>
                    <th class="py-2">Status</th>
                    <th class="py-2">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($ingredients as $ingredient)
                <tr class="border-b hover:bg-pink-50 transition" 
                    x-show="[
                        '{{ strtolower($ingredient->name) }}',
                        '{{ strtolower($ingredient->description) }}',
                        '{{ strtolower($ingredient->currentStock) }}',
                        '{{ strtolower($ingredient->minStockLevel) }}',
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
                    <td class="py-2 ">{{ $ingredient->name }}</td>
                    <td class="py-2">{{ $ingredient->currentStock }}</td>
                    <td class="py-2">0</td>
                    <td class="py-2">{{ $ingredient->currentStock }}</td>
                    <td class="py-2">{{ $ingredient->minStockLevel }}</td>
                    <td class="py-2">
                        @if ($ingredient->currentStock <= 0)
                            <span class="text-red-500 font-semibold">Out of Stock</span>
                        @elseif ($ingredient->currentStock <= $ingredient->minStockLevel)
                            <span class="text-yellow-500 font-semibold">Low Stock</span>
                        @else
                            <span class="text-green-600 font-semibold">Available</span>
                        @endif
                    </td>
                    <td class="py-2">
                        <button class="text-blue-500" @click="showEditModal = true; selectedIngredient = {{ $ingredient->toJson() }}">Edit</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-10 text-center text-gray-400">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-box-open text-3xl opacity-50"></i>
                            No ingredient found
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Add Ingredient Modal --}}
    <div x-cloak x-show="showAddModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50 px-4 sm:px-6">
        <div @click.away="showAddModal = false" class="bg-white w-full max-w-xs sm:max-w-md md:max-w-lg rounded-2xl shadow-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Add Ingredient</h2>
                <button @click="showAddModal = false" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>

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
                        <label class="text-sm font-medium block mb-1">Stock</label>
                        <input type="number" name="current_stock" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm" min="0" required>
                    </div>
                    <div>
                        <label class="text-sm font-medium block mb-1">Min Stock</label>
                        <input type="number" name="min_stock" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm" min="0" required>
                    </div>
                </div>
                <div class="flex justify-end space-x-2 sm:space-x-3 mt-6">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-lg border text-sm">Cancel</button>
                    <button type="submit" class="px-6 py-2 rounded-lg bg-pink-500 text-white font-semibold text-sm">Add</button>
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

            <form method="POST" :action="'/admin/inventory/' + selectedIngredient.id">
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
                        <label class="text-sm font-medium block mb-1">Stock</label>
                        <input type="number" min="0" name="current_stock" x-model="selectedIngredient.currentStock" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium block mb-1">Min Stock</label>
                        <input type="number" min="0" name="min_stock" x-model="selectedIngredient.minStockLevel" class="w-full px-3 py-2 bg-gray-100 rounded-lg text-sm">
                    </div>
                </div>
                <div class="flex justify-end space-x-2 sm:space-x-3 mt-6">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-lg border text-sm">Cancel</button>
                    <button class="px-6 py-2 rounded-lg bg-pink-500 text-white font-semibold text-sm">Save</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
