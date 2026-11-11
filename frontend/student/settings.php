<?php
// NSBM STUDY CIRCLE - Student Portal Account Settings
include_once __DIR__ . '/includes/auth_check.php';
$page_title = 'Account settings';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account settings - Student Portal | NSBM Study Circle</title>
    <script>(function(){const t=localStorage.getItem('study_circle_theme')||localStorage.getItem('theme')||'light';document.documentElement.setAttribute('data-bs-theme',t);})();</script>
    <!-- Website Favicon -->
    <link rel="icon" type="image/svg+xml" href="../assets/images/logo.svg">
    <link rel="icon" type="image/jpeg" href="../assets/images/logo.jpg">
    <!-- Google Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/student.css">
</head>
<body>

<div class="student-layout">
    <!-- Sidebar Partial -->
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="student-main-content">
        <!-- Topbar Partial -->
        <?php include __DIR__ . '/includes/topbar.php'; ?>

        <div class="student-content-body">
            <!-- Page Header -->
            <div class="student-page-header">
                <div>
                    <h2 class="student-page-heading">Account settings</h2>
                    <p class="student-page-subheading">Manage security, privacy, and communication preferences.</p>
                </div>
            </div>

            <!-- Settings Layout Grid -->
            <div class="student-settings-grid">
                <!-- Left Nav Tabs -->
                <div class="student-settings-nav">
                    <a href="#sec-security" class="student-settings-tab-btn active" data-tab="security">Security</a>
                    <a href="#sec-notifications" class="student-settings-tab-btn" data-tab="notifications">Notifications</a>
                    <a href="#sec-privacy" class="student-settings-tab-btn" data-tab="privacy">Privacy</a>
                    <a href="#sec-account" class="student-settings-tab-btn" data-tab="account">Account</a>
                </div>

                <!-- Right Content Panels -->
                <div>
                    <!-- Security Card -->
                    <div class="student-card mb-4" id="sec-security">
                        <h3 class="student-card-title mb-1" style="font-size: 1.1rem;">Security</h3>
                        <p class="student-item-subtitle mb-4">Use a strong password that you do not reuse elsewhere.</p>

                        <form id="securityForm">
                            <!-- Current Password with Eye Icon Toggle -->
                            <div class="mb-3" style="max-width: 480px;">
                                <label class="form-label small fw-bold" for="currentPasswordInput">Current password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="currentPasswordInput" placeholder="Enter current password" value="Student@2025" required>
                                    <button class="btn btn-outline-secondary toggle-password-visibility" type="button" data-target="currentPasswordInput" title="Toggle current password visibility" style="border-color: #CBD5E1;">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text small text-muted">Click the eye icon to view or hide your password.</div>
                            </div>

                            <!-- New & Confirm Password with Visibility Toggles -->
                            <div class="row g-3 mb-4" style="max-width: 640px;">
                                <div class="col-sm-6">
                                    <label class="form-label small fw-bold" for="newPasswordInput">New password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="newPasswordInput" placeholder="At least 8 characters" required minlength="8">
                                        <button class="btn btn-outline-secondary toggle-password-visibility" type="button" data-target="newPasswordInput" title="Toggle new password visibility" style="border-color: #CBD5E1;">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text small text-muted" id="passwordStrengthText">Min. 8 characters with letters & numbers</div>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small fw-bold" for="confirmPasswordInput">Confirm new password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="confirmPasswordInput" placeholder="Repeat password" required minlength="8">
                                        <button class="btn btn-outline-secondary toggle-password-visibility" type="button" data-target="confirmPasswordInput" title="Toggle confirm password visibility" style="border-color: #CBD5E1;">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text small text-muted" id="passwordMatchText">Must match new password</div>
                                </div>
                            </div>

                            <button type="submit" class="student-btn-green" id="updatePasswordBtn">
                                <i class="bi bi-shield-check me-1"></i> Update password
                            </button>
                        </form>
                    </div>

                    <!-- Email Notifications Card -->
                    <div class="student-card mb-4" id="sec-notifications">
                        <h3 class="student-card-title mb-1" style="font-size: 1.1rem;">Email notifications</h3>
                        <p class="student-item-subtitle mb-4">Choose which activity should reach your email inbox.</p>

                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <span class="fw-semibold small">Answers to my questions</span>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" checked>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <span class="fw-semibold small">Group and session updates</span>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" checked>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <span class="fw-semibold small">Resource review decisions</span>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" checked>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between py-2">
                                <span class="fw-semibold small">NSBM Study Circle product news</span>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Privacy Card -->
                    <div class="student-card mb-4" id="sec-privacy">
                        <h3 class="student-card-title mb-1" style="font-size: 1.1rem;">Privacy</h3>
                        <p class="student-item-subtitle mb-4">Control how other members see your activity.</p>

                        <div class="mb-4" style="max-width: 480px;">
                            <label class="form-label small fw-bold">Profile visibility</label>
                            <select class="form-select">
                                <option selected>NSBM Study Circle members</option>
                                <option>Only enrolled course peers</option>
                                <option>Private (Only group leaders)</option>
                            </select>
                        </div>

                        <div class="d-flex flex-column gap-3">
                            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
                                <span class="fw-semibold small">Show my contribution history</span>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" checked>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between py-2">
                                <span class="fw-semibold small">Allow group invitations</span>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" checked>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Deletion Card -->
                    <div class="student-card mb-0" id="sec-account">
                        <h3 class="student-card-title text-danger mb-1" style="font-size: 1.1rem;">Account</h3>
                        <p class="student-item-subtitle mb-4">Account deletion permanently removes your profile and private preferences.</p>

                        <div>
                            <button type="button" class="btn btn-outline-danger px-3 py-2 fw-semibold" id="triggerDeleteAccountBtn" data-bs-toggle="modal" data-bs-target="#deleteAccountModal" style="border-radius: 8px; font-size: 0.88rem;">
                                <i class="bi bi-trash3 me-1"></i> Delete account
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal: Delete Account Confirmation -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 14px; border: 1px solid #FECACA;">
            <div class="modal-header border-bottom-0 pb-0">
                <div class="d-flex align-items-center gap-2 text-danger">
                    <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                    <h5 class="modal-title fw-bold" id="deleteAccountModalLabel">Delete Student Account</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-secondary small mb-3">
                    Are you sure you want to permanently delete your NSBM Study Circle account? This action is <strong>irreversible</strong> and will immediately delete:
                </p>
                <ul class="text-secondary small mb-3">
                    <li>Your student profile details, bio, and academic identity</li>
                    <li>Your uploaded lecture notes and revision guides</li>
                    <li>Your study group memberships and discussion posts</li>
                    <li>Your saved bookmarks and download history</li>
                </ul>
                <div class="form-check mb-3 p-3 rounded" style="background: #FEF2F2; border: 1px solid #FEE2E2;">
                    <input class="form-check-input ms-0 me-2" type="checkbox" id="confirmDeleteCheck">
                    <label class="form-check-label small fw-semibold text-danger" for="confirmDeleteCheck">
                        I understand that deleting my account is permanent and cannot be undone.
                    </label>
                </div>
                <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                    <button type="button" class="student-btn-outline" data-bs-dismiss="modal">Keep My Account</button>
                    <button type="button" class="btn btn-danger px-3 py-2 fw-semibold" id="confirmDeleteAccountBtn" style="border-radius: 8px; font-size: 0.88rem;" disabled>
                        <i class="bi bi-trash3 me-1"></i> Permanently Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/student.js"></script>
</body>
</html>
