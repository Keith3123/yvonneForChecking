<div class="text-center pt-16">
    <h2 class="text-3xl font-bold mb-8">
        Customer Testimonials
    </h2>
</div>

{{-- Filters --}}
<div class="flex flex-wrap justify-center gap-4">

    {{-- Star Rating Filter --}}
    <div class="flex items-center gap-2 border border-gray-300 rounded-lg px-4 py-2 focus-within:ring-2 focus-within:ring-pink-400 focus-within:border-pink-400">
        <i class="fas fa-star text-pink-400 text-sm"></i>
        <select id="filter-rating" class="text-sm text-gray-600 focus:outline-none bg-transparent">
            <option value="">All Ratings</option>
            @for ($i = 5; $i >= 1; $i--)
                <option value="{{ $i }}">
                    {{ str_repeat('★', $i) . str_repeat('☆', 5 - $i) }} ({{ $i }} star{{ $i > 1 ? 's' : '' }})
                </option>
            @endfor
        </select>
    </div>

    {{-- Product Type Filter --}}
    <div class="flex items-center gap-2 border border-gray-300 rounded-lg px-4 py-2 focus-within:ring-2 focus-within:ring-pink-400 focus-within:border-pink-400">
        <i class="fas fa-tag text-pink-400 text-sm"></i>
        <select id="filter-category" class="text-sm text-gray-600 focus:outline-none bg-transparent">
            <option value="">All Categories</option>
            @foreach($productTypes as $type)
                <option value="{{ $type->productTypeID }}">{{ $type->productType }}</option>
            @endforeach
        </select>
    </div>

    {{-- Product Filter --}}
    <div class="flex items-center gap-2 border border-gray-300 rounded-lg px-4 py-2 focus-within:ring-2 focus-within:ring-pink-400 focus-within:border-pink-400">
        <i class="fas fa-shopping-bag text-pink-400 text-sm"></i>
        <select id="filter-product" class="text-sm text-gray-600 focus:outline-none bg-transparent">
            <option value="">All Products</option>
            @foreach($products as $product)
                <option value="{{ $product->productID }}">{{ $product->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Clear Filters --}}
    <button id="clear-filters" style="display:none;"
        class="flex items-center gap-2 border border-pink-300 text-pink-400 rounded-lg px-4 py-2 text-sm hover:bg-pink-50 transition">
        <i class="fas fa-times"></i> Clear Filters
    </button>

</div>

{{-- Carousel --}}
<div class="relative w-full max-w-7xl mx-auto mt-6 mb-10 px-10">

    {{-- Prev Button --}}
    <button id="prev-btn"
        class="absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-white border border-gray-200 shadow rounded-full w-9 h-9 flex items-center justify-center hover:bg-pink-50 transition">
        <i class="fas fa-chevron-left text-pink-400"></i>
    </button>

    {{-- Carousel Wrapper --}}
    <div class="overflow-hidden py-4">
        <div id="testimonials-track" class="flex transition-transform duration-500 ease-in-out items-stretch gap-6">
            @include('partials.homepage.testimonial-cards', ['testimonials' => $testimonials])
        </div>
    </div>

    {{-- Next Button --}}
    <button id="next-btn"
        class="absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-white border border-gray-200 shadow rounded-full w-9 h-9 flex items-center justify-center hover:bg-pink-50 transition">
        <i class="fas fa-chevron-right text-pink-400"></i>
    </button>

    {{-- Dots --}}
    <div id="testimonials-dots" class="flex justify-center gap-2 mt-4"></div>

</div>

<script>
    const track = document.getElementById('testimonials-track');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const filterRating = document.getElementById('filter-rating');
    const filterCategory = document.getElementById('filter-category');
    const filterProduct = document.getElementById('filter-product');
    const clearBtn = document.getElementById('clear-filters');

    let currentIndex = 0;

    const CARD_WIDTH = 380;
    const GAP = 24;

    function getCards() {
        return Array.from(track.children);
    }

    function buildCarousel() {
        const cards = getCards();
        const total = cards.length;

        if (total === 0) {
            track.innerHTML = '<p class="text-gray-400 w-full text-center py-10">No testimonials found.</p>';
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
            return;
        }

        // enforce consistent card size
        cards.forEach(card => {
            card.style.width = `${CARD_WIDTH}px`;
            card.style.minWidth = `${CARD_WIDTH}px`;
            card.style.maxWidth = `${CARD_WIDTH}px`;
            card.style.height = `340px`;
            card.style.flex = `0 0 ${CARD_WIDTH}px`;
        });

        track.style.justifyContent = 'flex-start';
        track.style.transform = 'translateX(0px)';
        currentIndex = 0;

        prevBtn.style.display = 'flex';
        nextBtn.style.display = 'flex';
    }

function getMaxIndex() {
    const cards = getCards();
    const total = cards.length;

    // how many "pages" we can move based on viewport
    const visible = Math.floor(track.parentElement.offsetWidth / (CARD_WIDTH + GAP));

    return Math.max(0, total - visible);
}

function goTo(index) {
    const cards = getCards();
    if (!cards.length) return;

    const maxIndex = getMaxIndex();

    // LOOPING BEHAVIOR (THIS IS THE FIX)
    if (index > maxIndex) {
        currentIndex = 0; // back to start
    } else if (index < 0) {
        currentIndex = maxIndex; // go to end
    } else {
        currentIndex = index;
    }

    const offset = currentIndex * (CARD_WIDTH + GAP);
    track.style.transform = `translateX(-${offset}px)`;
}

    // Buttons
    prevBtn.addEventListener('click', () => {
        goTo(currentIndex - 1);
    });

    nextBtn.addEventListener('click', () => {
        goTo(currentIndex + 1);
    });

    // Fetch
    function fetchTestimonials() {
        const params = new URLSearchParams({
            rating: filterRating.value,
            category: filterCategory.value,
            product: filterProduct.value,
        });

        const hasFilter = filterRating.value || filterCategory.value || filterProduct.value;
        clearBtn.style.display = hasFilter ? 'inline-block' : 'none';

        fetch(`{{ url('/filter-testimonials') }}?${params}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.text())
        .then(html => {
            track.innerHTML = html;
            buildCarousel();
        });
    }

    // Filters
    filterRating.addEventListener('change', fetchTestimonials);
    filterCategory.addEventListener('change', fetchTestimonials);
    filterProduct.addEventListener('change', fetchTestimonials);

    clearBtn.addEventListener('click', () => {
        filterRating.value = '';
        filterCategory.value = '';
        filterProduct.value = '';
        fetchTestimonials();
    });

    window.addEventListener('resize', () => {
        buildCarousel();
        goTo(currentIndex);
    });

    // init
    buildCarousel();
</script>