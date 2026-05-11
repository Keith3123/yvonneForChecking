// resources/js/catalog/handlers/PaluwaganHandler.js

export default class PaluwaganHandler {
    constructor(cartService) {
        this.cartService = cartService;
        this.modal = null;
        this.packageId = null;
        this._selectedMonth = null;      // { month, status, label, waitingCount }
    }

    populateModal(card, modal) {
        this.modal = modal;
        this.packageId = card.dataset.id;
        this.modal.dataset.package = this.packageId;
        this._selectedMonth = null;

        // ── Basic info ──────────────────────────────────
        this.modal.querySelector('#paluwagan-name').textContent = card.dataset.name;
        this.modal.querySelector('#paluwagan-image').src = card.dataset.image;

        const descEl = this.modal.querySelector('#paluwagan-desc');
        descEl.innerHTML = '';
        (card.dataset.description || '')
            .split('\n')
            .map(l => l.trim())
            .filter(Boolean)
            .forEach(line => {
                const li = document.createElement('li');
                li.textContent = line;
                descEl.appendChild(li);
            });

        // ── Price / duration ────────────────────────────
        if (card.dataset.servings) {
            try {
                const [serving] = JSON.parse(card.dataset.servings);
                if (serving) {
                    const total    = parseFloat(serving.price    || 0);
                    const duration = parseInt(serving.size      || 1);
                    this.modal.querySelector('#paluwagan-total').textContent    = total.toFixed(2);
                    this.modal.querySelector('#paluwagan-monthly').textContent  = (total / duration).toFixed(2);
                    this.modal.querySelector('#paluwagan-duration').textContent = duration + ' months';
                }
            } catch (e) { console.error('Invalid servings JSON:', e); }
        }

        // ── Reset steps ─────────────────────────────────
        this.modal.querySelector('#paluwagan-step1').classList.remove('hidden');
        this.modal.querySelector('#paluwagan-step2').classList.add('hidden');

        this.attachListeners();
    }

    attachListeners() {
        if (!this.modal) return;

        const joinBtn    = this.modal.querySelector('#join-paluwagan');
        const backBtn    = this.modal.querySelector('#back-paluwagan');
        const confirmBtn = this.modal.querySelector('#confirmEnrollmentBtn');
        const closeBtn   = this.modal.querySelector('#close-modal-paluwagan');
        const overlay    = this.modal.querySelector('.modal-overlay');

        if (joinBtn && !joinBtn.dataset.listener) {
            joinBtn.addEventListener('click', () => {
                this.modal.querySelector('#paluwagan-step1').classList.add('hidden');
                this.modal.querySelector('#paluwagan-step2').classList.remove('hidden');
                this.modal.querySelector('#paluwagan-image2').src =
                    this.modal.querySelector('#paluwagan-image').src;
                this._resetMonthGrid();
                this.loadAvailableMonths(this.packageId);
            });
            joinBtn.dataset.listener = 'true';
        }

        if (backBtn && !backBtn.dataset.listener) {
            backBtn.addEventListener('click', () => {
                this.modal.querySelector('#paluwagan-step2').classList.add('hidden');
                this.modal.querySelector('#paluwagan-step1').classList.remove('hidden');
            });
            backBtn.dataset.listener = 'true';
        }

        if (confirmBtn && !confirmBtn.dataset.listener) {
            confirmBtn.addEventListener('click', () => this.handleConfirmEnrollment());
            confirmBtn.dataset.listener = 'true';
        }

        if (closeBtn && !closeBtn.dataset.listener) {
            closeBtn.addEventListener('click', () => this.modal.classList.add('hidden'));
            closeBtn.dataset.listener = 'true';
        }

        if (overlay && !overlay.dataset.listener) {
            overlay.addEventListener('click', () => this.modal.classList.add('hidden'));
            overlay.dataset.listener = 'true';
        }
    }

    openModal() {
        if (!this.modal) return;
        this.modal.classList.remove('hidden');
    }

