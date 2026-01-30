@extends('layouts.admin')

@section('content')
<div class="p-6">

    {{-- Page Title --}}
    <h1 class="text-3xl sm:text-4xl font-bold text-gray-800">Product Management</h1>
    <p class="text-gray-500 mt-1">View and manage the products displayed in the customer catalog</p>

    {{-- STATS CARDS --}}
    <div class="grid grid-cols-5 gap-4 mb-8 mt-6">
        <div class="bg-white shadow rounded-xl p-4 text-center">
            <h3 class="text-gray-500 text-sm">Total Products</h3>
            <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
        </div>

        <div class="bg-white shadow rounded-xl p-4 text-center">
            <h3 class="text-gray-500 text-sm">Cakes</h3>
            <p class="text-2xl font-bold text-pink-600">{{ $stats['cakes'] }}</p>
        </div>

        <div class="bg-white shadow rounded-xl p-4 text-center">
            <h3 class="text-gray-500 text-sm">Cupcakes</h3>
            <p class="text-2xl font-bold text-purple-600">{{ $stats['cupcakes'] }}</p>
        </div>

        <div class="bg-white shadow rounded-xl p-4 text-center">
            <h3 class="text-gray-500 text-sm">Food Items</h3>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['food_items'] }}</p>
        </div>

        <div class="bg-white shadow rounded-xl p-4 text-center">
            <h3 class="text-gray-500 text-sm">Paluwagan</h3>
            <p class="text-2xl font-bold text-green-600">{{ $stats['paluwagan'] }}</p>
        </div>
    </div>

    {{-- SEARCH + ADD --}}
    <div class="flex justify-between items-center mb-6">

        <div class="relative w-1/2">
            <input id="searchInput" type="text"
                class="w-full border rounded-lg pl-10 p-2"
                placeholder="Search for products...">
            <span class="absolute left-3 top-2.5 text-gray-400 text-xl">🔍</span>
        </div>

        <button id="openAddModal"
            class="bg-pink-600 hover:bg-pink-700 text-white px-6 py-3 rounded-lg shadow">
            + Add Product
        </button>
    </div>

    {{-- FILTER BUTTONS --}}
    <div class="flex gap-4 mb-5">
        <button class="categoryBtn active px-5 py-2 rounded-full bg-pink-600 text-white"
                data-category="">
            All
        </button>

        @foreach ($types as $t)
            <button class="categoryBtn px-5 py-2 rounded-full border"
                    data-category="{{ $t->productTypeID }}">
                {{ $t->productType }}
            </button>
        @endforeach
    </div>

    {{-- PRODUCT TABLE --}}
    <div class="bg-white p-6 rounded-xl shadow overflow-x-auto">
        <table class="w-full text-left">
            <thead>
                <tr class="border-b">
                    <th class="p-3">Name</th>
                    <th class="p-3">Category</th>
                    <th class="p-3">Price</th>
                    <th class="p-3">Stock</th>
                    <th class="p-3">Options</th>
                    <th class="p-3 text-center">Actions</th>
                </tr>
            </thead>

            <tbody id="productTable">
                @include('admin.products.product-table')
            </tbody>
        </table>
    </div>

</div>

{{-- ADD MODAL --}}
<div id="addModal" class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center">
    <div class="bg-white rounded-xl p-6 w-2/3">
        <h2 class="text-xl font-bold mb-4">Add Product</h2>

        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf

            <label class="font-semibold">Product Name</label>
            <input type="text" name="name" class="w-full border p-3 rounded-lg mb-4" required>

            <label class="font-semibold">Product Category</label>
            <select name="productTypeID" class="w-full border p-3 rounded-lg mb-4" required>
                @foreach ($types as $t)
                    <option value="{{ $t->productTypeID }}">{{ $t->productType }}</option>
                @endforeach
            </select>

            <label class="font-semibold">Description</label>
            <textarea name="description" rows="4" class="w-full border p-3 rounded-lg mb-4"></textarea>

            <label class="font-semibold">Price (₱)</label>
            <input type="number" name="price" min="1" step="0.01" class="w-full border p-3 rounded-lg mb-4" required>

            <label class="font-semibold">Stock</label>
            <input type="number" name="stock" min="0" class="w-full border p-3 rounded-lg mb-4" required>

            <label class="font-semibold">Image</label>
            <input type="file" name="imageURL" accept="image/*" class="w-full border p-3 rounded-lg mb-4" required>

            <label class="font-semibold">Status</label>
            <select name="isAvailable" class="w-full border p-3 rounded-lg mb-6">
                <option value="1">Available</option>
                <option value="0">Unavailable</option>
            </select>

            <div class="flex justify-between">
                <button type="button" id="closeAddModal"
                    class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded-lg">Cancel</button>

                <button type="submit"
                    class="bg-pink-600 hover:bg-pink-700 text-white px-6 py-2 rounded-lg">Create</button>
            </div>
        </form>
    </div>
