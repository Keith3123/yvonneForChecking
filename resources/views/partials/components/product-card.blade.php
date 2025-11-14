<div class="bg-pink-100 rounded-xl shadow-md hover:shadow-lg transition p-4">
  <img src="{{ $image }}" alt="{{ $title }}" class="rounded-lg mb-4 w-full h-40 object-cover">
  <h3 class="text-lg font-semibold mb-2">{{ $title }}</h3>
  <p class="text-gray-700 mb-3">{{ $price }}</p>
  <a href="{{ route('catalog') }}" 
     class="block text-center bg-yellow-400 hover:bg-yellow-500 text-black px-4 py-2 rounded-lg font-semibold">
     View Product
  </a>
</div>
