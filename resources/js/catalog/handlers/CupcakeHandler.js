export default class CupcakeHandler {
    constructor(cartService) { this.cartService = cartService; }

    populateModal(card, modal) {
        let id = modal.querySelector('#cupcake-id') || Object.assign(document.createElement('input'), { type:'hidden', id:'cupcake-id' });
        modal.appendChild(id);
        id.value = card.dataset.id;

        modal.querySelector('#cupcake-name').textContent = card.dataset.name;

        const promo = parseFloat(card.dataset.promo || 0);
        const customization = card.dataset.customization === 'true';
        modal.querySelector('#cupcake-customization').classList.toggle('hidden', !customization);
        modal.querySelector('#cupcake-image').classList.toggle('hidden', customization);

        if (!customization) {
            modal.querySelector('#cupcake-image').src = card.dataset.image;
        }

        // Show promo badge
        this.showPromoBadge(modal, promo);

        const price = parseFloat(card.dataset.price || 0);
        const servings = JSON.parse(card.dataset.servings || '[]');
        const originalPrice = servings[0]?.originalPrice || price;

        this.setupQuantity(modal, price, originalPrice, promo);
        this.bindAddToCart(modal);
    }

    showPromoBadge(modal, promo) {
        let badge = modal.querySelector('.promo-badge');
        if (!badge) {
            badge = document.createElement('div');
            badge.className = 'promo-badge mb-2';
            const nameEl = modal.querySelector('#cupcake-name');
            if (nameEl) nameEl.parentNode.insertBefore(badge, nameEl.nextSibling);
        }

        if (promo > 0) {
            badge.innerHTML = `<span class="inline-block bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full">${promo}% OFF</span>`;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    setupQuantity(modal, price, originalPrice, promo) {
        let qty = 1;
        const qtyEl = modal.querySelector('#quantity-cupcake');
        const totalEl = modal.querySelector('#cupcake-total');
        const priceEl = modal.querySelector('#cupcake-price');

        const update = () => {
            qtyEl.textContent = qty;
            if (promo > 0 && originalPrice !== price) {
                priceEl.innerHTML = `<span class="line-through text-gray-400 text-sm mr-1">₱${originalPrice.toFixed(2)}</span> ₱${price.toFixed(2)}`;
            } else {
                priceEl.textContent = `₱${price.toFixed(2)}`;
            }
            totalEl.textContent = `₱${(price * qty).toFixed(2)}`;
        };

        modal.querySelector('#increase-qty-cupcake').onclick = () => { qty++; update(); };
        modal.querySelector('#decrease-qty-cupcake').onclick = () => { if (qty > 1) qty--; update(); };

        update();
    }

    bindAddToCart(modal) {
        modal.querySelector('#add-to-cart-cupcake').onclick = e => {
            e.stopPropagation();

            const priceText = modal.querySelector('#cupcake-price').textContent || modal.querySelector('#cupcake-price').innerText;
            const priceMatch = priceText.match(/₱([\d,.]+)$/);
            const price = priceMatch ? parseFloat(priceMatch[1].replace(',', '')) : 0;

            this.cartService.sendToCart({
                id: parseInt(modal.querySelector('#cupcake-id').value),
                name: modal.querySelector('#cupcake-name').textContent,
                image: modal.querySelector('#cupcake-image')?.src ?? null,
                price: price,
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