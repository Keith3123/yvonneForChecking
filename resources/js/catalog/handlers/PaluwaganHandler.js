// resources/js/catalog/handlers/PaluwaganHandler.js

export default class PaluwaganHandler {
    constructor(cartService) {
        this.cartService          = cartService;
        this.modal                = null;
        this.packageId            = null;
        this._selectedMonth       = null;
        this._selectedDay         = null;
        this._selectedTime        = null;
        this._isSelectedMonthFull = false;
    }

    populateModal(card, modal) {
        this.modal                 = modal;
        this.packageId             = card.dataset.id;
        this.modal.dataset.package = this.packageId;
        this._selectedMonth        = null;
        this._selectedDay          = null;
        this._selectedTime         = null;
        this._isSelectedMonthFull  = false;

        modal.querySelector('#paluwagan-name').textContent = card.dataset.name;
        modal.querySelector('#paluwagan-image').src        = card.dataset.image;

        const descEl = modal.querySelector('#paluwagan-desc');
        descEl.innerHTML = '';
        (card.dataset.description || '')
            .split('\n').map(l => l.trim()).filter(Boolean)
            .forEach(line => {
                const li = document.createElement('li');
                li.textContent = line;
                descEl.appendChild(li);
            });

        if (card.dataset.servings) {
            try {
                const [serving] = JSON.parse(card.dataset.servings);
                if (serving) {
                    const total    = parseFloat(serving.price || 0);
                    const duration = parseInt(serving.size   || 1);
                    modal.querySelector('#paluwagan-total').textContent    = total.toFixed(2);
                    modal.querySelector('#paluwagan-monthly').textContent  = (total / duration).toFixed(2);
                    modal.querySelector('#paluwagan-duration').textContent = duration + ' months';
                }
            } catch (e) { console.error('Servings parse error:', e); }
        }

        modal.querySelector('#paluwagan-step1').classList.remove('hidden');
        modal.querySelector('#paluwagan-step2').classList.add('hidden');
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
                this._resetStep2();
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
        if (this.modal) this.modal.classList.remove('hidden');
    }

    // ─────────────────────────────────────────────────────────
    //  RESET STEP 2
    // ─────────────────────────────────────────────────────────
    _resetStep2() {
        this._selectedMonth       = null;
        this._selectedDay         = null;
        this._selectedTime        = null;
        this._isSelectedMonthFull = false;

        const $ = id => this.modal.querySelector(`#${id}`);

        // Month grid
        $('month-cards-grid').innerHTML = '';
        $('month-cards-grid').classList.add('hidden');
        $('months-loading').classList.remove('hidden');
        $('months-loading').innerHTML = `
            <svg class="animate-spin h-5 w-5 text-pink-500 mx-auto" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            <p class="text-gray-400 text-xs mt-1">Loading months...</p>`;

        // Day grid
        $('day-picker-section').classList.add('hidden');
        $('day-cards-grid').innerHTML = '';
        $('day-cards-grid').classList.add('hidden');
        $('day-loading').classList.add('hidden');

        // Time picker — just hide the section and clear the input
        const timeSection = this.modal.querySelector('#time-picker-section');
        if (timeSection) timeSection.classList.add('hidden');
        const hourEl = this.modal.querySelector('#time-hour');
        const minEl  = this.modal.querySelector('#time-minute');
        if (hourEl) hourEl.value = '';
        if (minEl)  minEl.value  = '';

        // Hidden inputs & button
        $('waitlist-notice').classList.add('hidden');
        $('start-month').value = '';
        $('start-day').value   = '';
        $('start-time').value  = '';
        $('confirmEnrollmentBtn').disabled    = true;
        $('confirmEnrollmentBtn').textContent = 'Confirm Subscription';

        // Remove day legend if present
        const oldLegend = this.modal.querySelector('#day-legend');
        if (oldLegend) oldLegend.remove();
    }

