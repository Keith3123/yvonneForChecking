@extends('layouts.admin')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Product Management</h1>
        <button id="openAddModal" class="bg-pink-600 text-white px-4 py-2 rounded">+ Add Product</button>
    </div>

    <!-- FILTERS -->
    <div class="flex gap-4 mb-4">
        <select id="filterCategory" class="border p-2 rounded border-pink-200 focus:ring-pink-500 focus:outline-none focus:ring-2">
            <option value="">All Categories</option>
        </select>

        <select id="filterStatus" class="border p-2 rounded border-pink-200 focus:ring-pink-500 focus:outline-none focus:ring-2">
            <option value="">All Status</option>
            <option value="Available">Available</option>
            <option value="Unavailable">Unavailable</option>
        </select>
    </div>

    <table class="w-full text-left border border-pink-200" id="productTableWrapper">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 border-b">Product Name</th>
                <th class="p-2 border-b">Category</th>
                <th class="p-2 border-b">Price</th>
                <th class="p-2 border-b">Status</th>
                <th class="p-2 border-b">Servings</th>
                <th class="p-2 border-b">Actions</th>
            </tr>
        </thead>
        <tbody id="productTable">
            @include('admin.products.product-table', ['products'=>$products])
        </tbody>
    </table>
</div>

{{-- Add Product Modal --}}
@include('admin.products.add-modal')

{{-- Edit Product Modal --}}
@include('admin.products.edit-modal')

<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Feedback Popup --}}
<div id="feedbackMessage" class="fixed inset-0 flex items-center justify-center hidden z-50 pointer-events-none">
    <div id="feedbackBox" class="px-8 py-6 rounded-xl shadow-xl text-white text-lg font-semibold pointer-events-auto"></div>
</div>

{{-- Delete Confirmation Modal --}}
<div id="confirmModal" class="fixed inset-0 hidden items-center justify-center z-50">
    <div class="absolute inset-0 bg-black opacity-30"></div>
    <div class="bg-white rounded-xl shadow-lg p-6 z-50 w-96 text-center">
        <h2 class="text-xl font-bold mb-2">Delete Product</h2>
        <p class="text-gray-600 mb-6">Are you sure you want to delete this product?</p>
        <div class="flex justify-center gap-4">
            <button id="confirmDelete" class="px-4 py-2 bg-red-600 text-white rounded">Delete</button>
            <button id="cancelDelete" class="px-4 py-2 bg-gray-300 rounded">Cancel</button>
        </div>
    </div>
</div>

<script>
// -------------------------
// TABLE FILTER LOGIC
// -------------------------
function populateCategoryFilter() {
    const table = document.getElementById('productTable');
    const categorySelect = document.getElementById('filterCategory');

    // Get unique categories from table
    const categories = [...new Set(Array.from(table.querySelectorAll('tr td:nth-child(2)'))
        .map(td => td.textContent.trim()))];

    // Add options
    categories.forEach(cat => {
        const option = document.createElement('option');
        option.value = cat;
        option.textContent = cat;
        categorySelect.appendChild(option);
    });
}

function filterTable() {
    const category = document.getElementById('filterCategory').value;
    const status = document.getElementById('filterStatus').value;
    const rows = document.querySelectorAll('#productTable tr');

    rows.forEach(row => {
        const tdCategory = row.cells[1].textContent.trim();
        const tdStatus = row.cells[3].textContent.trim();

        const matchCategory = !category || tdCategory === category;
        const matchStatus = !status || tdStatus === status;

        row.style.display = (matchCategory && matchStatus) ? '' : 'none';
    });
}

populateCategoryFilter();

document.getElementById('filterCategory').addEventListener('change', filterTable);
document.getElementById('filterStatus').addEventListener('change', filterTable);



let deleteID = null;

