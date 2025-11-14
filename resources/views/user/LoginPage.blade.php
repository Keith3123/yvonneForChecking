@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col justify-start items-center pt-16 text-center">
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Login</h2>
        <p class="text-sm text-gray-500">Sign in to your account to place orders and join Paluwagan</p>
    </div>

    <div class="flex w-[280px] bg-[#f8f6f4] rounded-full mb-4 text-sm font-medium">
        <button type="button" onclick="window.location.href='{{ route('register') }}'" 
                class="w-1/2 py-2 rounded-full text-gray-600 hover:bg-gray-100 transition">Register</button>
        <button type="button" class="w-1/2 py-2 rounded-full text-pink-500 bg-[#fce7ef] font-medium">Login</button>
    </div>

    @if (session('success'))
    <div id="success-message" class="mb-4 bg-green-100 text-green-700 text-sm font-medium p-2 rounded-md shadow-sm">
        {{ session('success') }}
    </div>
    @endif

    @if ($errors->has('loginError'))
    <div class="mb-4 text-sm text-red-500 font-medium">
        {{ $errors->first('loginError') }}
    </div>
    @endif

    <form action="{{ route('login.store') }}" method="POST" 
          class="w-full max-w-sm bg-white p-6 rounded-lg shadow-md text-left" 
          id="loginForm" novalidate>
        @csrf
        <div class="flex flex-col gap-4 mb-6 text-sm font-medium">
            <label class="flex flex-col">
                <span class="mb-1">Username</span>
                <input type="text" name="username" placeholder="Enter your username*" required 
                       class="w-full border border-gray-300 rounded-lg p-2 focus:ring-1 focus:ring-pink-300 outline-none">
            </label>

            <label class="flex flex-col relative">
                <span class="mb-1">Password</span>
                <input type="password" name="password" placeholder="Enter your password*" required 
                    class="w-full border border-gray-300 rounded-lg p-2 focus:ring-1 focus:ring-pink-300 outline-none password-input">
                <button type="button" class="absolute right-2 top-8 text-gray-500 toggle-password">
                    <i class="far fa-eye"></i>
                </button>
            </label>
        </div>

        <button type="submit" 
                class="w-full bg-[#fce7ef] hover:bg-pink-200 text-gray-700 font-medium py-2 rounded-lg transition">Login</button>

        <div class="mt-4 flex items-start gap-2 text-xs text-gray-500">
            <span class="text-yellow-400 text-base">💡</span>
            <p>Order ahead of time with a 2–3 days reservation for normal orders.</p>
        </div>
    </form>
</div>

@vite('resources/js/login.js')
@endsection