    // ─────────────────────────────────────────────────────────
    //  MONTH GRID
    // ─────────────────────────────────────────────────────────
    loadAvailableMonths(packageID) {
        fetch(`/user/paluwagan/available-months/${packageID}`, { credentials: 'same-origin' })
            .then(async res => {
                const text = await res.text();
                if (!res.ok) throw new Error(`Server ${res.status}`);
                return JSON.parse(text);
            })
            .then(months => this._renderMonthGrid(months))
            .catch(err => {
                const el = this.modal.querySelector('#months-loading');
                if (el) el.innerHTML =
                    `<p class="text-red-500 text-sm text-center py-3">Failed to load: ${err.message}</p>`;
            });
    }

    _renderMonthGrid(months) {
        const grid    = this.modal.querySelector('#month-cards-grid');
        const loading = this.modal.querySelector('#months-loading');

        loading.classList.add('hidden');

        if (!months || months.length === 0) {
            loading.innerHTML = '<p class="text-gray-400 text-sm text-center py-4">No months configured yet.</p>';
            loading.classList.remove('hidden');
            return;
        }

        grid.innerHTML = '';
        const current = new Date().getMonth() + 1;
        const sorted  = [...months].sort((a, b) =>
            ((a.month - current + 12) % 12) - ((b.month - current + 12) % 12)
        );

        sorted.forEach(item => {
            const isFull     = item.isFull ?? false;
            const taken      = item.activeCount ?? 0;
            const slotCap    = item.slotCap ?? 20;
            const almostFull = !isFull && taken >= Math.floor(slotCap * 0.75);

            const cardClass = isFull
                ? 'border-red-200 bg-red-50 hover:bg-red-100 cursor-pointer'
                : almostFull
                    ? 'border-yellow-200 bg-yellow-50 hover:bg-yellow-100 cursor-pointer'
                    : 'border-green-200 bg-green-50 hover:bg-green-100 cursor-pointer';

            const badge = isFull
                ? `<span class="text-[10px] font-bold text-red-600 bg-red-100 border border-red-200 px-1.5 py-0.5 rounded-full">Full ${taken}/${slotCap}</span>`
                : almostFull
                    ? `<span class="text-[10px] font-bold text-yellow-700 bg-yellow-100 border border-yellow-300 px-1.5 py-0.5 rounded-full">${taken}/${slotCap} slots taken</span>`
                    : `<span class="text-[10px] font-bold text-green-700 bg-green-100 border border-green-300 px-1.5 py-0.5 rounded-full">${taken}/${slotCap} slots taken</span>`;

            const waitlistNote = isFull
                ? `<p class="text-[9px] text-red-500 mt-0.5">Tap to join waitlist</p>` : '';
            const userBadge = item.userDay
                ? `<p class="text-[9px] text-blue-600 mt-0.5">You: day ${item.userDay}${item.userStatus === 'waiting' ? ' ⏳' : ' ✓'}</p>`
                : '';

            const card = document.createElement('div');
            card.dataset.month = item.month;
            card.className = `relative border-2 rounded-xl p-2 text-center transition select-none ${cardClass}`;
            card.innerHTML = `
                <p class="font-bold text-sm text-gray-800">${item.label}</p>
                ${userBadge}${waitlistNote}
                <div class="mt-1 flex justify-center">${badge}</div>`;

            card.addEventListener('click', () => this._selectMonth(card, item));
            grid.appendChild(card);
        });

        grid.classList.remove('hidden');
    }

