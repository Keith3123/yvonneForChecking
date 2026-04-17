@forelse($testimonials as $testimonial)
    @php
        $customer   = $testimonial->order->customer ?? null;
        $orderItems = $testimonial->order->orderItems ?? collect();
    @endphp

    <div class="group bg-white rounded-xl shadow-md p-6 
        {{-- Exact Size Lock --}}
        w-[380px] min-w-[380px] max-w-[380px] 
        h-[340px] min-h-[340px] max-h-[340px] 
        {{-- Layout --}}
        flex flex-col flex-shrink-0 overflow-hidden
        hover:scale-105 duration-300">

        <div class="flex-grow flex flex-col overflow-hidden">
            {{-- Stars --}}
            <div class="text-yellow-400 mt-3 mb-3 text-lg">
                @for ($i = 0; $i < $testimonial->rating; $i++)
                    <i class="fas fa-star"></i>
                @endfor
                @for ($i = $testimonial->rating; $i < 5; $i++)
                    <i class="far fa-star"></i>
                @endfor
            </div>

            {{-- Username --}}
            <div class="font-bold text-lg text-gray-800">
                {{ $customer ? $customer->username : 'Anonymous' }}
            </div>

            {{-- Product Badges --}}
            @if($orderItems->isNotEmpty())
                <div class="mt-2 flex flex-wrap justify-start gap-1">
                    @foreach($orderItems as $item)
                        @php
                            $product  = $item->product ?? null;
                            $category = $product->productType ?? null;
                        @endphp
                        @if($product)
                            <span class="inline-block bg-pink-100 text-pink-800 text-xs font-medium px-2.5 py-1 rounded-full">
                                {{ $category ? $category->productType . ' · ' . $product->name : $product->name }}
                            </span>
                        @endif
                    @endforeach
                </div>
            @endif

            {{-- Comment --}}
            <div class="mt-3 overflow-y-auto px-2 custom-scrollbar-hidden flex-grow">
                <p class="mt-3 mx-3 text-base text-gray-600 italic">
                    "{{ $testimonial->comment }}"
                </p>
            </div>
            {{-- Date --}}
            <p class="mt-3 mb-2 text-sm text-gray-400">
                {{ $testimonial->created_at->format('M d, Y') }}
            </p>
        </div>
    </div>

    <style>
        /* 1. Hide scrollbar by default for Chrome, Safari and Opera */
        .custom-scrollbar-hidden::-webkit-scrollbar {
            width: 4px;
            background: transparent;
        }

        .custom-scrollbar-hidden::-webkit-scrollbar-thumb {
            background: transparent;
            border-radius: 10px;
            transition: background 0.3s ease;
        }

        /* 2. Show scrollbar on group hover */
        .group:hover .custom-scrollbar-hidden::-webkit-scrollbar-thumb {
            background: #f472b6; /* Tailwind pink-400 */
        }

        /* 3. Logic for Firefox */
        .custom-scrollbar-hidden {
            scrollbar-width: none; /* Hide by default */
        }

        .group:hover .custom-scrollbar-hidden {
            scrollbar-width: thin;
            scrollbar-color: #f472b6 transparent;
        }
    </style>
@empty
    <p class="text-gray-400 text-lg mt-10 w-full text-center">No testimonials found.</p>
@endforelse