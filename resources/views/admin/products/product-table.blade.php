@foreach ($products as $p)
<tr class="border-b hover:bg-gray-50 transition">
    <td class="p-4">
        <div class="flex items-center gap-3">
            <img src="{{ asset('storage/' . $p->imageURL) }}"
                 class="w-14 h-14 rounded-lg object-cover shadow">
            <div>
                <p class="font-semibold text-gray-800">{{ $p->name }}</p>
                <p class="text-xs text-gray-500">{{ $p->description }}</p>
            </div>
        </div>
    </td>

    <td class="p-4 whitespace-nowrap">
        <div class="inline-flex items-center gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-medium
                @if($p->productTypeID == 1) bg-pink-100 text-pink-700
                @elseif($p->productTypeID == 2) bg-purple-100 text-purple-700
                @elseif($p->productTypeID == 3) bg-yellow-100 text-yellow-700
                @elseif($p->productTypeID == 4) bg-green-100 text-green-700
                @endif">
                {{ $p->type->productType }}
            </span>
        </div>
    </td>


    <td class="p-4 font-semibold text-gray-800">
        ₱{{ number_format($p->price, 2) }}
    </td>

    <td class="p-4 whitespace-nowrap">
        @if($p->stock > 0 && $p->isAvailable == 1)
            <span class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">
                In Stock ({{ $p->stock }})
            </span>
        @elseif($p->stock == 0)
            <span class="px-3 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">
                Out of Stock
            </span>
        @else
            <span class="px-3 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">
                Unavailable
            </span>
        @endif
    </td>


    <td class="p-4">
        <div class="flex gap-2 flex-wrap">
            @if($p->sizes)
                <span class="px-2 py-1 bg-gray-100 rounded text-xs">{{ count(json_decode($p->sizes)) }} sizes</span>
            @endif

            @if($p->flavors)
                <span class="px-2 py-1 bg-gray-100 rounded text-xs">{{ count(json_decode($p->flavors)) }} flavors</span>
            @endif

            @if($p->items)
                <span class="px-2 py-1 bg-gray-100 rounded text-xs">{{ count(json_decode($p->items)) }} items</span>
            @endif
        </div>
    </td>

    <td class="p-4 text-center">
    <div class="flex items-center justify-center gap-2">

        <!-- Edit -->
        <button
            type="button"
            onclick="openEditModal({{ $p->productID }})"
            class="p-2 rounded text-pink-500 hover:text-pink-700 transition"
            aria-label="Edit product">
            <i class="fas fa-pen fa-fw"></i>
        </button>

        <!-- Delete -->
        <form method="POST"
              action="{{ route('admin.products.delete', $p->productID) }}"
              onsubmit="return confirm('Delete this product?');">
            @csrf
            @method('DELETE')

            <button
                type="submit"
                class="p-2 rounded text-red-600 hover:text-red-700 transition"
                aria-label="Delete product">
                <i class="fas fa-trash fa-fw"></i>
            </button>
        </form>

    </div>
</td>

</tr>
@endforeach
