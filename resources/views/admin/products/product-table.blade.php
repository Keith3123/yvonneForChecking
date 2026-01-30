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

    <td class="p-4">
        <span class="px-3 py-1 rounded-full text-xs font-medium
            @if($p->productTypeID == 1) bg-pink-100 text-pink-700
            @elseif($p->productTypeID == 2) bg-purple-100 text-purple-700
            @elseif($p->productTypeID == 3) bg-yellow-100 text-yellow-700
            @elseif($p->productTypeID == 4) bg-green-100 text-green-700
            @endif">
            {{ $p->type->productType }}
        </span>
    </td>

    <td class="p-4 font-semibold text-gray-800">
        ₱{{ number_format($p->price, 2) }}
    </td>

    <td class="p-4">
        <span class="px-3 py-1 text-xs font-medium rounded-full
            @if ($p->stock > 20)
                bg-green-100 text-green-700
            @elseif($p->stock > 5)
                bg-yellow-100 text-yellow-700
            @else
                bg-red-100 text-red-700
            @endif">
            {{ $p->stock }} in stock
        </span>
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
        <div class="flex gap-4 justify-center">
            <button onclick="openEditModal({{ $p->productID }})"
                    class="text-blue-600 text-xl">✏️</button>

            <form method="POST"
                  action="{{ route('admin.products.delete', $p->productID) }}"
                  onsubmit="return confirm('Delete this product?');">
                @csrf
                @method('DELETE')
                <button class="text-red-600 text-xl">🗑️</button>
            </form>
        </div>
    </td>
</tr>
@endforeach
