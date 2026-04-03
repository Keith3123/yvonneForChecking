<tr id="productRow{{ $product->productID }}" class="border-b hover:bg-pink-50 transition">
    <td class="p-4 flex gap-3 items-center">
        <img src="{{ asset('storage/products/' . ($product->imageURL ?? 'default.png')) }}" 
             class="w-14 h-14 rounded-lg object-cover shadow">
        <div class="relative">
            <p class="font-semibold">{{ $product->name }}</p>
            <p class="text-xs text-gray-500">{{ $product->description ?? '' }}</p>

            @if($product->promo)
                <span class="absolute top-0 right-0 bg-red-600 text-white text-xs px-2 py-1 rounded">
                    {{ $product->promo }}% OFF
                </span>
            @endif
        </div>
    </td>

    <td class="p-4">
        <span class="px-3 py-1 rounded-full text-xs font-medium
            @if($product->productTypeID == 2) bg-purple-100 text-purple-700
            @elseif($product->productTypeID == 3) bg-yellow-100 text-yellow-700
            @elseif($product->productTypeID == 4) bg-green-100 text-green-700
            @elseif($product->productTypeID == 5) bg-pink-100 text-pink-700
            @endif">
            {{ $product->type->productType ?? 'N/A' }}
        </span>
    </td>

    <td class="p-4">
        @php
            $priceDisplay = collect($product->servings)->map(function($serving) use ($product){
                $price = $serving->price ?? 0;
                if($product->promo) $price -= ($product->promo / 100) * $price;
                return $price;
            })->min() ?? 0;
        @endphp
        ₱{{ number_format($priceDisplay, 2) }}
    </td>

    <td class="p-4">
        @if($product->isAvailable)
            <span class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">
                Available
            </span>
        @else
            <span class="px-3 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">
                Unavailable
            </span>
        @endif
    </td>

    <td class="p-4">
        <div class="flex gap-2 flex-wrap">
            @forelse($product->servings as $serving)
                <span class="px-2 py-1 bg-gray-100 rounded text-xs">
                    {{ $serving->size ?? 'Standard' }}
                </span>
            @empty
                <span class="px-2 py-1 bg-gray-100 rounded text-xs">0 servings</span>
            @endforelse
        </div>
    </td>

    <td class="p-4 text-center">
        <div class="flex gap-2 justify-center">
            <button type="button" onclick="openEditModal({{ $product->productID }})" 
                class="text-pink-500 hover:text-pink-700 p-2 rounded">
                <i class="fas fa-pen"></i>
            </button>

            <button type="button" onclick="deleteProduct('{{ $product->productID }}')" 
                class="text-red-600 hover:text-red-700 p-2 rounded">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </td>
</tr>