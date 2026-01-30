<section class="relative h-[80vh] flex items-center justify-center text-center overflow-hidden">
    <img src="{{ asset('images/Hero_Background_overlay.webp') }}" 
         class="absolute inset-0 w-full h-full object-cover scale-105">

    <div class="absolute inset-0 bg-black/40"></div>

    <div class="relative z-20 text-white max-w-2xl px-4 animate-fade-up">
        <h1 class="text-4xl md:text-5xl text-pink-300 font-semibold mb-4">
            Yvonne's Cakes & Pastries
        </h1>

        <p class="text-lg md:text-xl mb-6 py-4 opacity-90">
            Satisfy your cravings! Get exclusive deals and delicious food delivered to your door.
        </p>

        @php $user = session('logged_in_user'); @endphp

        @if(!$user)
            <a href="#products"
               class="inline-block bg-pink-400 px-8 py-3 rounded-lg text-lg
                      transition-all duration-300
                      hover:-translate-y-1 hover:shadow-xl hover:shadow-pink-400/40">
                Browse Products
            </a>
        @else
            <a href="{{ route('catalog') }}"
               class="inline-block bg-pink-400 text-white px-8 py-3 rounded-lg text-lg
                      transition-all duration-300
                      hover:-translate-y-1 hover:shadow-xl hover:shadow-pink-400/40">
                Order Now
            </a>
        @endif
    </div>
</section>