    _selectMonth(card, item) {
        this.modal.querySelectorAll('#month-cards-grid > div').forEach(c =>
            c.classList.remove('ring-2', 'ring-pink-500', 'ring-offset-1')
        );
        card.classList.add('ring-2', 'ring-pink-500', 'ring-offset-1');

        this._selectedMonth       = item;
        this._selectedDay         = null;
        this._selectedTime        = null;
        this._isSelectedMonthFull = item.isFull ?? false;

        this.modal.querySelector('#start-month').value = item.month;
        this.modal.querySelector('#start-day').value   = '';
        this.modal.querySelector('#start-time').value  = '';
        this.modal.querySelector('#confirmEnrollmentBtn').disabled    = true;
        this.modal.querySelector('#confirmEnrollmentBtn').textContent = 'Pick a day…';

        // Hide & reset time picker when month changes
        const timeSection = this.modal.querySelector('#time-picker-section');
        if (timeSection) timeSection.classList.add('hidden');
        const timeInput = this.modal.querySelector('#time-input');
        if (timeInput) timeInput.value = '';

        const notice     = this.modal.querySelector('#waitlist-notice');
        const noticeText = this.modal.querySelector('#waitlist-notice-text');

        if (this._isSelectedMonthFull) {
            notice.classList.remove('hidden');
            const waitPos = (item.waitingCount ?? 0) + 1;
            const slotCap = item.slotCap ?? 20;
            noticeText.textContent =
                `${item.label} is full (${slotCap}/${slotCap}). Pick your preferred day and time — ` +
                `you'll join the waiting list as position #${waitPos}.`;
        } else {
            notice.classList.add('hidden');
        }

        const section = this.modal.querySelector('#day-picker-section');
        section.classList.remove('hidden');
        this.modal.querySelector('#selected-month-label').textContent = item.label;

        const dayGrid = this.modal.querySelector('#day-cards-grid');
        dayGrid.innerHTML = '';
        dayGrid.classList.add('hidden');
        const oldLegend = this.modal.querySelector('#day-legend');
        if (oldLegend) oldLegend.remove();
        this.modal.querySelector('#day-loading').classList.remove('hidden');

        this.loadAvailableDays(this.packageId, item.month);
    }

    // ─────────────────────────────────────────────────────────
    //  DAY GRID
    // ─────────────────────────────────────────────────────────
    loadAvailableDays(packageID, month) {
        fetch(`/user/paluwagan/available-days/${packageID}/${month}`, { credentials: 'same-origin' })
            .then(async res => {
                const text = await res.text();
                if (!res.ok) throw new Error(`Server ${res.status}`);
                return JSON.parse(text);
            })
            .then(days => this._renderDayGrid(days))
            .catch(err => {
                const loading = this.modal.querySelector('#day-loading');
                loading.classList.remove('hidden');
                loading.innerHTML =
                    `<p class="text-red-500 text-xs text-center py-2">Failed: ${err.message}</p>`;
            });
    }

    _renderDayGrid(days) {
        const grid    = this.modal.querySelector('#day-cards-grid');
        const loading = this.modal.querySelector('#day-loading');

        loading.classList.add('hidden');
        grid.innerHTML = '';

        const oldLegend = this.modal.querySelector('#day-legend');
        if (oldLegend) oldLegend.remove();

        // Weekday headers
        ['Su','Mo','Tu','We','Th','Fr','Sa'].forEach(label => {
            const h = document.createElement('div');
            h.className   = 'text-center text-[9px] font-bold text-gray-400 pb-0.5';
            h.textContent = label;
            grid.appendChild(h);
        });

        // Blank offset cells for calendar alignment
        const month    = this._selectedMonth?.month ?? 1;
        const year     = new Date().getFullYear();
        const firstDow = new Date(year, month - 1, 1).getDay();
        for (let i = 0; i < firstDow; i++) {
            grid.appendChild(document.createElement('div'));
        }

        // Day cells
        days.forEach(dayItem => {
            const { day, isTaken, currentUserStatus } = dayItem;

            let bg, numColor, subLabel, subColor, canPick;

            if (currentUserStatus === 'active') {
                bg = 'bg-blue-100 border-blue-400'; numColor = 'text-blue-700';
                subLabel = '✓ You'; subColor = 'text-blue-500'; canPick = false;
            } else if (currentUserStatus === 'waiting') {
                bg = 'bg-yellow-100 border-yellow-400'; numColor = 'text-yellow-700';
                subLabel = '⏳'; subColor = 'text-yellow-500'; canPick = false;
            } else if (isTaken) {
                bg = 'bg-orange-50 border-orange-300'; numColor = 'text-orange-600';
                subLabel = 'taken'; subColor = 'text-orange-400'; canPick = true;
            } else {
                bg = 'bg-green-50 border-green-300'; numColor = 'text-gray-800';
                subLabel = ''; subColor = ''; canPick = true;
            }

            const card = document.createElement('div');
            card.className = [
                'border-2 rounded-lg py-1 text-center transition select-none',
                bg,
                canPick
                    ? 'cursor-pointer hover:ring-2 hover:ring-pink-400 hover:border-pink-400 hover:bg-pink-50'
                    : 'cursor-default opacity-75',
            ].join(' ');

            card.innerHTML = `
                <p class="font-bold text-sm leading-none ${numColor}">${day}</p>
                ${subLabel ? `<p class="text-[8px] ${subColor} leading-tight">${subLabel}</p>` : ''}`;

            if (canPick) {
                card.addEventListener('click', () => this._selectDay(card, dayItem));
            }

            grid.appendChild(card);
        });

        // Legend
        const legend = document.createElement('div');
        legend.id        = 'day-legend';
        legend.className = 'flex flex-wrap gap-x-3 gap-y-1 mt-2 text-[10px] text-gray-500';
        legend.innerHTML = `
            <span class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-green-400 inline-block"></span> Open
            </span>
            <span class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-orange-400 inline-block"></span> Taken (tap to join waitlist)
            </span>
            <span class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-blue-400 inline-block"></span> You joined
            </span>
            <span class="flex items-center gap-1">
                <span class="w-2 h-2 rounded-full bg-yellow-400 inline-block"></span> You're waiting
            </span>`;
        grid.after(legend);

        grid.classList.remove('hidden');
    }

