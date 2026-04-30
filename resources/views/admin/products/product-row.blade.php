<tr id="productRow{{ $product->productID }}" data-category-id="{{ $product->productTypeID }}"
    data-available="{{ $product->isAvailable ? '1' : '0' }}" class="border-b hover:bg-pink-50/30 transition">
    {{-- Product Name + Image --}}
    <td class="p-3">
        <div class="flex gap-3 items-center">
            @php
                $imgPath = null;
                if ($product->imageURL) {
                    if (file_exists(public_path('images/products/' . $product->imageURL))) {
                        $imgPath = asset('images/products/' . $product->imageURL);
                    } elseif (file_exists(storage_path('app/public/products/' . $product->imageURL))) {
                        $imgPath = asset('storage/products/' . $product->imageURL);
                    }
                }
            @endphp

            @if($imgPath)
                <img src="{{ $imgPath }}"
                    class="w-12 h-12 rounded-lg object-cover shadow-sm border border-gray-100">
            @else
                <div class="w-12 h-12 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center">
                    <i class="fas fa-image text-gray-300 text-lg"></i>
                </div>
            @endif

            <div>
                <p class="font-semibold product-name text-gray-800">{{ $product->name }}</p>
                <p class="text-xs text-gray-400 max-w-[200px] truncate">{{ $product->description ?? 'No description' }}</p>
            </div>
        </div>
    </td>

    {{-- Category --}}
    <td class="p-3">
        @php
            $categoryColors = [
                2 => 'bg-purple-100 text-purple-700',
                3 => 'bg-yellow-100 text-yellow-700',
                4 => 'bg-green-100 text-green-700',
                5 => 'bg-pink-100 text-pink-700',
                6 => 'bg-blue-100 text-blue-700',
                7 => 'bg-orange-100 text-orange-700',
            ];
            $colorClass = $categoryColors[$product->productTypeID] ?? 'bg-gray-100 text-gray-700';
        @endphp
        <span class="product-category px-2.5 py-1 rounded-full text-xs font-medium {{ $colorClass }}">
            {{ $product->type->productType ?? $product->productType->productType ?? 'N/A' }}
        </span>
    </td>

    {{-- Servings & Prices --}}
    <td class="p-3">
        @forelse($product->servings as $serving)
            <div class="flex items-center gap-2 mb-1">
                <span class="px-2 py-0.5 bg-gray-100 rounded text-xs font-medium text-gray-600">{{ $serving->size }}</span>
                <span class="text-sm font-semibold text-gray-800">₱{{ number_format($serving->price, 2) }}</span>
                <span class="text-[10px] text-gray-400">({{ $serving->servingSize }} {{ $serving->unit }})</span>
            </div>
        @empty
            <span class="text-xs text-gray-400 italic">No servings added</span>
        @endforelse
    </td>

    {{-- Ingredients --}}
    <td class="p-3">
        @php
            $allIngs = collect();
            foreach($product->servings as $serving) {
                if($serving->ingredientsList) {
                    foreach($serving->ingredientsList as $item) {
                        if($item->ingredient) {
                            $allIngs->push($item->ingredient->name);
                        }
                    }
                }
            }
            $uniqueIngs = $allIngs->unique()->values();
        @endphp
        @if($uniqueIngs->isNotEmpty())
            <div class="flex flex-wrap gap-1">
                @foreach($uniqueIngs->take(4) as $ingName)
                    <span class="px-1.5 py-0.5 bg-orange-50 text-orange-700 rounded text-[10px] font-medium">{{ $ingName }}</span>
                @endforeach
                @if($uniqueIngs->count() > 4)
                    <span class="text-[10px] text-gray-400 font-medium">+{{ $uniqueIngs->count() - 4 }} more</span>
                @endif
            </div>
        @else
            <span class="text-xs text-gray-400 italic">No ingredients</span>
        @endif
    </td>

    {{-- Status --}}
    <td class="p-3">
        <span class="product-status px-2.5 py-1 text-xs font-medium rounded-full 
            {{ $product->isAvailable ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
            {{ $product->isAvailable ? 'Available' : 'Unavailable' }}
        </span>
    </td>

    {{-- Promo --}}
    <td class="p-3">
        @if($product->promo && $product->promo > 0)
            <span class="bg-red-100 text-red-700 px-2.5 py-1 rounded-full text-xs font-bold">
                {{ $product->promo }}% OFF
            </span>
        @else
            <span class="text-gray-300 text-xs">—</span>
        @endif
    </td>

    {{-- Actions --}}
    <td class="p-3 text-center">
        <div class="flex gap-1.5 justify-center">
            <button onclick="openEditModal({{ $product->productID }})"
                class="text-pink-500 hover:text-pink-700 p-1.5 rounded hover:bg-pink-50 transition"
                title="Edit">
            <i class="fas fa-pen text-sm"></i>
        </button>

        {{-- Toggle availability instead of delete --}}
        <button onclick="toggleAvailability({{ $product->productID }}, {{ $product->isAvailable }})"
                class="p-1.5 rounded transition {{ $product->isAvailable ? 'text-green-500 hover:text-green-700 hover:bg-green-50' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50' }}"
                title="{{ $product->isAvailable ? 'Set Unavailable' : 'Set Available' }}"
                id="toggleBtn{{ $product->productID }}">
            <i class="fas {{ $product->isAvailable ? 'fa-eye' : 'fa-eye-slash' }} text-sm"></i>
        </button>
        </div>
    </td>
</tr>