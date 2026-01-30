export default class PaluwaganHandler {
    constructor(cartService) {
        this.cartService = cartService;
        this.modal = null;
        this.packageId = null;
    }

    populateModal(card, modal) {
        this.modal = modal;
        this.packageId = card.dataset.id;

        // Populate info
        modal.querySelector('#paluwagan-name').textContent = card.dataset.name;
        modal.querySelector('#paluwagan-image').src = card.dataset.image;

        const descEl = modal.querySelector('#paluwagan-desc');
        descEl.innerHTML = '';
        (card.dataset.description || '').split('\n')
            .map(line => line.trim())
            .filter(line => line !== '')
            .forEach(line => {
                const li = document.createElement('li');
                li.textContent = line;
                descEl.appendChild(li);
            });

        // Price/duration
        if (card.dataset.servings) {
            const servings = JSON.parse(card.dataset.servings);
            if (servings.length > 0) {
                const serving = servings[0];
                modal.querySelector('#paluwagan-total').textContent = parseFloat(serving.price).toFixed(2);
                modal.querySelector('#paluwagan-monthly').textContent = 
                    parseFloat(serving.price / (parseInt(serving.size) || 1)).toFixed(2);
                modal.querySelector('#paluwagan-duration').textContent = (serving.size || 1) + ' months';
            }
        }

        // Step 1 visible initially
        modal.querySelector('#paluwagan-step1').classList.remove('hidden');
        modal.querySelector('#paluwagan-step2').classList.add('hidden');

        // Only attach listeners once
        this.attachListeners();
    }

    attachListeners() {
        if (!this.modal) return;

        // Join button
        const joinBtn = this.modal.querySelector('#join-paluwagan');
        if (!joinBtn.dataset.listener) {
            joinBtn.addEventListener('click', () => {
                this.modal.querySelector('#paluwagan-step1').classList.add('hidden');
                this.modal.querySelector('#paluwagan-step2').classList.remove('hidden');
                this.modal.querySelector('#paluwagan-image2').src =
                    this.modal.querySelector('#paluwagan-image').src;
            });
            joinBtn.dataset.listener = true; // mark as attached
        }

        // Back button
        const backBtn = this.modal.querySelector('#back-paluwagan');
        if (!backBtn.dataset.listener) {
            backBtn.addEventListener('click', () => {
                this.modal.querySelector('#paluwagan-step2').classList.add('hidden');
                this.modal.querySelector('#paluwagan-step1').classList.remove('hidden');
            });
            backBtn.dataset.listener = true;
        }

        // Confirm enrollment
        const confirmBtn = this.modal.querySelector('#confirmEnrollmentBtn');
        if (!confirmBtn.dataset.listener) {
            confirmBtn.addEventListener('click', () => this.handleConfirmEnrollment());
            confirmBtn.dataset.listener = true;
        }

        // Close button (X)
        const closeBtn = this.modal.querySelector('#close-modal-paluwagan');
        if (!closeBtn.dataset.listener) {
            closeBtn.addEventListener('click', () => this.modal.classList.add('hidden'));
            closeBtn.dataset.listener = true;
        }

        // Overlay click
        const overlay = this.modal.querySelector('.modal-overlay');
        if (!overlay.dataset.listener) {
            overlay.addEventListener('click', () => this.modal.classList.add('hidden'));
            overlay.dataset.listener = true;
        }
    }

    openModal() {
        if (!this.modal) return;
        this.modal.classList.remove('hidden');
    }

    handleConfirmEnrollment() {
        if (!this.modal || !this.packageId) {
            alert('Package not selected!');
            return;
        }

        const startMonth = this.modal.querySelector('#start-month').value;

        fetch('/paluwagan/join', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                packageID: this.packageId,
                startMonth: startMonth
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Successfully joined Paluwagan!');
                this.modal.classList.add('hidden');
                window.location.href = '/paluwagan';
            } else {
                alert(data.error || 'Failed to join Paluwagan');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Network error while joining Paluwagan.');
        });
    }
}
