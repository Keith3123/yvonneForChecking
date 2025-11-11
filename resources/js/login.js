document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('loginForm');

    form.addEventListener('submit', (e) => {
        const inputs = form.querySelectorAll('input[required]');
        for (let input of inputs) {
            if (!input.checkValidity()) {
                e.preventDefault();
                input.reportValidity();
                return;
            }
        }
    });
});
