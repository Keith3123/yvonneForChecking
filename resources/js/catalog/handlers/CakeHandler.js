export default class CakeHandler {
    constructor(cartService) {
        this.cartService = cartService;
    }

    populateModal(card, modal) {
        let idInput = modal.querySelector('#cake-id');
        if (!idInput) {
            idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.id = 'cake-id';
            modal.appendChild(idInput);
        }
        idInput.value = card.dataset.id;

        const customizationCard = modal.querySelector('#cake-customization');
        const imageEl = modal.querySelector('#cake-image');
        const isCustomization = card.dataset.customization === 'true';

        modal.querySelector('#cake-name').textContent = card.dataset.name;

        if (isCustomization) {
            customizationCard.classList.remove('hidden');
            imageEl.classList.add('hidden');

            const sizeSelect = modal.querySelector('#cake-size');
            const flavorSelect = modal.querySelector('#cake-flavor');
            const shapeSelect = modal.querySelector('#cake-shape');
            const icingSelect = modal.querySelector('#cake-icing');

            sizeSelect.innerHTML = '';
            flavorSelect.innerHTML = '';
            shapeSelect.innerHTML = '';
            icingSelect.innerHTML = '';

            const servings = JSON.parse(card.dataset.servings || '[]');
            servings.forEach(s => {
                if (s.size) sizeSelect.innerHTML += `<option value="${s.price}">${s.size}</option>`;
                if (s.flavor) flavorSelect.innerHTML += `<option value="${s.flavor}">${s.flavor}</option>`;
                if (s.shape) shapeSelect.innerHTML += `<option value="${s.shape}">${s.shape}</option>`;
                if (s.icing) icingSelect.innerHTML += `<option value="${s.icing}">${s.icing}</option>`;
            });

            this.setupQuantity(modal, sizeSelect, '#cake-price', '#cake-total', '#quantity-cake');
        } else {
            customizationCard.classList.add('hidden');
            imageEl.classList.remove('hidden');
            imageEl.src = card.dataset.image;

            const price = parseFloat(card.dataset.price || 0);
            modal.querySelector('#cake-price').textContent = `₱${price.toFixed(2)}`;
            this.setupQuantity(modal, { value: price }, '#cake-price', '#cake-total', '#quantity-cake');
        }

        this.bindAddToCart(modal);
    }

    setupQuantity(modal, priceSource, priceSelector, totalSelector, qtySelector) {
        let qty = 1;
        const qtyEl = modal.querySelector(qtySelector);
        const priceEl = modal.querySelector(priceSelector);
        const totalEl = modal.querySelector(totalSelector);

        const updateTotal = () => {
            const price = parseFloat(priceSource.value || priceSource) || 0;
            qtyEl.textContent = qty;
            priceEl.textContent = `₱${price.toFixed(2)}`;
            totalEl.textContent = `₱${(price * qty).toFixed(2)}`;
        };

        priceSource.addEventListener?.('change', updateTotal);

        modal.querySelector('#increase-qty-cake').onclick = () => { qty++; updateTotal(); };
        modal.querySelector('#decrease-qty-cake').onclick = () => { if (qty > 1) qty--; updateTotal(); };

        updateTotal();
    }

    bindAddToCart(modal) {
        const btn = modal.querySelector('#add-to-cart-cake');
        btn.onclick = e => {
            e.stopPropagation();

            const isCustomization = !modal.querySelector('#cake-customization').classList.contains('hidden');

            this.cartService.sendToCart({
                id: parseInt(modal.querySelector('#cake-id').value),
                name: modal.querySelector('#cake-name').textContent,
                image: modal.querySelector('#cake-image')?.src ?? null,
                price: parseFloat(modal.querySelector('#cake-price').textContent.replace('₱', '')),
                quantity: parseInt(modal.querySelector('#quantity-cake').textContent),
                productType: 'Cake',
                customization: isCustomization ? {
                    flavor: modal.querySelector('#cake-flavor option:checked')?.textContent,
                    shape: modal.querySelector('#cake-shape option:checked')?.textContent,
                    icing: modal.querySelector('#cake-icing option:checked')?.textContent,
                    message: modal.querySelector('#cake-message')?.value || null
                } : null
            });

            modal.classList.add('hidden');
        };
    }

    openModal(modal) {
        modal.classList.remove('hidden');
    }
}
