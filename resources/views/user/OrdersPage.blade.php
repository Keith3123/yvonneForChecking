@extends('layouts.app')

@section('no-footer')
@endsection

<!-- J
@section('content')
<div id="cancelModal" class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/40 backdrop-blur-sm">

    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6 text-center">

        <!-- ICON -->
        <div class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-red-100 mb-4">
            <i class="fas fa-trash text-red-500 text-lg"></i>
        </div>

        <!-- TITLE -->
        <h2 class="text-lg font-semibold text-gray-800">
            Cancel Order?
        </h2>

        <!-- MESSAGE -->
        <p class="text-sm text-gray-500 mt-2">
            Are you sure you want to cancel this order? This action cannot be undone.
        </p>

        <!-- BUTTONS -->
        <div class="flex gap-3 mt-6">
            <button onclick="closeCancelModal()"
                class="flex-1 py-2.5 rounded-lg bg-gray-100 text-gray-600 font-medium hover:bg-gray-200 transition">
                Cancel
            </button>

            <button onclick="confirmCancelOrder()"
                class="flex-1 py-2.5 rounded-lg bg-red-500 text-white font-semibold hover:bg-red-600 shadow-md transition">
                Yes, Cancel
            </button>
        </div>

    </div>
</div>

<div class="bg-gradient-to-b from-[#FFF8F5] to-[#FFEDEA] min-h-screen flex justify-center py-12 px-4 overflow-y-auto">
    <div class="w-full max-w-5xl">

        {{-- Header --}}
        <div class="mb-10 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">My Orders</h2>
                <p class="text-gray-600 text-sm mt-1">
                    Track the status of your orders and view order history
                </p>
            </div>
            <a href="{{ route('catalog') }}" 
               class="inline-flex items-center justify-center bg-orange-300 hover:bg-orange-400 text-gray-900 px-5 py-2.5 rounded-lg font-semibold text-sm shadow transition">
                Go to Catalog
            </a>
        </div>

        {{-- Orders List --}}
        <div class="space-y-8">
            @forelse($orders as $order)
                <div class="bg-white rounded-xl shadow-md border border-red-100 p-6 md:p-8 relative {{ in_array($order->status, ['Done', 'Cancelled']) ? 'opacity-75 grayscale-[0.5]' : '' }}">

                    {{-- Order Header --}}
                    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-6">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800">
                                Order #{{ $order->orderID }}
                            </h2>
                            <p class="text-sm text-gray-500">
                                Placed on {{ $order->orderDate->timezone('Asia/Manila')->format('F d, Y \a\t h:i A') }}
                            </p>
                        </div>

                        <span class="text-xs font-semibold px-4 py-1.5 rounded-full
                            @switch($order->status)
                                @case('Done') bg-green-100 text-green-700 ring-1 ring-green-200 @break
                                @case('Cancelled') bg-red-100 text-red-700 ring-1 ring-red-200 @break
                                @default bg-yellow-100 text-yellow-700 ring-1 ring-yellow-200
                            @endswitch">
                            {{ $order->status }}
                        </span>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="mb-6">
                        @php
                            $statusSteps = ['Pending', 'Confirmed', 'Preparing', 'Out for Delivery', 'Done'];
                            $currentIndex = array_search($order->status, $statusSteps);
                            $currentIndex = $currentIndex === false ? 0 : $currentIndex;
                            $progressWidth = $currentIndex / (count($statusSteps) - 1) * 100;
                        @endphp

                        <div class="relative w-full">
                            <div class="w-full h-2 bg-gray-200 rounded-full"></div>
                            <div class="absolute top-0 left-0 h-2 bg-gradient-to-r from-green-400 to-green-600 rounded-full transition-all duration-500"
                                style="width: {{ $progressWidth }}%;"></div>

                            {{-- Dots --}}
                            <div class="absolute top-1/2 left-0 w-full flex justify-between transform -translate-y-1/2">
                                @foreach($statusSteps as $index => $step)
                                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center
                                        {{ $currentIndex >= $index ? 'bg-green-500 border-green-500' : 'bg-white border-gray-300' }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Labels --}}
                        <div class="flex justify-between text-xs text-gray-500 mt-4">
                            @foreach($statusSteps as $step)
                                <span>{{ $step }}</span>
                            @endforeach
                        </div>
                    </div>

                    {{-- Order Items --}}
                    <div class="space-y-5">
                        @foreach($order->orderItems as $item)
                            <div class="flex gap-4 items-center">
                                @php
                                    $productImageURL = $item->product->imageURL ?? null;
                                @endphp
                                <img src="{{ $productImageURL ? asset('storage/products/'.$productImageURL) : asset('images/sample_food.jpg') }}"
                                     alt="Food Package"
                                     class="w-24 h-24 object-cover rounded-lg border shadow-sm">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-800">{{ $item->product->name ?? 'Product Name' }}</h3>
                                    <p class="text-sm text-gray-500">Quantity: {{ $item->qty }}</p>
                                </div>
                                <div class="text-right font-semibold text-gray-800">
                                    ₱{{ $item->subtotal }}
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Delivery & Payment Info --}}
                    <div class="grid md:grid-cols-2 gap-6 border-t border-gray-200 pt-6 mt-6">
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Delivery Information</h4>
                            <p class="text-sm text-gray-600 mb-1">📅 {{ $order->deliveryDate ? $order->deliveryDate->format('F d, Y \a\t h:i A') : 'Not Set' }}</p>
                            <p class="text-sm text-gray-600">📍 {{ $order->deliveryAddress }}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Payment Information</h4>
                            <div class="text-sm text-gray-700 space-y-1">
                                 @php
                                $vatRate = 0.12;
                                // The sum of items is the inclusive Total
                                $totalAmount = $order->orderItems->sum('subtotal'); 
                                
                                // Extract VATable Sales (Subtotal)
                                $subtotal = round($totalAmount / (1 + $vatRate), 2);
                                
                                // Extract VAT Amount
                                $vatAmount = round($totalAmount - $subtotal, 2);
                            @endphp

                            <p class="text-gray-500">VATable Sales 
                                <span class="float-right font-semibold text-gray-800">₱{{ number_format($subtotal, 2) }}</span>
                            </p>
                            <p class="text-gray-500">VAT 
                                <span class="float-right font-semibold text-gray-800">₱{{ number_format($vatAmount, 2) }}</span>
                            </p>
                            <div class="border-t border-dashed pt-2 mt-2">
                                <p class="text-base font-bold text-gray-900">Total Amount Due 
                                    <span class="float-right">₱{{ number_format($totalAmount, 2) }}</span>
                                </p>

                            </div>
                        </div>
                    </div>
                     </div>

                    {{-- Action Buttons --}}
                    <div class="flex flex-wrap gap-3 mt-6">
                        {{-- View Receipt --}}
                        <button onclick="openReceiptModal('{{ $order->orderID }}')" 
                            class="bg-gray-900 text-white text-sm font-semibold px-6 py-2.5 rounded-lg hover:bg-gray-800 shadow transition">
                            <i class="far fa-file-alt mr-2"></i> View Receipt
                        </button>

                        @if($order->status === 'Pending')
                            <button 
    onclick="openCancelModal('{{ $order->orderID }}')" 
    class="border border-red-300 text-red-600 text-sm font-semibold px-6 py-2.5 rounded-lg hover:bg-red-50 transition">
    <i class="fas fa-times mr-2"></i> Cancel Order
