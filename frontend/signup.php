<?php
// ============================================================================
// NSBM STUDY CIRCLE - DEDICATED SIGNUP PAGE
// Matches Visual Reference Screenshots: 2-Column Split Layout, 2-Step Flow
// ============================================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect to respective dashboard
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: admin/index.php");
        exit();
    } else {
        header("Location: student/index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - NSBM Study Circle</title>
    <!-- Website Favicon using official NSBM Study Circle Logo -->
    <link rel="icon" type="image/svg+xml" href="assets/images/logo.svg">
    <link rel="icon" type="image/jpeg" href="assets/images/logo.jpg">
    <link rel="alternate icon" type="image/jpeg" href="../src/logo.jpg">
    <link rel="apple-touch-icon" href="assets/images/logo.svg">
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/signup.css">
    <script>(function(){const t=localStorage.getItem('theme')||localStorage.getItem('study_circle_theme')||'light';document.documentElement.setAttribute('data-bs-theme',t);})();</script>
</head>
<body>

    <div class="signup-page-wrapper">
        <!-- ==================================================================
             LEFT PANEL: Branding, Navigation, 2-Step Form Card, Footer
             ================================================================== -->
        <div class="signup-left-panel">
            <!-- Top Bar: Logo & Brand + Theme Toggle & Back to Website -->
            <div class="signup-top-bar">
                <a href="index.php" class="signup-brand" aria-label="NSBM Study Circle Home">
                    <div class="signup-brand-logo-wrapper">
                        <img src="assets/images/logo.svg" onerror="this.src='../src/logo.jpg'" alt="NSBM Study Circle Logo" class="signup-brand-logo-img">
                    </div>
                    <div>
                        <div class="signup-brand-title">NSBM Study Circle</div>
                        <div class="signup-brand-subtitle">Learn Together. Grow Together.</div>
                    </div>
                </a>

                <div class="signup-top-actions">
                    <!-- Circular Theme Toggle Button -->
                    <button type="button" class="btn-signup-theme" id="signupThemeToggle" aria-label="Toggle theme" title="Toggle theme">
                        <i class="bi bi-moon-fill" id="signupThemeIcon"></i>
                    </button>
                    <!-- Back to website Link -->
                    <a href="index.php" class="btn-back-website">
                        <i class="bi bi-arrow-left"></i>
                        <span>Back to website</span>
                    </a>
                </div>
            </div>

            <!-- Centered Signup Card Container -->
            <div class="signup-card-container">
                <div class="signup-card">
                    <!-- Two-Step Indicator (Matches Figma Reference) -->
                    <div class="step-indicator-wrapper">
                        <!-- Step 1 Node -->
                        <div class="step-node active" id="stepNode1">
                            <div class="step-circle active" id="stepCircle1">1</div>
                            <span class="step-label">Account details</span>
                        </div>
                        <!-- Connecting Line -->
                        <div class="step-line" id="stepLine"></div>
                        <!-- Step 2 Node -->
                        <div class="step-node" id="stepNode2">
                            <div class="step-circle inactive" id="stepCircle2">2</div>
                            <span class="step-label">Academic profile</span>
                        </div>
                    </div>

                    <!-- Alert message container -->
                    <div id="signupAlertBox"></div>

                    <!-- STEP 1 FORM: Account Details -->
                    <form id="signupStep1Form" novalidate>
                        <div class="signup-eyebrow">JOIN THE COMMUNITY</div>
                        <h1 class="signup-title">Create your NSBM Study Circle account</h1>
                        <p class="signup-desc">Start with your basic account information.</p>

                        <!-- First Name and Last Name Side-by-Side -->
                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label for="firstName" class="form-label-custom">First name</label>
                                <input type="text" class="form-control-custom" id="firstName" name="first_name" placeholder="First name" required>
                                <div class="invalid-feedback-custom" id="firstNameError">Please enter your first name.</div>
                            </div>
                            <div class="col-sm-6">
                                <label for="lastName" class="form-label-custom">Last name</label>
                                <input type="text" class="form-control-custom" id="lastName" name="last_name" placeholder="Last name" required>
                                <div class="invalid-feedback-custom" id="lastNameError">Please enter your last name.</div>
                            </div>
                        </div>

                        <!-- Email Address -->
                        <div class="mb-3">
                            <label for="email" class="form-label-custom">Email address</label>
                            <input type="email" class="form-control-custom" id="email" name="email" placeholder="name@example.com" required>
                            <div class="invalid-feedback-custom" id="emailError">Please enter a valid email address.</div>
                        </div>

                        <!-- Create Password with Visibility Toggle -->
                        <div class="mb-3">
                            <label for="password" class="form-label-custom">Create password</label>
                            <div class="password-input-group">
                                <input type="password" class="form-control-custom pe-5" id="password" name="password" placeholder="At least 8 characters" required minlength="8">
                                <button type="button" class="btn-toggle-password" id="togglePasswordBtn" aria-label="Toggle password visibility">
                                    <i class="bi bi-eye" id="passwordEyeIcon"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback-custom" id="passwordError">Password must be at least 8 characters.</div>
                        </div>

                        <!-- Terms & Community Guidelines Checkbox -->
                        <div class="mb-4">
                            <div class="form-check custom-checkbox d-flex align-items-center gap-2">
                                <input class="form-check-input" type="checkbox" id="termsCheck" required>
                                <label class="form-check-label" for="termsCheck">
                                    I agree to the <a href="#communityGuidelinesModal" class="terms-link" data-bs-toggle="modal">community guidelines and terms</a>.
                                </label>
                            </div>
                            <div class="invalid-feedback-custom" id="termsError">You must accept the community guidelines and terms.</div>
                        </div>

                        <!-- Green Continue Button -->
                        <button type="submit" class="btn-continue-signup" id="btnContinueStep1">
                            <span>Continue</span>
                            <i class="bi bi-arrow-right"></i>
                        </button>

                        <!-- Log In Prompt -->
                        <div class="signup-login-prompt">
                            Already have an account? <a href="login.php" class="signup-login-link">Log in</a>
                        </div>
                    </form>

                    <!-- STEP 2 FORM: Academic Details (Hidden Initially) -->
                    <form id="signupStep2Form" style="display: none;" novalidate>
                        <div class="signup-eyebrow">STEP 2 OF 2 · ACADEMIC PROFILE</div>
                        <h2 class="signup-title" style="font-size: 1.55rem;">Personalize your learning circle</h2>
                        <p class="signup-desc">Select your faculty to get matched with module materials and sessions.</p>

                        <!-- Faculty Selection -->
                        <div class="mb-3">
                            <label for="facultySelect" class="form-label-custom">Faculty <span class="text-danger">*</span></label>
                            <select class="form-control-custom form-select" id="facultySelect" name="faculty" required>
                                <option value="" selected disabled>Select your faculty</option>
                                <option value="Computing">Faculty of Computing</option>
                                <option value="Business">Faculty of Business</option>
                                <option value="Engineering">Faculty of Engineering</option>
                                <option value="Science">Faculty of Science</option>
                            </select>
                            <div class="invalid-feedback-custom" id="facultyError">Please select your faculty.</div>
                        </div>

                        <!-- Degree Program -->
                        <div class="mb-3">
                            <label for="degreeProgram" class="form-label-custom">Degree program <span class="text-danger">*</span></label>
                            <input type="text" class="form-control-custom" id="degreeProgram" name="degree_program" placeholder="e.g. BSc (Hons) Software Engineering" required>
                            <div class="invalid-feedback-custom" id="degreeError">Please enter your degree program.</div>
                        </div>

                        <!-- Student ID & Academic Year in 2 columns -->
                        <div class="row g-3 mb-4">
                            <div class="col-sm-6">
                                <label for="studentId" class="form-label-custom">Student ID (optional)</label>
                                <input type="text" class="form-control-custom" id="studentId" name="student_id" placeholder="e.g. CB009842">
                            </div>
                            <div class="col-sm-6">
                                <label for="academicYear" class="form-label-custom">Academic year</label>
                                <select class="form-control-custom form-select" id="academicYear" name="academic_year">
                                    <option value="Year 1" selected>Year 1 (Freshman)</option>
                                    <option value="Year 2">Year 2 (Sophomore)</option>
                                    <option value="Year 3">Year 3 (Junior)</option>
                                    <option value="Year 4">Year 4 (Senior)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Action Buttons: Back + Complete Registration -->
                        <div class="d-flex gap-2">
                            <button type="button" class="btn-back-step" id="btnBackToStep1">
                                <i class="bi bi-arrow-left"></i>
                                <span>Back</span>
                            </button>
                            <button type="submit" class="btn-continue-signup flex-grow-1" id="btnCompleteSignup">
                                <span>Complete Registration</span>
                                <i class="bi bi-check2-circle"></i>
                            </button>
                        </div>

                        <!-- Log In Prompt -->
                        <div class="signup-login-prompt">
                            Already have an account? <a href="login.php" class="signup-login-link">Log in</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Footer: Copyright, Privacy & Help -->
            <div class="signup-footer">
                &copy; 2026 NSBM Study Circle &middot; <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy</a> &middot; <a href="contact.php">Help</a>
            </div>
        </div>

        <!-- ==================================================================
             RIGHT PANEL: Dark Navy Community Branding & 3 Feature Sections
             ================================================================== -->
        <div class="signup-right-panel">
            <div class="signup-right-content">
                <!-- Green Small Heading -->
                <div class="community-eyebrow">LEARN WITH YOUR COMMUNITY</div>

                <!-- Main Heading -->
                <h2 class="community-main-title">A stronger semester starts with the right circle.</h2>

                <!-- Description -->
                <p class="community-main-desc">
                    Create a focused academic profile and connect with the resources and people relevant to you.
                </p>

                <!-- Three Feature Sections (01, 02, 03) -->
                <div class="feature-item-row">
                    <div class="feature-badge-num">01</div>
                    <div>
                        <div class="feature-item-title">Find trusted material</div>
                        <p class="feature-item-desc">Organized for the modules you study.</p>
                    </div>
                </div>

                <div class="feature-item-row">
                    <div class="feature-badge-num">02</div>
                    <div>
                        <div class="feature-item-title">Ask better questions</div>
                        <p class="feature-item-desc">Learn from thoughtful peer explanations.</p>
                    </div>
                </div>

                <div class="feature-item-row">
                    <div class="feature-badge-num">03</div>
                    <div>
                        <div class="feature-item-title">Build consistent progress</div>
                        <p class="feature-item-desc">Plan focused sessions and study together.</p>
                    </div>
                </div>
            </div>

            <!-- Bottom Footnote -->
            <div class="signup-right-footer">
                Independent student learning platform
            </div>
        </div>
    </div>

    <!-- ======================================================================
         COMMUNITY GUIDELINES & TERMS MODAL
         ====================================================================== -->
    <div class="modal fade" id="communityGuidelinesModal" tabindex="-1" aria-labelledby="guidelinesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header border-bottom pb-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-success fs-4"></i>
                        <h5 class="modal-title fw-bold" id="guidelinesModalLabel">NSBM Community Guidelines &amp; Terms</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <h6 class="fw-bold text-dark">1. Academic Integrity &amp; Originality</h6>
                    <p class="small text-muted mb-3">All shared notes, summaries, and guides must be your own work or properly attributed. Never upload copyrighted university exam papers or plagiarized materials.</p>
                    
                    <h6 class="fw-bold text-dark">2. Respectful &amp; Inclusive Collaboration</h6>
                    <p class="small text-muted mb-3">Study Circle is an open, constructive academic environment. Treat peers, moderators, and student mentors with courtesy and respect.</p>
                    
                    <h6 class="fw-bold text-dark">3. Authentic Student Identity</h6>
                    <p class="small text-muted mb-3">Use your genuine name and university email address to maintain accountability and protect student privacy.</p>
                </div>
                <div class="modal-footer border-top pt-2">
                    <button type="button" class="btn btn-success fw-bold px-4 rounded-pill" data-bs-dismiss="modal" id="btnAcceptModalTerms">I Understand &amp; Agree</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================================
         PRIVACY POLICY MODAL
         ====================================================================== -->
    <div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold" id="privacyModalLabel">Privacy Policy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <p class="small text-muted">Your personal data is encrypted and securely stored. We never sell student information. Your account data is exclusively used to connect you with study resources, academic discussions, and relevant faculty study groups.</p>
                </div>
                <div class="modal-footer border-top pt-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Interactive Client-side Signup Engine (Validation & Step Flow) -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Theme toggle synchronization
            const themeToggleBtn = document.getElementById('signupThemeToggle');
            const themeIcon = document.getElementById('signupThemeIcon');
            const savedTheme = localStorage.getItem('theme') || localStorage.getItem('study_circle_theme') || 'light';

            function applyTheme(theme) {
                document.documentElement.setAttribute('data-bs-theme', theme);
                localStorage.setItem('theme', theme);
                localStorage.setItem('study_circle_theme', theme);
                if (themeIcon) {
                    themeIcon.className = (theme === 'dark') ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
                }
            }
            applyTheme(savedTheme);

            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', () => {
                    const currentTheme = document.documentElement.getAttribute('data-bs-theme');
                    applyTheme(currentTheme === 'dark' ? 'light' : 'dark');
                });
            }

            // 2. Password visibility toggle
            const togglePasswordBtn = document.getElementById('togglePasswordBtn');
            const passwordInput = document.getElementById('password');
            const passwordEyeIcon = document.getElementById('passwordEyeIcon');

            if (togglePasswordBtn && passwordInput && passwordEyeIcon) {
                togglePasswordBtn.addEventListener('click', () => {
                    const isPassword = passwordInput.getAttribute('type') === 'password';
                    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                    passwordEyeIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
                });
            }

            // 3. Modal terms accept button shortcut
            const btnAcceptModalTerms = document.getElementById('btnAcceptModalTerms');
            const termsCheck = document.getElementById('termsCheck');
            if (btnAcceptModalTerms && termsCheck) {
                btnAcceptModalTerms.addEventListener('click', () => {
                    termsCheck.checked = true;
                    termsCheck.classList.remove('is-invalid');
                    const err = document.getElementById('termsError');
                    if (err) err.style.display = 'none';
                });
            }

            // 4. Form references & Step 1 Validation
            const step1Form = document.getElementById('signupStep1Form');
            const step2Form = document.getElementById('signupStep2Form');
            const firstNameInput = document.getElementById('firstName');
            const lastNameInput = document.getElementById('lastName');
            const emailInput = document.getElementById('email');
            const btnBackToStep1 = document.getElementById('btnBackToStep1');

            // Step Indicator Elements
            const stepCircle1 = document.getElementById('stepCircle1');
            const stepCircle2 = document.getElementById('stepCircle2');
            const stepNode1 = document.getElementById('stepNode1');
            const stepNode2 = document.getElementById('stepNode2');
            const stepLine = document.getElementById('stepLine');
            const alertBox = document.getElementById('signupAlertBox');

            // Clear validation errors on input
            [firstNameInput, lastNameInput, emailInput, passwordInput].forEach(inp => {
                if (inp) {
                    inp.addEventListener('input', () => {
                        inp.classList.remove('is-invalid');
                        const err = document.getElementById(inp.id + 'Error');
                        if (err) err.style.display = 'none';
                    });
                }
            });

            if (termsCheck) {
                termsCheck.addEventListener('change', () => {
                    termsCheck.classList.remove('is-invalid');
                    const err = document.getElementById('termsError');
                    if (err) err.style.display = 'none';
                });
            }

            // Email validation regex
            function isValidEmail(val) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
            }

            // Handle Step 1 Submit
            step1Form.addEventListener('submit', (e) => {
                e.preventDefault();
                let isValid = true;

                // Validate First Name
                if (!firstNameInput.value.trim()) {
                    firstNameInput.classList.add('is-invalid');
                    document.getElementById('firstNameError').style.display = 'block';
                    isValid = false;
                }

                // Validate Last Name
                if (!lastNameInput.value.trim()) {
                    lastNameInput.classList.add('is-invalid');
                    document.getElementById('lastNameError').style.display = 'block';
                    isValid = false;
                }

                // Validate Email
                if (!emailInput.value.trim() || !isValidEmail(emailInput.value.trim())) {
                    emailInput.classList.add('is-invalid');
                    document.getElementById('emailError').style.display = 'block';
                    isValid = false;
                }

                // Validate Password (min 8 characters)
                if (!passwordInput.value || passwordInput.value.length < 8) {
                    passwordInput.classList.add('is-invalid');
                    document.getElementById('passwordError').style.display = 'block';
                    isValid = false;
                }

                // Validate Terms Checkbox
                if (!termsCheck.checked) {
                    termsCheck.classList.add('is-invalid');
                    document.getElementById('termsError').style.display = 'block';
                    isValid = false;
                }

                if (!isValid) return;

                // Smoothly Transition to Step 2
                step1Form.style.display = 'none';
                step2Form.style.display = 'block';

                // Update Step Indicator
                stepCircle1.className = 'step-circle completed';
                stepCircle1.innerHTML = '<i class="bi bi-check-lg"></i>';
                stepNode1.classList.remove('active');
                stepLine.className = 'step-line active';
                stepCircle2.className = 'step-circle active';
                stepNode2.classList.add('active');

                // Smooth scroll up if on mobile
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });

            // Handle Back to Step 1
            if (btnBackToStep1) {
                btnBackToStep1.addEventListener('click', () => {
                    step2Form.style.display = 'none';
                    step1Form.style.display = 'block';

                    // Revert Step Indicator
                    stepCircle1.className = 'step-circle active';
                    stepCircle1.textContent = '1';
                    stepNode1.classList.add('active');
                    stepLine.className = 'step-line';
                    stepCircle2.className = 'step-circle inactive';
                    stepNode2.classList.remove('active');
                });
            }

            // Handle Step 2 Complete
            step2Form.addEventListener('submit', (e) => {
                e.preventDefault();
                const facultySelect = document.getElementById('facultySelect');
                const degreeProgram = document.getElementById('degreeProgram');
                let step2Valid = true;

                if (!facultySelect.value) {
                    facultySelect.classList.add('is-invalid');
                    document.getElementById('facultyError').style.display = 'block';
                    step2Valid = false;
                }

                if (!degreeProgram.value.trim()) {
                    degreeProgram.classList.add('is-invalid');
                    document.getElementById('degreeError').style.display = 'block';
                    step2Valid = false;
                }

                if (!step2Valid) return;

                // Send registration payload to backend/controllers/auth_controller.php to save in MySQL (`users` & `login_logs`)
                const btnComplete = document.getElementById('btnCompleteSignup');
                btnComplete.disabled = true;
                btnComplete.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Creating account...';

                const fullName = `${firstNameInput.value.trim()} ${lastNameInput.value.trim()}`.trim();
                const studentIdVal = document.getElementById('studentId')?.value.trim() || '';
                const academicYearVal = document.getElementById('academicYear')?.value || 'Year 1';

                fetch('../backend/controllers/auth_controller.php?action=register&format=json', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'register',
                        name: fullName,
                        first_name: firstNameInput.value.trim(),
                        last_name: lastNameInput.value.trim(),
                        email: emailInput.value.trim(),
                        password: passwordInput.value,
                        faculty: facultySelect.value,
                        degree_program: degreeProgram.value.trim(),
                        student_id: studentIdVal,
                        academic_year: academicYearVal
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data && data.user) {
                        localStorage.setItem('nsbm_current_user_id', String(data.user.id));
                    }
                    localStorage.removeItem('nsbm_student_profile');
                    alertBox.innerHTML = `
                        <div class="alert alert-success border-0 rounded-3 shadow-sm py-3 mb-4" role="alert">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                <span class="fw-bold">Registration Saved to MySQL!</span>
                            </div>
                            <p class="small text-muted mb-0">Welcome to NSBM Study Circle, <strong>${fullName}</strong>! Redirecting to your Student Dashboard...</p>
                        </div>
                    `;
                    step2Form.style.display = 'none';
                    setTimeout(() => {
                        window.location.href = 'student/index.php';
                    }, 1000);
                })
                .catch(() => {
                    window.location.href = 'login.php?signup_success=1';
                });
            });
        });
    </script>
</body>
</html>

