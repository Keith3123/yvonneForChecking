<section id="products" class="py-16 bg-gray-100">
  <div class="max-w-6xl mx-auto px-6 animate-fade-up">
    <h2 class="text-3xl font-bold text-center mb-10">
        Menu
    </h2>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">

      @if ($featuredProducts->isEmpty())
        <div class="col-span-full text-center text-gray-500">
            <p>No products available for this category.</p>
        </div>
      @else

        @foreach ($featuredProducts as $product)

            @if (strtolower($product['productType']) === 'paluwagan')

                <div class="paluwagan-card bg-white rounded-lg shadow
                            transition-all duration-300
                            hover:-translate-y-2 hover:shadow-2xl
                            cursor-pointer overflow-hidden">

                    <img src="{{ asset($product['imageURL']) }}"
                         class="w-full h-40 object-cover
                                transition-transform duration-500 hover:scale-105">

                    <div class="p-4">
                        <h3 class="font-semibold text-lg text-center">
                            {{ $product['name'] }}
                        </h3>
                        <p class="text-pink-600 font-semibold">
                          {{ $product['price'] ?? ($product['servings'][0]['price'] ?? 'N/A') }}
                        </p>
                    </div>
                </div>

            @else

                <div 
                    onclick="window.location.href='{{ session('logged_in_user') ? route('catalog') : route('register') }}'"
                    class="bg-white rounded-lg shadow
                           transition-all duration-300
                           hover:-translate-y-2 hover:shadow-2xl
                           cursor-pointer overflow-hidden">

                    <img src="{{ asset($product['imageURL']) }}"
                         class="w-full h-40 object-cover
                                transition-transform duration-500 hover:scale-105">

                    <div class="p-4">
                        <h3 class="font-semibold text-lg text-center">
                            {{ $product['name'] }}
                        </h3>
                        <p class="text-pink-600 font-semibold">
                          {{ $product['price'] ?? ($product['servings'][0]['price'] ?? 'N/A') }}
                        </p>
                    </div>
                </div>

            @endif

        @endforeach

      @endif

    </div>
  </div>
</section>
