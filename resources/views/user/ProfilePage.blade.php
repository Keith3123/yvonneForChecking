@extends('layouts.app')

@section('no-footer')
@endsection

@section('content')
<div class="bg-[#FFF6F6] min-h-screen py-10">
    <div class="max-w-5xl mx-auto px-6">

        {{-- Back Button --}}
        <a href="{{ route('catalog') }}" class="flex items-center text-gray-700 mb-8 hover:text-[#F69491]">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <span class="text-lg font-medium">Back to Catalog</span>
        </a>

        {{-- Header --}}
        <h1 class="text-2xl font-bold mb-1">My Profile</h1>
        <p class="text-gray-500 mb-8">Manage your account information and security</p>

        {{-- Tabs --}}
        <div class="flex w-full bg-[#f8f6f4] rounded-full mb-6 text-sm font-medium">
            <button id="tab-profile"
                    class="w-1/2 py-2 rounded-full text-pink-500 bg-[#fce7ef] font-medium"
                    onclick="showTab('profile')">
                Profile Information
            </button>
            <button id="tab-security"
                    class="w-1/2 py-2 rounded-full text-gray-600 hover:bg-gray-100 transition"
                    onclick="showTab('security')">
                Security
            </button>
        </div>

        {{-- PROFILE INFORMATION TAB --}}
        <div id="profile-tab" class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
            <h2 class="text-lg font-semibold mb-4">Personal Information</h2>
            <p class="text-sm text-gray-500 mb-6">Update your personal details</p>

            <form id="profileForm" action="{{ route('profile.update') }}" method="POST">
                @csrf

                @if(isset($user))
                <div class="grid md:grid-cols-2 gap-5 text-sm">
                    <div>
                        <label class="block text-gray-700 mb-1 font-medium">Username</label>
                        <input type="text" value="{{ $user['username'] }}" readonly
                            class="w-full border border-gray-300 rounded-lg p-2 bg-gray-100 outline-none">
                        <p class="text-xs text-gray-400 mt-1">Username cannot be changed</p>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-gray-700 mb-1 font-medium">First Name</label>
                            <input name="firstName" type="text" value="{{ $user['firstName'] }}" readonly
                                class="profile-field w-full border border-gray-300 rounded-lg p-2 bg-gray-100 outline-none">
                        </div>

                        <div>
                            <label class="block text-gray-700 mb-1 font-medium">M.I.</label>
                            <input name="mi" type="text" maxlength="1" value="{{ $user['mi'] ?? '' }}" readonly
                                class="profile-field w-full border border-gray-300 rounded-lg p-2 bg-gray-100 outline-none">
                        </div>

                        <div>
                            <label class="block text-gray-700 mb-1 font-medium">Last Name</label>
                            <input name="lastName" type="text" value="{{ $user['lastName'] }}" readonly
                                class="profile-field w-full border border-gray-300 rounded-lg p-2 bg-gray-100 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-1 font-medium">Email Address</label>
                        <input name="email" type="email" value="{{ $user['email'] }}" readonly
                            class="profile-field w-full border border-gray-300 rounded-lg p-2 bg-gray-100 outline-none">
                    </div>

                    <div>
                        <label class="block text-gray-700 mb-1 font-medium">Contact Number</label>
                        <input name="phone" type="text" value="{{ $user['phone'] }}" readonly
                            class="profile-field w-full border border-gray-300 rounded-lg p-2 bg-gray-100 outline-none">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-gray-700 mb-1 font-medium">Delivery Address</label>
                        <input name="address" type="text" value="{{ $user['address'] }}" readonly
                            class="profile-field w-full border border-gray-300 rounded-lg p-2 bg-gray-100 outline-none">
                    </div>
                </div>
                @else
                    <p class="text-red-500">User data not found. Please log in to view your profile.</p>
                @endif

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" id="cancelBtn"
                        class="hidden bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium px-4 py-2 rounded-lg transition">
                        Cancel
                    </button>

                    <button type="submit" id="saveBtn"
                        class="hidden bg-[#F9B3B0] hover:bg-[#F69491] text-white font-medium px-4 py-2 rounded-lg transition">
                        Save Changes
                    </button>

                    <button type="button" id="editBtn"
                        class="bg-[#fce7ef] hover:bg-pink-200 text-gray-700 font-medium px-4 py-2 rounded-lg transition">
                        Edit Profile
                    </button>
                </div>
            </form>
        </div>

        {{-- SECURITY TAB --}}
        <div id="security-tab" class="hidden bg-white rounded-lg shadow-md p-6 border border-gray-200">
            <h2 class="text-lg font-semibold mb-4">Password & Security</h2>
            <p class="text-sm text-gray-500 mb-6">Manage your password and account security</p>

            <div class="flex items-center justify-between bg-[#FFF8F8] p-4 rounded-lg border border-[#F9B3B0]">
                <div>
                    <p class="font-medium text-gray-700">Password</p>
                    <p class="text-sm text-gray-500">Last changed: Never</p>
                </div>
                <button type="button" onclick="openPasswordModal()"
                        class="bg-[#F9B3B0] hover:bg-[#F69491] text-white font-medium px-4 py-2 rounded-lg transition">
                    Change Password
                </button>
            </div>

            <div class="mt-6">
                <h3 class="font-semibold text-gray-800 mb-2">Security Tips</h3>
                <ul class="list-disc pl-6 text-gray-600 text-sm space-y-1">
                    <li>Use a strong, unique password</li>
                    <li>Never share your password with anyone</li>
                    <li>Change your password regularly</li>
                    <li>Keep your contact information up to date</li>
                </ul>
            </div>

            <div class="mt-8 border-t pt-4 text-sm text-gray-600">
                <p><strong>Account ID:</strong> {{ $user['customerID'] }}</p>
                <p><strong>Account Type:</strong> 
                    <span class="inline-block px-2 py-1 bg-[#fce7ef] text-pink-700 rounded-md text-xs ml-1">Customer</span>
                </p>
                <p><strong>Member Since:</strong> Recently Joined</p>
            </div>
        </div>

        <!-- CHANGE PASSWORD MODAL -->
        <div id="passwordModal"
            class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">

            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">

                <h2 class="text-xl font-semibold mb-4">Change Password</h2>

                <form action="{{ route('password.update') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="block text-gray-700 text-sm font-medium mb-1">Current Password</label>
                        <input type="password" name="current_password" required
                               class="w-full border border-gray-300 rounded-lg p-2 outline-none focus:border-pink-400">
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 text-sm font-medium mb-1">New Password</label>
                        <input type="password" name="new_password" required minlength="6"
                               class="w-full border border-gray-300 rounded-lg p-2 outline-none focus:border-pink-400">
                    </div>

                    <div class="mb-3">
                        <label class="block text-gray-700 text-sm font-medium mb-1">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" required
                               class="w-full border border-gray-300 rounded-lg p-2 outline-none focus:border-pink-400">
                    </div>

                    <div class="mt-5 flex justify-end gap-3">
                        <button type="button" onclick="closePasswordModal()"
                                class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg">
                            Cancel
                        </button>

                        <button type="submit"
                                class="px-4 py-2 bg-[#F9B3B0] hover:bg-[#F69491] text-white rounded-lg">
                            Update Password
                        </button>
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
    editBtn.onclick = function () {
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
    cancelBtn.onclick = function () {
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

        profileTab.classList.add('hidden');
        securityTab.classList.add('hidden');

        profileBtn.classList.remove('bg-[#fce7ef]', 'text-pink-500');
        securityBtn.classList.remove('bg-[#fce7ef]', 'text-pink-500');

        if (tab === 'security') {
            securityTab.classList.remove('hidden');
            securityBtn.classList.add('bg-[#fce7ef]', 'text-pink-500');
        } else {
            profileTab.classList.remove('hidden');
            profileBtn.classList.add('bg-[#fce7ef]', 'text-pink-500');
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
</script>
@endsection