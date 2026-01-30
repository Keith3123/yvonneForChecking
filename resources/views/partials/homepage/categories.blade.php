<section class="py-6 bg-white">
    <div class="max-w-6xl mx-auto px-6">
        <div class="flex space-x-4 justify-center overflow-x-auto pb-2 no-scrollbar animate-fade-up">

            {{-- ALL CATEGORY --}}
            <a href="{{ route('home') }}#products"
               class="relative flex-shrink-0 px-5 py-3 font-medium text-pink-700
                      after:absolute after:left-1/2 after:bottom-1
                      after:h-[2px] after:w-0 after:bg-pink-500
                      after:transition-all after:duration-300
                      hover:after:w-full hover:after:left-0">
                <span class="flex items-center space-x-2">
                    <i class="{{ $categoryIcons['all'] ?? 'fas fa-tag' }}"></i>
                    <span>All</span>
                </span>
            </a>

            @foreach ($categories as $cat)
                @php
                    $iconClass = $categoryIcons[strtolower($cat->productType)] ?? 'fas fa-tag';
                @endphp

                <a href="{{ route('home', ['type' => $cat->productTypeID]) }}#products"
                   class="relative flex-shrink-0 px-5 py-3 font-medium text-pink-700
                          after:absolute after:left-1/2 after:bottom-1
                          after:h-[2px] after:w-0 after:bg-pink-500
                          after:transition-all after:duration-300
                          hover:after:w-full hover:after:left-0">
                    <span class="flex items-center space-x-2">
                        <i class="{{ $iconClass }}"></i>
                        <span>{{ $cat->productType }}</span>
                    </span>
                </a>
            @endforeach

        </div>
    </div>
</section>
