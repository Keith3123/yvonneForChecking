{{-- Login Form --}}
<form action="{{ route('login.store') }}" method="POST"
    class="space-y-6 text-sm"
    id="loginForm" novalidate>
    @csrf

    <input type="hidden" name="redirect_to" value="{{ route('checkout') }}">

    {{-- Fields --}}
    <div class="space-y-4">

        {{-- Username --}}
        <label class="flex flex-col">
            <span class="mb-1 font-medium text-gray-700">Username</span>
            <input type="text" name="username" required
                placeholder="Enter your username"
                class="w-full border border-gray-300 rounded-xl p-3
                focus:ring-2 focus:ring-pink-300 focus:border-pink-300 outline-none transition">
        </label>

        {{-- Password --}}
        <label class="flex flex-col relative">
            <span class="mb-1 font-medium text-gray-700">Password</span>
            <input type="password" name="password" required
                placeholder="Enter your password"
                class="w-full border border-gray-300 rounded-xl p-3 pr-10
                focus:ring-2 focus:ring-pink-300 focus:border-pink-300 outline-none password-input transition">

            <button type="button"
                class="absolute right-3 top-10 text-gray-400 hover:text-pink-500 toggle-password">
                <i class="far fa-eye-slash"></i>
            </button>
        </label>

    </div>

    {{-- Submit --}}
    <button type="submit"
        id="loginBtn"
        class="w-full bg-pink-500 hover:bg-pink-600 text-white font-semibold py-3 rounded-xl transition flex items-center justify-center gap-2">

        <span id="loginText">Login</span>

        <svg id="loginSpinner"
            class="hidden w-5 h-5 animate-spin text-white"
            xmlns="http://www.w3.org/2000/svg"
            fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10"
                stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
        </svg>
    </button>

    {{-- Info --}}
    <div class="flex items-start gap-2 text-xs text-gray-500">
        <span class="text-yellow-400 text-base">💡</span>
        <p>Order ahead of time with a 2–3 days reservation for normal orders.</p>
    </div>
</form>

@vite('resources/js/login.js')