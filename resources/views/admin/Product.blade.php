@extends('layouts.admin')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Product Management</h1>
            <p class="text-gray-500 text-sm">Manage products, servings, and ingredients</p>
        </div>
        <button id="openAddModal" class="bg-pink-600 text-white px-5 py-2.5 rounded-lg hover:bg-pink-700 transition shadow">
            + Add Product
        </button>
    </div>

    {{-- FILTERS --}}
    <div class="flex gap-4 mb-4 flex-wrap">
        <input type="text" id="filterSearch" placeholder="Search product..."
            class="border p-2 rounded-lg border-pink-200 focus:ring-pink-500 focus:outline-none focus:ring-2 w-64">

        <select id="filterCategory" class="border p-2 rounded-lg border-pink-200 focus:ring-pink-500 focus:outline-none focus:ring-2">
            <option value="">All Categories</option>
            @foreach($types as $t)
                @if(strtolower($t->productType) !== 'paluwagan')
                    <option value="{{ $t->productTypeID }}">{{ $t->productType }}</option>
                @endif
            @endforeach
        </select>

        <select id="filterStatus" class="border p-2 rounded-lg border-pink-200 focus:ring-pink-500 focus:outline-none focus:ring-2">
            <option value="">All Status</option>
            <option value="Available">Available</option>
            <option value="Unavailable">Unavailable</option>
        </select>
    </div>

    {{-- PRODUCT TABLE --}}
    <div class="bg-white rounded-xl border border-pink-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
            <table class="w-full text-left text-sm min-w-max">
                <thead class="bg-gray-50 sticky top-0 z-10 border-b">
                    <tr>
                        <th class="p-3">Product</th>
                        <th class="p-3">Category</th>
                        <th class="p-3">Servings & Prices</th>
                        <th class="p-3">Ingredients</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Promo</th>
                        <th class="p-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="productTable">
                    @include('admin.products.product-table', ['products' => $products])
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODALS --}}
@include('admin.products.add-modal', ['types' => $types, 'ingredients' => $ingredients])
@include('admin.products.edit-modal', ['types' => $types, 'ingredients' => $ingredients])

<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Feedback Popup --}}
<div id="feedbackMessage" class="fixed inset-0 flex items-center justify-center hidden z-50 pointer-events-none">
    <div id="feedbackBox" class="px-8 py-6 rounded-xl shadow-xl text-white text-lg font-semibold pointer-events-auto"></div>
    {{-- ADD THIS -- toast element that showToast() looks for --}}
<div id="feedbackToast"
     class="fixed top-6 left-1/2 -translate-x-1/2 z-[200] hidden px-6 py-3 rounded-xl shadow-xl text-white font-semibold text-sm">
</div>
</div>


{{-- PASS INGREDIENTS TO JS --}}
<script>
    window.ALL_INGREDIENTS = @json($ingredients);
</script>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
let deleteID = null;

