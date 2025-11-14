document.addEventListener('DOMContentLoaded', () => {
    const steps = document.querySelectorAll('.step');
    const circles = document.querySelectorAll('.progress-step');
    const progressLine = document.getElementById('progress-line');
    let currentStep = 0;

    const updateProgressBar = (index) => {
        const total = circles.length - 1;
        const progress = (index / total) * 100;
        progressLine.style.width = `${progress}%`;

        circles.forEach((circle, i) => {
            if (i <= index) {
                circle.classList.add('bg-pink-400', 'text-white');
                circle.classList.remove('bg-gray-300', 'text-gray-700');
            } else {
                circle.classList.remove('bg-pink-400', 'text-white');
                circle.classList.add('bg-gray-300', 'text-gray-700');
            }
        });
    };

    const showStep = (index) => {
        steps.forEach((step, i) => step.classList.toggle('hidden', i !== index));
        updateProgressBar(index);
    };

    showStep(currentStep);

    // Validate current step
    const validateStep = (index) => {
        const currentInputs = steps[index].querySelectorAll('input[required]');
        let valid = true;

        currentInputs.forEach(input => {
            input.classList.remove('border-red-500');
            if (!input.checkValidity()) {
                input.reportValidity();
                valid = false;
            }
        });

        // Step 3 extra password validation
        if (index === 2) {
            const password = steps[index].querySelector('input[name="password"]');
            const confirmPassword = steps[index].querySelector('input[name="password_confirmation"]');
            const errorMsg = confirmPassword.parentElement.querySelector('.password-error');

            errorMsg.classList.add('hidden');
            password.classList.remove('border-red-500');
            confirmPassword.classList.remove('border-red-500');

            if (password.value !== confirmPassword.value) {
                password.classList.add('border-red-500');
                confirmPassword.classList.add('border-red-500');
                errorMsg.classList.remove('hidden');
                valid = false;
            }
        }

        return valid;
    };

    // Next buttons
    document.querySelectorAll('.next-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (currentStep < steps.length - 1 && validateStep(currentStep)) {
                currentStep++;
                showStep(currentStep);
            }
        });
    });

    // Back buttons
    document.querySelectorAll('.back-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (currentStep > 0) {
                currentStep--;
                showStep(currentStep);
            }
        });
    });

    // Toggle password visibility (eye icon)
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.previousElementSibling;
            input.type = input.type === 'password' ? 'text' : 'password';
        });
    });

    // Live password confirmation check (Step 3)
    const step3 = steps[2];
    if (step3) {
        const password = step3.querySelector('input[name="password"]');
        const confirmPassword = step3.querySelector('input[name="password_confirmation"]');
        const errorMsg = confirmPassword.parentElement.querySelector('.password-error');

        const checkMatch = () => {
            if (confirmPassword.value === "") {
                confirmPassword.classList.remove('border-red-500');
                errorMsg.classList.add('hidden');
                return;
            }

            if (password.value !== confirmPassword.value) {
                password.classList.add('border-red-500');
                confirmPassword.classList.add('border-red-500');
                errorMsg.classList.remove('hidden');
            } else {
                password.classList.remove('border-red-500');
                confirmPassword.classList.remove('border-red-500');
                errorMsg.classList.add('hidden');
            }
        };

        password.addEventListener('input', checkMatch);
        confirmPassword.addEventListener('input', checkMatch);
    }

    // Step 3 submit
    const form = document.getElementById('registerForm');
    form.addEventListener('submit', (e) => {
        if (!validateStep(2)) {
            e.preventDefault();
            const confirmPassword = step3.querySelector('input[name="password_confirmation"]');
            confirmPassword.focus();
        }
    });
});
