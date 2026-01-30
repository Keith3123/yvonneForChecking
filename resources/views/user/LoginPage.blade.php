@extends('layouts.app')

@section('no-footer')
@endsection

@section('content')
<div class="min-h-screen flex flex-col items-center pt-16 text-center bg-[#FFF8F5]">

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Login</h2>
        <p class="text-sm text-gray-500">
            Sign in to your account to place orders and join Paluwagan
        </p>
    </div>

    {{-- Tabs --}}
    <div class="flex w-[280px] bg-white rounded-full mb-5 p-1 text-sm font-medium shadow">
        <button type="button"
            onclick="window.location.href='{{ route('register') }}'"
            class="w-1/2 py-2 rounded-full text-gray-600 transition hover:bg-gray-100">
            Register
        </button>
        <button type="button"
            class="w-1/2 py-2 rounded-full bg-pink-100 text-pink-600 font-semibold">
            Login
        </button>
    </div>

    @if (session('success'))
        <div class="mb-4 bg-green-100 text-green-700 text-sm font-medium p-2 rounded-md shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->has('loginError'))
        <div class="mb-4 text-sm text-red-500 font-medium">
            {{ $errors->first('loginError') }}
        </div>
    @endif

    {{-- Form --}}
    <form action="{{ route('login.store') }}" method="POST"
          class="w-full max-w-sm bg-white p-6 rounded-xl shadow-lg text-left"
          id="loginForm" novalidate>
        @csrf

        <div class="flex flex-col gap-4 mb-6 text-sm font-medium">
            <label class="flex flex-col">
                <span class="mb-1">Username</span>
                <input type="text" name="username" required
                       placeholder="Enter your username*"
                       class="w-full border border-gray-300 rounded-lg p-2
                              focus:ring-2 focus:ring-pink-300 focus:border-pink-300 outline-none">
            </label>

            <label class="flex flex-col relative">
                <span class="mb-1">Password</span>
                <input type="password" name="password" required
                       placeholder="Enter your password*"
                       class="w-full border border-gray-300 rounded-lg p-2
                              focus:ring-2 focus:ring-pink-300 focus:border-pink-300
                              outline-none password-input">
                <button type="button"
                        class="absolute right-3 top-8 text-gray-500 hover:text-pink-500 toggle-password">
                    <i class="far fa-eye-slash"></i>
                </button>
            </label>
        </div>

        <button type="submit"
            class="w-full bg-pink-500 hover:bg-pink-600
                   text-white font-semibold py-2 rounded-lg
                   transition hover:shadow-md">
            Login
        </button>

        <div class="mt-4 flex items-start gap-2 text-xs text-gray-500">
            <span class="text-yellow-400 text-base">💡</span>
            <p>Order ahead of time with a 2–3 days reservation for normal orders.</p>
        </div>
    </form>
</div>

@vite('resources/js/login.js')
@endsection
