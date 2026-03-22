document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('registerForm');
    if (!form) return;

    const steps = form.querySelectorAll('.step');
    let currentStep = 0;

    /**
     * =========================
     * PASSWORD RULE CHECK
     * =========================
     */
    let passwordValid = false;

    const validatePasswordRules = (password) => {
        return {
            length: password.length >= 8,
            upper: /[A-Z]/.test(password),
            lower: /[a-z]/.test(password),
            number: /\d/.test(password),
        };
    };

    /**
     * =========================
     * PROGRESS BAR (PAGE)
     * =========================
     */
    const circlesPage = document.querySelectorAll('.progress-step');
    const progressLinePage = document.getElementById('progress-line');

    const updateProgressPage = (index) => {
        if (!circlesPage.length || !progressLinePage) return;

        const total = circlesPage.length - 1;
        const progress = (index / total) * 100;

        progressLinePage.style.width = `${progress}%`;

        circlesPage.forEach((circle, i) => {
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
        updateProgressPage(index);
    };

    showStep(currentStep);

    /**
     * =========================
     * VALIDATION
     * =========================
     */
    const validateStep = (index) => {
        const inputs = steps[index].querySelectorAll('input[required]');
        let valid = true;

        inputs.forEach(input => {
            input.classList.remove('border-red-500');

            if (!input.checkValidity()) {
                input.reportValidity();
                valid = false;
            }
        });

        // STEP 3 VALIDATION
        if (index === 2) {
            const password = steps[index].querySelector('input[name="password"]');
            const confirmPassword = steps[index].querySelector('input[name="password_confirmation"]');
            const errorMsg = confirmPassword.parentElement.querySelector('.password-error');

            if (errorMsg) errorMsg.classList.add('hidden');

            if (!passwordValid) {
                password.classList.add('border-red-500');
                valid = false;
            }

            if (password.value !== confirmPassword.value) {
                confirmPassword.classList.add('border-red-500');
                if (errorMsg) errorMsg.classList.remove('hidden');
                valid = false;
            }
        }

        return valid;
    };

    /**
     * =========================
     * NAVIGATION
     * =========================
     */
    form.querySelectorAll('.next-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (currentStep < steps.length - 1 && validateStep(currentStep)) {
                currentStep++;
                showStep(currentStep);
            }
        });
    });

    form.querySelectorAll('.back-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (currentStep > 0) {
                currentStep--;
                showStep(currentStep);
            }
        });
    });

    /**
     * =========================
     * PASSWORD TOGGLE
     * =========================
     */
    form.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', () => {
            const container = btn.closest('label');
            const input = container.querySelector('.password-input');
            const icon = btn.querySelector('i');

            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            }
        });
    });

    /**
     * =========================
     * USERNAME CHECK
     * =========================
     */
    const usernameField = form.querySelector('input[name="username"]');
    const usernameError = form.querySelector('#username-error');
    let usernameValid = false;

    if (usernameField) {
        usernameField.addEventListener('input', () => {
            const username = usernameField.value;

            if (username.length > 3) {
                fetch('/check-username', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ username })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.exists) {
                        usernameField.classList.add('border-red-500');
                        usernameError?.classList.remove('hidden');
                        usernameValid = false;
                    } else {
                        usernameField.classList.remove('border-red-500');
                        usernameError?.classList.add('hidden');
                        usernameValid = true;
                    }
                });
            }
        });
    }

    /**
     * =========================
     * EMAIL CHECK
     * =========================
     */
    const emailField = form.querySelector('input[name="email"]');
    let emailValid = false;

    if (emailField) {
        const emailError = document.createElement('span');
        emailError.className = "text-red-500 text-xs mt-1 hidden";
        emailError.innerText = "Email already exists";
        emailField.parentElement.appendChild(emailError);

        emailField.addEventListener('input', () => {
            const email = emailField.value;

            if (email.length > 5) {
                fetch('/check-email', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ email })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.exists) {
                        emailField.classList.add('border-red-500');
                        emailError.classList.remove('hidden');
                        emailValid = false;
                    } else {
                        emailField.classList.remove('border-red-500');
                        emailError.classList.add('hidden');
                        emailValid = true;
                    }
                });
            }
        });
    }

    /**
     * =========================
     * PHONE VALIDATION
     * =========================
     */
    const phoneField = form.querySelector('input[name="phone"]');

    if (phoneField) {
        const phoneError = document.createElement('span');
        phoneError.className = "text-red-500 text-xs mt-1 hidden";
        phoneError.innerText = "Invalid phone number (must be 11 digits)";
        phoneField.parentElement.appendChild(phoneError);

        phoneField.addEventListener('input', () => {
            const value = phoneField.value;
            const valid = /^\d{11}$/.test(value);

            if (!valid) {
                phoneField.classList.add('border-red-500');
                phoneError.classList.remove('hidden');
            } else {
                phoneField.classList.remove('border-red-500');
                phoneError.classList.add('hidden');
            }
        });
    }

    /**
     * =========================
     * PASSWORD LIVE UI CHECK
     * =========================
     */
    const step3 = steps[2];

    if (step3) {
        const password = step3.querySelector('input[name="password"]');
        const confirmPassword = step3.querySelector('input[name="password_confirmation"]');
        const errorMsg = confirmPassword?.parentElement.querySelector('.password-error');

        const rulesContainer = step3.querySelector('.password-rules');

        const ruleElements = {
            length: rulesContainer?.querySelector('[data-rule="length"]'),
            upper: rulesContainer?.querySelector('[data-rule="upper"]'),
            lower: rulesContainer?.querySelector('[data-rule="lower"]'),
            number: rulesContainer?.querySelector('[data-rule="number"]'),
        };

        const updateUI = (value) => {
            const checks = validatePasswordRules(value);

            passwordValid = Object.values(checks).every(v => v);

            Object.keys(checks).forEach(key => {
                const el = ruleElements[key];
                if (!el) return;

                if (checks[key]) {
                    el.textContent = "✔ " + el.textContent.substring(2);
                    el.classList.add('text-green-500');
                    el.classList.remove('text-gray-400');
                } else {
                    el.textContent = "✖ " + el.textContent.substring(2);
                    el.classList.remove('text-green-500');
                    el.classList.add('text-gray-400');
                }
            });

            password.classList.toggle('border-red-500', !passwordValid);
        };

        const checkMatch = () => {
            if (!confirmPassword.value) return;

            if (password.value !== confirmPassword.value) {
                confirmPassword.classList.add('border-red-500');
                errorMsg?.classList.remove('hidden');
            } else {
                confirmPassword.classList.remove('border-red-500');
                errorMsg?.classList.add('hidden');
            }
        };

        password?.addEventListener('input', () => {
            updateUI(password.value);
            checkMatch();
        });

        confirmPassword?.addEventListener('input', checkMatch);
    }

    /**
     * =========================
     * SUBMIT VALIDATION
     * =========================
     */
    form.addEventListener('submit', (e) => {
        const passOK = validateStep(2);

        if (!passOK || !passwordValid || usernameValid === false || emailValid === false) {
            e.preventDefault();

            // balik sa step 3
            currentStep = 2;
            showStep(currentStep);

            if (!passwordValid) {
                form.querySelector('input[name="password"]').focus();
            }
        }
    });

});