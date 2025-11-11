<section class="relative h-[90vh] flex items-center justify-center text-center overflow-hidden">
    <img src="{{ asset('images/homePage.jpg') }}" 
         alt="Yvonne's Cakes Background" 
         class="absolute inset-0 w-full h-full object-cover z-0">

    <div class="absolute inset-0 bg-black/40 z-10"></div>

    <div class="relative z-20 text-white px-4">
        <h1 class="text-4xl md:text-6xl font-extrabold mb-6">
            Welcome to <span class="text-pink-300">Yvonne's Cakes & Pastries</span>
        </h1>

        @php $user = session('logged_in_user'); @endphp

        @if(!$user)
            <a href="{{ route('register') }}" 
               class="bg-pink-400 hover:bg-pink-500 text-white font-bold px-8 py-3 rounded-lg text-lg transition">
               Register
            </a>
        @else
            <a href="{{ route('catalog') }}" 
               class="bg-yellow-400 hover:bg-yellow-500 text-black font-bold px-8 py-3 rounded-lg text-lg transition">
               Browse More
            </a>
        @endif
    </div>
</section>
