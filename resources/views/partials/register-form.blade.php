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
                <input type="text" name="lastName" value="{{ old('lastName') }}" required
                    class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-pink-300 outline-none">
            </label>

            <label class="flex flex-col">
                <span class="mb-1 font-medium text-gray-700">First Name</span>
                <input type="text" name="firstName" value="{{ old('firstName') }}" required
                    class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-pink-300 outline-none">
            </label>

            <label class="flex flex-col">
                <span class="mb-1 font-medium text-gray-700">Middle Name</span>
                <input type="text" name="mi" value="{{ old('mi') }}"
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
                <input type="email" name="email" value="{{ old('email') }}" required
                    class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-pink-300 outline-none">
            </label>

            <label class="flex flex-col">
                <span class="mb-1 font-medium text-gray-700">Address</span>
                <input type="text" name="address" value="{{ old('address') }}" required
                    class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-pink-300 outline-none">
            </label>

            <label class="flex flex-col">
                <span class="mb-1 font-medium text-gray-700">Phone Number</span>
                <input type="text" name="phone" value="{{ old('phone') }}" required
                    class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-pink-300 outline-none">
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
                    id="username"
                    class="w-full border border-gray-300 rounded-xl p-3 focus:ring-2 focus:ring-pink-300 outline-none">

                <span class="text-red-500 text-xs mt-1 hidden" id="username-error">
                    Username already exists
                </span>
            </label>

            {{-- Password --}}
            <label class="flex flex-col relative">
                <span class="mb-1 font-medium text-gray-700">
                    Password
                    <span class="text-gray-400 text-xs">
                        (Min 8 chars, 1 uppercase, 1 lowercase, 1 number)
                    </span>
                </span>

                <input type="password" name="password" required
                    class="w-full border border-gray-300 rounded-xl p-3 pr-10 password-input focus:ring-2 focus:ring-pink-300 outline-none">

                <button type="button"
                    class="absolute right-3 top-10 text-gray-400 toggle-password">
                    <i class="far fa-eye-slash"></i>
                </button>
            </label>

            {{-- Confirm Password --}}
            <label class="flex flex-col relative">
                <span class="mb-1 font-medium text-gray-700">Confirm Password</span>

                <input type="password" name="password_confirmation" required
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

@vite('resources/js/register.js')