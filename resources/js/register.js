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

    // ✅ Validate current step using browser’s native validation
    const validateStep = (index) => {
        const currentInputs = steps[index].querySelectorAll('input[required]');
        for (let input of currentInputs) {
            if (!input.checkValidity()) {
                input.reportValidity(); // native "Please fill out this field"
                return false;
            }
        }

        // Step 3 extra password validation
        if (index === 2) {
            const password = steps[index].querySelector('input[name="password"]');
            const confirmPassword = steps[index].querySelector('input[name="password_confirmation"]');
            const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;

            if (!regex.test(password.value)) {
                alert("Password must be at least 8 characters long and include uppercase, lowercase, and a number.");
                password.focus();
                return false;
            }

            if (password.value !== confirmPassword.value) {
                alert("Confirm Password does not match the Password!");
                confirmPassword.focus();
                return false;
            }
        }

        return true;
    };

    showStep(currentStep);

    // Next buttons
    document.querySelectorAll('.next-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            if (currentStep < steps.length - 1) {
                if (validateStep(currentStep)) {
                    currentStep++;
                    showStep(currentStep);
                }
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
});
