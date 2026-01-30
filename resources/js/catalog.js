import ProductModalFactory from './catalog/ProductModalFactory';
import CartService from './catalog/CartService';
import FoodTrayHandler from './catalog/handlers/FoodTrayHandler';
import FoodPackageHandler from './catalog/handlers/FoodPackageHandler';
import CakeHandler from './catalog/handlers/CakeHandler';
import CupcakeHandler from './catalog/handlers/CupcakeHandler';
import PaluwaganHandler from './catalog/handlers/PaluwaganHandler';

document.addEventListener('DOMContentLoaded', () => {

    // ================= CATEGORY FILTER =================
    const categoryButtons = document.querySelectorAll('.category-btn');
    const productCards = document.querySelectorAll('.product-card');

    categoryButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const category = btn.dataset.category;

            // Active state
            categoryButtons.forEach(b => b.classList.remove('active-category'));
            btn.classList.add('active-category');

            // Filter products
            productCards.forEach(card => {
                if (category === 'all') {
                    card.classList.remove('hidden');
                } else {
                    card.classList.toggle(
                        'hidden',
                        card.dataset.category !== category
                    );
                }
            });
        });
    });


    // ================= CART SERVICE =================
    const cartService = new CartService('/cart/add', '/cart/sidebar');

    const handlers = {
        foodtray: new FoodTrayHandler(cartService),
        foodpackage: new FoodPackageHandler(cartService),
        cake: new CakeHandler(cartService),
        cupcake: new CupcakeHandler(cartService),
        paluwagan: new PaluwaganHandler(cartService)
    };
    
    // ================= PRODUCT CARD CLICK =================
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('click', e => {
            e.stopPropagation();

            const handler = handlers[card.dataset.category];
            if (!handler) return;

            const modal = ProductModalFactory.getModal(card.dataset.category);
            if (!modal) return;

            handler.populateModal(card, modal);
            handler.openModal(modal);

            addOutsideClickListener(modal);
        });
    });

    // ================= MODAL CLOSE =================
    document.addEventListener('click', e => {
        if (e.target.matches('[id^="close-"]')) {
            const modal = e.target.closest('.fixed');
            if (modal) modal.classList.add('hidden');
        }
    });

    // ================= MOBILE CART =================
    const mobileCartBtn = document.getElementById('mobile-cart-btn');
    const mobileCartModal = document.getElementById('mobile-cart-modal');
    const closeMobileCart = document.getElementById('close-mobile-cart');

    if (mobileCartBtn && mobileCartModal) {
        mobileCartBtn.addEventListener('click', e => {
            e.stopPropagation();
            refreshCart(); // ✅ always fresh when opened
            mobileCartModal.classList.remove('hidden');
        });
    }

    if (closeMobileCart && mobileCartModal) {
        closeMobileCart.addEventListener('click', () => {
            mobileCartModal.classList.add('hidden');
        });
    }

    if (mobileCartModal) {
        mobileCartModal.addEventListener('click', e => {
            if (e.target === mobileCartModal) {
                mobileCartModal.classList.add('hidden');
            }
        });
    }

    // ================= CART EVENTS =================
    bindCartEvents();
});


// ================= MODAL OUTSIDE CLICK =================
function addOutsideClickListener(modal) {
    const overlay = modal.querySelector('.modal-overlay');
    if (!overlay) return;

    function handleOutside(e) {
        if (e.target === overlay) {
            modal.classList.add('hidden');
            document.removeEventListener('click', handleOutside);
        }
    }

    setTimeout(() => {
        document.addEventListener('click', handleOutside);
    }, 0);
}


// ================= CART AJAX =================
function bindCartEvents() {

    document.addEventListener('click', e => {

        const btn = e.target.closest('button');
        if (!btn || !btn.dataset.url) return;

        e.preventDefault();
        e.stopPropagation();

        fetch(btn.dataset.url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': btn.dataset.csrf
            },
            body: new URLSearchParams({
                id: btn.dataset.id,
                action: btn.classList.contains('ajax-increase')
                    ? 'increase'
                    : btn.classList.contains('ajax-decrease')
                    ? 'decrease'
                    : 'remove'
            })
        })
        .then(() => refreshCart())
        .catch(err => console.error('Cart update failed:', err));
    });
}


// ================= ADD TO CART REAL-TIME FIX =================
document.addEventListener('cart:updated', () => {
    refreshCart();
});


// ================= CART REFRESH =================
function refreshCart() {
    fetch('/cart/sidebar')
        .then(res => res.text())
        .then(html => {
            document.querySelectorAll('#cart-sidebar').forEach(el => {
                el.innerHTML = html;
            });
        })
        .catch(err => console.error('Cart refresh failed:', err));
}


