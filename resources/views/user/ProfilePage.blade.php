@extends('layouts.app')

@section('no-footer')
@endsection

@section('content')

{{-- SUCCESS / ERROR TOAST --}}
@foreach(['success' => 'pink', 'error' => 'red'] as $type => $color)
@if(session($type))
<div id="{{ $type }}-toast"
    class="fixed top-10 left-1/2 -translate-x-1/2 z-[100] flex items-center w-full max-w-xs p-4
           text-gray-700 bg-white rounded-xl shadow-2xl border-l-4 border-{{ $color }}-500 animate-bounce"
    role="alert">
    <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8
                text-{{ $color }}-500 bg-{{ $color }}-100 rounded-lg">
        <i class="fas fa-{{ $type === 'success' ? 'check' : 'times' }}"></i>
    </div>
    <div class="ml-3 text-sm font-semibold">{{ session($type) }}</div>
</div>
<script>
    setTimeout(() => {
        const t = document.getElementById('{{ $type }}-toast');
        if (t) { t.style.opacity = '0'; t.style.transition = 'opacity 0.5s'; setTimeout(() => t.remove(), 500); }
    }, 4000);
</script>
@endif
@endforeach

<div class="bg-gradient-to-br from-[#FFF6F6] to-[#FFFDFD] min-h-screen py-12">
    <div class="max-w-5xl mx-auto px-6">

        {{-- Back Button --}}
        <a href="{{ route('catalog') }}"
            class="inline-flex items-center gap-2 mb-8 text-sm font-medium text-gray-600 hover:text-pink-500 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Catalog
        </a>

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">My Profile</h1>
            <p class="text-gray-500 text-sm mt-1">Manage your account information and security</p>
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

        {{-- ══════════════════════════════════════════ --}}
        {{-- PROFILE TAB                               --}}
        {{-- ══════════════════════════════════════════ --}}
        <div id="profile-tab" class="bg-white rounded-2xl shadow-sm border p-8 mb-10">

            <h2 class="text-xl font-semibold text-gray-800 mb-1">Personal Information</h2>
            <p class="text-sm text-gray-500 mb-6">Update your personal details</p>

            <form id="profileForm" action="{{ route('profile.update') }}" method="POST">
                @csrf

                @if(isset($user))
                <div class="grid md:grid-cols-2 gap-6 text-sm">

                    <div>
                        <label class="font-medium text-gray-700 mb-1 block">Username</label>
                        <input type="text" name="username" value="{{ $user->username }}"
                            class="profile-field w-full rounded-lg border bg-gray-100 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none">
                        <p class="text-xs text-gray-400 mt-1">Your username must be unique</p>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="font-medium text-gray-700 mb-1 block">First Name</label>
                            <input name="firstName" value="{{ $user->firstName }}" readonly
                                class="profile-field w-full rounded-lg border bg-gray-100 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none">
                        </div>
                        <div>
                            <label class="font-medium text-gray-700 mb-1 block">M.I.</label>
                            <input name="mi" value="{{ $user->mi ?? '' }}" readonly maxlength="1"
                                class="profile-field w-full rounded-lg border bg-gray-100 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none">
                        </div>
                        <div>
                            <label class="font-medium text-gray-700 mb-1 block">Last Name</label>
                            <input name="lastName" value="{{ $user->lastName }}" readonly
                                class="profile-field w-full rounded-lg border bg-gray-100 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none">
                        </div>
                    </div>

                    {{-- EMAIL ROW --}}
                    <div>
                        <label class="font-medium text-gray-700 mb-1 block">Email Address</label>
                        <div class="flex items-center gap-2 flex-wrap">
                            <input id="currentEmail" name="email" value="{{ $user->email }}" readonly
                                class="flex-1 min-w-0 rounded-lg border bg-gray-100 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none text-sm">

                            @if($user->email_verified_at)
                                <span class="px-3 py-2 text-xs font-semibold rounded-lg bg-green-100 text-green-700 whitespace-nowrap">
                                    ✓ Verified
                                </span>
                            @else
                                <span class="px-3 py-2 text-xs font-semibold rounded-lg bg-yellow-100 text-yellow-700 whitespace-nowrap">
                                    ⚠ Not Verified
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-400 mt-1">To change your email, use the Google bind option in the Security tab</p>
                    </div>

                    <div>
                        <label class="font-medium text-gray-700 mb-1 block">Contact Number</label>
                        <input name="phone" value="{{ $user->phone }}" readonly
                            class="profile-field w-full rounded-lg border bg-gray-100 px-3 py-2 focus:ring-2 focus:ring-pink-300 outline-none">
                    </div>

                    <div class="md:col-span-2">
                        <label class="font-medium text-gray-700 mb-1 block">Personal Address</label>
                        <input name="address" value="{{ $user->address }}" readonly
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

        {{-- ══════════════════════════════════════════ --}}
        {{-- SECURITY TAB                              --}}
        {{-- ══════════════════════════════════════════ --}}
        <div id="security-tab" class="hidden bg-white rounded-2xl shadow-sm border p-8">

            <h2 class="text-xl font-semibold text-gray-800 mb-1">Password & Security</h2>
            <p class="text-sm text-gray-500 mb-6">Manage your password and account security</p>

            {{-- Password Row --}}
            <div class="flex justify-between items-center bg-pink-50 border border-pink-200 p-5 rounded-xl mb-4">
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

            {{-- ─── GOOGLE BINDING ROW ─── --}}
            <div class="flex justify-between items-center bg-gray-50 border border-gray-200 p-5 rounded-xl">
                <div class="flex items-center gap-3">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    <div>
                        <p class="font-medium text-gray-800">Google Account</p>
                        @if($user->google_id)
                            <p class="text-xs text-green-600 font-medium">✓ Linked · {{ $user->email }}</p>
                        @else
                            <p class="text-xs text-gray-500">Not linked</p>
                        @endif
                    </div>
                </div>

                @if($user->google_id)
                    <button type="button" onclick="openUnlinkModal()"
                        class="px-4 py-2 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition text-sm">
                        Unlink Google
                    </button>
                @else
                    <button type="button" onclick="openGoogleBindModal()"
                        class="px-4 py-2 rounded-lg bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition text-sm flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                        </svg>
                        Link Google Account
                    </button>
                @endif
            </div>

            {{-- Security Tips --}}
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
                <p><strong>Account ID:</strong> {{ $user->customerID }}</p>
                <p><strong>Account Type:</strong>
                    <span class="ml-1 px-2 py-1 bg-pink-100 text-pink-700 rounded text-xs">Customer</span>
                </p>
                <p><strong>Member Since:</strong> {{ \Carbon\Carbon::parse($user->created_at)->format('F d, Y') }}</p>
            </div>
        </div>

        {{-- ══════════════════════════════════════════ --}}
        {{-- PASSWORD MODAL                            --}}
        {{-- ══════════════════════════════════════════ --}}
        <div id="passwordModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800">Change Password</h2>
                    <button onclick="closePasswordModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
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
                        <button type="button" onclick="closePasswordModal()"
                            class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition">Cancel</button>
                        <button type="submit"
                            class="px-6 py-2 bg-pink-500 text-white rounded-lg hover:bg-pink-600 transition">Update Password</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ══════════════════════════════════════════ --}}
        {{-- UNLINK GOOGLE CONFIRMATION MODAL         --}}
        {{-- ══════════════════════════════════════════ --}}
        <div id="unlinkModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center">

                <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-unlink text-red-500 text-xl"></i>
                </div>

                <h2 class="text-lg font-bold text-gray-800 mb-1">Unlink Google Account?</h2>
                <p class="text-sm text-gray-500 mb-6">
                    You will no longer be able to sign in with Google. Make sure you have a password set before unlinking.
                </p>

                <div class="flex gap-3">
                    <button type="button" onclick="closeUnlinkModal()"
                        class="w-1/2 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 transition font-medium">
                        Cancel
                    </button>
                    <form action="{{ route('google.unlink') }}" method="POST" class="w-1/2">
                        @csrf
                        <button type="submit"
                            class="w-full py-2 rounded-xl bg-red-500 hover:bg-red-600 text-white transition font-medium">
                            Yes, Unlink
                        </button>
                    </form>
                </div>

            </div>
        </div>

        {{-- ══════════════════════════════════════════ --}}
        {{-- GOOGLE BIND MODAL                         --}}
        {{-- ══════════════════════════════════════════ --}}
        <div id="googleBindModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">

                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800">Link Google Account</h2>
                    <button type="button" onclick="closeGoogleBindModal()"
                        class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                </div>

                <p class="text-sm text-gray-500 mb-5">
                    Your account email will be updated and verified to match whichever Google account you sign in with.
                </p>

                {{-- Current email info --}}
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-5 text-sm text-yellow-800">
                    <p><strong>Current email:</strong> {{ $user->email }}</p>
                    <p class="mt-1 text-xs text-yellow-700">After linking, your email will be replaced by your Google account's email.</p>
                </div>

                {{-- Single CTA --}}
                <a href="{{ route('google.bind') }}"
                    class="flex items-center justify-center gap-3 w-full py-3 rounded-xl bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition shadow-sm font-medium">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                    </svg>
                    Continue with Google
                </a>

                <button type="button" onclick="closeGoogleBindModal()"
                    class="w-full mt-3 py-2 rounded-lg text-gray-500 hover:bg-gray-100 transition text-sm">
                    Cancel
                </button>

            </div>
        </div>

    </div>