    // ─────────────────────────────────────────────────────────
    //  DAY SELECTION → show time input
    // ─────────────────────────────────────────────────────────
    _selectDay(card, dayItem) {
    this.modal.querySelectorAll('#day-cards-grid > div').forEach(c =>
        c.classList.remove('ring-2', 'ring-pink-500', 'ring-offset-1')
    );
    card.classList.add('ring-2', 'ring-pink-500', 'ring-offset-1');

    this._selectedDay  = dayItem;
    this._selectedTime = null;
    this.modal.querySelector('#start-day').value  = dayItem.day;
    this.modal.querySelector('#start-time').value = '';
    this.modal.querySelector('#confirmEnrollmentBtn').disabled    = true;
    this.modal.querySelector('#confirmEnrollmentBtn').textContent = 'Pick a time…';

    // Show time picker section
    const monthLabel = this._selectedMonth?.label ?? '';
    const dayLabel   = `${monthLabel} ${dayItem.day}`;
    this.modal.querySelector('#selected-day-label').textContent = dayLabel;
    this.modal.querySelector('#time-picker-section').classList.remove('hidden');

    // Reset dropdowns
    const hourSelect   = this.modal.querySelector('#time-hour');
    const minuteSelect = this.modal.querySelector('#time-minute');
    hourSelect.value   = '';
    minuteSelect.value = '';

    // Clone to remove old listeners
    const newHour   = hourSelect.cloneNode(true);
    const newMinute = minuteSelect.cloneNode(true);
    hourSelect.parentNode.replaceChild(newHour, hourSelect);
    minuteSelect.parentNode.replaceChild(newMinute, minuteSelect);

    const onTimeChange = () => {
    const h = newHour.value;
    const m = newMinute.value;

    // When 6 PM (18) is selected, force minutes to 00 and disable the dropdown
    if (h === '18') {
        newMinute.value   = '00';
        newMinute.disabled = true;

        const val = '18:00';
        this._selectedTime = val;
        this.modal.querySelector('#start-time').value = val;

        const timeLabel  = '6:00 PM';
        const confirm    = this.modal.querySelector('#confirmEnrollmentBtn');
        const notice     = this.modal.querySelector('#waitlist-notice');
        const noticeText = this.modal.querySelector('#waitlist-notice-text');

        if (this._isSelectedMonthFull) {
            notice.classList.remove('hidden');
            const waitPos = (this._selectedMonth?.waitingCount ?? 0) + 1;
            noticeText.textContent =
                `${monthLabel} is full (${slotCap}/${slotCap}). You'll join the waiting list as position #${waitPos}.`;
            confirm.textContent = `Join Waiting List — ${monthLabel} ${dayItem.day}, ${timeLabel}`;
            confirm.disabled    = false;
        } else if (dayItem.isTaken) {
            notice.classList.remove('hidden');
            noticeText.textContent =
                `${monthLabel} ${dayItem.day} is already taken. You'll join the waiting list for this slot.`;
            confirm.textContent = `Join Waiting List — ${monthLabel} ${dayItem.day}, ${timeLabel}`;
            confirm.disabled    = false;
        } else {
            notice.classList.add('hidden');
            confirm.textContent = `Confirm — ${monthLabel} ${dayItem.day}, ${timeLabel}`;
            confirm.disabled    = false;
        }
        return;
    }

    // For all other hours, re-enable minutes
    newMinute.disabled = false;
    if (!h || !m) return;

    const val = `${h}:${m}`;
    this._selectedTime = val;
    this.modal.querySelector('#start-time').value = val;

    const hNum      = parseInt(h);
    const ampm      = hNum >= 12 ? 'PM' : 'AM';
    const h12       = hNum % 12 || 12;
    const timeLabel = `${h12}:${m} ${ampm}`;

    const confirm    = this.modal.querySelector('#confirmEnrollmentBtn');
    const notice     = this.modal.querySelector('#waitlist-notice');
    const noticeText = this.modal.querySelector('#waitlist-notice-text');

    if (this._isSelectedMonthFull) {
        notice.classList.remove('hidden');
        const waitPos = (this._selectedMonth?.waitingCount ?? 0) + 1;
        noticeText.textContent =
            `${monthLabel} is full (${slotCap}/${slotCap}). You'll join the waiting list as position #${waitPos}.`;
        confirm.textContent = `Join Waiting List — ${monthLabel} ${dayItem.day}, ${timeLabel}`;
        confirm.disabled    = false;
    } else if (dayItem.isTaken) {
        notice.classList.remove('hidden');
        noticeText.textContent =
            `${monthLabel} ${dayItem.day} is already taken. You'll join the waiting list for this slot.`;
        confirm.textContent = `Join Waiting List — ${monthLabel} ${dayItem.day}, ${timeLabel}`;
        confirm.disabled    = false;
    } else {
        notice.classList.add('hidden');
        confirm.textContent = `Confirm — ${monthLabel} ${dayItem.day}, ${timeLabel}`;
        confirm.disabled    = false;
    }
};

newHour.addEventListener('change', onTimeChange);
newMinute.addEventListener('change', onTimeChange);
}


