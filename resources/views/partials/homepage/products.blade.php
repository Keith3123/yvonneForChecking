<section id="products" class="py-16 bg-gray-100">
  <div class="max-w-6xl mx-auto px-6 animate-fade-up">
    <h2 class="text-3xl font-bold text-center mb-10">Menu</h2>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

      @if ($featuredProducts->isEmpty())
        <div class="col-span-full text-center text-gray-500">
            <p>No products available for this category.</p>
        </div>
      @else

        @foreach ($featuredProducts as $product)
          @php
              $origPrice  = $product['servings'][0]['price'] ?? 0;
              $promo      = $product['promo'] ?? null;
              $hasPromo   = $promo && $promo > 0;
              $discounted = $hasPromo ? round($origPrice * (1 - $promo / 100), 2) : $origPrice;
              $imgSrc     = asset('images/products/' . $product['imageURL']);
              $isPaluwagan = strtolower($product['productType']) === 'paluwagan';
              $destination = session('logged_in_user') ? route('catalog') : route('register');
          @endphp

          <div
            class="relative bg-white rounded-lg shadow
                   transition-all duration-300
                   hover:-translate-y-2 hover:shadow-2xl
                   cursor-pointer overflow-hidden"
            @if(!$isPaluwagan) onclick="window.location.href='{{ $destination }}'" @endif
          >
            {{-- Promo badge --}}
            @if($hasPromo)
              <div class="absolute top-2 right-2 z-10 bg-red-500 text-white text-[11px] font-bold px-2 py-0.5 rounded-full shadow">
                {{ $promo }}% OFF
              </div>
            @endif

            {{-- Image --}}
            <img src="{{ $imgSrc }}"
                 class="w-full h-40 object-cover transition-transform duration-500 hover:scale-105"
                 onerror="this.style.background='#fce7f3'; this.onerror=null; this.src='';">

            <div class="p-4">
              <h3 class="font-semibold text-base text-center text-gray-800 mb-1">
                {{ $product['name'] }}
              </h3>

              @if($hasPromo)
                <p class="text-center text-gray-400 line-through text-sm">
                  ₱ {{ number_format($origPrice, 2) }}
                </p>
                <p class="text-pink-600 font-bold text-center text-lg">
                  ₱ {{ number_format($discounted, 2) }}
                </p>
              @else
                <p class="text-pink-600 font-semibold text-center">
                  ₱ {{ number_format($origPrice, 2) }}
                </p>
              @endif
            </div>
          </div>

        @endforeach

      @endif
    </div>
  </div>
</section>