    // ─────────────────────────────────────────────────────
    //  MONTH GRID
    // ─────────────────────────────────────────────────────
    _resetMonthGrid() {
        this._selectedMonth = null;
        const grid    = this.modal.querySelector('#month-cards-grid');
        const loading = this.modal.querySelector('#months-loading');
        const legend  = this.modal.querySelector('#months-legend');
        const notice  = this.modal.querySelector('#waitlist-notice');
        const confirm = this.modal.querySelector('#confirmEnrollmentBtn');
        const hidden  = this.modal.querySelector('#start-month');

        if (grid)    { grid.innerHTML = ''; grid.classList.add('hidden'); }
        if (loading) loading.classList.remove('hidden');
        if (legend)  legend.classList.add('hidden');
        if (notice)  notice.classList.add('hidden');
        if (confirm) confirm.disabled = true;
        if (hidden)  hidden.value = '';
    }

    loadAvailableMonths(packageID) {
    if (!packageID) return;
 
    fetch(`/user/paluwagan/available-months/${packageID}`, {
        credentials: 'same-origin'
    })
    .then(async res => {
        const text = await res.text();
        if (!res.ok) {
            console.error('availableMonths server error:', res.status, text.slice(0, 300));
            throw new Error(`Server error ${res.status}`);
        }
        try { return JSON.parse(text); }
        catch (e) { throw new Error('Response is not valid JSON'); }
    })
    .then(months => this._renderMonthGrid(months))
    .catch(err => {
        console.error('Error loading months:', err);
        const loading = this.modal.querySelector('#months-loading');
        if (loading) {
            loading.classList.remove('hidden');
            loading.innerHTML =
                `<p class="text-red-500 text-sm text-center py-4">
                    Failed to load months: ${err.message}
                </p>`;
        }
    });
}

    _renderMonthGrid(months) {
        const grid    = this.modal.querySelector('#month-cards-grid');
        const loading = this.modal.querySelector('#months-loading');
        const legend  = this.modal.querySelector('#months-legend');

        if (loading) loading.classList.add('hidden');

        if (!months || months.length === 0) {
            if (loading) loading.innerHTML =
                '<p class="text-gray-400 text-sm text-center py-4">No available months configured yet.</p>';
            loading.classList.remove('hidden');
            return;
        }

        grid.innerHTML = '';

        // ✅ Sort months starting from current month going forward
    const currentMonth = new Date().getMonth() + 1; // 1-12
    const sorted = [...months].sort((a, b) => {
        const aOffset = (a.month - currentMonth + 12) % 12;
        const bOffset = (b.month - currentMonth + 12) % 12;
        return aOffset - bOffset;
    });

        sorted.forEach(item => {
            const card = document.createElement('div');
            card.dataset.month  = item.month;
            card.dataset.status = item.status; // 'available' | 'taken'

            // ── Determine card appearance ─────────────────
            const isAvailable = item.status === 'available';
            const isWaiting   = item.currentUserWaitPosition !== null;

            let cardClass, badgeHtml, subHtml, clickable;

            if (isAvailable) {
                cardClass = 'border-green-200 bg-green-50 hover:bg-green-100 cursor-pointer';
                badgeHtml = '<span class="text-[10px] font-bold text-green-700 bg-green-100 ' +
                            'border border-green-300 px-1.5 py-0.5 rounded-full">Open</span>';
                subHtml   = '';
                clickable = true;
            } else if (isWaiting) {
                // Current user is already in waitlist for this month
                cardClass = 'border-yellow-300 bg-yellow-50 hover:bg-yellow-100 cursor-pointer';
                badgeHtml = '<span class="text-[10px] font-bold text-yellow-700 bg-yellow-100 ' +
                            'border border-yellow-300 px-1.5 py-0.5 rounded-full">' +
                            '⏳ #' + item.currentUserWaitPosition + ' in line</span>';
                subHtml   = '<p class="text-[10px] text-yellow-600 mt-0.5 truncate">' +
                            'Taken by ' + (item.takenBy || 'someone') + '</p>';
                clickable = true;
            } else {
                // Taken, current user not yet waiting
                cardClass = 'border-red-200 bg-red-50 hover:bg-red-100 cursor-pointer';
                badgeHtml = '<span class="text-[10px] font-bold text-red-600 bg-red-100 ' +
                            'border border-red-200 px-1.5 py-0.5 rounded-full">Taken</span>';

                const waitLabel = item.waitingCount > 0
                    ? item.waitingCount + ' waiting'
                    : 'Join waitlist';
                subHtml = '<p class="text-[10px] text-red-500 mt-0.5 truncate">' +
                          (item.takenBy ? 'By ' + item.takenBy + ' • ' : '') + waitLabel + '</p>';
                clickable = true; // still clickable to join waitlist
            }

            card.className = `relative border-2 rounded-xl p-2.5 text-center transition select-none ${cardClass}`;
            card.innerHTML = `
                <p class="font-bold text-sm text-gray-800">${item.label}</p>
                ${subHtml}
                <div class="mt-1 flex justify-center">${badgeHtml}</div>`;

            if (clickable) {
                card.addEventListener('click', () => this._selectMonth(card, item));
            }

            grid.appendChild(card);
        });

        grid.classList.remove('hidden');
        if (legend) legend.classList.remove('hidden');
    }

