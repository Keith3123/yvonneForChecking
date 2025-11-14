<section class="py-16 bg-gray-100">
  <div class="max-w-6xl mx-auto px-6">
    <h2 class="text-3xl font-bold text-center mb-10">Featured Products</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-8">
      @foreach ($featuredProducts as $product)
        @include('partials.components.product-card', [
          'title' => $product['name'],
          'price' => $product['price'],
          'image' => $product['image']
        ])
      @endforeach
    </div>
  </div>
</section>
