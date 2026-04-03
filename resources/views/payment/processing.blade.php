@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full text-center">
        <h2 class="text-xl font-semibold mb-2">Processing Payment</h2>
        <p class="text-gray-600 mb-4">
            We are verifying your payment. Your order will appear shortly.
        </p>
        <div class="flex justify-center">
            <svg class="animate-spin h-8 w-8 text-pink-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
        </div>
    </div>
</div>

<script>
const sourceId = "{{ $sourceId }}";

async function checkStatus() {
    try {
        const res = await fetch(`/checkout/status/${sourceId}`);
        const data = await res.json();

        if (data.status === 'paid') {
            window.location.href = "{{ route('checkout.payment.success') }}";
        } else if (data.status === 'failed') {
            window.location.href = "{{ route('checkout.payment.failed') }}";
        } else {
            setTimeout(checkStatus, 3000);
        }
    } catch (err) {
        setTimeout(checkStatus, 5000);
    }
}

checkStatus();
</script>
@endsection