let pendingOrders = [];
let isMuted       = false;
let audioCtx      = null;

function unlockAudio() {
    if (audioCtx) return;
    try {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    } catch (e) {}
}

function playNotifSound() {
    if (isMuted || !audioCtx) return;
    try {
        if (audioCtx.state === 'suspended') audioCtx.resume();
        [880, 660].forEach((freq, i) => {
            const osc  = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.type            = 'sine';
            osc.frequency.value = freq;
            const t = audioCtx.currentTime + (i * 0.25);
            gain.gain.setValueAtTime(0.4, t);
            gain.gain.exponentialRampToValueAtTime(0.001, t + 0.4);
            osc.start(t);
            osc.stop(t + 0.4);
        });
    } catch (e) {}
}

window.toggleMute = function () {
    isMuted = !isMuted;
    const icon = document.getElementById('mute-icon');
    if (icon) icon.className = isMuted ? 'fas fa-volume-mute' : 'fas fa-volume-up';
};

function showNotification(order) {
    pendingOrders.unshift(order);

    const panel     = document.getElementById('admin-order-notif');
    const mainText  = document.getElementById('notif-main-text');
    const orderList = document.getElementById('notif-order-list');
    if (!panel) return;

    const count = pendingOrders.length;
    mainText.innerHTML = `You have <span class="text-pink-600 font-bold text-xl">${count}</span> new order${count > 1 ? 's' : ''}`;

    orderList.innerHTML = pendingOrders.slice(0, 3).map(o => `
        <div class="flex items-center justify-between bg-pink-50 rounded-lg px-3 py-2 text-xs border border-pink-100">
            <div>
                <span class="font-semibold text-gray-700">#${o.orderID}</span>
                <span class="text-gray-500 ml-1">${o.customerName}</span>
            </div>
            <span class="font-bold text-pink-600">₱${o.totalAmount}</span>
        </div>
    `).join('');

    if (count > 3) {
        orderList.innerHTML += `<p class="text-xs text-center text-gray-400 mt-1">+${count - 3} more</p>`;
    }

    panel.classList.remove('hidden', 'notif-slide-out');
    void panel.offsetWidth;
    panel.classList.add('notif-slide-in');

    updateSidebarBadge(count);
    playNotifSound();
}

function updateSidebarBadge(count) {
    const badge = document.getElementById('admin-order-badge');
    if (!badge) return;
    if (count > 0) {
        badge.textContent = count > 99 ? '99+' : count;
        badge.classList.remove('hidden');
        badge.classList.add('flex');
    } else {
        badge.classList.add('hidden');
        badge.classList.remove('flex');
    }
}

window.goToNewOrders = function () {
    sessionStorage.setItem('highlight_order_ids', JSON.stringify(pendingOrders.map(o => o.orderID)));

    fetch('/admin/orders/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            'Content-Type': 'application/json',
        }
    });

    const panel = document.getElementById('admin-order-notif');
    if (panel) {
        panel.classList.remove('notif-slide-in');
        panel.classList.add('notif-slide-out');
        setTimeout(() => {
            panel.classList.add('hidden');
            pendingOrders = [];
            updateSidebarBadge(0);
        }, 300);
    }

    window.location.href = '/admin/orders';
};

document.addEventListener('DOMContentLoaded', () => {
    // AudioContext only created after first user click — fixes Chrome warning
    document.addEventListener('click',   unlockAudio, { once: false });
    document.addEventListener('keydown', unlockAudio, { once: false });

    setTimeout(() => {
        if (!window.Echo) {
            console.error('❌ Laravel Echo not initialized');
            return;
        }
        window.Echo.channel('admin-orders')
            .listen('.new.order', (data) => {
                console.log('📦 New order received:', data);
                showNotification(data);
            });
        console.log('✅ Listening on admin-orders channel');
    }, 500);
});