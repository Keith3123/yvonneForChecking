    export default class FoodTrayHandler {
        constructor(cartService) { this.cartService = cartService; }

        populateModal(card, modal) {
            // hidden ID
            let hiddenId = modal.querySelector('#foodtray-id');
            if (!hiddenId) {
                hiddenId = document.createElement('input');
                hiddenId.type = 'hidden';
                hiddenId.id = 'foodtray-id';
                modal.appendChild(hiddenId);
            }
            hiddenId.value = card.dataset.id;

            modal.querySelector('#foodtray-name').textContent = card.dataset.name;
            modal.querySelector('#foodtray-image').src = card.dataset.image;
            this.populateDescription(modal.querySelector('#foodtray-includes'), card.dataset.description);

            const servings = JSON.parse(card.dataset.servings || '[]');
            const select = modal.querySelector('#foodtray-size');
            select.innerHTML = '';
            servings.forEach(s => {
                const opt = document.createElement('option');
                opt.value = s.price;
                opt.textContent = `${s.size} - ₱${parseFloat(s.price).toFixed(2)}`;
                select.appendChild(opt);
            });

            this.setupQuantity(modal, select, '#foodtray-price', '#foodtray-total', '#quantity-foodtray');
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

        setupQuantity(modal, select, priceSelector, totalSelector, qtySelector) {
            let qty = 1;
            const qtyEl = modal.querySelector(qtySelector);
            const priceEl = modal.querySelector(priceSelector);
            const totalEl = modal.querySelector(totalSelector);

            const updateTotal = () => {
                qtyEl.textContent = qty;
                const price = parseFloat(select.value) || 0;
                priceEl.textContent = `₱${price.toFixed(2)}`;
                totalEl.textContent = `₱${(price * qty).toFixed(2)}`;
            };

            select.onchange = updateTotal;
            modal.querySelector('#increase-qty-foodtray').onclick = () => { qty++; updateTotal(); };
            modal.querySelector('#decrease-qty-foodtray').onclick = () => { if (qty > 1) qty--; updateTotal(); };
            updateTotal();
        }

        openModal(modal) {
            modal.classList.remove('hidden');
            modal.querySelector('#add-to-cart-foodtray').onclick = () => {
                const select = modal.querySelector('#foodtray-size');
                const id = parseInt(modal.querySelector('#foodtray-id').value);

                this.cartService.sendToCart({
                    id,
                    name: modal.querySelector('#foodtray-name').textContent,
                    image: modal.querySelector('#foodtray-image').src,
                    price: parseFloat(select.value),
                    quantity: parseInt(modal.querySelector('#quantity-foodtray').textContent)
                });

                modal.classList.add('hidden');
            };
        }
    }
