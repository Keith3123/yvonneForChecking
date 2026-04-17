@extends('layouts.admin')

@section('title', 'Paluwagan Management')

<div id="success-toast" class="hidden fixed top-10 left-1/2 -translate-x-1/2 z-[100] animate-bounce">
    <div class="bg-green-600 text-white px-8 py-4 rounded-full shadow-2xl flex items-center gap-3 border-2 border-white/20">
        <i class="fas fa-check-circle text-xl"></i>
        <span id="toast-message" class="font-bold whitespace-nowrap">Package updated successfully!</span>
    </div>
</div>

@section('content')
<div 
    x-data="{ 
        showDetails:false, 
        showAdd:false,
        showEdit:false,
        showDeleteModal: false, // <-- Add this
        editPackageID: null,
        editPackageName: '',
        editPackageDescription: '',
        editPackageTotal: '',
        editPackageDuration: '',
    
   deletePackage() {
    fetch(`/admin/paluwagan/package/${this.editPackageID}/delete`, {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json', 
            'X-CSRF-TOKEN': '{{ csrf_token() }}' 
        },
        body: JSON.stringify({ _method: 'DELETE' })
    })
    .then(async res => {
    const text = await res.text();

    try {
        return JSON.parse(text);
    } catch (e) {
        console.error('RAW RESPONSE (NOT JSON):', text);
        throw new Error('Server did not return JSON');
    }
})
    .then(res => {
        if (res.success) {
            // 1. Immediately hide the Red Modal
            this.showDeleteModal = false;

            // 2. Show the Green Toast in the middle
            const toast = document.getElementById('success-toast');
            document.getElementById('toast-message').innerText = 'Package deleted successfully!';
            toast.classList.remove('hidden');

            // 3. WAIT before reloading (Crucial for UX)
            setTimeout(() => {
                location.reload(); 
            }, 1500); 
        } else {
            showToast(res.message || 'Delete failed', 'error');
        }
    })
    .catch(err => console.error(err));
}

    }" 
    class="px-4 sm:px-10 py-8">
    
    <!-- Custom Delete Confirmation Modal -->
     
<div x-show="showDeleteModal" x-cloak 
     class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">
    <div @click.away="showDeleteModal = false" 
         class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl border border-gray-100 transform transition-all">
        
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-50 mb-4">
                <i class="fas fa-trash-alt text-red-500 text-xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900">Delete Package?</h3>
            <p class="text-gray-500 mt-2 text-sm leading-relaxed">
                Are you sure you want to remove <span class="font-semibold text-gray-800" x-text="editPackageName"></span>? This action cannot be undone.
            </p>
        </div>

        <div class="flex gap-3 mt-8">
            <button @click="showDeleteModal = false" 
                class="flex-1 px-4 py-3 bg-gray-100 text-gray-600 rounded-xl hover:bg-gray-200 font-semibold transition">
                Cancel
            </button>
            <button @click="deletePackage()" 
    class="flex-1 px-4 py-3 bg-red-500 text-white rounded-xl hover:bg-red-600 font-semibold shadow-lg shadow-red-200 transition">
    Yes, Delete
</button>
        </div>
    </div>
