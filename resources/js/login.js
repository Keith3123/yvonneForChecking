document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const loginText = document.getElementById('loginText');
    const spinner = document.getElementById('loginSpinner');

    // ✅ FIX: avoid JS error if form not found (modal or other page)
    if (form) {
        form.addEventListener('submit', (e) => {
            const inputs = form.querySelectorAll('input[required]');

            for (let input of inputs) {
                if (!input.checkValidity()) {
                    e.preventDefault();
                    input.reportValidity();
                    return;
                }
            }

            if (loginBtn && loginText && spinner) {
                loginBtn.disabled = true;
                loginText.textContent = 'Logging in...';
                spinner.classList.remove('hidden');
            }
        });

        // ✅ FORCE ENTER KEY TO SUBMIT FORM
        form.querySelectorAll('input').forEach(input => {
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    form.requestSubmit(); // triggers your submit event properly
                }
            });
        });
    }

    // ✅ Success message fade
    const message = document.getElementById('success-message');
    if (message) {
        setTimeout(() => {
            message.classList.add('opacity-0');
            setTimeout(() => message.remove(), 500);
        }, 1000);
    }

    // ✅ Password toggle (works for modal + page)
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.previousElementSibling;
            const icon = btn.querySelector('i');

            if (!input || !icon) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
    });

});

