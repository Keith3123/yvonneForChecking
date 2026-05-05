{{-- Registration Form --}}
<form action="{{ route('register.store') }}" method="POST"
    class="space-y-6 text-sm"
    id="registerForm">
    @csrf

    {{-- Progress --}}
    @include('partials.register.progress-modal')

    {{-- ================= STEP 1 ================= --}}
    <div class="step step-1 space-y-6">

        <p class="text-sm text-gray-500">
            Create an account to start ordering
        </p>

        @if ($errors->any())
        <div class="text-red-500 text-xs">
            {{ $errors->first() }}
        </div>
        @endif

        <div class="space-y-4">

            <label class="flex flex-col">
                <span class="mb-1 font-medium text-gray-700">Last Name</span>
                <input type="text" name="lastName" value="{{ old('lastName') }}" required autocomplete="family-name"
                    class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-pink-300 outline-none">
            </label>

            <label class="flex flex-col">
                <span class="mb-1 font-medium text-gray-700">First Name</span>
                <input type="text" name="firstName" value="{{ old('firstName') }}" required autocomplete="given-name"
                    class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-pink-300 outline-none">
            </label>

            <label class="flex flex-col">
                <span class="mb-1 font-medium text-gray-700">Middle Name</span>
                <input type="text" name="mi" value="{{ old('mi') }}" autocomplete="additional-name"
                    class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-pink-300 outline-none">
            </label>

        </div>

        <button type="button"
            class="next-btn w-full bg-pink-500 hover:bg-pink-600
                   text-white font-semibold py-3 rounded-xl transition">
            Continue
        </button>
    </div>

    {{-- ================= STEP 2 ================= --}}
    <div class="step step-2 hidden space-y-6">

        <p class="text-sm text-gray-500">
            Create an account to start ordering
        </p>

        <div class="space-y-4">

            <label class="flex flex-col">
                <span class="mb-1 font-medium text-gray-700">Email Address</span>
                {{-- nullable in controller, so not required here --}}
                <input type="email" name="email" value="{{ old('email') }}" autocomplete="email"
                    class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-pink-300 outline-none">
            </label>

            <label class="flex flex-col">
                <span class="mb-1 font-medium text-gray-700">Address</span>
                <input type="text" name="address" value="{{ old('address') }}" required autocomplete="street-address"
                    class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-pink-300 outline-none">
            </label>

            {{-- Phone: needs the inner <span> so JS validateStep(1) can find it --}}
            <label class="flex flex-col">
                <span class="mb-1 font-medium text-gray-700">Phone Number</span>
                <input type="text" name="phone" value="{{ old('phone') }}" required autocomplete="tel"
                    class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-pink-300 outline-none @error('phone') is-invalid border-red-500 @enderror">
                {{-- ✅ Required by JS: phone?.parentElement.querySelector('span') --}}
                <span class="text-red-500 text-xs mt-1 @error('phone') @else hidden @enderror">
                    @error('phone') {{ $message }} @else Enter a valid 11-digit phone number. @enderror
                </span>
            </label>

        </div>

        <div class="flex gap-3">
            <button type="button"
                class="back-btn w-1/2 bg-gray-200 hover:bg-gray-300
                       text-gray-700 font-medium py-3 rounded-xl transition">
                Back
            </button>

            <button type="button"
                class="next-btn w-1/2 bg-pink-500 hover:bg-pink-600
                       text-white font-semibold py-3 rounded-xl transition">
                Continue
            </button>
        </div>
    </div>

    {{-- ================= STEP 3 ================= --}}
    <div class="step step-3 hidden space-y-6">

        <p class="text-sm text-gray-500">
            Create an account to start ordering
        </p>

        <div class="space-y-4">

            {{-- Username --}}
            <label class="flex flex-col">
                <span class="mb-1 font-medium text-gray-700">Username</span>
                <input type="text" name="username" value="{{ old('username') }}" required
                    id="username" autocomplete="username"
                    class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-pink-300 outline-none">

                <span class="text-red-500 text-xs mt-1 hidden" id="username-error">
                    Username already exists
                </span>
            </label>

            {{-- Password --}}
            <label class="flex flex-col relative">
                <span class="mb-1 font-medium text-gray-700">
                    Password
                    <span class="text-gray-400 text-xs font-normal">
                        (Min 8 chars, 1 uppercase, 1 lowercase, 1 number)
                    </span>
                </span>

                <input type="password" name="password" required autocomplete="new-password"
                    class="w-full border border-gray-300 rounded-xl p-3 pr-10 password-input focus:ring-2 focus:ring-pink-300 outline-none">

                <button type="button"
                    class="absolute right-3 top-10 text-gray-400 toggle-password">
                    <i class="far fa-eye-slash"></i>
                </button>

                {{-- PASSWORD RULE UI --}}
                <div class="password-rules text-xs mt-2 space-y-1">
                    <p data-rule="length"    class="text-gray-400">At least 8 characters</p>
                    <p data-rule="uppercase" class="text-gray-400">One uppercase letter</p>
                    <p data-rule="lowercase" class="text-gray-400">One lowercase letter</p>
                    <p data-rule="number"    class="text-gray-400">One number</p>
                </div>
            </label>

            {{-- Confirm Password --}}
            <label class="flex flex-col relative">
                <span class="mb-1 font-medium text-gray-700">Confirm Password</span>

                <input type="password" name="password_confirmation" required autocomplete="new-password"
                    class="w-full border border-gray-300 rounded-xl p-3 pr-10 password-input focus:ring-2 focus:ring-pink-300 outline-none">

                <button type="button"
                    class="absolute right-3 top-10 text-gray-400 toggle-password">
                    <i class="far fa-eye-slash"></i>
                </button>

                <p class="text-red-500 text-xs mt-1 hidden password-error">
                    Passwords do not match
                </p>
            </label>

        </div>

        <div class="flex gap-3">
            <button type="button"
                class="back-btn w-1/2 bg-gray-200 hover:bg-gray-300
                       text-gray-700 font-medium py-3 rounded-xl transition">
                Back
            </button>

            <button type="submit"
                class="w-1/2 bg-pink-500 hover:bg-pink-600
                       text-white font-semibold py-3 rounded-xl transition">
                Create Account
            </button>
        </div>
    </div>