</div>

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Paluwagan Management</h1>
            <p class="text-gray-500 -mt-1 mb-2 text-sm sm:text-base">
                Monitor and manage paluwagan packages, schedules, and subscriptions
            </p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <button class="bg-pink-600 hover:bg-pink-700 text-white px-6 py-3 rounded-lg shadow w-full md:w-auto transition"
                    @click="showAdd = true">
                + Add Package
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-5 mb-8">
        @php
            $cards = [
                ['title'=>'Active Subscription','icon'=>'fas fa-file-contract','value'=>$summary['activeSubscriptions'] ?? 0,'desc'=>'Total active subscriptions'],
                ['title'=>'Collected Revenue','icon'=>'fas fa-circle-check','value'=>'₱'.number_format($summary['collectedRevenue'] ?? 0,2),'desc'=>'Total revenue collected'],
                ['title'=>'Expected Revenue','icon'=>'fas fa-calendar-days','value'=>'₱'.number_format($summary['expectedRevenue'] ?? 0,2),'desc'=>'Revenue expected for the month'],
                ['title'=>'Late Payments','icon'=>'fas fa-triangle-exclamation','value'=>$summary['latePayments'] ?? 0,'desc'=>'Late payments this month'],
            ];
        @endphp
        @foreach($cards as $card)
        <div class="bg-gradient-to-br from-pink-50 to-white border border-pink-100 shadow-sm hover:shadow-md transition rounded-xl p-4 md:p-5">
            <div class="flex items-start justify-between">
                <p class="text-gray-600 font-semibold text-sm sm:text-base">{{ $card['title'] }}</p>
                <div class="bg-white/80 rounded-full p-2 border border-pink-100">
                    <i class="{{ $card['icon'] }} text-pink-500"></i>
                </div>
            </div>
            <h3 class="text-xl sm:text-2xl font-bold mt-4">{{ $card['value'] }}</h3>
            <p class="text-gray-400 text-xs mt-1">{{ $card['desc'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Paluwagan Packages Section --}}
    <div class="border rounded-xl border-pink-200 p-5 bg-white shadow-sm mb-10">
    
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Paluwagan Packages</h2>
    </div>

    {{-- ✅ SCROLL CONTAINER --}}
    <div class="max-h-[500px] overflow-y-auto border rounded-lg">

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm sm:text-base min-w-max">
                
                {{-- ✅ STICKY HEADER (optional but clean UX) --}}
                <thead class="border-b text-gray-600 bg-gray-50 sticky top-0 z-10">
                    <tr>
                        <th class="py-2 px-3">ID</th>
                        <th class="py-2 px-3">Package Name</th>
                        <th class="py-2 px-3">What's Included</th>
                        <th class="py-2 px-3">Image</th>
                        <th class="py-2 px-3">Duration (Months)</th>
                        <th class="py-2 px-3">Monthly Payment</th>
                        <th class="py-2 px-3">Total Amount</th>
                        <th class="py-2 px-3">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($packages as $package)
                    <tr class="border-b hover:bg-pink-50 transition">
                        <td class="py-2 px-3">{{ $package->packageID }}</td>
                        <td class="py-2 px-3 font-medium">{{ $package->packageName }}</td>

                        <td class="py-2 px-3">
                            @php
                                $lines = preg_split('/\r\n|\r|\n|\. /', $package->description);
                            @endphp
                            <ul class="list-disc pl-5">
                                @foreach($lines as $item)
                                    @if(trim($item))
                                        <li>{{ trim($item) }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        </td>

                        <td class="py-2 px-3">
                            @if($package->image && file_exists(storage_path('app/public/products/' . $package->image)))
                            <img src="{{ asset('storage/products/' . $package->image) }}" 
                                alt="{{ $package->packageName }}" 
                                class="w-20 h-20 object-cover rounded">
                            @else
                            <span class="text-gray-400">No image</span>
                            @endif
                        </td>

                        <td class="py-2 px-3">{{ $package->durationMonths }}</td>
                        <td class="py-2 px-3">₱{{ number_format($package->monthlyPayment, 2) }}</td>
                        <td class="py-2 px-3">₱{{ number_format($package->totalAmount, 2) }}</td>

                        <td class="py-2 px-3 flex gap-2">
                            <button class="text-pink-600 hover:text-pink-800 text-sm sm:text-base"
                                @click="
                                    showEdit = true;
                                    editPackageID = {{ $package->packageID }};
                                    editPackageName = '{{ addslashes($package->packageName) }}';
                                    editPackageDescription = `{{ addslashes($package->description) }}`;
                                    editPackageTotal = '{{ $package->totalAmount }}';
                                    editPackageDuration = '{{ $package->durationMonths }}';
                                ">
                                Edit
                            </button>

                            <button class="text-red-500 hover:text-red-700 font-medium"
                                @click="
                                    editPackageID = {{ $package->packageID }}; 
                                    editPackageName = '{{ addslashes($package->packageName) }}';
                                    showDeleteModal = true;
                                ">
                                Delete
                            </button>
                        </td>
                    </tr>

                    @empty
                    <tr>
                        <td colspan="9" class="py-10 text-center text-gray-400">No packages found</td>
                    </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>
</div>

    {{-- Add Package Modal --}}
    <div x-show="showAdd" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div @click.away="showAdd = false" class="bg-white rounded-xl shadow-lg p-6 w-full max-w-lg">
            <h3 class="text-lg font-bold mb-4">Create Paluwagan Package</h3>
            <form id="addPackageForm" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">Package Name</label>
                    <input type="text" name="packageName" class="w-full border rounded px-3 py-2" required>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">What's Included (one line per item)</label>
                    <textarea name="description" rows="5" class="w-full border rounded px-3 py-2" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">Total Amount</label>
                    <input type="number" step="0.01" name="totalAmount" class="w-full border rounded px-3 py-2" required>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">Duration (Months)</label>
                    <input type="number" name="durationMonths" class="w-full border rounded px-3 py-2" required>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">Image</label>
                    <input type="file" name="image" accept="image/*" class="w-full mb-4 border p-2 rounded border-pink-200 focus:ring-pink-500 focus:outline-none focus:ring-2" required>
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="showAdd = false" class="px-4 py-2 border rounded hover:bg-gray-100">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-pink-600 text-white rounded hover:bg-pink-700">Create Paluwagan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Package Modal --}}
    <div x-show="showEdit" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
        <div @click.away="showEdit = false" class="bg-white rounded-xl shadow-lg p-6 w-full max-w-lg">
            <h3 class="text-lg font-bold mb-4">Edit Paluwagan Package</h3>
            
             <form x-ref="editForm" enctype="multipart/form-data" @submit.prevent="
            const formData = new FormData($refs.editForm);
            formData.append('_method','PUT');
            fetch(`/admin/paluwagan/package/${editPackageID}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(res=>res.json())
            .then(res=>{
                if(res.success){ 
                    const toast = document.getElementById('success-toast');
                    document.getElementById('toast-message').innerText = 'Package updated successfully!';
                    toast.classList.remove('hidden');
                    showEdit = false;
                    setTimeout(() => { location.reload(); }, 1500);
                } else {
showToast(res.message || 'Update failed', 'error');                }
            })
            .catch(err=>{ console.error(err); showToast('Error updating package', 'error'); });
        ">
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">Package Name</label>
                    <input type="text" name="packageName" x-model="editPackageName" class="w-full border rounded px-3 py-2" required>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">What's Included</label>
                    <textarea name="description" x-model="editPackageDescription" class="w-full border rounded px-3 py-2" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">Total Amount</label>
                    <input type="number" name="totalAmount" x-model="editPackageTotal" class="w-full border rounded px-3 py-2" required>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">Duration (Months)</label>
                    <input type="number" name="durationMonths" x-model="editPackageDuration" class="w-full border rounded px-3 py-2" required>
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-medium mb-1">Change Image (optional)</label>
                    <input type="file" name="image" accept="image/*" class="w-full mb-4 border p-2 rounded border-pink-200 focus:ring-pink-500 focus:outline-none focus:ring-2">
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="showEdit = false" class="px-4 py-2 border rounded hover:bg-gray-100">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-pink-600 text-white rounded hover:bg-pink-700">Update</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Month Management Section --}}
    <div class="border rounded-xl border-pink-200 p-5 bg-white shadow-sm mb-8">

        {{-- HEADER + SEARCH --}}
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-4 bg-white z-10">
            <h2 class="text-lg sm:text-xl font-semibold text-gray-800">
                Month Availability per Package
            </h2>

            <input type="text" id="searchPackage"
                placeholder="Search package..."
                class="border px-3 py-2 rounded-lg w-full md:w-64 text-sm">
        </div>

        {{-- 🔥 SCROLL CONTAINER --}}
        <div class="max-h-[500px] overflow-y-auto pr-2">

            @foreach($packages as $package)
            <div class="mb-4 border rounded-lg p-4 package-item">

                {{-- HEADER --}}
                <div class="flex justify-between items-center cursor-pointer package-header">
                    <h3 class="font-bold package-name">{{ $package->packageName }}</h3>

                    <div class="flex gap-2">
                        <button class="bulk-activate text-xs px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 transition"
                                data-package="{{ $package->packageID }}">
                            Activate All
                        </button>

                        <button class="bulk-deactivate text-xs px-3 py-1 bg-gray-500 text-white rounded hover:bg-gray-600 transition"
                                data-package="{{ $package->packageID }}">
                            Deactivate All
                        </button>
                    </div>
                </div>

                {{-- MONTH GRID (DEFAULT COLLAPSED) --}}
                <div class="grid grid-cols-6 gap-3 mt-3 month-grid hidden">
                    @foreach(range(1,12) as $month)
                        @php
                            $record = $package->monthAvailability->firstWhere('month', $month);
                            $isActive = $record ? $record->status === 'active' : false;
                        @endphp

                        <div 
                            data-month="{{ $month }}"
                            data-package="{{ $package->packageID }}"
                            class="month-box p-2 border rounded cursor-pointer transition 
                            {{ $isActive ? 'bg-pink-100 border-pink-400' : '' }}">
                            
                            <div class="flex justify-between items-center">
                                <span class="text-sm">
                                    {{ \Carbon\Carbon::create()->month($month)->format('F') }}
                                </span>

                                <input type="checkbox"
                                    class="toggle-month"
                                    {{ $isActive ? 'checked' : '' }}>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        @endforeach
    </div>
</div>

{{-- Subscriptions Table --}}
<div class="border rounded-xl border-pink-200 p-5 bg-white shadow-sm">

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg sm:text-xl font-semibold text-gray-800">
            Customer Subscriptions
        </h2>
    </div>

    <div class="flex flex-col md:flex-row gap-3 mb-4">

    <!-- 🔍 Search -->
    <input 
        type="text" 
        id="searchCustomer"
        placeholder="Search customer..."
        class="border px-3 py-2 rounded-lg w-full md:w-2/4 text-sm"
    >

    <!-- 📊 Status Filter -->
    <select id="statusFilter"
        class="border px-3 py-2 rounded-lg w-full md:w-1/4 text-sm">
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="completed">Completed</option>
        <option value="cancelled">Cancelled</option>
    </select>

    <!-- 📅 Next Due Filter -->
    <select id="dueFilter"
        class="border px-3 py-2 rounded-lg w-full md:w-1/4 text-sm">
        <option value="">All Due Dates</option>
        <option value="today">Due Today</option>
        <option value="week">Due This Week</option>
        <option value="overdue">Overdue</option>
    </select>

    </div>

    <div class="overflow-x-auto max-h-[500px] overflow-y-auto">
        <table class="w-full text-left text-sm sm:text-base min-w-max">
            <thead class="border-b text-gray-600 bg-gray-50 sticky top-0">
                <tr>
                    <th class="py-2 px-3">Entry ID</th>
                    <th class="py-2 px-3">Customer</th>
                    <th class="py-2 px-3">Package</th>
                    <th class="py-2 px-3 text-center">Progress</th>
                    <th class="py-2 px-3">Monthly Payment</th>
                    <th class="py-2 px-3">Paid</th>
                    <th class="py-2 px-3">Remaining</th>
                    <th class="py-2 px-3">Next Due</th>
                    <th class="py-2 px-3">Status</th>
                    <th class="py-2 px-3">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($subscriptions as $sub)
                <tr class="border-b hover:bg-pink-50 transition subscription-row" 
                    data-name="{{ strtolower($sub['customerName']) }}"
                    data-status="{{ $sub['status'] }}"
                    data-due="{{ $sub['nextDueDate'] }}">

                    <td class="py-2 px-3">{{ $sub['entryID'] }}</td>

                    <td class="py-2 px-3 font-medium">
                        {{ $sub['customerName'] }}
                    </td>

                    <td class="py-2 px-3">
                        {{ $sub['packageName'] }}
                    </td>

                    {{-- CIRCLE PROGRESS --}}
                    <td class="py-2 px-3 text-center">
                        @php
                            $percent = $sub['totalMonths'] > 0
                                ? round(($sub['monthsPaid'] / $sub['totalMonths']) * 100)
                                : 0;
                        @endphp

                        <div class="relative w-12 h-12 mx-auto">
                            <svg class="w-12 h-12 transform -rotate-90">
                                <circle cx="24" cy="24" r="20"
                                    stroke="#e5e7eb"
                                    stroke-width="4"
                                    fill="none"/>
                                <circle cx="24" cy="24" r="20"
                                    stroke="#ec4899"
                                    stroke-width="4"
                                    fill="none"
                                    stroke-dasharray="126"
                                    stroke-dashoffset="{{ 126 - (126 * $percent / 100) }}"
                                    stroke-linecap="round"/>
                            </svg>

                            <span class="absolute inset-0 flex items-center justify-center text-xs font-bold">
                                {{ $percent }}%
                            </span>
                        </div>
                    </td>

                    <td class="py-2 px-3">
                        ₱{{ number_format($sub['monthlyPayment'], 2) }}
                    </td>

                    <td class="py-2 px-3 text-green-600 font-semibold">
                        ₱{{ number_format($sub['totalPaid'], 2) }}
                    </td>

                    <td class="py-2 px-3 text-red-500 font-semibold">
                        ₱{{ number_format($sub['totalAmount'] - $sub['totalPaid'], 2) }}
                    </td>

                    <td class="py-2 px-3">
                        {{ $sub['nextDueDate'] ? \Carbon\Carbon::parse($sub['nextDueDate'])->format('M d, Y') : '-' }}
                    </td>

                    <td class="py-2 px-3">
                        <span class="px-2 py-1 text-xs rounded-full 
                            @switch($sub['status'])
                                @case('active') bg-green-100 text-green-800 @break
                                @case('completed') bg-blue-100 text-blue-800 @break
                                @case('cancelled') bg-red-100 text-red-800 @break
                            @endswitch">
                                {{ ucfirst($sub['status']) }}
                        </span>
                    </td>

                    {{-- ACTIONS --}}
                    <td class="py-2 px-3">
                        <div class="flex gap-2">

                            {{-- SHOW COMPLETE ONLY IF ACTIVE --}}
                            @if($sub['status'] === 'active')
                                <button onclick="markComplete('{{ $sub['entryID'] }}')"
                                    class="bg-green-500 text-white px-3 py-1 rounded">
                                    Complete
                                </button>
                            @endif

                            {{-- SHOW REPLACE ONLY IF CANCELLED --}}
                            @if($sub['status'] === 'cancelled')
                                <button onclick="reassignCustomer('{{ $sub['entryID'] }}')"
                                    class="bg-blue-500 text-white px-3 py-1 rounded">
                                    Replace Customer
                                </button>
                            @endif

                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="py-10 text-center text-gray-400">
                        No subscriptions found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
// =========================
// SINGLE TOGGLE (UNCHANGED LOGIC)
// =========================
function updateMonth(packageID, month, status, parent, checkbox) {
    fetch('/admin/paluwagan/month/toggle', {
        method: 'POST',
        headers: {
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body: JSON.stringify({packageID, month, status})
    })
    .then(res => res.json())
    .then(res => {
        if (!res.success) throw new Error();

        if (status === 'active') {
            parent.classList.add('bg-pink-100', 'border-pink-400');
        } else {
            parent.classList.remove('bg-pink-100', 'border-pink-400');
        }
    })
    .catch(() => {
        showToast('Update failed');
        checkbox.checked = !checkbox.checked;
    });
}

// =========================
// INDIVIDUAL CHECKBOX
// =========================
document.addEventListener('change', function(e){
    if(e.target.classList.contains('toggle-month')){
        const parent = e.target.closest('.month-box');
        const month = parent.dataset.month;
        const packageID = parent.dataset.package;
        const isChecked = e.target.checked;

        const status = isChecked ? 'active' : 'inactive';

        updateMonth(packageID, month, status, parent, e.target);
    }
});

// =========================
// BULK ACTION
// =========================
function bulkAction(packageID, activate) {
    document.querySelectorAll(`.month-box[data-package="${packageID}"]`)
    .forEach(box => {
        const checkbox = box.querySelector('.toggle-month');
        const month = box.dataset.month;

        checkbox.checked = activate;

        updateMonth(
            packageID,
            month,
            activate ? 'active' : 'inactive',
            box,
            checkbox
        );
    });
}

// 🔥 Use EVENT DELEGATION (fixes future bugs)
document.addEventListener('click', function(e){

    if(e.target.classList.contains('bulk-activate')){
        e.stopPropagation();
        bulkAction(e.target.dataset.package, true);
    }

    if(e.target.classList.contains('bulk-deactivate')){
        e.stopPropagation();
        bulkAction(e.target.dataset.package, false);
    }

    // =========================
    // COLLAPSE / EXPAND
    // =========================
    if(e.target.closest('.package-header')){
        const container = e.target.closest('.package-item');
        const grid = container.querySelector('.month-grid');
        grid.classList.toggle('hidden');
    }

});

// =========================
// SEARCH
// =========================
document.getElementById('searchPackage').addEventListener('input', function(){
    const val = this.value.toLowerCase();

    document.querySelectorAll('.package-item').forEach(item => {
        const name = item.querySelector('.package-name').textContent.toLowerCase();
        item.style.display = name.includes(val) ? '' : 'none';
    });
});

// =========================
// ADD PACKAGE (UNCHANGED)
// =========================
document.getElementById('addPackageForm').addEventListener('submit', function(e){
    e.preventDefault();
    const formData = new FormData(this);

    fetch('{{ url("admin/paluwagan/package/create") }}', {
        method: 'POST',
        body: formData
    })
    .then(res=>res.json())
    .then(res=>{
        if(res.success){
            const toast = document.getElementById('success-toast');
            document.getElementById('toast-message').innerText = "Package created successfully!";
            toast.classList.remove('hidden');

            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
showToast(res.message || 'Create failed', 'error');        }
    })
    .catch(err => { console.error(err); showToast('Error creating package', 'error'); });
});

function markComplete(entryID) {
    fetch(`/admin/paluwagan/entry/${entryID}/complete`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) throw new Error(data.message);

showToast(data.message || 'Marked as complete');       
 location.reload();
    })
    .catch(err => {
        console.error(err);
        showToast(err.message || 'Error completing subscription', 'error');
    });
}
function showToast(message, type = 'success') {
    const toast = document.getElementById('success-toast');
    const text = document.getElementById('toast-message');

    text.innerText = message;

    // Reset colors
    toast.firstElementChild.classList.remove(
        'bg-green-600',
        'bg-red-600',
        'bg-yellow-500'
    );

    // Apply type
    if (type === 'success') {
        toast.firstElementChild.classList.add('bg-green-600');
    } else if (type === 'error') {
        toast.firstElementChild.classList.add('bg-red-600');
    } else {
        toast.firstElementChild.classList.add('bg-yellow-500');
    }

    toast.classList.remove('hidden');

    setTimeout(() => {
        toast.classList.add('hidden');
    }, 2000);
}

function reassignCustomer(entryID) {
    const newCustomer = prompt("Enter new customer:");

    if (!newCustomer) return;

    fetch(`/admin/paluwagan/entry/${entryID}/reassign`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ customer: newCustomer })
    })
    .then(res => res.json())
    .then(res => {
        if (!res.success) throw new Error(res.message);

        showToast('Customer replaced successfully', 'success');
        location.reload();
    })
    .catch(err => {
        console.error(err);
        showToast(err.message || 'Error reassigning customer', 'error');
    });
}

    
    const searchInput = document.getElementById('searchCustomer');
    const statusFilter = document.getElementById('statusFilter');
    const dueFilter = document.getElementById('dueFilter');

    function filterSubscriptions() {
        const searchVal = searchInput.value.toLowerCase();
        const statusVal = statusFilter.value;
        const dueVal = dueFilter.value;

        const today = new Date();

        document.querySelectorAll('.subscription-row').forEach(row => {
            const name = row.dataset.name;
            const status = row.dataset.status;
            const dueDateStr = row.dataset.due;

            let show = true;

            // 🔍 NAME FILTER
            if (searchVal && !name.includes(searchVal)) {
                show = false;
            }

            // 📊 STATUS FILTER
            if (statusVal && status !== statusVal) {
                show = false;
            }

            // 📅 DUE FILTER
            if (dueVal && dueDateStr) {
                const dueDate = new Date(dueDateStr);

                if (dueVal === 'today') {
                    show = dueDate.toDateString() === today.toDateString();
                }

                if (dueVal === 'week') {
                    const diff = (dueDate - today) / (1000 * 60 * 60 * 24);
                    show = diff >= 0 && diff <= 7;
                }

                if (dueVal === 'overdue') {
                    show = dueDate < today;
                }
            }

            row.style.display = show ? '' : 'none';
        });
    }

    // 🔥 EVENT LISTENERS
    searchInput.addEventListener('input', filterSubscriptions);
    statusFilter.addEventListener('change', filterSubscriptions);
    dueFilter.addEventListener('change', filterSubscriptions);
</script>
@endsection