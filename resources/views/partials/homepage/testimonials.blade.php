<div class="text-center mt-7 mb-4">
    <h2 class="text-3xl font-bold">
        Customer Testimonials
    </h2>
</div>

<div class="flex flex-wrap justify-center gap-8 mt-8 mb-20">
    @foreach($testimonials as $testimonial)

        @php
            $customer = $testimonial->order->customer ?? null;
        @endphp

        <div class="group bg-white rounded-xl shadow-md  p-6 w-96 text-center hover:scale-105 duration-300">

            {{-- Stars --}}
            <div class="text-yellow-400 mb-3 text-lg ">
                @for ($i = 0; $i < $testimonial->rating; $i++)
                    <i class="fas fa-star"></i>
                @endfor
                @for ($i = $testimonial->rating; $i < 5; $i++)
                    <i class="far fa-star "></i>
                @endfor
            </div>

            {{-- Username --}}
            <div class="font-bold text-lg text-gray-800 ">
                {{ $customer ? $customer->username : 'Anonymous' }}
            </div>

            {{-- Comment --}}
            <p class="mt-2 text-lg text-gray-600 italic ">
                "{{ $testimonial->comment }}"
            </p>

            {{-- Date --}}
            <p class="mt-3 text-sm text-gray-400 ">
                {{ $testimonial->created_at->format('M d, Y') }}
            </p>

        </div>
    @endforeach
</div>