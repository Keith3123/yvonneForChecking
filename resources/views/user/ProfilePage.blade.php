@extends('layouts.app')

@section('no-footer')
@endsection

@section('content')
{{-- SUCCESS FEEDBACK TOAST --}}
@if(session('success'))
    <div id="success-toast" class="fixed top-10 left-1/2 -translate-x-1/2 z-[100] flex items-center w-full max-w-xs p-4 text-gray-700 bg-white rounded-xl shadow-2xl border-l-4 border-pink-500 animate-bounce" role="alert">
        <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-pink-500 bg-pink-100 rounded-lg">
            <i class="fas fa-check"></i>
        </div>
        <div class="ml-3 text-sm font-semibold">{{ session('success') }}</div>
    </div>

    <script>
        setTimeout(() => {
            const toast = document.getElementById('success-toast');
            if(toast) {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.5s ease';
                setTimeout(() => toast.remove(), 500);
            }
        }, 4000);
    </script>
@endif

<div class="bg-gradient-to-br from-[#FFF6F6] to-[#FFFDFD] min-h-screen py-12">
    <div class="max-w-5xl mx-auto px-6">

        {{-- Back Button --}}
        <a href="{{ route('catalog') }}"
            class="inline-flex items-center gap-2 mb-8 text-sm font-medium text-gray-600
                  hover:text-pink-500 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 19l-7-7 7-7" />
            </svg>
            Back to Catalog
        </a>

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">My Profile</h1>
            <p class="text-gray-500 text-sm mt-1">
                Manage your account information and security
            </p>
        </div>

        {{-- Tabs --}}
        <div class="flex w-full bg-white rounded-full shadow-sm border p-1 mb-8 text-sm font-medium">
            <button id="tab-profile"
                class="w-1/2 py-2 rounded-full transition bg-pink-100 text-pink-600"
                onclick="showTab('profile')">
                Profile Information
            </button>

            <button id="tab-security"
                class="w-1/2 py-2 rounded-full transition text-gray-600 hover:bg-gray-100"
                onclick="showTab('security')">
                Security
            </button>
        </div>


        {{-- PROFILE TAB --}}
        <div id="profile-tab"
            class="bg-white rounded-2xl shadow-sm border p-8 mb-10">

            <h2 class="text-xl font-semibold text-gray-800 mb-1">
                Personal Information
            </h2>
            <p class="text-sm text-gray-500 mb-6">
                Update your personal details
            </p>

            <form id="profileForm" action="{{ route('profile.update') }}" method="POST">
                @csrf

                @if(isset($user))
                <div class="grid md:grid-cols-2 gap-6 text-sm">

                    <div>
                        <label class="font-medium text-gray-700 mb-1 block">Username</label>
                        <input type="text" name="username" value="{{ $user['username'] }}"
                            class="profile-field w-full rounded-lg border bg-gray-100 px-3 py-2
                            focus:ring-2 focus:ring-pink-300 outline-none">
                        <p class="text-xs text-gray-400 mt-1">Your username must be unique</p>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="font-medium text-gray-700 mb-1 block">First Name</label>
                            <input name="firstName" value="{{ $user['firstName'] }}" readonly
                                class="profile-field w-full rounded-lg border bg-gray-100 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none">
                        </div>
                        <div>
                            <label class="font-medium text-gray-700 mb-1 block">M.I.</label>
                            <input name="mi" value="{{ $user['mi'] ?? '' }}" readonly maxlength="1"
                                class="profile-field w-full rounded-lg border bg-gray-100 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none">
                        </div>
                        <div>
                            <label class="font-medium text-gray-700 mb-1 block">Last Name</label>
                            <input name="lastName" value="{{ $user['lastName'] }}" readonly
                                class="profile-field w-full rounded-lg border bg-gray-100 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="font-medium text-gray-700 mb-1 block">Email Address</label>
                        <input name="email" value="{{ $user['email'] }}" readonly
                            class="profile-field w-full rounded-lg border bg-gray-100 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none">
                    </div>

                    <div>
                        <label class="font-medium text-gray-700 mb-1 block">Contact Number</label>
                        <input name="phone" value="{{ $user['phone'] }}" readonly
                            class="profile-field w-full rounded-lg border bg-gray-100 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none">
                    </div>

                    <div class="md:col-span-2">
                        <label class="font-medium text-gray-700 mb-1 block">Personal Address</label>
                        <input name="address" value="{{ $user['address'] }}" readonly
                            class="profile-field w-full rounded-lg border bg-gray-100 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none">
                    </div>
                </div>
                @endif

                <div class="mt-8 flex justify-end gap-3">
                    <button type="button" id="cancelBtn"
                        class="hidden px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 transition">
                        Cancel
                    </button>

                    <button type="submit" id="saveBtn"
                        class="hidden px-5 py-2 rounded-lg bg-pink-500 text-white hover:bg-pink-600 transition">
                        Save Changes
                    </button>

                    <button type="button" id="editBtn"
                        class="px-5 py-2 rounded-lg bg-pink-100 text-pink-700 hover:bg-pink-200 transition">
                        Edit Profile
                    </button>
                </div>
            </form>
        </div>

        {{-- SECURITY TAB --}}
        <div id="security-tab"
            class="hidden bg-white rounded-2xl shadow-sm border p-8">

            <h2 class="text-xl font-semibold text-gray-800 mb-1">Password & Security</h2>
            <p class="text-sm text-gray-500 mb-6">
                Manage your password and account security
            </p>

            <div class="flex justify-between items-center bg-pink-50 border border-pink-200 p-5 rounded-xl">
                <div>
                    <p class="font-medium text-gray-800">Password</p>
                    <p class="text-xs text-gray-500">
    Last changed: 
    @if($user->password_changed_at)
        {{ \Carbon\Carbon::parse($user->password_changed_at)->diffForHumans() }}
    @else
        Never
    @endif
