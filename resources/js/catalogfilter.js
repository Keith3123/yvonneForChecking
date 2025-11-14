document.addEventListener("DOMContentLoaded", () => {
    // --------------------------
    // CATEGORY FILTER
    // --------------------------
    const categoryButtons = document.querySelectorAll(".category-btn");
    const products = document.querySelectorAll(".product-card");

    categoryButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            const category = btn.dataset.category;
            products.forEach(product => {
                if (category === "all" || product.dataset.category === category) {
                    product.classList.remove("hidden");
                } else product.classList.add("hidden");
            });
            categoryButtons.forEach(b => b.classList.remove("bg-[#FFEFEA]", "shadow-lg"));
            btn.classList.add("bg-[#FFEFEA]", "shadow-lg");
        });
    });

    // --------------------------
    // MODALS
    // --------------------------
    const modals = {
        foodtrays: document.getElementById("foodtray-modal"),
        foodpackage: document.getElementById("foodpackage-modal"),
        cake: document.getElementById("cake-modal"),
        cupcake: document.getElementById("cupcake-modal"),
        paluwagan: document.getElementById("paluwagan-modal")
    };

    const formatPHP = v => `₱ ${parseFloat(v).toFixed(2)}`;

    // --------------------------
    // CART COUNT
    // --------------------------
    const cartCountEl = document.getElementById('cart-count');
    const updateCartCount = count => {
        if (cartCountEl) cartCountEl.textContent = `${count} item(s) added`;
    };

    // --------------------------
    // ADD TO CART FUNCTION
    // --------------------------
    const addToCart = (item, modal) => {
        fetch('/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(item)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                updateCartCount(data.cartCount);
                alert(`${item.name} added to cart`);
                if (modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            }
        });
    };

    // --------------------------
    // OPEN MODALS
    // --------------------------
    document.querySelectorAll(".product-card").forEach(card => {
        card.addEventListener("click", () => {
            const category = card.dataset.category;
            const name = card.dataset.name;
            const desc = card.dataset.description;
            const image = card.dataset.image;
            const price = parseFloat(card.dataset.price.replace(/[₱, ]/g, "")) || 0;

            let modal, qtyEl, nameEl, descEl, imageEl, priceEl, totalEl;

            switch (category) {
                case "foodtrays":
                    modal = modals.foodtrays;
                    nameEl = document.getElementById("modal-name");
                    descEl = document.getElementById("modal-description");
                    imageEl = document.getElementById("modal-image");
                    priceEl = document.getElementById("modal-price");
                    totalEl = document.getElementById("modal-total");
                    qtyEl = document.getElementById("quantity");
                    break;
                case "foodpackage":
                    modal = modals.foodpackage;
                    nameEl = document.getElementById("package-name");
                    descEl = document.getElementById("package-desc");
                    imageEl = document.getElementById("package-image");
                    priceEl = document.getElementById("package-price");
                    totalEl = document.getElementById("package-total");
                    qtyEl = document.getElementById("quantity-package");
                    break;
                case "cake":
                    modal = modals.cake;
                    nameEl = document.getElementById("cake-name");
                    descEl = document.getElementById("cake-desc");
                    imageEl = document.getElementById("cake-image");
                    priceEl = document.getElementById("cake-price");
                    totalEl = document.getElementById("cake-total");
                    qtyEl = document.getElementById("quantity-cake");
                    break;
                case "cupcake":
                    modal = modals.cupcake;
                    nameEl = document.getElementById("cupcake-name");
                    descEl = document.getElementById("cupcake-desc");
                    imageEl = document.getElementById("cupcake-image");
                    priceEl = document.getElementById("cupcake-price");
                    totalEl = document.getElementById("cupcake-total");
                    qtyEl = document.getElementById("quantity-cupcake");
                    break;
                case "paluwagan":
                    modal = modals.paluwagan;
                    const step1 = document.getElementById("paluwagan-step1");
                    const step2 = document.getElementById("paluwagan-step2");
                    document.getElementById("paluwagan-name").textContent = name;
                    document.getElementById("paluwagan-desc").textContent = desc;
                    document.getElementById("paluwagan-image").src = image;
                    document.getElementById("paluwagan-image2").src = image;
                    document.getElementById("paluwagan-total").textContent = `₱${price.toLocaleString()}`;
                    document.getElementById("paluwagan-monthly").textContent = `₱${(price / 10).toLocaleString()}`;
                    step1.classList.remove("hidden");
                    step2.classList.add("hidden");
                    modal.classList.remove("hidden");
                    modal.classList.add("flex");
                    return; // paluwagan handled separately
            }

            if (modal) {
                nameEl.textContent = name;
                descEl.textContent = desc;
                imageEl.src = image;
                priceEl.textContent = formatPHP(price);
                totalEl.textContent = formatPHP(price);
                qtyEl.textContent = 1;
                modal.classList.remove("hidden");
                modal.classList.add("flex");
            }
        });
    });

    // --------------------------
    // QUANTITY HANDLERS
    // --------------------------
    function setupQtyHandlers(qtyId, priceId, totalId) {
        const qtyEl = document.getElementById(qtyId);
        const priceEl = document.getElementById(priceId);
        const totalEl = document.getElementById(totalId);

        document.getElementById(`increase-${qtyId}`)?.addEventListener("click", () => {
            qtyEl.textContent = parseInt(qtyEl.textContent) + 1;
            totalEl.textContent = formatPHP(parseFloat(priceEl.textContent.replace(/[₱, ]/g, "")) * parseInt(qtyEl.textContent));
        });
        document.getElementById(`decrease-${qtyId}`)?.addEventListener("click", () => {
            if (parseInt(qtyEl.textContent) > 1) {
                qtyEl.textContent = parseInt(qtyEl.textContent) - 1;
                totalEl.textContent = formatPHP(parseFloat(priceEl.textContent.replace(/[₱, ]/g, "")) * parseInt(qtyEl.textContent));
            }
        });
    }

    ["quantity", "quantity-package", "quantity-cake", "quantity-cupcake"].forEach(id => {
        const priceId = id.replace("quantity", "price");
        const totalId = id.replace("quantity", "total");
        setupQtyHandlers(id, priceId, totalId);
    });

    // --------------------------
    // ADD TO CART BUTTONS
    // --------------------------
    document.querySelectorAll(".add-to-cart-btn").forEach(btn => {
        btn.addEventListener("click", function () {
            const id = this.dataset.id;
            const name = this.dataset.name;
            const price = parseFloat(this.dataset.price.replace(/[₱, ]/g, "")) || 0;
            const image = this.dataset.image;
            let quantity = parseInt(this.dataset.quantity) || 1;

            const modal = this.closest('div[id$="-modal"]');
            if (modal) {
                const qtyEl = modal.querySelector('span[id^="quantity"]');
                if (qtyEl) quantity = parseInt(qtyEl.textContent);
            }

            addToCart({ id, name, price, quantity, image }, modal);
        });
    });

    // --------------------------
    // GENERIC MODAL CLOSE/CANCEL HANDLER
    // --------------------------
    Object.entries(modals).forEach(([key, modal]) => {
        if (!modal) return;

        const closeModal = () => {
            modal.classList.add("hidden");
            modal.classList.remove("flex");
        };

        // buttons with class
        modal.querySelectorAll('.close-modal, .cancel-modal').forEach(btn => btn.addEventListener("click", closeModal));

        // optional: support specific cancel ids like cancel-foodtrays
        const cancelId = `cancel-${key}`;
        document.getElementById(cancelId)?.addEventListener("click", closeModal);

        // click outside modal closes it
        modal.addEventListener("click", e => {
            if (e.target === modal) closeModal();
        });
    });

    // --------------------------
    // PALUWAGAN MULTI-STEP
    // --------------------------
    if (modals.paluwagan) {
        const step1 = document.getElementById("paluwagan-step1");
        const step2 = document.getElementById("paluwagan-step2");

        document.getElementById("join-paluwagan")?.addEventListener("click", () => {
            step1.classList.add("hidden");
            step2.classList.remove("hidden");
        });

        document.getElementById("back-paluwagan")?.addEventListener("click", () => {
            step2.classList.add("hidden");
            step1.classList.remove("hidden");
        });

        const closePaluwagan = () => {
            modals.paluwagan.classList.add("hidden");
            modals.paluwagan.classList.remove("flex");
        };

        ["cancel-paluwagan", "close-modal-paluwagan"].forEach(id => {
            document.getElementById(id)?.addEventListener("click", closePaluwagan);
        });

        modals.paluwagan.addEventListener("click", e => {
            if (e.target === modals.paluwagan) closePaluwagan();
        });

        document.getElementById("confirm-paluwagan")?.addEventListener("click", () => {
            const startMonth = document.getElementById("start-month").value;
            alert(`You have joined the Paluwagan! Starting month: ${startMonth}`);
            closePaluwagan();
        });
    }
});
