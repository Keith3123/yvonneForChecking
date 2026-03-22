<div id="authModal"
    class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">

    <div class="bg-[#FFF8F5] w-full max-w-lg rounded-2xl shadow-2xl relative overflow-hidden">

        <!-- CLOSE -->
        <button onclick="closeAuthModal()"
            class="absolute top-4 right-5 text-2xl text-gray-500 hover:text-gray-700">
            &times;
        </button>

        <!-- HEADER -->
        <div class="px-8 pt-8 pb-4 mb-6 text-center">
            <h2 class="text-xl font-semibold text-gray-800">
                Welcome to Yvonne’s Cakes & Pastries
            </h2>
            <p class="text-sm text-gray-500">
                Custom cakes, pastries and food trays for every occasion
            </p>
        </div>

        <!-- TABS -->
        <div class="px-8 pb-4">
            <div class="flex bg-white rounded-full p-1 shadow-inner text-sm font-medium">
                <button onclick="showLogin()" id="loginTab"
                    class="w-1/2 py-2 rounded-full bg-pink-100 text-pink-600 font-semibold transition">
                    Login
                </button>

                <button onclick="showRegister()" id="registerTab"
                    class="w-1/2 py-2 rounded-full text-gray-600 hover:bg-gray-100 transition">
                    Register
                </button>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="px-8 pb-8">

            <div id="loginSection">
                @include('partials.login-form')
            </div>

            <div id="registerSection" class="hidden">
                @include('partials.register-form')
            </div>

        </div>

    </div>
</div>