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
        formData.append('payment', payment);

        btn.disabled = true;
        btn.innerText = 'Processing...';

        try {

            let endpoint = payment === 'cod'
                ? window.checkoutRoutes.placeOrder
                : window.checkoutRoutes.paymongo;

            const res = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await res.json();

            if (!res.ok) {
                throw new Error(data.error || 'Request failed');
            }

            if (payment === 'cod') {
                showToast(data.message, 'success');
                setTimeout(() => window.location.href = '/orders', 1500);
            }

            if (payment === 'gcash') {
                if (data.checkout_url) {
                    window.location.href = data.checkout_url;
                } else {
                    throw new Error('No checkout URL returned');
                }
            }

        } catch (err) {
            console.error(err);
            showToast(err.message || 'Something went wrong', 'error');

            btn.disabled = false;
            btn.innerText = 'Place Order';
        }
    });

});