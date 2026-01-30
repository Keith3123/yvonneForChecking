export default class CupcakeHandler {
    constructor(cartService) { this.cartService = cartService; }

    populateModal(card, modal) {
        let id = modal.querySelector('#cupcake-id') || Object.assign(document.createElement('input'), { type:'hidden', id:'cupcake-id' });
        modal.appendChild(id);
        id.value = card.dataset.id;

        modal.querySelector('#cupcake-name').textContent = card.dataset.name;

        const customization = card.dataset.customization === 'true';
        modal.querySelector('#cupcake-customization').classList.toggle('hidden', !customization);
        modal.querySelector('#cupcake-image').classList.toggle('hidden', customization);

        if (!customization) {
            modal.querySelector('#cupcake-image').src = card.dataset.image;
        }

        this.setupQuantity(modal, parseFloat(card.dataset.price || 0));
        this.bindAddToCart(modal);
    }

    setupQuantity(modal, price) {
        let qty = 1;
        const qtyEl = modal.querySelector('#quantity-cupcake');
        const totalEl = modal.querySelector('#cupcake-total');
        const priceEl = modal.querySelector('#cupcake-price');

        const update = () => {
            qtyEl.textContent = qty;
            priceEl.textContent = `₱${price.toFixed(2)}`;
            totalEl.textContent = `₱${(price * qty).toFixed(2)}`;
        };

        modal.querySelector('#increase-qty-cupcake').onclick = () => { qty++; update(); };
        modal.querySelector('#decrease-qty-cupcake').onclick = () => { if (qty > 1) qty--; update(); };

        update();
    }

    bindAddToCart(modal) {
        modal.querySelector('#add-to-cart-cupcake').onclick = e => {
            e.stopPropagation();

            this.cartService.sendToCart({
                id: parseInt(modal.querySelector('#cupcake-id').value),
                name: modal.querySelector('#cupcake-name').textContent,
                image: modal.querySelector('#cupcake-image')?.src ?? null,
                price: parseFloat(modal.querySelector('#cupcake-price').textContent.replace('₱', '')),
                quantity: parseInt(modal.querySelector('#quantity-cupcake').textContent),
                productType: 'Cupcake'
            });

            modal.classList.add('hidden');
        };
    }

    openModal(modal) {
        modal.classList.remove('hidden');
    }
}