</div>

{{-- EDIT MODAL --}}
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-40 hidden items-center justify-center">
    <div class="bg-white rounded-xl p-6 w-2/3">
        <h2 class="text-xl font-bold mb-4">Edit Product</h2>

        <form id="editForm" method="POST" enctype="multipart/form-data">
            @csrf

            <label class="font-semibold">Product Name</label>
            <input id="editName" type="text" name="name" class="w-full border p-3 rounded-lg mb-4">

            <label class="font-semibold">Product Category</label>
            <select id="editCategory" name="productTypeID" class="w-full border p-3 rounded-lg mb-4">
                @foreach ($types as $t)
                    <option value="{{ $t->productTypeID }}">{{ $t->productType }}</option>
                @endforeach
            </select>

            <label class="font-semibold">Description</label>
            <textarea id="editDescription" name="description" rows="4" class="w-full border p-3 rounded-lg mb-4"></textarea>

            <label class="font-semibold">Price (₱)</label>
            <input id="editPrice" type="number" name="price" min="1" step="0.01" class="w-full border p-3 rounded-lg mb-4">

            <label class="font-semibold">Stock</label>
            <input id="editStock" type="number" name="stock" min="0" class="w-full border p-3 rounded-lg mb-4">

            <label class="font-semibold">Image</label>
            <input type="file" name="imageURL" accept="image/*" class="w-full border p-3 rounded-lg mb-4">

            <label class="font-semibold">Status</label>
            <select id="editStatus" name="isAvailable" class="w-full border p-3 rounded-lg mb-6">
                <option value="1">Available</option>
                <option value="0">Unavailable</option>
            </select>

            <div class="flex justify-between">
                <button type="button" id="closeEditModal"
                    class="bg-gray-300 hover:bg-gray-400 px-4 py-2 rounded-lg">Cancel</button>

                <button type="submit"
                    class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

    const searchInput = document.getElementById('searchInput');
    const buttons     = document.querySelectorAll('.categoryBtn');
    const tableBody   = document.getElementById('productTable');

    function fetchProducts() {
        const search = searchInput.value;
        const activeBtn = document.querySelector('.categoryBtn.active');
        const category = activeBtn ? activeBtn.dataset.category : '';

        fetch(`/admin/products/ajax/fetch?search=${search}&category=${category}`)
            .then(res => res.json())
            .then(data => tableBody.innerHTML = data.html);
    }

    searchInput.addEventListener('input', fetchProducts);

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            buttons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            fetchProducts();
        });
    });

    // ADD MODAL
    const addModal = document.getElementById('addModal');
    const openAdd = document.getElementById('openAddModal');
    const closeAdd = document.getElementById('closeAddModal');

    openAdd.addEventListener('click', () => addModal.classList.remove('hidden'));
    closeAdd.addEventListener('click', () => addModal.classList.add('hidden'));

    // EDIT MODAL
    const editModal = document.getElementById('editModal');
    const closeEdit = document.getElementById('closeEditModal');

    closeEdit.addEventListener('click', () => editModal.classList.add('hidden'));
});

function openEditModal(id) {
    fetch(`/admin/products/modal/edit/${id}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('editName').value = data.product.name;
            document.getElementById('editCategory').value = data.product.productTypeID;
            document.getElementById('editDescription').value = data.product.description;
            document.getElementById('editPrice').value = data.product.price;
            document.getElementById('editStock').value = data.product.stock;
            document.getElementById('editStatus').value = data.product.isAvailable;

            const form = document.getElementById('editForm');
            form.action = `/admin/products/${id}/update`;

            document.getElementById('editModal').classList.remove('hidden');
        });
}
</script>

@endsection
