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

    const message = document.getElementById('success-message');
    if (message) {
        setTimeout(() => {
            message.classList.add('opacity-0', 'transition-opacity', 'duration-500');
            setTimeout(() => message.remove(), 500);
        }, 5000);
    }

    // Correct toggle logic: fa-eye = hidden, fa-eye-slash = visible
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.previousElementSibling;
            const icon = btn.querySelector('i');

            if (input.type === 'password') {
                // Show password
                input.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                // Hide password
                input.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        });
    }); 
});
