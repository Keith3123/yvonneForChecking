@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
<div 
    x-data="{ 
        showDetails:false, 
        showAdd:false,
        user: {}  
    }" 
    class="px-4 md:px-10 py-6"
>

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3">
        <div>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-800">User Management</h1>
            <p class="text-gray-500 text-sm md:text-base">{{ $users->total() }} registered users</p>
        </div>

        <button 
            @click="showAdd = true"
            class="bg-pink-600 hover:bg-pink-700 text-white px-6 py-3 rounded-lg shadow w-full md:w-auto transition"
        >
            Add Admin
        </button>
    </div>

    {{-- Search + Filters --}}
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="relative md:col-span-2">
            <input 
        type="text"
        placeholder="Search users by name, email, or username..."
        class="w-full border rounded-lg pl-10 p-3 focus:outline-none focus:ring-2 focus:ring-pink-500"
    >
    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-lg"></i>

        </div>

        <div class="flex gap-2">
            <button class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 hover:bg-pink-100 transition">
                All
            </button>
            <button class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 hover:bg-pink-100 transition">
                Active
            </button>
            <button class="w-full bg-white border border-gray-200 rounded-xl px-4 py-3 hover:bg-pink-100 transition">
                Inactive
            </button>
        </div>
    </div>

    {{-- User Table --}}
    <div class="mt-6 bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-bold text-gray-800">Users List</h2>
            <p class="text-gray-500 text-sm">Manage all registered users</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-gray-500 text-sm font-medium">Username</th>
                        <th class="px-6 py-4 text-gray-500 text-sm font-medium">User ID</th>
                        <th class="px-6 py-4 text-gray-500 text-sm font-medium">Role ID</th>
                        <th class="px-6 py-4 text-gray-500 text-sm font-medium">Status</th>
                        <th class="px-6 py-4 text-gray-500 text-sm font-medium text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                        <tr class="border-b hover:bg-pink-50 transition">
                            <td class="px-6 py-4 font-semibold text-gray-800">{{ $u->username }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $u->userID }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ $u->roleID }}</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-sm"
                                      :class="{
                                          'bg-green-100 text-green-700': {{ $u->status }} == 1,
                                          'bg-gray-200 text-gray-600': {{ $u->status }} == 0
                                      }"
                                >
                                    {{ $u->status == 1 ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button 
                                    @click="showDetails = true; user = {{ $u->toJson() }}"
                                    class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-pink-100 transition"
                                >
                                    View Details
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-6 text-center text-gray-500">
                                No users yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $users->links() }}
    </div>

    {{-- USER DETAILS MODAL --}}
    <div 
        x-show="showDetails"
        class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-3"
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
                    <p class="font-semibold">Role ID</p>
                    <p x-text="user.roleID" class="text-gray-700"></p>
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

            {{-- Activate / Deactivate Button --}}
            <div class="mt-4">
                <button 
                    @click="
                        fetch('{{ url('/admin/users/toggle-status') }}/' + user.userID, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(res => res.json())
                        .then(data => { user.status = data.status; })
                    "
                    class="px-4 py-2 rounded-lg text-white transition"
                    :class="user.status == 1 ? 'bg-red-500 hover:bg-red-600' : 'bg-gray-400 cursor-not-allowed'"
                >
                    <span x-text="user.status == 1 ? 'Deactivate User' : 'Inactive'"></span>
                </button>
            </div>
        </div>
    </div>

    {{-- ADD ADMIN MODAL --}}
    <div 
        x-show="showAdd"
        class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-3"
        x-cloak
    >
        <div class="bg-white rounded-2xl w-full md:w-[700px] p-6 relative shadow-xl">
            <button 
                @click="showAdd = false"
                class="absolute top-3 right-3 text-gray-500 hover:text-black"
            >
                ✕
            </button>

            <h2 class="text-xl md:text-2xl font-semibold">Add Admin</h2>
            <p class="text-gray-500 mb-6 text-sm md:text-base">Please fill in input fields</p>

            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.users.storeAdmin') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf

                <div>
                    <label class="font-medium">Username</label>
                    <input name="username" type="text" value="{{ old('username') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-pink-300" required>
                </div>

                <div class="col-span-2">
                    <label class="font-medium">Select Role</label>
                    <select name="roleID" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-pink-300" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->roleID }}" {{ old('roleID') == $role->roleID ? 'selected' : '' }}>{{ $role->roleName }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="font-medium">Password</label>
                    <input name="password" type="password" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-pink-300" required>
                </div>

                <div>
                    <label class="font-medium">Confirm Password</label>
                    <input name="password_confirmation" type="password" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-pink-300" required>
                </div>

                <div class="col-span-2 flex justify-end gap-2 mt-4">
                    <button 
                        type="button"
                        @click="showAdd = false"
                        class="px-5 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition"
                    >
                        Cancel
                    </button>

                    <button 
                        type="submit"
                        class="px-5 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition"
                    >
                        Create Account
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
