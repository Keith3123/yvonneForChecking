<section class="py-16 bg-gray-100">
  <div class="max-w-6xl mx-auto px-6">
    <h2 class="text-3xl font-bold text-center mb-10">Featured Products</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
      @foreach (['Fried Chicken', 'Grilled Fish', 'Pork BBQ', 'Chop Suey'] as $product)
        @include('partials.components.product-card', [
          'title' => $product,
          'price' => '₱' . rand(100, 400),
          'image' => '/images/sample-product.jpg'
        ])
      @endforeach
    </div>
  </div>
</section>
