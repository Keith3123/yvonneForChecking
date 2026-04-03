@extends('layouts.admin')

@section('title', 'User Management')

@section('content')

<div 
        x-data="{ 
            showDetails:false, 
            showAdd:false,
            showConfirm:false,
            user: {},
            actionType: '', // 'deactivate' or 'reactivate'
            search: '',
            statusFilter: 'all',
            roleFilter: 'all'
        }" 
    class="px-4 md:px-10 py-6"
>

{{-- Header --}}
<div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3">
    <div>
        <h1 class="text-3xl md:text-4xl font-bold text-gray-800">Users Management</h1>
        <p class="text-gray-500 text-sm md:text-base">{{ $users->total() }} registered users</p>
    </div>

    <button 
        @click="showAdd = true"
        class="bg-pink-600 hover:bg-pink-700 text-white px-6 py-3 rounded-lg shadow w-full md:w-auto transition"
    >
        Add User
    </button>
</div>

{{-- Search + Filters --}}
<div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">

    <div class="relative md:col-span-2">
        <input 
            type="text"
            x-model="search"
            placeholder="Search users..."
            class="w-full border rounded-xl pl-10 pr-4 py-3 shadow-sm focus:ring-2 border-pink-300 focus:ring-pink-500 focus:outline-none"
        >
        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
    </div>

    <div>
        <select x-model="statusFilter" class="w-full border rounded-xl px-4 py-3 bg-white shadow-sm border-pink-300 focus:ring-2 focus:ring-pink-500">
            <option value="all">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
    </div>

    <div>
        <select x-model="roleFilter" class="w-full border rounded-xl px-4 py-3 bg-white shadow-sm border-pink-300 focus:ring-2 focus:ring-pink-500">
            <option value="all">All Roles</option>
            @foreach($roles as $role)
                <option value="{{ $role->roleID }}">{{ $role->roleName }}</option>
            @endforeach
        </select>
    </div>

</div>

{{-- TABLE --}}
<div class="mt-6 bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="font-bold text-gray-800">Users List</h2>
        <p class="text-gray-500 text-sm">Manage all registered users</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-sm">Username</th>
                    <th class="px-6 py-4 text-sm">User ID</th>
                    <th class="px-6 py-4 text-sm">Role Name</th>
                    <th class="px-6 py-4 text-sm">Status</th>
                    <th class="px-6 py-4 text-center text-sm">Actions</th>
                </tr>
            </thead>    
            <tbody>
                @forelse($users as $u)
                    <tr 
                        id="userRow{{ $u->userID }}"
                        class="border-b hover:bg-pink-50"
                        x-show="
                            (search === '' || 
                                '{{ strtolower($u->username) }}'.includes(search.toLowerCase()) ||
                                '{{ strtolower($u->userID) }}'.includes(search.toLowerCase())
                            )
                            &&
                            (statusFilter === 'all' || statusFilter == '{{ $u->status }}')
                            &&
                            (roleFilter === 'all' || roleFilter == '{{ $u->roleID }}')
                        "
                    >
                        <td class="px-6 py-4 font-semibold">{{ $u->username }}</td>
                        <td class="px-6 py-4">{{ $u->userID }}</td>
                        <td class="px-6 py-4">{{ $u->role->roleName ?? $u->roleID }}</td>

                        <td class="px-6 py-4">
                            <span class="status-badge px-3 py-1 rounded-full text-sm {{ $u->status == 1 ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                                {{ $u->status == 1 ? 'Active' : 'Inactive' }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-center">
                            <button 
                                @click="showDetails = true; user = {{ $u->toJson() }}"
                                class="px-4 py-2 border rounded-lg hover:bg-pink-100"
                            >
                                View Details
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-6 text-gray-500">
                            No users yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $users->links() }}
</div>

{{-- USER DETAILS MODAL --}}
<div 
    x-show="showDetails"
    x-transition
    class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-3 overflow-auto"
    x-cloak
