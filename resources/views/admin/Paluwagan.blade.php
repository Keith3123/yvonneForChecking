@extends('layouts.admin')

@section('title', 'Paluwagan Management')

@section('content')
<div 
    x-data="{ 
        showDetails:false, 
        showAdd:false,
        showEdit:false,
        editPackageID: null,
        editPackageName: '',
        editPackageDescription: '',
        editPackageTotal: '',
        editPackageDuration: ''
    }" 
    class="px-4 sm:px-10 py-8">

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
    <div class="border rounded-xl border-pink-200 p-5 bg-white shadow-sm mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Paluwagan Packages</h2>
            <button class="px-4 py-2 border border-pink-300 rounded-lg text-sm sm:text-base hover:bg-pink-50 transition" id="refreshPackages">
                Refresh
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm sm:text-base min-w-max">
                <thead class="border-b text-gray-600 bg-gray-50">
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
                            <button @click="
                                if(confirm('Are you sure you want to delete this package?')){
                                    fetch(`/admin/paluwagan/package/{{ $package->packageID }}/delete`, {
                                        method:'POST',
                                        headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
                                        body: JSON.stringify({_method:'DELETE'})
                                    })
                                    .then(res=>res.json())
                                    .then(res=>{ if(res.success){ alert('Package deleted!'); location.reload(); } else alert(res.message||'Delete failed'); })
                                    .catch(err=>{ console.error(err); alert('Error deleting package'); });
                                }
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
                    if(res.success){ alert('Package updated!'); location.reload(); }
                    else alert(res.message||'Update failed');
                })
                .catch(err=>{ console.error(err); alert('Error updating package'); });
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
        <h2 class="text-lg sm:text-xl font-semibold text-gray-800 mb-4">Month Availability</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4">
            @foreach($months as $key => $month)
            <div class="flex items-center justify-between border rounded-lg p-3 hover:shadow-md transition cursor-pointer {{ $month['status'] == 'active' ? 'bg-pink-50 border-pink-300' : '' }}" 
                data-month="{{ $key }}">
                <span class="text-sm">{{ $month['label'] }}</span>
                <input type="checkbox" class="toggle-month" {{ $month['status']=='active'?'checked':'' }}>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Subscriptions Table --}}
    <div class="border rounded-xl border-pink-200 p-5 bg-white shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg sm:text-xl font-semibold text-gray-800">Customer Subscriptions</h2>
            <button class="px-4 py-2 border border-pink-300 rounded-lg hover:bg-pink-50 transition" id="refreshSubscriptions">Refresh</button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm sm:text-base min-w-max">
                <thead class="border-b text-gray-600 bg-gray-50">
                    <tr>
                        <th class="py-2 px-3">Entry ID</th>
                        <th class="py-2 px-3">Customer</th>
                        <th class="py-2 px-3">Package</th>
                        <th class="py-2 px-3">Progress</th>
                        <th class="py-2 px-3">Monthly Payment</th>
                        <th class="py-2 px-3">Paid</th>
                        <th class="py-2 px-3">Remaining</th>
                        <th class="py-2 px-3">Next Due</th>
                        <th class="py-2 px-3">Status</th>
                        <th class="py-2 px-3">Actions</th>
                    </tr>
                </thead>
                <tbody id="subscriptionsTable">
                    <tr>
                        <td colspan="10" class="py-10 text-center text-gray-400">No subscriptions found</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.querySelectorAll('.toggle-month').forEach(input => {
    input.addEventListener('change', function(){
        const month = this.closest('[data-month]').dataset.month;
        const status = this.checked ? 'active' : 'inactive';
        fetch('/admin/paluwagan/month/toggle', {
            method: 'POST',
            headers: {
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':'{{ csrf_token() }}'
            },
            body: JSON.stringify({month, status})
        })
        .then(res=>res.json())
        .then(res=>{ if(res.success) console.log('Month updated:', res); });
    });
});

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
            alert('Package created successfully!');
            location.reload();
        } else {
            alert(res.message || 'Failed');
        }
    })
    .catch(err => { console.error(err); alert('Error creating package'); });
});
</script>
@endsection