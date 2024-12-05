<?php
session_start();
require_once '../../connections/db.php';  // Database connection for user info
require_once '../../connections/db_school_data.php';  // Database connection for school data

// Check if the user is logged in and required session variables are present
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["teacher_id"], $_SESSION["school_id"])) {
    header("location: ../login_teacher.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];
$school_id = $_SESSION['school_id'];

// Fetch modules taught by the teacher where assigned = 1
$moduleQuery = "SELECT module_id, module_code FROM schoolhu_school_data.modules_taught WHERE teacher_id = ? AND school_id = ? AND assigned = 1";
$modules = [];
if ($moduleStmt = $schoolDataConn->prepare($moduleQuery)) {
    $moduleStmt->bind_param("ii", $teacher_id, $school_id);
    $moduleStmt->execute();
    $moduleResult = $moduleStmt->get_result();
    while ($moduleRow = $moduleResult->fetch_assoc()) {
        $modules[] = $moduleRow;
    }
    $moduleStmt->close();
}

$search = $_GET['search'] ?? '';
$moduleFilter = $_GET['moduleFilter'] ?? '';

// SQL for fetching submissions only for assigned modules
$sql = "SELECT st.id AS student_id, st.fullname, st.student_number, a.assignment_name, sub.file_path, sub.submission_date
        FROM schoolhu_school_data.assignment_submissions sub
        JOIN schoolhu_school_data.assignments a ON sub.assignment_id = a.id
        JOIN schoolhu_school_data.modules_taught mt ON a.module_id = mt.module_id
        JOIN schoolhu_userinfo.student_info st ON sub.student_id = st.id
        WHERE mt.teacher_id = ? AND mt.school_id = ? AND mt.assigned = 1";

if (!empty($search)) {
    $sql .= " AND (st.fullname LIKE CONCAT('%',?,'%') OR st.student_number LIKE CONCAT('%',?,'%'))";
}
if (!empty($moduleFilter)) {
    $sql .= " AND mt.module_id = ?";
}
$sql .= " ORDER BY sub.submission_date DESC";

if ($stmt = $schoolDataConn->prepare($sql)) {
    if (!empty($search) && !empty($moduleFilter)) {
        $stmt->bind_param("iissi", $teacher_id, $school_id, $search, $search, $moduleFilter);
    } elseif (!empty($search)) {
        $stmt->bind_param("iiss", $teacher_id, $school_id, $search, $search);
    } elseif (!empty($moduleFilter)) {
        $stmt->bind_param("iii", $teacher_id, $school_id, $moduleFilter);
    } else {
        $stmt->bind_param("ii", $teacher_id, $school_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $submitted_assignments[] = $row;
    }
    $stmt->close();
} else {
    echo "SQL Error: " . $schoolDataConn->error;
}

$schoolDataConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submitted Assignments</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        .card:hover {
            transform: scale(1.02);
            transition: transform 0.3s ease-in-out;
        }
    </style>
</head>
<body class="bg-blue-50">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-blue-900 text-white flex flex-col">
            <div class="px-6 py-8">
                <h1 class="text-xl font-semibold">Teaching Dashboard</h1>
                <nav class="mt-10">
                    <a href="../myteach.php" class="flex items-center py-2.5 px-4 rounded hover:bg-blue-800 transition duration-300">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                    <a href="/logout" class="flex items-center py-2.5 px-4 rounded hover:bg-blue-800 transition duration-300">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </nav>
            </div>
        </div>
        <!-- Content Area -->
        <div class="flex-1 p-6">
            <header class="bg-white shadow p-6 rounded-lg mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Submitted Assignments</h2>
                <!-- Search and Filter Form -->
                <form class="flex mt-4 space-x-2">
                    <input type="text" name="search" placeholder="Search by name or number..." class="flex-grow border p-2 rounded-l focus:ring-blue-500 focus:border-blue-500">
                    <select name="moduleFilter" class="border p-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Modules</option>
                        <?php foreach ($modules as $module): ?>
                            <option value="<?= htmlspecialchars($module['module_id']); ?>" <?= ($moduleFilter == $module['module_id'] ? 'selected' : ''); ?>>
                                <?= htmlspecialchars($module['module_code']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-r hover:bg-blue-600 transition duration-300">Search</button>
                </form>
            </header>
            <div class="space-y-4">
                <?php if (!empty($submitted_assignments)): ?>
                    <?php foreach ($submitted_assignments as $assignment): ?>
                    <div class="card p-6 bg-white rounded-lg shadow">
                        <h2 class="font-semibold text-lg text-gray-800"><?= htmlspecialchars($assignment['assignment_name']); ?></h2>
                        <p class="text-gray-600">Submitted by: <?= htmlspecialchars($assignment['fullname']); ?> (Student Number: <?= htmlspecialchars($assignment['student_number']); ?>)</p>
                        <p class="text-gray-600">Submission Date: <?= htmlspecialchars($assignment['submission_date']); ?></p>
                        <div class="flex items-center justify-between">
                            <a href="<?= htmlspecialchars($assignment['file_path']); ?>" download class="inline-flex items-center text-blue-600 hover:text-blue-800 transition-colors duration-300">
                                <i class="fas fa-download mr-2 download-icon"></i>
                                Download Submission
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-gray-600 text-center">No assignments have been submitted yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
