/**
 * Smart Campus - Authentication & Login Script
 * Handles client-side form validation, AJAX submission, and user feedback
 */

document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const loginBtn = document.getElementById('loginBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');
    const alertBox = document.getElementById('alertBox');
    const togglePasswordBtn = document.getElementById('togglePassword');
    const toggleIcon = document.getElementById('toggleIcon');

    // Toggle Password Visibility
    if (togglePasswordBtn) {
        togglePasswordBtn.addEventListener('click', function () {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    }

    // Helper: Show Alert
    function showAlert(message, type = 'danger') {
        alertBox.className = `alert alert-${type} py-2 px-3 small mb-3`;
        alertBox.innerHTML = message;
        alertBox.classList.remove('d-none');
    }

    // Helper: Hide Alert
    function hideAlert() {
        alertBox.classList.add('d-none');
        alertBox.innerHTML = '';
    }

    // Helper: Email format validator
    function isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email.toLowerCase());
    }

    // Handle Form Submit
    loginForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        hideAlert();

        const email = emailInput.value.trim();
        const password = passwordInput.value;

        // Step 1: Client-side Validation
        let hasError = false;

        if (!email || !isValidEmail(email)) {
            emailInput.classList.add('is-invalid');
            hasError = true;
        } else {
            emailInput.classList.remove('is-invalid');
        }

        if (!password) {
            passwordInput.classList.add('is-invalid');
            hasError = true;
        } else {
            passwordInput.classList.remove('is-invalid');
        }

        if (hasError) {
            showAlert('<i class="bi bi-exclamation-triangle-fill me-2"></i>Please fill in all required fields correctly.', 'danger');
            return;
        }

        // Step 2: Display loading state
        loginBtn.disabled = true;
        btnText.classList.add('d-none');
        btnSpinner.classList.remove('d-none');

        try {
            // Step 3: Send AJAX Request to PHP Backend
            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);

            const response = await fetch('../../backend/auth/login_process.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.status === 'success') {
                showAlert(`<i class="bi bi-check-circle-fill me-2"></i>Welcome, <strong>${result.name}</strong>! Redirecting to ${result.role} dashboard...`, 'success');
                setTimeout(() => {
                    window.location.href = result.redirect;
                }, 1000);
            } else {
                showAlert(`<i class="bi bi-x-circle-fill me-2"></i>${result.message || 'Authentication failed.'}`, 'danger');
                loginBtn.disabled = false;
                btnText.classList.remove('d-none');
                btnSpinner.classList.add('d-none');
            }
        } catch (error) {
            console.error('Login error:', error);
            showAlert('<i class="bi bi-exclamation-circle-fill me-2"></i>Could not connect to server. Ensure Apache & MySQL are running in XAMPP.', 'danger');
            loginBtn.disabled = false;
            btnText.classList.remove('d-none');
            btnSpinner.classList.add('d-none');
        }
    });
});

// Global function for quick test credential buttons
function fillCredentials(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
    document.getElementById('email').classList.remove('is-invalid');
    document.getElementById('password').classList.remove('is-invalid');
}
