document.addEventListener('DOMContentLoaded', () => {

    const btn = document.getElementById('place-order-btn');
    const form = document.getElementById('checkoutForm');
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    btn?.addEventListener('click', async (e) => {
        e.preventDefault();

        const payment = document.querySelector('input[name="payment"]:checked')?.value;

        if (!payment) {
            showToast('Please select a payment method', 'warning');
            return;
        }

        const formData = new FormData(form);
        formData.append('payment', payment); // ✅ append selected payment

        // Disable button to prevent multiple clicks
        btn.disabled = true;
        btn.innerText = 'Processing...';

        try {
            let res, data;

            // COD
            if (payment === 'cod') {
                res = await fetch(window.checkoutRoutes.placeOrder, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                    body: formData
                });

                data = await res.json();

                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => {
                    window.location.href = '/orders';
                    }, 1500);
                } else {
                    showToast(data.error || 'Error', 'error');
                }
            }

            // GCASH
            if (payment === 'gcash') {
                res = await fetch(window.checkoutRoutes.paymongo, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                    body: formData
                });

                data = await res.json();

                if (data.checkout_url) {
                    window.location.href = data.checkout_url;
                } else {
                    showToast(data.error || 'GCash error', 'error');
                }
            }

        } catch (err) {
            console.error(err);
            showToast('Something went wrong. Please try again.', 'error');

            // Re-enable button on error
            btn.disabled = false;
            btn.innerText = 'Place Order';

            
        }
    });

});