>
    <div class="bg-white rounded-2xl w-full md:w-[600px] p-6 relative shadow-xl">
        <button 
            @click="showDetails = false"
            class="absolute top-3 right-3 text-gray-500 hover:text-black"
        >
            ✕
        </button>

        <h2 class="text-xl md:text-2xl font-semibold mb-1">User Details</h2>
        <p class="text-gray-500 mb-6 text-sm md:text-base">
            Complete information about <span x-text="user.username"></span>
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="font-semibold">User ID</p>
                <p x-text="user.userID" class="text-gray-700"></p>
            </div>

            <div>
                <p class="font-semibold">Username</p>
                <p x-text="user.username" class="text-gray-700"></p>
            </div>

            <div class="md:col-span-2">
                <p class="font-semibold">Role Name</p>
                <p x-text="user.role?.roleName ?? user.roleID" class="text-gray-700"></p>
            </div>

            <div class="md:col-span-2">
                <p class="font-semibold">Status</p>
                <span 
                    class="px-3 py-1 rounded-full text-sm"
                    :class="user.status == 1 ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600'"
                    x-text="user.status == 1 ? 'Active' : 'Inactive'">
                </span>
            </div>
        </div>
        
        {{-- CONFIRMATION MODAL --}}
        <div 
            x-show="showConfirm"
            x-transition
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-3 overflow-auto"
            x-cloak
        >
            <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-xl">

                <h2 class="text-xl font-bold mb-2">
                    <span x-text="actionType == 'deactivate' ? 'Deactivate User' : 'Reactivate User'"></span>
                </h2>

                <p class="text-gray-600 mb-6">
                    Are you sure you want to 
                    <strong x-text="actionType"></strong> 
                    this account?
                </p>

                <div class="flex justify-end gap-2">
                    <button 
                        @click="showConfirm = false"
                        class="px-4 py-2 bg-gray-300 rounded-lg hover:bg-gray-400"
                    >
                        Cancel
                    </button>

                    <button 
                        @click="
                            fetch('/admin/users/toggle-status/' + user.userID, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            })
                            .then(res => res.json())
                            .then(data => {
                                user.status = data.status;

                                const row = document.querySelector('#userRow' + user.userID);
                                if(row){
                                    const badge = row.querySelector('.status-badge');

                                    badge.textContent = data.status == 1 ? 'Active' : 'Inactive';
                                    badge.className = data.status == 1 
                                        ? 'status-badge px-3 py-1 rounded-full text-sm bg-green-100 text-green-700'
                                        : 'status-badge px-3 py-1 rounded-full text-sm bg-gray-200 text-gray-600';
                                }

                                showConfirm = false;
                            })
                            .catch(() => alert('Failed to toggle status.'));
                        "
                        :class="actionType == 'deactivate' 
                            ? 'bg-red-500 hover:bg-red-600 text-white' 
                            : 'bg-green-500 hover:bg-green-600 text-white'"
                        class="px-4 py-2 rounded-lg"
                    >
                        Confirm
                    </button>
                </div>

            </div>
        </div> 

        {{-- ACTIVATE / DEACTIVATE BUTTON --}}
        <div class="mt-4">
            <button 
                @click="
                    actionType = user.status == 1 ? 'deactivate' : 'reactivate';
                    showConfirm = true;
                "
                :class="user.status == 1 
                    ? 'bg-red-500 hover:bg-red-600 text-white' 
                    : 'bg-green-500 hover:bg-green-600 text-white'"
                class="px-4 py-2 rounded-lg transition w-full md:w-auto"
            >
                <span x-text="user.status == 1 ? 'Deactivate' : 'Reactivate'"></span>
            </button>
        </div>

    </div>
</div>

{{-- ADD USER MODAL --}}
<div x-show="showAdd" x-transition.opacity class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-3 overflow-auto" x-cloak>
    <div class="bg-white rounded-2xl w-full md:w-[700px] p-6 relative shadow-xl">

        <button @click="showAdd = false" class="absolute top-3 right-3 text-gray-500 hover:text-black rounded-full w-8 h-8 flex items-center justify-center">✕</button>

        <h2 class="text-xl font-semibold mb-4">Add User</h2>

        <form id="addUserForm" action="{{ route('admin.users.storeAdmin') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4" novalidate>
            @csrf

            <div>
                <label>Username</label>
                <input name="username" class="w-full border p-2 rounded" required>
            </div>

            <div class="col-span-2">
                <label>Role</label>
                <select name="roleID" class="w-full border p-2 rounded" required>
                    @foreach($roles as $role)
                        <option value="{{ $role->roleID }}">{{ $role->roleName }}</option>
                    @endforeach
                </select>
            </div>

            {{-- PASSWORD --}}
            <div class="relative">
                <label>Password</label>
                <input id="password" name="password" type="password" class="w-full border p-2 rounded pr-10" required>

                <button type="button" class="toggle-password absolute right-3 top-9 text-gray-500 hover:text-pink-600" tabindex="-1" aria-label="Toggle password visibility">
                    <i class="fas fa-eye-slash"></i>
                </button>
            </div>

            {{-- CONFIRM --}}
            <div class="relative">
                <label>Confirm Password</label>
                <input id="confirmPassword" name="password_confirmation" type="password" class="w-full border p-2 rounded pr-10" required>

                <p id="passwordError" class="text-red-500 text-sm hidden mt-1">Password does not match</p>
            </div>

            <div class="col-span-2 flex justify-end gap-2 mt-4">
                <button type="button" @click="showAdd=false" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">Cancel</button>

                <button id="addUserBtn" type="submit" class="bg-yellow-500 text-white px-4 py-2 rounded flex gap-2 items-center hover:bg-yellow-600 transition">
                    <span id="addUserText">Create Account</span>
                    <span id="addUserSpinner" class="hidden">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                </button>
            </div>
        </form>

    </div>
</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    // --- PASSWORD VALIDATION & TOGGLE ---
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmPassword');
    const errorMsg = document.getElementById('passwordError');

    const checkMatch = () => {
        if (!confirmPassword.value) {
            confirmPassword.classList.remove('border-red-500', 'border-green-500');
            errorMsg.classList.add('hidden');
            return;
        }

        if (password.value !== confirmPassword.value) {
            confirmPassword.classList.add('border-red-500');
            confirmPassword.classList.remove('border-green-500');
            errorMsg.classList.remove('hidden');
        } else {
            confirmPassword.classList.remove('border-red-500');
            confirmPassword.classList.add('border-green-500');
            errorMsg.classList.add('hidden');
        }
    };

    password?.addEventListener('input', checkMatch);
    confirmPassword?.addEventListener('input', checkMatch);

    // --- ADD USER FORM SUBMISSION ---
    const form = document.getElementById('addUserForm');
    const btn = document.getElementById('addUserBtn');
    const text = document.getElementById('addUserText');
    const spinner = document.getElementById('addUserSpinner');

    form?.addEventListener('submit', (e) => {
        if (password.value !== confirmPassword.value) {
            e.preventDefault();
            errorMsg.classList.remove('hidden');
            confirmPassword.focus();
            return;
        }

        btn.disabled = true;
        text.textContent = 'Creating...';
        spinner.classList.remove('hidden');
    });

    // --- TOGGLE PASSWORD VISIBILITY ---
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.previousElementSibling;
            const icon = btn.querySelector('i');
            if (!input || !icon) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
    });

});
</script>

@endsection