</button>
                        @endif

                        @if($order->status === 'Done')
                            <button onclick="rateOrder('{{ $order->orderID }}')" 
                                class="bg-yellow-500 text-white text-sm font-semibold px-6 py-2.5 rounded-lg hover:bg-yellow-600 shadow transition">
                                <i class="fas fa-star mr-2"></i> Rate
                            </button>
                        @endif
                    </div>

                </div>

                {{-- Receipt Modal --}}
               <div id="receiptModal-{{ $order->orderID }}" class="fixed inset-0 bg-black/50 hidden z-[60] flex items-center justify-center p-4">
                    <div class="bg-white rounded-xl w-full max-w-md p-6 max-h-[90vh] overflow-y-auto shadow-2xl">

                        {{-- Close Button --}}
                        <div class="flex justify-end">
                            <button onclick="closeReceiptModal('{{ $order->orderID }}')" class="text-xl font-bold">&times;</button>
                        </div>

                        {{-- Receipt Content --}}
                        <div class="text-center mb-5">
                            <div class="text-xl font-bold">Yvonne's Cakes & Pastries</div>
                            <div class="text-sm text-gray-500">Bacaca Road</div>
                            <div class="text-sm text-gray-500">Davao City</div>
                            <div class="text-sm text-gray-500">Phone: 0912-345-6789</div>
                            <div class="border-t mt-4 pt-4">
                                <div class="text-center border-b border-dashed pb-4 mb-4">
                                    <h3 class="text-xl font-bold text-gray-800">OFFICIAL RECEIPT</h3>
                                    <p class="text-xs text-gray-500 uppercase tracking-widest">Order #{{ $order->orderID }}</p>
                                    <p class="text-xs text-gray-400">{{ $order->orderDate->format('F d, Y \a\t h:i A') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 mb-4">
                            <div class="flex justify-between text-sm">
                                <span>Payment</span>
                                <span class="font-semibold text-right">{{ strtoupper($order->payment->method ?? 'COD') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span>Delivery</span>
                                <span class="font-semibold text-right">{{ $order->deliveryDate ? $order->deliveryDate->format('Y-m-d h:i A') : 'Not Set' }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span>Address</span>
                                <span class="font-semibold text-right text-xs">{{ $order->deliveryAddress }}</span>
                            </div>
                        </div>

                        <hr class="my-3">

                        {{-- Items --}}
                        <div class="text-xs">
                            <div class="flex font-semibold text-xs">
                                <span class="w-1/2">Item</span>
                                <span class="w-1/4 justify">Qty</span>
                                <span class="w-1/4 text-right">Total</span>
                            </div>
                            <div class="space-y-3 mb-6">
                                @foreach($order->orderItems as $item)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">{{ $item->qty }}x {{ $item->product->name ?? 'Product' }}</span>
                                        <span class="font-medium text-gray-800">₱{{ number_format($item->subtotal, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <hr class="my-3">

                        {{-- Totals --}}
                         <div class="border-t border-dashed pt-4 space-y-2">
                            @php
                                $vatRate = 0.12;
                                $totalAmount = $order->orderItems->sum('subtotal');
                                $vatableSales = round($totalAmount / (1 + $vatRate), 2);
                                $vatAmount = round($totalAmount - $vatableSales, 2);
                            @endphp

                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">VATable Sales</span>
                                <span class="text-gray-800">₱{{ number_format($vatableSales, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">VAT</span>
                                <span class="text-gray-800">₱{{ number_format($vatAmount, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold text-gray-900 pt-2 border-t">
                                <span>TOTAL</span>
                                <span>₱{{ number_format($totalAmount, 2) }}</span>
                            </div>
                        </div>

                                    {{-- Footer Info --}}
                        <div class="mt-6 pt-4 border-t text-center text-xs text-gray-400">
                            <p class="mt-1">Thank you for ordering!</p>
                        </div>
                                {{-- EXPORT --}}
                        <div class="flex justify-center mt-4">
                            <a href="{{ route('orders.receipt.pdf', $order->orderID) }}"
                            class="bg-white text-gray-700 border border-gray-300 px-4 py-2 rounded-lg hover:bg-gray-300 text-sm font-semibold transition flex items-center">
                                <i class="fas fa-file-pdf mr-2"></i>
                                Export PDF
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Rate Order Modal --}}
                <div id="rateModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
                    <div class="bg-white w-full max-w-md rounded-2xl shadow-lg p-6 relative">
                        
                        {{-- Close Button --}}
                        <button onclick="closeRateModal()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>

                        <h2 class="text-xl font-bold mb-4">Rate Your Order</h2>

                        <form id="rateForm" action="{{ route('rate.order') }}" method="POST">
                            @csrf
                            <input type="hidden" name="orderID" id="rateOrderID">
                            <input type="hidden" name="rating" id="ratingValue">

                            {{-- Star Rating --}}
                            <div class="flex justify-center mb-4 space-x-2 text-2xl">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star cursor-pointer text-gray-300 star" data-value="{{ $i }}"></i>
                                @endfor
                            </div>

                            {{-- Comment --}}
                            <textarea name="comment" rows="4"
                                class="w-full border rounded-lg p-3 text-sm focus:ring focus:ring-yellow-200"
                                placeholder="Leave a comment or testimonial..."></textarea>

                            {{-- Submit RATE--}}
                            <button type="submit"
                                class="w-full mt-4 bg-yellow-500 text-white py-2 rounded-lg hover:bg-yellow-600 transition font-semibold">
                                Submit Rating
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-20">You have not placed any orders yet.</p>
            @endforelse
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
     function openReceiptModal(id) {
        document.getElementById('receiptModal-' + id).classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Prevent scroll
    }

    function closeReceiptModal(id) {
        document.getElementById('receiptModal-' + id).classList.add('hidden');
        document.body.style.overflow = 'auto'; // Restore scroll
    }

    // function updateStatus(orderID, status) {
    //     fetch(`/orders/${orderID}/update-status`, {
    //         method: 'POST',
    //         headers: {
    //             'Content-Type': 'application/json',
    //             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    //         },
    //         body: JSON.stringify({ status })
    //     })
    //     .then(res => res.json())
    //     .then(data => {
    //         alert(data.message);
    //         if (data.status === 'success') {
    //             location.reload();
    //         }
    //     })
    //     .catch(err => {
    //         console.error(err);
    //         alert('Something went wrong.');
    //     });
    // }

    // function cancelOrder(orderID) {
    // fetch(`/orders/${orderID}/cancel`, {
    //     method: 'POST',
    //     headers: {
    //         'Content-Type': 'application/json',
    //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    //     }
    // })
    // .then(res => res.json())
    // .then(data => {
    //      showToast(data.message, data.status === 'success' ? 'success' : 'error');

    //     if (data.status === 'success') {
    //         setTimeout(() => location.reload(), 1500);
    //     }
        
    // })
    // .catch(err => {
    //     console.error(err);
    //     showToast('Something went wrong.', 'error');
    // });
    // }

    function rateOrder(orderID) {
        document.getElementById('rateOrderID').value = orderID;
        document.getElementById('rateModal').classList.remove('hidden');
        document.getElementById('rateModal').classList.add('flex');
    }

    function closeRateModal() {
        document.getElementById('rateModal').classList.add('hidden');
        document.getElementById('rateModal').classList.remove('flex');

        resetStars();
    }

    const stars = document.querySelectorAll('.star');
    const ratingInput = document.getElementById('ratingValue');

    stars.forEach(star => {
        star.addEventListener('click', function () {
            let value = this.getAttribute('data-value');
            ratingInput.value = value;

            stars.forEach(s => {
                s.classList.remove('text-yellow-400');
                s.classList.add('text-gray-300');
            });

            for (let i = 0; i < value; i++) {
                stars[i].classList.remove('text-gray-300');
                stars[i].classList.add('text-yellow-400');
            }
        });
    });

    function resetStars() {
        stars.forEach(s => {
            s.classList.remove('text-yellow-400');
            s.classList.add('text-gray-300');
        });
        ratingInput.value = '';
    }
    document.getElementById('rateForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);

    fetch("{{ route('rate.order') }}", {
        method: "POST",
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            closeRateModal();
            showToast(data.message, 'success');

            // OPTIONAL: disable rate button after success
            // setTimeout(() => location.reload(), 1200);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Something went wrong.', 'error');
    });
});
//     function showToast(message, type = 'success') {
//     const container = document.getElementById('toast-container');

//     const toast = document.createElement('div');

//     let bgColor = 'bg-[#10a345]'; // Exact green from screenshot
//     let iconColor = 'text-[#10a345]'; // Green icon inside white circle

//     if (type === 'error') {
//         bgColor = 'bg-red-600';
//         iconColor = 'text-red-600';
//     }

//     // Fully rounded (pill) shape and centered text
//     toast.className = `
//         ${bgColor} text-white px-8 py-3 rounded-full shadow-lg 
//         flex items-center gap-3 font-semibold 
//         animate-slideDown transition-all duration-500
//     `;

//     toast.innerHTML = `
//         <!-- White Circular Icon Box -->
//         <div class="flex-shrink-0 w-6 h-6 bg-white rounded-full flex items-center justify-center">
//             <svg class="w-4 h-4 ${iconColor}" fill="none" stroke="currentColor" stroke-width="4" viewBox="0 0 24 24">
//                 <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
//             </svg>
//         </div>
//         <!-- Success Message -->
//         <span class="text-[15px] tracking-wide">${message}</span>
//     `;

//     container.appendChild(toast);

//     // Fade out and remove
//     setTimeout(() => {
//         toast.classList.add('opacity-0', '-translate-y-4');
//         setTimeout(() => toast.remove(), 500);
//     }, 3000);
// }
let selectedOrderID = null;

function openCancelModal(orderID) {
    selectedOrderID = orderID;
    const modal = document.getElementById('cancelModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeCancelModal() {
    const modal = document.getElementById('cancelModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function confirmCancelOrder() {
    if (!selectedOrderID) return;

    fetch(`/orders/${selectedOrderID}/cancel`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(res => res.json())
    .then(data => {
        closeCancelModal();

        showToast(data.message, data.status === 'success' ? 'success' : 'error');

        if (data.status === 'success') {
            setTimeout(() => location.reload(), 1500);
        }
    })
    .catch(err => {
        console.error(err);
        closeCancelModal();
        showToast('Something went wrong.', 'error');
    });
}
</script>
@endsection