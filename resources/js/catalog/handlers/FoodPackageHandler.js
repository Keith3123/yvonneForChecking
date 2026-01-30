export default class FoodPackageHandler {
    constructor(cartService) { this.cartService = cartService; }

    populateModal(card, modal) {
        let idInput = modal.querySelector('#foodpackage-id');
        if (!idInput) {
            idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.id = 'foodpackage-id';
            modal.appendChild(idInput);
        }
        idInput.value = card.dataset.id;

        modal.querySelector('#foodpackage-name').textContent = card.dataset.name;
        modal.querySelector('#foodpackage-image').src = card.dataset.image;
        this.populateDescription(modal.querySelector('#foodpackage-includes'), card.dataset.description);

        const servings = JSON.parse(card.dataset.servings || '[]');
        const price = servings[0]?.price || 0;

        modal.querySelector('#foodpackage-price').textContent = `₱${parseFloat(price).toFixed(2)}`;
        modal.querySelector('#foodpackage-total').textContent = `₱${parseFloat(price).toFixed(2)}`;

        this.setupQuantity(modal, price);
    }

    populateDescription(ulEl, description) {
        ulEl.innerHTML = '';
        if (!description) return;
        description.split('\n').forEach(line => {
            if (line.trim()) {
                const li = document.createElement('li');
                li.textContent = line.trim();
                ulEl.appendChild(li);
            }
        });
    }

    setupQuantity(modal, price) {
        let qty = 1;
        const qtyEl = modal.querySelector('#quantity-foodpackage');
        const totalEl = modal.querySelector('#foodpackage-total');

        const updateTotal = () => {
            qtyEl.textContent = qty;
            totalEl.textContent = `₱${(price * qty).toFixed(2)}`;
        };

        modal.querySelector('#increase-qty-foodpackage').onclick = () => { qty++; updateTotal(); };
        modal.querySelector('#decrease-qty-foodpackage').onclick = () => { if (qty > 1) qty--; updateTotal(); };
        updateTotal();
    }

    openModal(modal) {
        modal.classList.remove('hidden');
        modal.querySelector('#add-to-cart-foodpackage').onclick = () => {
            const productID = parseInt(modal.querySelector('#foodpackage-id').value);
            if (!productID) { console.error('Invalid productID for food package'); return; }

            const price = parseFloat(modal.querySelector('#foodpackage-price').textContent.replace('₱', ''));
            this.cartService.sendToCart({
                id: productID,
                name: modal.querySelector('#foodpackage-name').textContent,
                image: modal.querySelector('#foodpackage-image').src,
                price: price,
                quantity: parseInt(modal.querySelector('#quantity-foodpackage').textContent)
            });
            modal.classList.add('hidden');
        };
    }
}