</div>

{{-- ══════════════════════════════════════════ --}}
{{-- JAVASCRIPT                                --}}
{{-- ══════════════════════════════════════════ --}}
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '{{ csrf_token() }}';

// ─── Tab switching ───
function showTab(tab) {
    const profileTab  = document.getElementById('profile-tab');
    const securityTab = document.getElementById('security-tab');
    const profileBtn  = document.getElementById('tab-profile');
    const securityBtn = document.getElementById('tab-security');

    if (tab === 'profile') {
        profileTab.classList.remove('hidden');
        securityTab.classList.add('hidden');
        profileBtn.className  = "w-1/2 py-2 rounded-full transition bg-pink-100 text-pink-600";
        securityBtn.className = "w-1/2 py-2 rounded-full transition text-gray-600 hover:bg-gray-100";
    } else {
        securityTab.classList.remove('hidden');
        profileTab.classList.add('hidden');
        securityBtn.className = "w-1/2 py-2 rounded-full transition bg-pink-100 text-pink-600";
        profileBtn.className  = "w-1/2 py-2 rounded-full transition text-gray-600 hover:bg-gray-100";
    }
}

// ─── Edit / Cancel / Save ───
const editBtn   = document.getElementById('editBtn');
const saveBtn   = document.getElementById('saveBtn');
const cancelBtn = document.getElementById('cancelBtn');
const fields    = document.querySelectorAll('.profile-field');
let original    = {};

