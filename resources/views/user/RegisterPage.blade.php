@extends('layouts.app')
@section('no-footer')
@endsection
@section('content')
<div class="min-h-screen flex flex-col items-center pt-16 text-center bg-[#FFF8F5]">

    <div class="mb-6">
        <h2 class="text-lg font-semibold">Welcome to Yvonne's Cakes & Pastries</h2>
        <p class="text-xs text-gray-500">
            Custom cakes, pastries and food trays for every occasion
        </p>
    </div>

    {{-- Tabs --}}
    <div class="flex w-[280px] bg-white rounded-full mb-5 p-1 text-sm font-medium shadow">
        <button type="button"
            class="w-1/2 py-2 rounded-full bg-pink-100 text-pink-600 font-semibold">
            Register
        </button>
        <button type="button"
            onclick="window.location.href='{{ route('login') }}'"
            class="w-1/2 py-2 rounded-full text-gray-600 hover:bg-gray-100 transition">
            Login
        </button>
    </div>

    {{-- Registration Form --}}
    <form action="{{ route('register.store') }}" method="POST"
        class="w-full max-w-sm bg-white p-6 rounded-xl shadow-lg text-left"
        id="registerForm">
        @csrf

        @include('partials.register.progress')

        {{-- Step 1 --}}
        <div class="step step-1">
            <p class="text-sm text-gray-500 mb-4">
                Create an account to start ordering
            </p>

            @if ($errors->any())
            <div class="text-red-500 text-xs mb-3">
                {{ $errors->first() }}
            </div>
            @endif

            <div class="flex flex-col gap-3 mb-6 text-sm">
                <label class="flex flex-col">
                    <span class="mb-1">Last Name</span>
                    <input type="text" name="lastName" value="{{ old('lastName') }}" required
                        class="border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-pink-300 outline-none">
                </label>

                <label class="flex flex-col">
                    <span class="mb-1">First Name</span>
                    <input type="text" name="firstName" value="{{ old('firstName') }}" required
                        class="border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-pink-300 outline-none">
                </label>

                <label class="flex flex-col">
                    <span class="mb-1">Middle Initial</span>
                    <input type="text" name="mi" value="{{ old('mi') }}"
                        class="border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-pink-300 outline-none @error('mi') border-red-500 @enderror">
                    @error('mi')
                        <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                    @enderror
                </label>
            </div>

            <button type="button"
                class="next-btn w-full bg-pink-500 hover:bg-pink-600
                       text-white font-semibold py-2 rounded-lg transition">
                Continue
            </button>
        </div>

        {{-- Step 2 --}}
        <div class="step step-2 hidden">
            <p class="text-sm text-gray-500 mb-4">
                Create an account to start ordering
            </p>

            <div class="flex flex-col gap-3 mb-6 text-sm">
                <label>Email Address
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="w-full border border-gray-300 rounded-lg p-2 mt-1 focus:ring-2 focus:ring-pink-300 outline-none">
                </label>

                <label>Personal Address
                    <input type="text" name="address" value="{{ old('address') }}" required
                        class="w-full border border-gray-300 rounded-lg p-2 mt-1 focus:ring-2 focus:ring-pink-300 outline-none">
                </label>

                <label>Phone Number
                    <input type="text" name="phone" value="{{ old('phone') }}" required
                        class="w-full border border-gray-300 rounded-lg p-2 mt-1 focus:ring-2 focus:ring-pink-300 outline-none @error('phone') is-invalid @enderror">
                    <span class="text-red-500 text-xs mt-1 @error('phone') @else hidden @enderror">
                        @error('phone') {{ $message }} @else Enter a valid 11-digit phone number. @enderror
                    </span>
                </label>
            </div>

            <div class="flex gap-3">
                <button type="button"
                    class="back-btn w-1/2 bg-gray-200 hover:bg-gray-300
                           text-gray-700 font-medium py-2 rounded-lg transition">
                    Back
                </button>
                <button type="button"
                    class="next-btn w-1/2 bg-pink-500 hover:bg-pink-600
                           text-white font-semibold py-2 rounded-lg transition">
                    Continue
                </button>
            </div>
        </div>

        {{-- Step 3 --}}
        <div class="step step-3 hidden">
            <p class="text-sm text-gray-500 mb-4">
                Create an account to start ordering
            </p>

            <div class="flex flex-col gap-3 mb-4 text-sm">
                {{-- Username --}}
                <label class="flex flex-col relative">
                    <span class="mb-1">Username</span>
                    <input type="text" name="username" value="{{ old('username') }}" required
                        class="w-full border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-pink-300 outline-none" id="username">
                    <span class="text-red-500 text-sm mt-1 hidden" id="username-error">Username already exists</span>
                </label>

                {{-- Password --}}
                <label class="flex flex-col relative">
                    <span class="mb-1">Password</span>
                    <input type="password" name="password" required
                        class="w-full border border-gray-300 rounded-lg p-2 password-input focus:ring-2 focus:ring-pink-300 outline-none">
                    <button type="button" class="absolute right-2 top-9 text-gray-500 toggle-password">
                        <i class="far fa-eye-slash"></i>
                    </button>
                    <div class="text-xs mt-2 space-y-1 password-rules">
                        <p data-rule="length"    class="text-gray-400">At least 8 characters</p>
                        <p data-rule="uppercase" class="text-gray-400">1 uppercase letter</p>
                        <p data-rule="lowercase" class="text-gray-400">1 lowercase letter</p>
                        <p data-rule="number"    class="text-gray-400">1 number</p>
                    </div>
                </label>

                {{-- Confirm Password --}}
                <label class="flex flex-col relative">
                    <span class="mb-1">Confirm Password</span>
                    <input type="password" name="password_confirmation" required
                        class="w-full border border-gray-300 rounded-lg p-2 password-input focus:ring-2 focus:ring-pink-300 outline-none">
                    <button type="button" class="absolute right-2 top-9 text-gray-500 toggle-password">
                        <i class="far fa-eye-slash"></i>
                    </button>
                    <p class="text-red-500 text-xs mt-1 hidden password-error">The password confirmation does not match.</p>
                </label>
            </div>

            {{-- Terms & Conditions --}}
            <div class="mb-5">
                <label class="flex items-start gap-2 cursor-pointer group">
                    <input type="checkbox" id="terms-checkbox"
                        class="mt-0.5 accent-pink-500 w-4 h-4 shrink-0 cursor-pointer">
                    <span class="text-xs text-gray-500 leading-relaxed">
                        I have read and agree to the
                        <button type="button" id="open-terms-modal"
                            class="text-pink-500 underline hover:text-pink-700 font-medium">
                            Terms and Conditions
                        </button>
                        of Yvonne's Cakes &amp; Pastries.
                    </span>
                </label>
                <p class="text-red-500 text-xs mt-1 hidden" id="terms-error">
                    You must agree to the Terms and Conditions to continue.
                </p>
            </div>

            <div class="flex gap-3">
                <button type="button"
                    class="back-btn w-1/2 bg-gray-200 hover:bg-gray-300
                           text-gray-700 font-medium py-2 rounded-lg transition">
                    Back
                </button>
                <button type="submit"
                    class="submit-btn w-1/2 bg-pink-500 hover:bg-pink-600
                           text-white font-semibold py-2 rounded-lg transition">
                    Create Account
                </button>
            </div>
        </div>
    </form>

    {{-- ===================== OTP MODAL ===================== --}}
    <div id="otpModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl mx-4">
            <h2 class="text-lg font-semibold text-center mb-2">Phone Verification</h2>
            <p class="text-sm text-gray-500 text-center mb-4">
                Enter the 6-digit OTP sent to your phone
            </p>

            <input type="text" id="otpInput" maxlength="6" inputmode="numeric"
                class="w-full border border-gray-300 rounded-xl p-3 text-center text-lg tracking-widest focus:ring-2 focus:ring-pink-300 outline-none"
                placeholder="000000">

            <p id="otpError" class="text-red-500 text-sm mt-2 hidden text-center"></p>

            <p id="otpTimer" data-duration="300"
                class="text-gray-500 text-sm mt-2 text-center">
                OTP expires in: 05:00
            </p>

            <button type="button" id="resendOtpBtn"
                class="w-full mt-3 text-pink-500 hover:text-pink-600 text-sm font-medium">
                Resend OTP
            </button>

            <div class="flex gap-3 mt-4">
                <button type="button" id="closeOtpModal"
                    class="w-1/2 bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 rounded-xl">
                    Cancel
                </button>
                <button type="button" id="verifyOtpBtn"
                    class="w-1/2 bg-pink-500 hover:bg-pink-600 text-white py-2 rounded-xl">
                    Verify
                </button>
            </div>
        </div>
    </div>

    {{-- ===================== T&C MODAL ===================== --}}
    <div id="terms-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 flex flex-col max-h-[80vh]">

            {{-- Header --}}
            <div class="flex items-center justify-between p-5 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-800 w-full text-center">Terms & Conditions</h2>
                <button id="close-terms-modal" type="button"
                    class="text-gray-400 hover:text-gray-600 transition text-xl leading-none">&times;</button>
            </div>

            {{-- Body — text-left overrides the parent text-center --}}
            <div class="overflow-y-auto p-5 text-sm text-gray-600 space-y-4 flex-1 text-left">
                <p class="text-xs text-gray-400">Last updated: {{ date('F d, Y') }}</p>

                <section>
                    <h3 class="font-semibold text-gray-700 mb-1">1. Acceptance of Terms</h3>
                    <p>By creating an account on Yvonne's Cakes & Pastries, you agree to be bound by these Terms and Conditions. If you do not agree, please do not register or use our services.</p>
                </section>
                <section>
                    <h3 class="font-semibold text-gray-700 mb-1">2. Account Responsibility</h3>
                    <p>You are responsible for maintaining the confidentiality of your account credentials. You agree to notify us immediately of any unauthorized use of your account. One person may not hold more than five (5) accounts using the same phone number.</p>
                </section>
                <section>
                    <h3 class="font-semibold text-gray-700 mb-1">3. Orders & Payments</h3>
                    <p>All orders are subject to availability and confirmation. Prices are subject to change without prior notice. We reserve the right to cancel any order due to pricing errors or product unavailability. Payments via GCash are processed through PayMongo and are subject to their terms.</p>
                </section>
                <section>
                    <h3 class="font-semibold text-gray-700 mb-1">4. Paluwagan Participation</h3>
                    <p>Joining a Paluwagan package is a financial commitment. Cancellations and release requests are subject to admin approval and the schedule agreed upon at the time of enrollment. We reserve the right to reassign slots if payment obligations are not met.</p>
                </section>
                <section>
                    <h3 class="font-semibold text-gray-700 mb-1">5. Privacy</h3>
                    <p>We collect personal information (name, phone number, address, email) solely for order fulfillment and account management. We do not sell your data to third parties.</p>
                </section>
                <section>
                    <h3 class="font-semibold text-gray-700 mb-1">6. Google Binding</h3>
                    <p>To prevent fraudulent orders and ensure secure payment processing, all customers are required to link a verified Gmail account to their profile.</p>
                </section>
                <section>
                    <h3 class="font-semibold text-gray-700 mb-1">7. Product Availability</h3>
                    <p>All items are made to order. Availability of custom cakes, pastries, and food trays may vary depending on season and supply. We will contact you if any ordered item cannot be fulfilled.</p>
                </section>
                <section>
                    <h3 class="font-semibold text-gray-700 mb-1">8. Modifications</h3>
                    <p>Yvonne's Cakes & Pastries reserves the right to update these Terms at any time. Continued use of the platform after changes constitutes acceptance of the new terms.</p>
                </section>
                <section>
                    <h3 class="font-semibold text-gray-700 mb-1">9. Contact</h3>
                    <p>For any concerns regarding these terms, please reach out to us through our official channels.</p>
                </section>
            </div>

            {{-- Footer --}}
            <div class="p-4 border-t border-gray-100 flex justify-end gap-3">
                <button id="close-terms-modal-btn" type="button"
                    class="px-4 py-2 text-sm rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 transition">
                    Close
                </button>
                <button id="accept-terms-btn" type="button"
                    class="px-4 py-2 text-sm rounded-lg bg-pink-500 hover:bg-pink-600 text-white font-semibold transition">
                    I Agree
                </button>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal          = document.getElementById('terms-modal');
    const openBtn        = document.getElementById('open-terms-modal');
    const closeBtn       = document.getElementById('close-terms-modal');
    const closeBtnFooter = document.getElementById('close-terms-modal-btn');
    const acceptBtn      = document.getElementById('accept-terms-btn');
    const checkbox       = document.getElementById('terms-checkbox');

    const openModal  = () => { modal.classList.remove('hidden'); modal.classList.add('flex'); };
    const closeModal = () => { modal.classList.add('hidden');  modal.classList.remove('flex'); };

    openBtn?.addEventListener('click', openModal);
    closeBtn?.addEventListener('click', closeModal);
    closeBtnFooter?.addEventListener('click', closeModal);

    acceptBtn?.addEventListener('click', () => {
        if (checkbox) checkbox.checked = true;
        document.getElementById('terms-error')?.classList.add('hidden');
        closeModal();
    });

    modal?.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
});
</script>

@vite('resources/js/register.js')
@endsection