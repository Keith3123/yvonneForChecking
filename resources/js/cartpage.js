document.addEventListener('DOMContentLoaded', function () {

    const cartCountEl = document.getElementById('cart-count');
    let modal = document.getElementById('product-modal');
    let quantityEl = document.getElementById('quantity');

    let modalData = {}; // dynamic product info

    function updateCartCount(count) {
        if(cartCountEl) cartCountEl.textContent = `${count} item(s) added`;
    }

    function updateModalTotal() {
        document.getElementById('modal-total').textContent = `₱ ${(modalData.price * parseInt(quantityEl.textContent)).toFixed(2)}`;
    }

    // Open modal
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('click', function () {
            modalData = {
                id: this.dataset.id,
                name: this.dataset.name,
                price: parseFloat(this.dataset.price),
                image: this.dataset.image,
                description: this.dataset.description,
            };

            document.getElementById('modal-name').textContent = modalData.name;
            document.getElementById('modal-description').textContent = modalData.description;
            document.getElementById('modal-image').src = modalData.image;
            document.getElementById('modal-price').textContent = `₱ ${modalData.price.toFixed(2)}`;
            quantityEl.textContent = 1;
            updateModalTotal();
            modal.classList.remove('hidden');
        });
    });

    // Close modal
    document.getElementById('close-modal').addEventListener('click', () => modal.classList.add('hidden'));
    document.getElementById('cancel-modal').addEventListener('click', () => modal.classList.add('hidden'));

    // Quantity buttons
    document.getElementById('increase-qty').addEventListener('click', () => {
        quantityEl.textContent = parseInt(quantityEl.textContent) + 1;
        updateModalTotal();
    });
    document.getElementById('decrease-qty').addEventListener('click', () => {
        if(parseInt(quantityEl.textContent) > 1){
            quantityEl.textContent = parseInt(quantityEl.textContent) - 1;
            updateModalTotal();
        }
    });

    // Add to cart
    document.getElementById('add-to-cart-modal').addEventListener('click', function () {
        const qty = parseInt(quantityEl.textContent);

        fetch('/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({...modalData, quantity: qty})
        })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                updateCartCount(data.cartCount);
                alert(`${modalData.name} added to cart`);
                modal.classList.add('hidden');
            }
        });
    });  

    // Category filter
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const cat = btn.dataset.category;
            document.querySelectorAll('.product-card').forEach(card => {
                if(cat === 'all' || card.dataset.category === cat){
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        });
    });

});

