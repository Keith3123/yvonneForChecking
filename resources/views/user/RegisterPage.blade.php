@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col items-center pt-16 text-center bg-[#FFF8F5]">

    <div class="mb-6">
        <h2 class="text-lg font-semibold">Welcome to Yvonne’s Cakes & Pastries</h2>
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
                    <span class="mb-1">Middle Name</span>
                    <input type="text" name="mi" value="{{ old('mi') }}"
                           class="border border-gray-300 rounded-lg p-2 focus:ring-2 focus:ring-pink-300 outline-none">
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
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full border border-gray-300 rounded-lg p-2 mt-1">
                </label>

                <label>Address
                    <input type="text" name="address" value="{{ old('address') }}" required
                           class="w-full border border-gray-300 rounded-lg p-2 mt-1">
                </label>

                <label>Phone Number
                    <input type="text" name="phone" value="{{ old('phone') }}" required
                           class="w-full border border-gray-300 rounded-lg p-2 mt-1">
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

             <div class="flex flex-col gap-3 mb-6 text-sm">
                {{-- Username --}}
                <label class="flex flex-col relative">
                    <span class="mb-1">Username</span>
                    <input type="text" name="username" value="{{ old('username') }}" required
                        class="w-full border border-gray-300 rounded-lg p-2" id="username">
                    <span class="text-red-500 text-sm mt-1 hidden" id="username-error">Username already exists</span>
                </label>

                {{-- Password --}}
                <label class="flex flex-col relative">
                    <span class="mb-1">Password 
                        <span class="text-gray-400 text-xs">(Min 8 chars, 1 uppercase, 1 lowercase, 1 number)</span>
                    </span>
                    <input type="password" name="password" required
                        class="w-full border border-gray-300 rounded-lg p-2 password-input">
                    <button type="button" class="absolute right-2 top-9 text-gray-500 toggle-password">
                        <i class="far fa-eye-slash"></i>
                    </button>
                </label>

                {{-- Confirm Password --}}
                <label class="flex flex-col relative">
                    <span class="mb-1">Confirm Password</span>
                    <input type="password" name="password_confirmation" required
                        class="w-full border border-gray-300 rounded-lg p-2 password-input">
                    <button type="button" class="absolute right-2 top-9 text-gray-500 toggle-password">
                        <i class="far fa-eye-slash"></i>
                    </button>
                    <p class="text-red-500 text-xs mt-1 hidden password-error">The password confirmation does not match.</p>
                </label>

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
</div>

@vite('resources/js/register.js')
@endsection
