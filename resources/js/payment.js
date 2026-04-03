document.addEventListener('DOMContentLoaded', () => {

    const btn = document.getElementById('place-order-btn');
    const form = document.getElementById('checkoutForm');
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    btn?.addEventListener('click', async (e) => {
        e.preventDefault();

        const payment = document.querySelector('input[name="payment"]:checked')?.value;

        if (!payment) {
            alert('Select payment method');
            return;
        }

        const formData = new FormData(form);
        formData.append('payment', payment); // ✅ append selected payment


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
                    alert(data.message);
                    window.location.href = 'orders'; // ✅ redirect to orders page
                } else {
                    alert(data.error || 'Error');
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
                    alert(data.error || 'GCash error');
                }
            }

        } catch (err) {
            console.error(err);
            alert('Something broke. Check console.');
        }
    });

});