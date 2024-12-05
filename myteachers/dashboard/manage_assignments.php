<?php
session_start();
require_once '../../connections/db.php'; // Connection to the user info database
require_once '../../connections/db_school_data.php'; // Connection to the school data database

// Check user authentication and session variables
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_teacher.php");
    exit;
}

if (!isset($_SESSION["teacher_id"], $_SESSION["school_id"])) {
    exit('Session information not available. Please login again.');
}

$teacher_id = $_SESSION['teacher_id'];
$school_id = $_SESSION['school_id'];

// SQL query updated to only fetch assigned modules
$sql = "SELECT ss.subject_name, mt.module_code, mt.module_id 
        FROM modules_taught mt
        JOIN class_subject cs ON mt.module_id = cs.module_id
        JOIN school_subjects ss ON cs.subject_id = ss.subject_id
        WHERE mt.teacher_id = ? AND mt.school_id = ? AND mt.assigned = 1"; // Only fetch modules where assigned = 1
$modules = [];

if ($stmt = $schoolDataConn->prepare($sql)) {
    $stmt->bind_param("ii", $teacher_id, $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $modules[] = $row;
    }
    $stmt->close();
} else {
    die("SQL Error: " . $schoolDataConn->error);
}

$schoolDataConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assignments</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
</head>
<body class="bg-blue-200">
    <div class="flex min-h-screen">
        <!-- Sidebar for navigation -->
        <div class="w-64 h-screen bg-blue-800 text-white shadow-md">
            <div class="px-6 py-8">
                <h1 class="text-xl font-semibold">Teaching Dashboard</h1>
                <nav class="mt-10">
                    <a href="../myteach.php" class="flex items-center py-2.5 px-4 rounded hover:bg-blue-700">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                    <a href="/logout" class="flex items-center py-2.5 px-4 rounded hover:bg-blue-700">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </nav>
            </div>
        </div>
        <!-- Main content area -->
        <div class="flex-1 p-6">
            <header class="bg-white shadow p-6 rounded-lg mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Courses and Assignments</h2>
            </header>
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Manage Assignments for Your Courses</h3>
                <div class="mt-4">
                    <ul role="list" class="divide-y divide-gray-200">
                        <?php foreach ($modules as $module): ?>
                        <li class="py-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate"><i class="fas fa-book mr-2 text-blue-500"></i><?= htmlspecialchars($module['subject_name']); ?></p>
                                    <p class="text-sm text-gray-500 truncate"><i class="fas fa-code mr-2 text-green-500"></i>Module Code: <?= htmlspecialchars($module['module_code']); ?></p>
                                </div>
                                <div>
                                    <a href="view_assignments.php?module_id=<?= $module['module_id']; ?>" class="inline-flex items-center px-3 py-1 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        <i class="fas fa-edit mr-2"></i>Manage Assignments
                                    </a>
                                </div>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
