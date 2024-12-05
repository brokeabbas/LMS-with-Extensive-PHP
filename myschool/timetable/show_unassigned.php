<?php
session_start();
require_once '../../connections/db_school_data.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

$school_id = $_SESSION['school_id'] ?? null;

// Fetch unassigned modules with potential teacher details (even though they should not have any)
$unassignedQuery = "SELECT c.class_name, ss.subject_name, cs.module_code, mt.module_id, ti.name AS teacher_name, ti.teacher_number
                    FROM classes c
                    JOIN class_subject cs ON c.class_id = cs.class_id
                    JOIN school_subjects ss ON cs.subject_id = ss.subject_id
                    JOIN modules_taught mt ON cs.module_id = mt.module_id
                    LEFT JOIN userinfo.teacher_info ti ON mt.teacher_id = ti.id
                    WHERE c.school_id = ? AND mt.assigned = 0
                    ORDER BY c.class_name, ss.subject_name";
$unassignedModules = [];
$stmt = $schoolDataConn->prepare($unassignedQuery);
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $unassignedModules[$row['class_name']][$row['subject_name']][] = $row;
}
$stmt->close();

// Fetch available teachers who are not currently assigned to any modules
$teachersQuery = "SELECT ti.id, ti.name FROM userinfo.teacher_info ti
                   LEFT JOIN school_data.modules_taught mt ON ti.id = mt.teacher_id AND mt.assigned = 1
                   WHERE mt.teacher_id IS NULL";
$availableTeachers = [];
if ($stmt = $schoolDataConn->prepare($teachersQuery)) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $availableTeachers[$row['id']] = $row['name'];
    }
    $stmt->close();
} else {
    echo "Error preparing teacher query: " . $schoolDataConn->error;
}


// Handle reassignment submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reassign_teacher'])) {
    $module_id = $_POST['module_id'];
    $teacher_id = $_POST['teacher_id'];

    $reassignQuery = "UPDATE modules_taught SET teacher_id = ?, assigned = 1 WHERE module_id = ?";
    $stmt = $schoolDataConn->prepare($reassignQuery);
    $stmt->bind_param("ii", $teacher_id, $module_id);
    if (!$stmt->execute()) {
        echo "Error updating record: " . $schoolDataConn->error;
    }
    $stmt->close();
    header("location: show_unassigned.php"); // Refresh the page to update the list of unassigned modules
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reassign Teachers to Unassigned Modules</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
</head>
<body class="bg-light">
    <div class="container my-5">
        <h1 class="text-center mb-4">Reassign Teachers to Unassigned Modules</h1>
        <div class="mb-4">
            <a href="manage_assigned_teachers.php" class="btn btn-primary">Back to Management</a>
        </div>
        <?php if (!empty($unassignedModules)): ?>
            <?php foreach ($unassignedModules as $class_name => $subjects): ?>
                <div class="card mb-3">
                    <div class="card-header"><h2><?= htmlspecialchars($class_name); ?></h2></div>
                    <?php foreach ($subjects as $subject_name => $modules): ?>
                        <div class="card-body">
                            <h5><?= htmlspecialchars($subject_name); ?></h5>
                            <ul class="list-group">
                                <?php foreach ($modules as $module): ?>
                                    <li class="list-group-item">
                                        <form method="post" action="">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>Module: <?= htmlspecialchars($module['module_code']); ?></span>
                                                <select name="teacher_id" class="form-select form-select-sm w-auto">
                                                    <?php foreach ($availableTeachers as $id => $name): ?>
                                                        <option value="<?= $id; ?>"><?= htmlspecialchars($name); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <input type="hidden" name="module_id" value="<?= $module['module_id']; ?>">
                                                <button type="submit" name="reassign_teacher" class="btn btn-sm btn-success">Reassign</button>
                                            </div>
                                        </form>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

        <?php else: ?>
            <p class="text-center">No unassigned modules found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
