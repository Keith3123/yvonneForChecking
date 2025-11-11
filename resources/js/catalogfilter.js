document.addEventListener("DOMContentLoaded", () => {
    const categoryButtons = document.querySelectorAll(".category-btn");
    const productCards = document.querySelectorAll(".product-card");

    // -------- Category click filter ----------
    categoryButtons.forEach(btn => {
        btn.addEventListener("click", () => {
            const selected = btn.dataset.category;

            // Highlight selected button
            categoryButtons.forEach(b => b.classList.remove("bg-[#FFEFEA]"));
            btn.classList.add("bg-[#FFEFEA]");

            // Filter product cards
            productCards.forEach(card => {
                card.style.display = card.dataset.category === selected ? "block" : "none";
            });

            // Scroll product list to top
            const catalogSection = document.querySelector("#catalog-section");
            if (catalogSection) {
                catalogSection.scrollTo({ top: 0, behavior: "smooth" });
            }
        });
    });
});
