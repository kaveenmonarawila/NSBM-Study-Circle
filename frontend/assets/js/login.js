// ============================================================================
// NSBM STUDY CIRCLE - FRONTEND LOGIN VALIDATION & AJAX HANDLER
// Connects Login UI with backend/controllers/auth_controller.php
// ============================================================================

document.addEventListener('DOMContentLoaded', () => {
    // 1. Password Visibility Toggle
    const toggleBtns = document.querySelectorAll('.toggle-password-trigger');
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetInput = btn.closest('.input-group').querySelector('input');
            const icon = btn.querySelector('i');
            if (targetInput) {
                if (targetInput.type === 'password') {
                    targetInput.type = 'text';
                    if (icon) icon.className = 'bi bi-eye-slash';
                } else {
                    targetInput.type = 'password';
                    if (icon) icon.className = 'bi bi-eye';
                }
            }
        });
    });

    // 2. Generic Login Form AJAX Submission Handler
    const handleLoginForm = (formId, alertBoxId, roleInputId) => {
        const form = document.getElementById(formId);
        const alertBox = document.getElementById(alertBoxId);
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : 'Sign In';

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Authenticating...
                `;
            }

            const formData = new FormData(form);

            try {
                const response = await fetch('../backend/controllers/auth_controller.php?format=json', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    if (alertBox) {
                        alertBox.innerHTML = `
                            <div class="alert alert-success py-2 small" role="alert">
                                <i class="bi bi-check-circle-fill me-1"></i> ${data.message}
                            </div>
                        `;
                    }
                    setTimeout(() => {
                        window.location.href = data.redirect || 'student/index.php';
                    }, 500);
                } else {
                    if (alertBox) {
                        alertBox.innerHTML = `
                            <div class="alert alert-danger py-2 small" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> ${data.message}
                            </div>
                        `;
                    }
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnHtml;
                    }
                }
            } catch (err) {
                // If fetch fails (e.g. cross-origin or network error), fallback to native form submit
                form.submit();
            }
        });
    };

    // Attach to login forms
    handleLoginForm('loginForm', 'loginAlertBox', 'formRoleInput');
    handleLoginForm('modalLoginForm', 'modalLoginAlertBox', 'modalRoleInput');
});
