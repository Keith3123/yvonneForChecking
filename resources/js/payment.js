document.addEventListener('DOMContentLoaded', function() {
    // -------------------- PAYMENT TOGGLE --------------------
    const gcashDetails = document.getElementById('gcashDetails');
    const codNote = document.getElementById('codNote');
    const paymentRadios = document.querySelectorAll('input[name="payment"]');
    const orderTotal = document.getElementById('orderTotal');
    const checkoutForm = document.getElementById('checkoutForm');
    const placeOrderBtn = document.getElementById('place-order-btn');

    function togglePaymentDetails() {
        const checkedRadio = document.querySelector('input[name="payment"]:checked');
        const selected = checkedRadio ? checkedRadio.value : null;

        if (selected === 'gcash') {
            gcashDetails.classList.remove('hidden');
            codNote.classList.add('hidden');
        } else if (selected === 'cod') {
            gcashDetails.classList.add('hidden');
            codNote.classList.remove('hidden');
            orderTotal.textContent = document.getElementById('summaryTotal').textContent;
        } else {
            gcashDetails.classList.add('hidden');
            codNote.classList.add('hidden');
        }
    }

    // Listen to payment radio changes
    paymentRadios.forEach(radio => radio.addEventListener('change', togglePaymentDetails));
    togglePaymentDetails();

    // -------------------- PLACE ORDER AJAX --------------------
    if (placeOrderBtn) {
        placeOrderBtn.addEventListener('click', async (e) => {
            e.preventDefault(); // prevent default form submission
            placeOrderBtn.disabled = true;
            placeOrderBtn.textContent = 'Placing Order...';

            const formData = new FormData(checkoutForm);

            const paymentMethod = document.querySelector('input[name="payment"]:checked')?.value;
        
             if (paymentMethod === 'gcash') {
            const fileInput = document.querySelector('input[name="paymentProof"]');
            if (fileInput.files.length > 0) {
                formData.append('paymentProof', fileInput.files[0]);
            }

        }
            try {
                const response = await fetch(checkoutForm.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json' 
                    },
                    body: formData
                });

                // Parse JSON response
                const data = await response.json();

                if (data.success) {
                    alert(data.message || 'Order placed successfully!');
                    if (window.localStorage) localStorage.removeItem('cart');
                    window.location.href = data.redirect || '/orders';
                } else {
                    // Handle validation errors from Laravel
                    if (data.errors) {
                        let messages = Object.values(data.errors).flat().join('\n');
                        alert(messages);
                    } else {
                        alert(data.error || 'Failed to place order. Please check your inputs.');
                    }
                    placeOrderBtn.disabled = false;
                    placeOrderBtn.textContent = 'Place Order';
                }
            } catch (err) {
                console.error(err);
                alert('Network error or session expired. Please refresh and try again.');
                placeOrderBtn.disabled = false;
                placeOrderBtn.textContent = 'Place Order';
            }
        });
    }
});