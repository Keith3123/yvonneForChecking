<div id="cart-sidebar" class="flex flex-col h-full">

    {{-- CART ITEMS --}}
    <div class="overflow-y-auto divide-y divide-gray-200 mb-4" style="max-height: calc(100vh - 160px);">
        @if(count(session('cart', [])) > 0)
            @foreach(session('cart') as $key => $item)
                <div class="flex items-center justify-between py-3">

                    <div class="flex items-center gap-3">
                        <img src="{{ $item['image'] }}" class="w-12 h-12 object-cover rounded">
                        <div>
                            <p class="font-medium">{{ $item['name'] }}</p>
                            <p class="text-sm text-gray-500">
                                ₱{{ number_format($item['price'] * $item['quantity'], 0) }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 text-sm">
                        <button type="button" class="ajax-decrease px-2"
                            data-url="{{ route('cart.update') }}"
                            data-id="{{ $key }}"
                            data-csrf="{{ csrf_token() }}">−</button>

                        <span>{{ $item['quantity'] }}</span>

                        <button type="button" class="ajax-increase px-2"
                            data-url="{{ route('cart.update') }}"
                            data-id="{{ $key }}"
                            data-csrf="{{ csrf_token() }}">+</button>

                        <button type="button" class="ajax-remove text-red-500"
                            data-url="{{ route('cart.remove') }}"
                            data-id="{{ $key }}"
                            data-csrf="{{ csrf_token() }}">×</button>
                    </div>
                </div>
            @endforeach
        @else
            <p class="text-center text-gray-400 py-10">
                Your cart is empty
            </p>
        @endif
    </div>

    {{-- TOTAL & CHECKOUT --}}
    @php
        $cart = session('cart', []);
        $cartEmpty = count($cart) === 0;
        $total = collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']);
    @endphp

    <div class="sticky top-20 flex flex-col gap-2 bg-white pt-4">
        <div class="flex justify-between font-semibold text-sm px-2">
            <span>Total</span>
            <span>₱{{ number_format($total, 0) }}</span>
        </div>

        @if($cartEmpty)
            {{-- DISABLED CHECKOUT --}}
            <div
                id="checkout-disabled"
                class="
                    bg-gray-300 text-gray-500
                    py-2 text-center rounded mt-2 px-2
                    font-semibold
                    cursor-not-allowed
                    select-none
                "
            >
                Checkout
            </div>
        @else
            {{-- ENABLED CHECKOUT --}}
            <a
                href="{{ route('checkout') }}"
                class="
                    block bg-pink-500 text-white
                    py-2 text-center rounded mt-2 px-2
                    font-semibold
                    hover:bg-pink-600
                    transition
                    cursor-pointer
                "
            >
                Checkout
            </a>
        @endif
    </div>

</div>

{{-- SHAKE ANIMATION --}}
<style>
@keyframes shake {
    0% { transform: translateX(0); }
    25% { transform: translateX(-4px); }
    50% { transform: translateX(4px); }
    75% { transform: translateX(-4px); }
    100% { transform: translateX(0); }
}
.shake {
    animation: shake 0.4s;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const checkout = document.getElementById('checkout-disabled');
    if (!checkout) return;

    checkout.addEventListener('mouseenter', () => {
        checkout.classList.add('shake');
        setTimeout(() => checkout.classList.remove('shake'), 400);
    });

    checkout.addEventListener('click', () => {
        checkout.classList.add('shake');
        setTimeout(() => checkout.classList.remove('shake'), 400);
    });
});
</script>
