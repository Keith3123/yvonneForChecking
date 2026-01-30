// resources/js/catalog/CatalogController.js
import ProductModalFactory from './ProductModalFactory';

export default class CatalogController {
    constructor(handlers) {
        this.handlers = handlers;
        this.productCards = document.querySelectorAll('.product-card');
        this.categoryButtons = document.querySelectorAll('.category-btn');
    }

    init() {
        this.initCategoryFilter();
        this.initProductClicks();
        this.initModalCloseButtons();
    }

    initCategoryFilter() {
        this.categoryButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const category = btn.dataset.category;
                this.productCards.forEach(card => {
                    card.style.display = card.dataset.category === category ? 'block' : 'none';
                });
            });
        });
    }

    initProductClicks() {
        this.productCards.forEach(card => {
            card.addEventListener('click', () => {
                const category = card.dataset.category;
                const handler = this.handlers[category];
                if (!handler) return;

                const modal = ProductModalFactory.getModal(category);
                handler.populateModal(card, modal);
                handler.openModal(modal);
            });
        });
    }

    initModalCloseButtons() {
        document.querySelectorAll('[id^=close-]').forEach(btn => {
            btn.addEventListener('click', () => {
                const modal = btn.closest('.fixed.inset-0');
                if (modal) modal.classList.add('hidden');
            });
        });
    }

    
}