document.addEventListener('DOMContentLoaded', function () {

    // ==============================
    // MODAL OPEN/CLOSE
    // ==============================
    document.getElementById('openAddModal').addEventListener('click', () => {
        const modal = document.getElementById('addModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.getElementById('addProductForm').reset();
        document.getElementById('addServingsContainer').innerHTML = '';
        addServingRow('add');
    });

    document.getElementById('closeAddModal').addEventListener('click', () => {
        document.getElementById('addModal').classList.add('hidden');
        document.getElementById('addModal').classList.remove('flex');
    });

    document.getElementById('closeEditModal').addEventListener('click', () => {
        document.getElementById('editModal').classList.add('hidden');
        document.getElementById('editModal').classList.remove('flex');
    });

    // ==============================
    // ADD PRODUCT
    // ==============================
    document.getElementById('addProductForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = this.querySelector('[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Creating...';

        fetch("{{ route('admin.products.store') }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
            body: buildFormData('add'),
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.textContent = 'Create Product';
            if (data.success) {
                document.getElementById('productTable').insertAdjacentHTML('afterbegin', data.rowHTML);
                document.getElementById('addModal').classList.add('hidden');
                document.getElementById('addModal').classList.remove('flex');
                showToast('Product created!', 'green');
            } else {
                showToast(data.message || 'Failed', 'red');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.textContent = 'Create Product';
            showToast('Server error', 'red');
        });
    });

    // ==============================
    // EDIT PRODUCT
    // ==============================
    document.getElementById('editProductForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = this.querySelector('[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Saving...';

        const fd = buildFormData('edit');
        fd.append('_method', 'PUT');

        fetch(this.dataset.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
            body: fd,
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled = false;
            btn.textContent = 'Update Product';
            if (data.success) {
                const row = document.getElementById('productRow' + data.productID);
                if (row) row.outerHTML = data.rowHTML;
                document.getElementById('editModal').classList.add('hidden');
                document.getElementById('editModal').classList.remove('flex');
                showToast('Product updated!', 'green');
            } else {
                showToast(data.message || 'Update failed', 'red');
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.textContent = 'Update Product';
            showToast('Server error', 'red');
        });
    });

    // ==============================
    // FILTERS
    // ==============================
    document.getElementById('filterSearch').addEventListener('input', filterTable);
    document.getElementById('filterCategory').addEventListener('change', filterTable);
    document.getElementById('filterStatus').addEventListener('change', filterTable);

}); // end DOMContentLoaded

// ==============================
// OUTSIDE DOMContentLoaded — called from onclick attributes
// ==============================

function openEditModal(id) {
    fetch(`/admin/products/modal/edit/${id}`)
    .then(r => r.json())
    .then(data => {
        const p = data.product;
        document.getElementById('editName').value        = p.name;
        document.getElementById('editCategory').value    = p.productTypeID;
        document.getElementById('editDescription').value = p.description || '';
        document.getElementById('editStatus').value      = p.isAvailable;
        document.getElementById('editPromo').value       = p.promo || '';

        const form = document.getElementById('editProductForm');
        form.dataset.action = `/admin/products/${id}`;

        const container = document.getElementById('editServingsContainer');
        container.innerHTML = '';
        if (data.servings.length === 0) {
            addServingRow('edit');
        } else {
            data.servings.forEach(s => addServingRow('edit', s));
        }

        const modal = document.getElementById('editModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    })
    .catch(err => {
        console.error('Edit modal error:', err);
        showToast('Failed to load product', 'red');
    });
}

function toggleAvailability(id, currentStatus) {
    fetch(`/admin/products/${id}/toggle`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) { showToast('Failed to update status', 'red'); return; }

        const isNowAvailable = data.isAvailable;
        const row = document.getElementById('productRow' + id);
        if (!row) return;

        row.dataset.available = isNowAvailable ? '1' : '0';

        const badge = row.querySelector('.product-status');
        if (badge) {
            badge.textContent = isNowAvailable ? 'Available' : 'Unavailable';
            badge.className   = `product-status px-2.5 py-1 text-xs font-medium rounded-full
                ${isNowAvailable ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`;
        }

        const btn = document.getElementById('toggleBtn' + id);
        if (btn) {
            btn.title     = isNowAvailable ? 'Set Unavailable' : 'Set Available';
            btn.className = `p-1.5 rounded transition ${isNowAvailable
                ? 'text-green-500 hover:text-green-700 hover:bg-green-50'
                : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50'}`;
            btn.querySelector('i').className = `fas ${isNowAvailable ? 'fa-eye' : 'fa-eye-slash'} text-sm`;
            btn.setAttribute('onclick', `toggleAvailability(${id}, ${isNowAvailable ? 1 : 0})`);
        }

        showToast(
            isNowAvailable ? 'Product set to Available' : 'Product set to Unavailable',
            isNowAvailable ? 'green' : 'yellow'
        );
    })
    .catch(() => showToast('Server error', 'red'));
}

function addServingRow(mode, data = null) {
    const container = document.getElementById(mode === 'edit' ? 'editServingsContainer' : 'addServingsContainer');
    const div = document.createElement('div');
    div.className = 'serving-row border border-pink-200 rounded-lg p-3 mb-3 bg-pink-50/40';
    div.innerHTML = `
        <div class="flex justify-between items-center mb-2">
            <span class="text-sm font-semibold text-pink-700">Serving</span>
            <button type="button" onclick="this.closest('.serving-row').remove()"
                    class="text-red-400 hover:text-red-600 text-xs">✕ Remove</button>
        </div>
        <div class="grid grid-cols-2 gap-2 mb-2">
            <div>
                <label class="text-xs text-gray-500">Size Label</label>
                <input type="text" class="s-size w-full border p-1.5 rounded text-sm border-pink-200"
                       placeholder="e.g. Small, 6-inch" value="${data?.size ?? ''}">
            </div>
            <div>
                <label class="text-xs text-gray-500">Serving Amount</label>
                <input type="number" step="0.01" class="s-amount w-full border p-1.5 rounded text-sm border-pink-200"
                       placeholder="e.g. 6" value="${data?.servingSize ?? ''}">
            </div>
        </div>
        <div class="grid grid-cols-3 gap-2 mb-2">
            <div>
                <label class="text-xs text-gray-500">Unit</label>
                <select class="s-unit w-full border p-1.5 rounded text-sm border-pink-200">
                    ${['pcs','slices','cups','servings','box','tray'].map(u =>
                        `<option value="${u}" ${data?.unit === u ? 'selected' : ''}>${u}</option>`
                    ).join('')}
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500">Price (₱)</label>
                <input type="number" step="0.01" class="s-price w-full border p-1.5 rounded text-sm border-pink-200"
                       placeholder="0.00" value="${data?.price ?? ''}">
            </div>
            <div>
                <label class="text-xs text-gray-500">Qty Needed</label>
                <input type="number" step="0.01" class="s-qty-needed w-full border p-1.5 rounded text-sm border-pink-200"
                       placeholder="0" value="${data?.qtyNeeded ?? ''}">
            </div>
        </div>
        <div class="mt-2">
            <div class="flex justify-between items-center mb-1">
                <span class="text-xs font-semibold text-gray-600">Ingredients</span>
                <button type="button" onclick="addIngredientRow(this.closest('.serving-row'))"
                        class="text-xs text-pink-600 hover:text-pink-800 font-medium">+ Add Ingredient</button>
            </div>
            <div class="ingredients-container space-y-1.5"></div>
        </div>
    `;
    container.appendChild(div);
    (data?.ingredients ?? []).forEach(ing => addIngredientRow(div, ing));
}

function addIngredientRow(servingRow, data = null) {
    const container = servingRow.querySelector('.ingredients-container');
    const options   = window.ALL_INGREDIENTS.map(ing =>
        `<option value="${ing.ingredientID}" ${data?.ingredientID == ing.ingredientID ? 'selected' : ''}>${ing.name}</option>`
    ).join('');

    const div = document.createElement('div');
    div.className = 'ingredient-row flex gap-2 items-center';
    div.innerHTML = `
        <select class="ing-select flex-1 border p-1.5 rounded text-xs border-gray-200">
            <option value="">Select ingredient</option>
            ${options}
        </select>
        <input type="number" step="0.01" class="ing-qty w-20 border p-1.5 rounded text-xs border-gray-200"
               placeholder="Qty" value="${data?.qtyUsed ?? ''}">
        <button type="button" onclick="this.closest('.ingredient-row').remove()"
                class="text-red-400 hover:text-red-600 text-xs">✕</button>
    `;
    container.appendChild(div);
}

function buildFormData(mode) {
    const formID      = mode === 'edit' ? 'editProductForm'       : 'addProductForm';
    const containerID = mode === 'edit' ? 'editServingsContainer' : 'addServingsContainer';
    const form        = document.getElementById(formID);
    const formData    = new FormData(form);

    [...formData.keys()].filter(k => k.startsWith('servings[')).forEach(k => formData.delete(k));

    document.querySelectorAll(`#${containerID} .serving-row`).forEach((row, si) => {
        formData.append(`servings[${si}][size]`,        row.querySelector('.s-size').value);
        formData.append(`servings[${si}][servingSize]`, row.querySelector('.s-amount').value);
        formData.append(`servings[${si}][unit]`,        row.querySelector('.s-unit').value);
        formData.append(`servings[${si}][price]`,       row.querySelector('.s-price').value);
        formData.append(`servings[${si}][qtyNeeded]`,   row.querySelector('.s-qty-needed').value || 0);

        row.querySelectorAll('.ingredient-row').forEach((ingRow, ii) => {
            formData.append(`servings[${si}][ingredients][${ii}][ingredientID]`, ingRow.querySelector('.ing-select').value);
            formData.append(`servings[${si}][ingredients][${ii}][qtyUsed]`,      ingRow.querySelector('.ing-qty').value);
        });
    });

    return formData;
}

function filterTable() {
    const search   = document.getElementById('filterSearch').value.toLowerCase();
    const category = document.getElementById('filterCategory').value;
    const status   = document.getElementById('filterStatus').value;

    document.querySelectorAll('#productTable tr').forEach(row => {
        const name    = row.querySelector('.product-name')?.textContent.toLowerCase() || '';
        const catID   = row.dataset.categoryId || '';
        const isAvail = row.dataset.available  || '';

        // status filter: "Available" maps to isAvailable=1, "Unavailable" to 0
        const statusMatch = !status ||
            (status === 'Available'   && isAvail === '1') ||
            (status === 'Unavailable' && isAvail === '0');

        const ok = (!search   || name.includes(search))
                && (!category || catID === category)
                && statusMatch;

        row.style.display = ok ? '' : 'none';
    });
}

function showToast(msg, color) {
    const t = document.getElementById('feedbackToast');
    t.textContent = msg;
    t.className = `fixed top-6 left-1/2 -translate-x-1/2 z-[200] px-6 py-3 rounded-xl shadow-xl text-white font-semibold text-sm
        ${color === 'green' ? 'bg-green-600' : color === 'yellow' ? 'bg-yellow-500' : 'bg-red-600'}`;
    t.classList.remove('hidden');
    setTimeout(() => t.classList.add('hidden'), 3000);
}
</script>
@endsection