editBtn.onclick = function () {
    fields.forEach(input => {
        original[input.name] = input.value;
        input.readOnly = false;
        input.classList.replace('bg-gray-100', 'bg-white');
    });
    editBtn.classList.add('hidden');
    saveBtn.classList.remove('hidden');
    cancelBtn.classList.remove('hidden');
};

cancelBtn.onclick = function () {
    fields.forEach(input => {
        input.value = original[input.name];
        input.readOnly = true;
        input.classList.replace('bg-white', 'bg-gray-100');
    });
    saveBtn.classList.add('hidden');
    cancelBtn.classList.add('hidden');
    editBtn.classList.remove('hidden');
};

// ─── Password modal ───
function openPasswordModal()  { document.getElementById('passwordModal').classList.replace('hidden', 'flex'); }
function closePasswordModal() { document.getElementById('passwordModal').classList.replace('flex', 'hidden'); }

@if($errors->has('current_password') || $errors->has('new_password'))
    window.addEventListener('DOMContentLoaded', () => { showTab('security'); openPasswordModal(); });
@endif

// ─── Unlink Google Modal ───
function openUnlinkModal()  { document.getElementById('unlinkModal').classList.replace('hidden', 'flex'); }
function closeUnlinkModal() { document.getElementById('unlinkModal').classList.replace('flex', 'hidden'); }

// ─── Google Bind Modal ───
let bindTimer = null;

function openGoogleBindModal() {
    document.getElementById('googleBindModal').classList.replace('hidden', 'flex');
}

function closeGoogleBindModal() {
    document.getElementById('googleBindModal').classList.replace('flex', 'hidden');
}

// ─── Dynamic toast helper ───
function showToast(message, color = 'pink') {
    const toast = document.createElement('div');
    toast.className = `fixed top-10 left-1/2 -translate-x-1/2 z-[100] flex items-center w-full max-w-xs p-4 text-gray-700 bg-white rounded-xl shadow-2xl border-l-4 border-${color}-500`;
    toast.innerHTML = `
        <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-${color}-500 bg-${color}-100 rounded-lg">
            <i class="fas fa-check"></i>
        </div>
        <div class="ml-3 text-sm font-semibold">${message}</div>
    `;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity    = '0';
        toast.style.transition = 'opacity 0.5s';
        setTimeout(() => toast.remove(), 500);
    }, 4000);
}
</script>

@endsection