</p>
                </div>
                <button onclick="openPasswordModal()"
                    class="px-4 py-2 rounded-lg bg-pink-500 text-white hover:bg-pink-600 transition">
                    Change Password
                </button>
            </div>

            <div class="mt-6">
                <h3 class="font-semibold text-gray-800 mb-2">Security Tips</h3>
                <ul class="list-disc pl-6 text-gray-600 text-sm space-y-1">
                    <li>Use a strong, unique password</li>
                    <li>Never share your password</li>
                    <li>Change passwords regularly</li>
                    <li>Keep contact details updated</li>
                </ul>
            </div>

            <div class="mt-8 pt-4 border-t text-sm text-gray-600">
                <p><strong>Account ID:</strong> {{ $user['customerID'] }}</p>
                <p><strong>Account Type:</strong>
                    <span class="ml-1 px-2 py-1 bg-pink-100 text-pink-700 rounded text-xs">
                        Customer
                    </span>
                </p>
                <p><strong>Member Since:</strong> 
                    {{ \Carbon\Carbon::parse($user->created_at)->format('F d, Y') }}
                </p>
            </div>
        </div>

        {{-- PASSWORD MODAL --}}
     <div id="passwordModal" class="fixed inset-0 bg-black/40 hidden flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 overflow-hidden">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800">Change Password</h2>
                    <button onclick="closePasswordModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>

                <form action="{{ route('profile.password.update') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                        <input type="password" name="current_password" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none @error('current_password') border-red-500 @enderror">
                        @error('current_password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <input type="password" name="new_password" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" required
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none">
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="closePasswordModal()" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition">Cancel</button>
                        <button type="submit" class="px-6 py-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600 transition">Update Password</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>


<script>
    const editBtn = document.getElementById('editBtn');
    const saveBtn = document.getElementById('saveBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const fields = document.querySelectorAll('.profile-field');

    let original = {};

    // Edit button functionality
    editBtn.onclick = function() {
        fields.forEach(input => {
            original[input.name] = input.value;
            input.readOnly = false;
            input.classList.remove('bg-gray-100');
        });

        editBtn.classList.add('hidden');
        saveBtn.classList.remove('hidden');
        cancelBtn.classList.remove('hidden');
    };

    // Cancel button functionality
    cancelBtn.onclick = function() {
        fields.forEach(input => {
            input.value = original[input.name];
            input.readOnly = true;
            input.classList.add('bg-gray-100');
        });

        saveBtn.classList.add('hidden');
        cancelBtn.classList.add('hidden');
        editBtn.classList.remove('hidden');
    };

    // Switch between profile and security tabs
    function showTab(tab) {
        const profileTab = document.getElementById('profile-tab');
        const securityTab = document.getElementById('security-tab');
        const profileBtn = document.getElementById('tab-profile');
        const securityBtn = document.getElementById('tab-security');

        if (tab === 'profile') {
            profileTab.classList.remove('hidden');
            securityTab.classList.add('hidden');
            profileBtn.className = "w-1/2 py-2 rounded-full transition bg-pink-100 text-pink-600";
            securityBtn.className = "w-1/2 py-2 rounded-full transition text-gray-600 hover:bg-gray-100";
        } else {
            securityTab.classList.remove('hidden');
            profileTab.classList.add('hidden');
            securityBtn.className = "w-1/2 py-2 rounded-full transition bg-pink-100 text-pink-600";
            profileBtn.className = "w-1/2 py-2 rounded-full transition text-gray-600 hover:bg-gray-100";
        }
    }

    // Open change password modal
    function openPasswordModal() {
        document.getElementById('passwordModal').classList.remove('hidden');
    }

    // Close change password modal
    function closePasswordModal() {
 document.getElementById('passwordModal').classList.add('hidden');
    }
// Edit Profile Logic
    document.getElementById('editBtn').addEventListener('click', function() {
        document.querySelectorAll('.profile-field').forEach(el => {
            el.removeAttribute('readonly');
            el.classList.replace('bg-gray-100', 'bg-white');
        });
        this.classList.add('hidden');
        document.getElementById('saveBtn').classList.remove('hidden');
        document.getElementById('cancelBtn').classList.remove('hidden');
    });

    // Automatically re-open modal if there are password errors
    @if($errors->has('current_password') || $errors->has('new_password'))
        window.onload = function() {
            showTab('security');
            openPasswordModal();
        };
    @endif
    
</script>
@endsection