</form>

{{-- ================= OTP MODAL ================= --}}
{{-- ✅ Must be OUTSIDE <form> but in the same blade so register.js can find it --}}
<div id="otpModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl mx-4">
        <h2 class="text-lg font-semibold text-center mb-2">Phone Verification</h2>
        <p class="text-sm text-gray-500 text-center mb-4">
            Enter the 6-digit OTP sent to your phone
        </p>

        <input
            type="text"
            id="otpInput"
            maxlength="6"
            inputmode="numeric"
            class="w-full border border-gray-300 rounded-xl p-3 text-center text-lg tracking-widest focus:ring-2 focus:ring-pink-300 outline-none"
            placeholder="000000">

        <p id="otpError" class="text-red-500 text-sm mt-2 hidden text-center"></p>

        <p id="otpTimer"
            data-duration="300"
            class="text-gray-500 text-sm mt-2 text-center">
            OTP expires in: 05:00
        </p>

        <button type="button"
            id="resendOtpBtn"
            class="w-full mt-3 text-pink-500 hover:text-pink-600 text-sm font-medium">
            Resend OTP
        </button>

        <div class="flex gap-3 mt-4">
            <button type="button"
                id="closeOtpModal"
                class="w-1/2 bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 rounded-xl">
                Cancel
            </button>

            <button type="button"
                id="verifyOtpBtn"
                class="w-1/2 bg-pink-500 hover:bg-pink-600 text-white py-2 rounded-xl">
                Verify
            </button>
        </div>
    </div>
</div>

@vite('resources/js/register.js')