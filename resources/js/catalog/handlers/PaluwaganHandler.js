export default class PaluwaganHandler {
    constructor(cartService) {
        this.cartService = cartService;
        this.modal = null;
        this.packageId = null;
    }

    populateModal(card, modal) {
        this.modal = modal;
        this.packageId = card.dataset.id;

        // Store packageID in modal (optional but safe)
        this.modal.dataset.package = this.packageId;

        // ================= BASIC INFO =================
        this.modal.querySelector('#paluwagan-name').textContent = card.dataset.name;
        this.modal.querySelector('#paluwagan-image').src = card.dataset.image;

        // Description list
        const descEl = this.modal.querySelector('#paluwagan-desc');
        descEl.innerHTML = '';

        (card.dataset.description || '')
            .split('\n')
            .map(line => line.trim())
            .filter(line => line !== '')
            .forEach(line => {
                const li = document.createElement('li');
                li.textContent = line;
                descEl.appendChild(li);
            });

        // ================= PRICE + DURATION =================
        if (card.dataset.servings) {
            try {
                const servings = JSON.parse(card.dataset.servings);

                if (servings.length > 0) {
                    const serving = servings[0];

                    const total = parseFloat(serving.price || 0);
                    const duration = parseInt(serving.size || 1);

                    this.modal.querySelector('#paluwagan-total').textContent = total.toFixed(2);
                    this.modal.querySelector('#paluwagan-monthly').textContent =
                        (total / duration).toFixed(2);
                    this.modal.querySelector('#paluwagan-duration').textContent =
                        duration + ' months';
                }
            } catch (e) {
                console.error('Invalid servings JSON:', e);
            }
        }

        // ================= RESET STEPS =================
        this.modal.querySelector('#paluwagan-step1').classList.remove('hidden');
        this.modal.querySelector('#paluwagan-step2').classList.add('hidden');

        // ================= ATTACH EVENTS =================
        this.attachListeners();
    }

    attachListeners() {
        if (!this.modal) return;

        const joinBtn = this.modal.querySelector('#join-paluwagan');
        const backBtn = this.modal.querySelector('#back-paluwagan');
        const confirmBtn = this.modal.querySelector('#confirmEnrollmentBtn');
        const closeBtn = this.modal.querySelector('#close-modal-paluwagan');
        const overlay = this.modal.querySelector('.modal-overlay');

        // ================= JOIN BUTTON =================
        if (joinBtn && !joinBtn.dataset.listener) {
            joinBtn.addEventListener('click', () => {

                // Switch steps
                this.modal.querySelector('#paluwagan-step1').classList.add('hidden');
                this.modal.querySelector('#paluwagan-step2').classList.remove('hidden');

                // Copy image
                this.modal.querySelector('#paluwagan-image2').src =
                    this.modal.querySelector('#paluwagan-image').src;

                // Load months dynamically
                this.loadAvailableMonths(this.packageId);
            });

            joinBtn.dataset.listener = 'true';
        }

        // ================= BACK BUTTON =================
        if (backBtn && !backBtn.dataset.listener) {
            backBtn.addEventListener('click', () => {
                this.modal.querySelector('#paluwagan-step2').classList.add('hidden');
                this.modal.querySelector('#paluwagan-step1').classList.remove('hidden');
            });

            backBtn.dataset.listener = 'true';
        }

        // ================= CONFIRM BUTTON =================
        if (confirmBtn && !confirmBtn.dataset.listener) {
            confirmBtn.addEventListener('click', () => {
                this.handleConfirmEnrollment();
            });

            confirmBtn.dataset.listener = 'true';
        }

        // ================= CLOSE BUTTON =================
        if (closeBtn && !closeBtn.dataset.listener) {
            closeBtn.addEventListener('click', () => {
                this.modal.classList.add('hidden');
            });

            closeBtn.dataset.listener = 'true';
        }

        // ================= OVERLAY CLICK =================
        if (overlay && !overlay.dataset.listener) {
            overlay.addEventListener('click', () => {
                this.modal.classList.add('hidden');
            });

            overlay.dataset.listener = 'true';
        }
    }

    openModal() {
        if (!this.modal) return;
        this.modal.classList.remove('hidden');
    }

    // ================= LOAD AVAILABLE MONTHS =================
    loadAvailableMonths(packageID) {
        if (!packageID) {
            console.error('Missing packageID for loading months');
            return;
        }

        fetch(`/user/paluwagan/available-months/${packageID}`, {
            method: 'GET',
            credentials: 'same-origin' // 🔥 THIS IS THE FIX
        })
        .then(async res => {
            if (!res.ok) {
                const text = await res.text();
                console.error('Available Months ERROR RESPONSE:', text);
                throw new Error('Failed to fetch months');
            }
            return res.json();
        })
        .then(months => {

            const select = this.modal.querySelector('#start-month');
            if (!select) return;

            select.innerHTML = '';

            if (!months || months.length === 0) {
                const option = document.createElement('option');
                option.textContent = 'No available months';
                option.disabled = true;
                option.selected = true;
                select.appendChild(option);
                return;
            }

            months.forEach(item => {
                const option = document.createElement('option');
                option.value = item.month;
                option.textContent = item.label;
                select.appendChild(option);
            });
        })
        .catch(err => {
            console.error('Error loading months:', err);
            showToast('Failed to load available months. Check console.');
        });
    }

    // ================= CONFIRM ENROLLMENT =================
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

        fetch('/paluwagan/join', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    .content
            },
            body: JSON.stringify({
                packageID: this.packageId,
                startMonth: startMonth
            })
        })
        .then(async res => {
            if (res.status === 401) {
                showToast('Session expired. Please login again.');
                window.location.href = '/login';
                return;
            }

                if (!res.ok) {
                    const data = await res.json();

                    if (data.error) {
                        showToast(data.error); // 🔥 show real reason
                        return;
                    }

                    throw new Error('Join failed');
                }

            return res.json();
        })
        .then(data => {
            if (!data) return;

            if (data.success) {
                showToast(data.message || 'Successfully joined Paluwagan!');
                this.modal.classList.add('hidden');
                window.location.href = '/paluwagan';
            } else {
                showToast(data.error || 'Failed to join Paluwagan');
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Network/server error while joining Paluwagan.');
        });
    }
}