// -------------------------
// GLOBAL FUNCTION FOR DYNAMIC FIELDS
// -------------------------
function generateFields(category, container) {
    let html = "";

    if(category == 2){ // Food Package
        html = `
            <label>Package Includes</label>
            <textarea name="package_includes" class="w-full border p-2 mb-2 rounded border-pink-200"></textarea>

            <label>Serving</label>
            <input type="text" name="serving" class="w-full border p-2 mb-2 rounded border-pink-200">

            <label>Promo</label>
            <input type="text" name="promo" class="w-full border p-2 mb-2 rounded border-pink-200">

            <label>Price</label>
            <input type="number" name="price" class="w-full border p-2 mb-2 rounded border-pink-200">
        `;
    } else if(category == 3){ // Food Tray
        html = `
            <label>Serving</label>
            <input type="text" name="serving" class="w-full border p-2 mb-2 rounded border-pink-200">

            <label>Promo</label>
            <input type="text" name="promo" class="w-full border p-2 mb-2 rounded border-pink-200">

            <label>Size Prices</label>
            <div class="grid grid-cols-3 gap-2">
                <input type="number" name="size_s" placeholder="Small Price" class="border p-2 rounded border-pink-200">
                <input type="number" name="size_m" placeholder="Medium Price" class="border p-2 rounded border-pink-200">
                <input type="number" name="size_l" placeholder="Large Price" class="border p-2 rounded border-pink-200">
            </div>
        `;
    } else if(category == 4){ // Cake
        html = `
            <p class="text-sm text-gray-500 mb-2">Personalized message will be filled by the customer.</p>

            <label>Serving</label>
            <input type="text" name="serving" class="w-full border p-2 mb-2 rounded border-pink-200">

            <label>Promo</label>
            <input type="text" name="promo" class="w-full border p-2 mb-2 rounded border-pink-200">

            <label>Price</label>
            <input type="number" name="price" class="w-full border p-2 mb-2 rounded border-pink-200">
        `;
    } else if(category == 5){ // Cupcake
        html = `
            <label>Flavor</label>
            <input type="text" name="flavor" class="w-full border p-2 mb-2 rounded border-pink-200">

            <label>Icing Color</label>
            <input type="text" name="icing_color" class="w-full border p-2 mb-2 rounded border-pink-200">

            <label>Serving</label>
            <input type="text" name="serving" class="w-full border p-2 mb-2 rounded border-pink-200">

            <label>Promo</label>
            <input type="text" name="promo" class="w-full border p-2 mb-2 rounded border-pink-200">

            <label>Price</label>
            <input type="number" name="price" class="w-full border p-2 mb-2 rounded border-pink-200">
        `;
    }

    const containerDiv = document.getElementById(container);
    if(containerDiv) containerDiv.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', function() {
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    const addModal = document.getElementById('addModal');
    const openAdd = document.getElementById('openAddModal');
    const closeAdd = document.getElementById('closeAddModal');

    const editModal = document.getElementById('editModal');
    const closeEdit = document.getElementById('closeEditModal');

    const confirmModal = document.getElementById('confirmModal');
    const cancelDelete = document.getElementById('cancelDelete');
    const confirmDelete = document.getElementById('confirmDelete');

    const addForm = document.getElementById('addProductForm');
    const editForm = document.getElementById('editProductForm');

    // -------------------------
    // MODAL OPEN / CLOSE
    // -------------------------
    openAdd.addEventListener('click', () => addModal.classList.remove('hidden'));
    closeAdd.addEventListener('click', () => addModal.classList.add('hidden'));
    closeEdit.addEventListener('click', () => editModal.classList.add('hidden'));
    cancelDelete.addEventListener('click', () => confirmModal.classList.add('hidden'));

    // Setup category listeners for dynamic fields
    const addCategory = document.getElementById('addCategory');
    if(addCategory){
        addCategory.addEventListener('change', function() {
            generateFields(this.value, "dynamicFields");
        });
        generateFields(addCategory.value, "dynamicFields");
    }

    const editCategory = document.getElementById('editCategory');
    if(editCategory){
        editCategory.addEventListener('change', function() {
            generateFields(this.value, "editDynamicFields");
        });
    }

    // -------------------------
    // ADD PRODUCT
    // -------------------------
    addForm.addEventListener('submit', function(e){
        e.preventDefault();
        const submitBtn = addForm.querySelector('button[type="submit"]');
        submitBtn.disabled = true; submitBtn.innerText = "Creating...";
        const formData = new FormData(this);

        fetch("{{ route('admin.products.store') }}", {
            method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                document.getElementById('productTable').insertAdjacentHTML('afterbegin', data.rowHTML);
                showFeedback("Product successfully added!", "green");
                addForm.reset();
                addModal.classList.add('hidden');
                generateFields(addCategory.value, "dynamicFields");
            } else showFeedback("Failed to add product.", "red");
            submitBtn.disabled = false; submitBtn.innerText = "Create";
        })
        .catch(err => { console.error(err); submitBtn.disabled = false; submitBtn.innerText = "Create"; });
    });

    // -------------------------
    // EDIT PRODUCT
    // -------------------------
    editForm.addEventListener('submit', function(e){
        e.preventDefault();
        const formData = new FormData(this); formData.append('_method','PUT');
        const actionURL = this.dataset.action;

        fetch(actionURL, {
            method: 'POST', 
            headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                document.querySelector(`#productRow${data.productID}`).outerHTML = data.rowHTML;
                showFeedback("Product updated successfully!", "green");
                editModal.classList.add('hidden');
            } else showFeedback("Failed to update product.", "red");
        })
        .catch(err => console.error(err));
    });

    // -------------------------
    // DELETE PRODUCT
    // -------------------------
    confirmDelete.addEventListener('click', function() {
        fetch(`/admin/products/${deleteID}/delete`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                const row = document.getElementById('productRow' + deleteID);
                if(row) row.remove();
                showFeedback("Product deleted successfully!", "green");
            } else showFeedback("Failed to delete product.", "red");
            confirmModal.classList.add('hidden');
        })
        .catch(err => { console.error(err); showFeedback("Error deleting product.", "red"); confirmModal.classList.add('hidden'); });
    });
});