    // ─────────────────────────────────────────────────────────
    //  CONFIRM ENROLLMENT
    // ─────────────────────────────────────────────────────────
    handleConfirmEnrollment() {
        if (!this.packageId) { showToast('Package not selected!'); return; }

        const startMonth = this.modal.querySelector('#start-month').value;
        const startDay   = this.modal.querySelector('#start-day').value;
        const startTime  = this.modal.querySelector('#start-time').value;

        if (!startMonth) { showToast('Please select a month.'); return; }
        if (!startDay)   { showToast('Please select a day.');   return; }
        if (!startTime)  { showToast('Please enter a delivery time.'); return; }

        const confirm    = this.modal.querySelector('#confirmEnrollmentBtn');
        const origText   = confirm.textContent;
        confirm.disabled    = true;
        confirm.textContent = 'Processing…';

        fetch('/paluwagan/join', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                packageID:  this.packageId,
                startMonth: parseInt(startMonth),
                startDay:   parseInt(startDay),
                startTime:  startTime,
            }),
        })
        .then(async res => {
            const text = await res.text();
            if (res.status === 401) {
                showToast('Session expired. Please login again.');
                window.location.href = '/login';
                return null;
            }
            try { return JSON.parse(text); }
            catch (_) { throw new Error(`Server error (${res.status})`); }
        })
        .then(data => {
            if (!data) return;
            if (data.success) {
                showToast(data.message || 'Successfully joined!');
                this.modal.classList.add('hidden');
                setTimeout(() => window.location.href = '/paluwagan', 1200);
            } else {
                showToast(data.error || data.message || 'Failed to join.');
                confirm.disabled    = false;
                confirm.textContent = origText;
            }
        })
        .catch(err => {
            console.error('Join error:', err);
            showToast(err.message || 'Network error while joining.');
            confirm.disabled    = false;
            confirm.textContent = origText;
        });
    }
}