    _selectMonth(card, item) {
        // Deselect previous
        this.modal.querySelectorAll('#month-cards-grid > div').forEach(c => {
            c.classList.remove('ring-2', 'ring-pink-500', 'ring-offset-1');
        });

        // Highlight selected
        card.classList.add('ring-2', 'ring-pink-500', 'ring-offset-1');

        this._selectedMonth = item;
        this.modal.querySelector('#start-month').value = item.month;

        // ── Waitlist notice ───────────────────────────────
        const notice     = this.modal.querySelector('#waitlist-notice');
        const noticeText = this.modal.querySelector('#waitlist-notice-text');
        const confirmBtn = this.modal.querySelector('#confirmEnrollmentBtn');

        if (item.status === 'taken') {
            notice.classList.remove('hidden');
            if (item.currentUserWaitPosition !== null) {
                noticeText.textContent =
                    `You are already #${item.currentUserWaitPosition} in the waiting list for ${item.label}. ` +
                    `Confirming won't add a duplicate.`;
                confirmBtn.textContent = 'Already Waiting';
                confirmBtn.disabled = true;
            } else {
                const pos = (item.waitingCount || 0) + 1;
                noticeText.textContent =
                    `${item.label} is taken. You'll be added as #${pos} in the waiting list. ` +
                    `When the slot opens, you'll be notified and activated automatically.`;
                confirmBtn.textContent = 'Join Waitlist';
                confirmBtn.disabled = false;
            }
        } else {
            notice.classList.add('hidden');
            confirmBtn.textContent = 'Confirm Enrollment';
            confirmBtn.disabled = false;
        }
    }

    // ─────────────────────────────────────────────────────
    //  CONFIRM ENROLLMENT
    // ─────────────────────────────────────────────────────
    handleConfirmEnrollment() {
    if (!this.modal || !this.packageId) {
        showToast('Package not selected!');
        return;
    }
 
    const startMonth = this.modal.querySelector('#start-month').value;
    if (!startMonth) {
        showToast('Please select a start month.');
        return;
    }
 
    const confirmBtn = this.modal.querySelector('#confirmEnrollmentBtn');
    const origText   = confirmBtn.textContent;
    confirmBtn.disabled    = true;
    confirmBtn.textContent = 'Processing…';
 
    fetch('/paluwagan/join', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            packageID:  this.packageId,
            startMonth: startMonth
        })
    })
    // ── Safe parse: read raw text first, then try JSON ──────────────────────
    .then(async res => {
        const text = await res.text();
 
        if (res.status === 401) {
            showToast('Session expired. Please login again.');
            window.location.href = '/login';
            return null;
        }
 
        let data;
        try {
            data = JSON.parse(text);
        } catch (_) {
            // Server returned HTML (e.g. Laravel exception page)
            console.error('Non-JSON response from /paluwagan/join:', text.slice(0, 500));
            throw new Error(`Server error (${res.status}) — check Laravel logs`);
        }
 
        return data;
    })
    .then(data => {
        if (!data) return;
 
        if (data.success) {
            const msg = data.waiting
                ? (data.message || 'Added to waiting list!')
                : (data.message || 'Successfully joined Paluwagan!');
            showToast(msg);
            this.modal.classList.add('hidden');
            setTimeout(() => window.location.href = '/paluwagan', 1200);
        } else {
            showToast(data.error || data.message || 'Failed to join Paluwagan');
            confirmBtn.disabled    = false;
            confirmBtn.textContent = origText;
        }
    })
    .catch(err => {
        console.error('Join error:', err);
        showToast(err.message || 'Network/server error while joining Paluwagan.');
        confirmBtn.disabled    = false;
        confirmBtn.textContent = origText;
    });
}
}