// -------------------------
// OPEN EDIT MODAL
// -------------------------
function openEditModal(id) {
    fetch(`/admin/products/modal/edit/${id}`)
    .then(res => res.json())
    .then(data => {
        const form = document.getElementById('editProductForm');
        document.getElementById('editName').value = data.product.name;
        document.getElementById('editCategory').value = data.product.productTypeID;
        document.getElementById('editDescription').value = data.product.description || '';
        document.getElementById('editStatus').value = data.product.isAvailable;

        generateFields(data.product.productTypeID, "editDynamicFields");
        const dynamicFields = document.getElementById('editDynamicFields');

        // Populate dynamic fields if values exist
        ['package_includes','serving','promo','price','size_s','size_m','size_l','flavor','icing_color'].forEach(field=>{
            const el = dynamicFields.querySelector(`[name="${field}"]`);
            if(el && data.product[field] !== undefined) el.value = data.product[field] ?? '';
        });

        form.dataset.action = `/admin/products/${id}`;
        document.getElementById('editModal').classList.remove('hidden');
    })
    .catch(err => console.error("Error opening edit modal:", err));
}

// -------------------------
// DELETE BUTTON FUNCTION
// -------------------------
function deleteProduct(id){
    deleteID = id;
    const modal = document.getElementById('confirmModal');
    modal.classList.remove('hidden'); modal.classList.add('flex');
}

// -------------------------
// FEEDBACK POPUP
// -------------------------
function showFeedback(message, color){
    const overlay = document.getElementById('feedbackMessage');
    const box = document.getElementById('feedbackBox');

    box.className = "px-8 py-6 rounded-xl shadow-xl text-white text-lg font-semibold";
    if(color === "green") box.classList.add("bg-green-600");
    if(color === "red") box.classList.add("bg-red-600");

    box.innerText = message;
    overlay.classList.remove("hidden");
    setTimeout(()=> overlay.classList.add("hidden"), 2000);
}
</script>
@endsection