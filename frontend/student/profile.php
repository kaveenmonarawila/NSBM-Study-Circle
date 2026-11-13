<?php
// NSBM STUDY CIRCLE - Student Portal Student Profile
include_once __DIR__ . '/includes/auth_check.php';
$page_title = 'Student profile';

$uploadedCount = 0;
$downloadedCount = 0;
$answersCount = 0;
$memberSince = 'February 2025';

if (isset($conn) && $conn && !$conn->connect_error) {
    $uRes = $conn->query("SELECT COUNT(*) FROM resources WHERE uploaded_by = $currentUserId");
    if ($uRes) $uploadedCount = (int)$uRes->fetch_row()[0];

    $dRes = $conn->query("SELECT COUNT(*) FROM resource_downloads WHERE user_id = $currentUserId");
    if ($dRes) $downloadedCount = (int)$dRes->fetch_row()[0];

    $aRes = $conn->query("SELECT COUNT(*) FROM discussion_answers WHERE author_id = $currentUserId");
    if ($aRes) $answersCount = (int)$aRes->fetch_row()[0];

    if (!empty($currentUser['created_at'])) {
        $memberSince = date('F Y', strtotime($currentUser['created_at']));
    }
}

$userBio = !empty($currentUser['bio']) ? $currentUser['bio'] : 'NSBM undergraduate student collaborating and sharing resources on the NSBM Study Circle platform.';
$userInterests = !empty($currentUser['academic_interests']) ? $currentUser['academic_interests'] : 'Algorithms, Databases, Web Technologies';
$userVisibility = !empty($currentUser['profile_visibility']) ? $currentUser['profile_visibility'] : 'NSBM Study Circle members';
$facultyDisplay = (strpos(strtolower($currentUserFaculty), 'faculty') === 0) ? $currentUserFaculty : "Faculty of {$currentUserFaculty}";
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student profile - Student Dashboard | NSBM Study Circle</title>
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
                    <h2 class="student-page-heading">Student profile</h2>
                    <p class="student-page-subheading">Your academic identity and community contributions.</p>
                </div>
            </div>

            <!-- Profile Banner Card (Dynamically Rendered from MySQL) -->
            <div class="student-profile-hero">
                <div class="student-profile-cover"></div>
                <div class="student-profile-body">
                    <div class="d-flex align-items-end gap-3 flex-wrap">
                        <div class="student-profile-avatar-large" id="profileAvatarLarge"><?= htmlspecialchars($currentUserInitials) ?></div>
                        <div>
                            <h3 class="student-hero-heading mb-1" id="profileDisplayName" style="font-size: 1.45rem;"><?= htmlspecialchars($currentUserName) ?></h3>
                            <p class="text-muted mb-1" id="profileDisplayDegree" style="font-size: 0.88rem;"><?= htmlspecialchars($currentUserDegree) ?></p>
                            <span class="badge" id="profileDisplayFaculty" style="background-color: #EFF6FF; color: #1D4ED8; font-weight: 700; font-size: 0.75rem; padding: 4px 10px; border-radius: 6px;">
                                <?= htmlspecialchars($facultyDisplay) ?>
                            </span>
                        </div>
                    </div>
                    <div>
                        <button type="button" class="student-btn-outline" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="bi bi-pencil-square me-1"></i> Edit profile
                        </button>
                    </div>
                </div>
            </div>

            <!-- Two Column Row: About & Community Contribution -->
            <div class="student-grid-2col">
                <!-- About Card -->
                <div class="student-card mb-0">
                    <h3 class="student-card-title mb-3" style="font-size: 1.1rem;">About</h3>
                    <p class="student-item-subtitle mb-4" id="profileDisplayBio" style="line-height: 1.6; font-size: 0.88rem;">
                        <?= htmlspecialchars($userBio) ?>
                    </p>

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Academic interests</span>
                            <p class="fw-bold mt-1 mb-0" id="profileDisplayInterests" style="font-size: 0.88rem;"><?= htmlspecialchars($userInterests) ?></p>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Member since</span>
                            <p class="fw-bold mt-1 mb-0" id="profileDisplayMemberSince" style="font-size: 0.88rem;"><?= htmlspecialchars($memberSince) ?></p>
                        </div>
                        <div class="col-12 mt-3">
                            <span class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Profile visibility</span>
                            <p class="fw-bold mt-1 mb-0" id="profileDisplayVisibility" style="font-size: 0.88rem;"><?= htmlspecialchars($userVisibility) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Community Contribution Card -->
                <div class="student-card mb-0 d-flex flex-column justify-content-between">
                    <div>
                        <h3 class="student-card-title mb-4" style="font-size: 1.1rem;">Community contribution</h3>
                        <div class="row text-center mb-4">
                            <div class="col-4">
                                <a href="uploads.php" class="text-decoration-none">
                                    <div class="student-stat-value text-primary" style="font-size: 1.85rem;"><?= $uploadedCount ?></div>
                                    <div class="text-muted small fw-semibold">Resources</div>
                                </a>
                            </div>
                            <div class="col-4">
                                <a href="downloads.php" class="text-decoration-none">
                                    <div class="student-stat-value text-success" style="font-size: 1.85rem;"><?= $downloadedCount ?></div>
                                    <div class="text-muted small fw-semibold">Downloads</div>
                                </a>
                            </div>
                            <div class="col-4">
                                <a href="discussions.php" class="text-decoration-none">
                                    <div class="student-stat-value text-warning" style="font-size: 1.85rem;"><?= $answersCount ?></div>
                                    <div class="text-muted small fw-semibold">Answers</div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 rounded-3" style="background-color: var(--navy-surface-alt, #F8FAFC); border: 1px dashed var(--student-card-border);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="student-res-icon student-res-icon-green flex-shrink-0">
                                <i class="bi bi-patch-check"></i>
                            </div>
                            <div>
                                <h4 class="student-act-title mb-1" style="font-size: 0.88rem;">Verified NSBM Student Account</h4>
                                <p class="student-act-desc mb-0" style="font-size: 0.78rem;">Email: <?= htmlspecialchars($currentUser['email']) ?> · Student ID: <?= htmlspecialchars($currentUser['student_id'] ?? 'CB-Verified') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Academic Contributions Section -->
            <div class="student-card mt-4 mb-0">
                <div class="student-card-header">
                    <div>
                        <div class="student-card-eyebrow">YOUR CONTRIBUTIONS</div>
                        <h3 class="student-card-title">Shared materials</h3>
                    </div>
                    <a href="uploads.php" class="student-card-link">Manage uploads</a>
                </div>

                <div class="student-table-responsive">
                    <table class="student-table align-middle">
                        <thead>
                            <tr>
                                <th>Resource</th>
                                <th>Added</th>
                                <th>Status</th>
                                <th>Downloads</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $userUploads = [];
                            if (isset($conn) && $conn && !$conn->connect_error) {
                                $uSql = $conn->query("SELECT * FROM resources WHERE uploaded_by = $currentUserId ORDER BY created_at DESC LIMIT 5");
                                if ($uSql) {
                                    while ($r = $uSql->fetch_assoc()) $userUploads[] = $r;
                                }
                            }
                            ?>
                            <?php if (!empty($userUploads)): ?>
                                <?php foreach ($userUploads as $uRow): 
                                    $st = $uRow['status'] ?? 'Approved';
                                    $badgeSt = 'background-color: #ECFDF5; color: #059669;';
                                    if ($st === 'Pending') $badgeSt = 'background-color: #FEF3C7; color: #D97706;';
                                    elseif ($st === 'Rejected') $badgeSt = 'background-color: #FEF2F2; color: #DC2626;';
                                ?>
                                    <tr data-resource-id="<?= (int)$uRow['id'] ?>">
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="student-res-icon student-res-icon-blue">
                                                    <i class="bi bi-file-earmark-text"></i>
                                                </div>
                                                <div>
                                                    <a href="javascript:void(0)" data-resource-id="<?= (int)$uRow['id'] ?>" class="open-resource-modal text-decoration-none fw-bold" style="color: var(--navy-title);">
                                                        <?= htmlspecialchars($uRow['title']) ?>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-muted"><?= date('d M Y', strtotime($uRow['created_at'])) ?></td>
                                        <td>
                                            <span class="badge" style="<?= $badgeSt ?> font-weight: 700; font-size: 0.75rem; padding: 4px 10px; border-radius: 6px;">
                                                <?= htmlspecialchars($st) ?>
                                            </span>
                                        </td>
                                        <td class="fw-semibold"><?= (int)$uRow['downloads'] ?></td>
                                        <td class="text-end">
                                            <a href="javascript:void(0)" data-resource-id="<?= (int)$uRow['id'] ?>" class="open-resource-modal student-btn-sm-view">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        You haven't uploaded any resources yet. <a href="uploads.php" class="text-success fw-bold">Upload your first material</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal: Edit Profile -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 14px; border: 1px solid var(--student-card-border);">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="editProfileModalLabel">Edit Student Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editProfileForm">
                    <div class="mb-3">
                        <label class="form-label small fw-bold" for="profileEditName">Full Name</label>
                        <input type="text" id="profileEditName" class="form-control" value="<?= htmlspecialchars($currentUserName) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold" for="profileEditDegree">Degree Program & Year</label>
                        <input type="text" id="profileEditDegree" class="form-control" value="<?= htmlspecialchars($currentUserDegree) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold" for="profileEditFaculty">Faculty</label>
                        <select id="profileEditFaculty" class="form-select" style="border-radius: 8px;">
                            <option value="Faculty of Computing" <?= strpos(strtolower($currentUserFaculty), 'computing') !== false ? 'selected' : '' ?>>Faculty of Computing</option>
                            <option value="Faculty of Business" <?= strpos(strtolower($currentUserFaculty), 'business') !== false ? 'selected' : '' ?>>Faculty of Business</option>
                            <option value="Faculty of Engineering" <?= strpos(strtolower($currentUserFaculty), 'engineering') !== false ? 'selected' : '' ?>>Faculty of Engineering</option>
                            <option value="Faculty of Science" <?= strpos(strtolower($currentUserFaculty), 'science') !== false ? 'selected' : '' ?>>Faculty of Science</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold" for="profileEditBio">Academic Bio</label>
                        <textarea id="profileEditBio" class="form-control" rows="3"><?= htmlspecialchars($userBio) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold" for="profileEditInterests">Academic Interests</label>
                        <input type="text" id="profileEditInterests" class="form-control" value="<?= htmlspecialchars($userInterests) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold" for="profileEditVisibility">Profile Visibility</label>
                        <select id="profileEditVisibility" class="form-select" style="border-radius: 8px;">
                            <option value="NSBM Study Circle members" <?= $userVisibility === 'NSBM Study Circle members' ? 'selected' : '' ?>>NSBM Study Circle members</option>
                            <option value="Only enrolled course peers" <?= $userVisibility === 'Only enrolled course peers' ? 'selected' : '' ?>>Only enrolled course peers</option>
                            <option value="Private (Only group leaders)" <?= $userVisibility === 'Private (Only group leaders)' ? 'selected' : '' ?>>Private (Only group leaders)</option>
                        </select>
                    </div>
                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                        <button type="button" class="student-btn-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="student-btn-green" id="profileSaveBtn">
                            <i class="bi bi-check2 me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    window.__NSBM_CURRENT_USER_ID = <?= (int)$currentUserId ?>;
    localStorage.setItem('nsbm_current_user_id', '<?= (int)$currentUserId ?>');
    localStorage.setItem('nsbm_current_user_name', <?= json_encode($currentUserName) ?>);
</script>

<!-- Bootstrap 5 JS Bundle & Student script -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/student.js"></script>
</body>
</html>
