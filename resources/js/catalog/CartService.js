export default class CartService {
    constructor(addEndpoint, sidebarEndpoint = '/cart/sidebar') {
        this.addEndpoint = addEndpoint;
        this.sidebarEndpoint = sidebarEndpoint;
    }

    async sendToCart(payload) {
        const res = await fetch(this.addEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (!data.success) return;

        await this.refreshSidebar();
    }

    async refreshSidebar() {
        const html = await fetch(this.sidebarEndpoint).then(r => r.text());
        const sidebar = document.getElementById('cart-sidebar');
        if (sidebar) sidebar.innerHTML = html;

        document.dispatchEvent(new Event('cart:updated'));
    }
}
