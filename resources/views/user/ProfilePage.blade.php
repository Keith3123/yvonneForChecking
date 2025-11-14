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

            <div class="grid md:grid-cols-2 gap-5 text-sm">
                <div>
                    <label class="block text-gray-700 mb-1 font-medium">Username</label>
                    <input type="text" value="dsd" readonly
                        class="w-full border border-gray-300 rounded-lg p-2 bg-gray-100 outline-none">
                    <p class="text-xs text-gray-400 mt-1">Username cannot be changed</p>
                </div>

                <div>
                    <label class="block text-gray-700 mb-1 font-medium">Full Name</label>
                    <input type="text" value="jas" readonly
                        class="w-full border border-gray-300 rounded-lg p-2 bg-gray-100 outline-none">
                </div>

                <div>
                    <label class="block text-gray-700 mb-1 font-medium">Email Address</label>
                    <input type="email" value="jasjas@gmail.com" readonly
                        class="w-full border border-gray-300 rounded-lg p-2 bg-gray-100 outline-none">
                </div>

                <div>
                    <label class="block text-gray-700 mb-1 font-medium">Contact Number</label>
                    <input type="text" value="1212122112" readonly
                        class="w-full border border-gray-300 rounded-lg p-2 bg-gray-100 outline-none">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-gray-700 mb-1 font-medium">Delivery Address</label>
                    <input type="text" value="rrtret" readonly
                        class="w-full border border-gray-300 rounded-lg p-2 bg-gray-100 outline-none">
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button class="bg-[#fce7ef] hover:bg-pink-200 text-gray-700 font-medium px-4 py-2 rounded-lg transition">
                    Edit Profile
                </button>
            </div>
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
                <button class="bg-[#F9B3B0] hover:bg-[#F69491] text-white font-medium px-4 py-2 rounded-lg transition">
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
                <p><strong>Account ID:</strong> 1763606167332</p>
                <p><strong>Account Type:</strong> 
                    <span class="inline-block px-2 py-1 bg-[#fce7ef] text-pink-700 rounded-md text-xs ml-1">Customer</span>
                </p>
                <p><strong>Member Since:</strong> Recently Joined</p>
            </div>
        </div>
    </div>
</div>

<script>
    function showTab(tab) {
        const profileTab = document.getElementById('profile-tab');
        const securityTab = document.getElementById('security-tab');
        const profileBtn = document.getElementById('tab-profile');
        const securityBtn = document.getElementById('tab-security');

        // Hide both tabs first
        profileTab.classList.add('hidden');
        securityTab.classList.add('hidden');

        // Reset button styles
        profileBtn.classList.remove('bg-[#fce7ef]', 'text-pink-500');
        profileBtn.classList.add('text-gray-600');
        securityBtn.classList.remove('bg-[#fce7ef]', 'text-pink-500');
        securityBtn.classList.add('text-gray-600');

        // Show the selected tab
        if (tab === 'security') {
            securityTab.classList.remove('hidden');
            securityBtn.classList.add('bg-[#fce7ef]', 'text-pink-500');
        } else {
            profileTab.classList.remove('hidden');
            profileBtn.classList.add('bg-[#fce7ef]', 'text-pink-500');
        }
    }
</script>
@endsection
