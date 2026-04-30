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
        const promo = parseFloat(card.dataset.promo || 0);

        modal.querySelector('#cake-name').textContent = card.dataset.name;

        // Show promo badge
        this.showPromoBadge(modal, promo);

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
                if (s.size) {
                    const label = promo > 0 && s.originalPrice && s.originalPrice !== s.price
                        ? `${s.size} - ₱${parseFloat(s.price).toFixed(2)} (was ₱${parseFloat(s.originalPrice).toFixed(2)})`
                        : s.size;
                    sizeSelect.innerHTML += `<option value="${s.price}">${label}</option>`;
                }
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
            const servings = JSON.parse(card.dataset.servings || '[]');
            const originalPrice = servings[0]?.originalPrice || price;

            // Show original price crossed out
            const priceEl = modal.querySelector('#cake-price');
            if (promo > 0 && originalPrice !== price) {
                priceEl.innerHTML = `<span class="line-through text-gray-400 text-sm mr-1">₱${originalPrice.toFixed(2)}</span> ₱${price.toFixed(2)}`;
            } else {
                priceEl.textContent = `₱${price.toFixed(2)}`;
            }

            this.setupQuantity(modal, { value: price }, '#cake-price', '#cake-total', '#quantity-cake');
        }

        this.bindAddToCart(modal);
    }

    showPromoBadge(modal, promo) {
        let badge = modal.querySelector('.promo-badge');
        if (!badge) {
            badge = document.createElement('div');
            badge.className = 'promo-badge mb-2';
            const nameEl = modal.querySelector('#cake-name');
            if (nameEl) nameEl.parentNode.insertBefore(badge, nameEl.nextSibling);
        }

        if (promo > 0) {
            badge.innerHTML = `<span class="inline-block bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full">${promo}% OFF</span>`;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }

    setupQuantity(modal, priceSource, priceSelector, totalSelector, qtySelector) {
        let qty = 1;
        const qtyEl = modal.querySelector(qtySelector);
        const totalEl = modal.querySelector(totalSelector);

        const updateTotal = () => {
            const price = parseFloat(priceSource.value || priceSource) || 0;
            qtyEl.textContent = qty;
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

            // Extract numeric price from potentially HTML content
            const priceText = modal.querySelector('#cake-price').textContent || modal.querySelector('#cake-price').innerText;
            const priceMatch = priceText.match(/₱([\d,.]+)$/);
            const price = priceMatch ? parseFloat(priceMatch[1].replace(',', '')) : 0;

            this.cartService.sendToCart({
                id: parseInt(modal.querySelector('#cake-id').value),
                name: modal.querySelector('#cake-name').textContent,
                image: modal.querySelector('#cake-image')?.src ?? null,
                price: price,
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