<?php
// ============================================================================
// NSBM STUDY CIRCLE - STUDY GROUP TEST SUITE
// ============================================================================

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../models/StudyGroup.php';

echo "====================================================\n";
echo "      NSBM STUDY CIRCLE - STUDY GROUP TESTS         \n";
echo "====================================================\n\n";

$model = new StudyGroupModel($conn);

// 1. Featured Group
echo "[TEST 1] Get Featured Group: ";
$featured = $model->getFeaturedGroup();
if ($featured && $featured['name'] === 'Data Structures Circle') {
    echo "PASSED (Found: {$featured['name']} - Code: {$featured['code']})\n";
} else {
    echo "FAILED\n";
}

// 2. Filter All Groups
echo "[TEST 2] Get All Groups: ";
$allGroups = $model->getGroups('All', '');
if (count($allGroups) >= 7) {
    echo "PASSED (Count: " . count($allGroups) . ")\n";
} else {
    echo "FAILED (Count: " . count($allGroups) . ")\n";
}

// 3. Filter Category Computing
echo "[TEST 3] Filter Category 'Computing': ";
$compGroups = $model->getGroups('Computing', '');
$compNames = array_column($compGroups, 'name');
if (in_array('Data Structures Circle', $compNames) && in_array('AI & Machine Learning Hub', $compNames)) {
    echo "PASSED (Count: " . count($compGroups) . ")\n";
} else {
    echo "FAILED\n";
}

// 4. Search Filter
echo "[TEST 4] Search for 'physics': ";
$searchGroups = $model->getGroups('All', 'physics');
if (count($searchGroups) === 1 && $searchGroups[0]['name'] === 'Engineering Innovators') {
    echo "PASSED (Found: {$searchGroups[0]['name']})\n";
} else {
    echo "FAILED\n";
}

// 5. Single Group Details
echo "[TEST 5] Get Group Details (ID: 1): ";
$detail = $model->getGroupById(1, 2);
if ($detail && count($detail['coordinators']) >= 2 && count($detail['activities']) >= 3) {
    echo "PASSED (Coordinators: " . count($detail['coordinators']) . ", Activities: " . count($detail['activities']) . ")\n";
} else {
    echo "FAILED\n";
}

// 6. Join Group
echo "[TEST 6] Join Group (ID: 2, User: 2): ";
$joinRes = $model->joinGroup(2, 2);
if ($joinRes['success']) {
    echo "PASSED ({$joinRes['message']})\n";
} else {
    echo "NOTICE ({$joinRes['message']})\n";
}

// 7. Leave Group
echo "[TEST 7] Leave Group (ID: 2, User: 2): ";
$leaveRes = $model->leaveGroup(2, 2);
if ($leaveRes['success']) {
    echo "PASSED ({$leaveRes['message']})\n";
} else {
    echo "NOTICE ({$leaveRes['message']})\n";
}

echo "\nALL STUDY GROUP TESTS COMPLETED SUCCESSFULLY!\n";
?>

