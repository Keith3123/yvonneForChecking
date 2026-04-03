document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('registerForm');
    if (!form) return;

    const steps = form.querySelectorAll('.step');
    let currentStep = 0;

    let passwordValid = false;
    let usernameValid = true;
    let emailValid = true;

    /**
     * =========================
     * PASSWORD RULES
     * =========================
     */
    const validatePasswordRules = (password) => ({
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /\d/.test(password),
    });

    /**
     * =========================
     * PROGRESS BAR
     * =========================
     */
    const circles = document.querySelectorAll('.progress-step, .progress-step-modal');
    const progressLine =
        document.getElementById('progress-line') ||
        document.getElementById('progress-line-modal');

    const updateProgress = (index) => {
        if (!circles.length || !progressLine) return;
        const total = circles.length - 1;
        const progress = (index / total) * 100;
        progressLine.style.width = `${progress}%`;

        circles.forEach((circle, i) => {
            circle.classList.toggle('bg-pink-400', i <= index);
            circle.classList.toggle('text-white', i <= index);
            circle.classList.toggle('bg-gray-300', i > index);
            circle.classList.toggle('text-gray-700', i > index);
        });
    };

    const showStep = (index) => {
        steps.forEach((step, i) => step.classList.toggle('hidden', i !== index));
        updateProgress(index);
    };

    /**
     * =========================
     * STEP VALIDATION
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

        if (index === 2) {
            const password = steps[index].querySelector('input[name="password"]');
            const confirmPassword = steps[index].querySelector('input[name="password_confirmation"]');
            const errorMsg = confirmPassword.parentElement.querySelector('.password-error');

            if (!passwordValid) {
                password.classList.add('border-red-500');
                valid = false;
            }

            if (password.value !== confirmPassword.value) {
                confirmPassword.classList.add('border-red-500');
                errorMsg?.classList.remove('hidden');
                valid = false;
            }
        }

        return valid;
    };

    /**
     * =========================
     * NAVIGATION BUTTONS
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
            const input = btn.closest('label').querySelector('.password-input');
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
        phoneError.innerText = "Invalid phone number (11 digits)";
        phoneField.parentElement.appendChild(phoneError);

        phoneField.addEventListener('input', () => {
            const valid = /^\d{11}$/.test(phoneField.value);
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
     * PASSWORD LIVE UI
     * =========================
     */
    const step3 = steps[2];
    if (step3) {
        const password = step3.querySelector('input[name="password"]');
        const confirmPassword = step3.querySelector('input[name="password_confirmation"]');
        const errorMsg = confirmPassword?.parentElement.querySelector('.password-error');
        const rulesContainer = step3.querySelector('.password-rules');

        const ruleLength = rulesContainer?.querySelector('[data-rule="length"]');
        const ruleUpper = rulesContainer?.querySelector('[data-rule="uppercase"]');
        const ruleLower = rulesContainer?.querySelector('[data-rule="lowercase"]');
        const ruleNumber = rulesContainer?.querySelector('[data-rule="number"]');

        const updateRuleUI = () => {
            const checks = validatePasswordRules(password.value);
            const update = (el, condition) => {
                if (!el) return;
                const text = el.textContent.replace(/^✔|✖/, '').trim();
                el.classList.remove('text-red-500', 'text-green-500');
                if (condition) {
                    el.classList.add('text-green-500');
                    el.innerHTML = `✔ ${text}`;
                } else {
                    el.classList.add('text-red-500');
                    el.innerHTML = `✖ ${text}`;
                }
            };
            update(ruleLength, checks.length);
            update(ruleUpper, checks.uppercase);
            update(ruleLower, checks.lowercase);
            update(ruleNumber, checks.number);
            passwordValid = Object.values(checks).every(Boolean);
            password.classList.remove('border-red-500', 'border-green-500');
            password.classList.add(passwordValid ? 'border-green-500' : 'border-red-500');
        };

        const checkMatch = () => {
            if (!confirmPassword.value) return;
            if (password.value !== confirmPassword.value) {
                confirmPassword.classList.add('border-red-500');
                confirmPassword.classList.remove('border-green-500');
                errorMsg?.classList.remove('hidden');
            } else {
                confirmPassword.classList.remove('border-red-500');
                confirmPassword.classList.add('border-green-500');
                errorMsg?.classList.add('hidden');
            }
        };

        password?.addEventListener('input', () => {
            updateRuleUI();
            checkMatch();
        });
        confirmPassword?.addEventListener('input', checkMatch);
    }

    /**
 * =========================
 * ENTER KEY NAVIGATION (FIXED)
 * =========================
 */
form.querySelectorAll('input').forEach(input => {
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();

            // If NOT last step → go next
            if (currentStep < steps.length - 1) {
                const nextBtn = steps[currentStep].querySelector('.next-btn');
                if (nextBtn) nextBtn.click();
            } 
            // If last step → submit
            else {
                form.requestSubmit();
            }
        }
    });
});

    /**
     * =========================
     * SERVER-SIDE ERROR DETECTION
     * =========================
     */
    const firstErrorInput = document.querySelector('.is-invalid');
    if (firstErrorInput) {
        const stepWithError = firstErrorInput.closest('.step');
        if (stepWithError) {
            currentStep = [...steps].indexOf(stepWithError);
        }
    }
    showStep(currentStep);

    /**
     * =========================
     * FORM SUBMIT
     * =========================
     */
    form.addEventListener('submit', (e) => {
        const validStep = validateStep(2);
        if (!validStep || !passwordValid || !usernameValid || !emailValid) {
            e.preventDefault();
            currentStep = 2;
            showStep(currentStep);
        }
    });
});