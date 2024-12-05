<?php
session_start();
require_once '../../connections/db.php'; // This should be your connection to the userinfo database
require_once '../../connections/db_school_data.php'; // This should connect to the school_data database

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

// Fetch the modules that the teacher is assigned to, where assigned = 1
$sql = "SELECT ss.subject_name, mt.module_code, mt.module_id FROM modules_taught mt
        JOIN class_subject cs ON mt.module_id = cs.module_id
        JOIN school_subjects ss ON cs.subject_id = ss.subject_id
        WHERE mt.teacher_id = ? AND mt.school_id = ? AND mt.assigned = 1"; // Ensuring we only select assigned modules
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
    <title>Course Management</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        .slide-in-left { animation: slideInFromLeft 0.5s ease-out forwards; }
        @keyframes slideInFromLeft { 0% { transform: translateX(-100%); } 100% { transform: translateX(0); } }
        .fade-in { animation: fadeIn 1s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .scale-in { animation: scaleIn 0.5s ease-out; }
        @keyframes scaleIn { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .hover-rise:hover { transform: translateY(-5px); transition: transform 0.3s ease; }
        .custom-font { font-family: 'Nunito', sans-serif; }
        .custom-header { background: linear-gradient(to right, #667eea, #764ba2); color: white; }
        .btn-assign { transition: background-color 0.3s ease-in-out, transform 0.2s ease; }
        .btn-assign:hover { background-color: #3b82f6; transform: scale(1.05); }
        aside { position: sticky; top: 0; height: 100vh; overflow-y: auto; }
        /* Custom scrollbar styles */
        aside::-webkit-scrollbar { width: 12px; }
        aside::-webkit-scrollbar-track { background: #1a202c; border-radius: 6px; }
        aside::-webkit-scrollbar-thumb { background: #4a5568; border-radius: 6px; }
        aside::-webkit-scrollbar-thumb:hover { background: #2d3748; }
    </style>
</head>
<body class="bg-gradient-to-r from-blue-500 to-purple-500 font-sans leading-normal tracking-normal text-white">
    <div class="flex min-h-screen">
        <!-- Sidebar for navigation -->
        <aside class="w-64 bg-gradient-to-b from-gray-800 to-gray-900 text-white p-5 slide-in-left">
            <div class="px-6 py-8">
                <h1 class="text-xl font-semibold">Teaching Dashboard</h1>
                <nav class="mt-10 space-y-4">
                    <a href="../myteach.php" class="flex items-center p-2 rounded hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                    </a>
                    <a href="manage_assignments.php" class="flex items-center p-2 rounded hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-tasks mr-3"></i>Manage Assignments
                    </a>
                    <a href="submitted_assignment.php" class="flex items-center p-2 rounded hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-upload mr-3"></i>Submitted Assignments
                    </a>
                    <a href="/logout" class="flex items-center p-2 rounded hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-sign-out-alt mr-3"></i>Logout
                    </a>
                </nav>
            </div>
        </aside>
        <!-- Main content area -->
        <div class="flex-1 flex flex-col">
            <header class="custom-header p-6 shadow-lg">
                <div class="max-w-7xl mx-auto flex justify-between items-center">
                    <h2 class="text-2xl font-bold">Courses and Assignments</h2>
                    <i class="fas fa-chalkboard-teacher text-lg"></i>
                </div>
            </header>
            <main class="p-6 bg-white text-gray-800 flex-1">
                <h3 class="text-lg leading-6 font-medium custom-font">Set Assignments for Your Courses <i class="fas fa-tasks"></i></h3>
                <div class="mt-6">
                    <ul role="list" class="divide-y divide-gray-200">
                        <?php foreach ($modules as $module): ?>
                        <li class="py-4 hover-rise">
                            <div class="flex justify-between items-center custom-font">
                                <div>
                                    <i class="fas fa-book text-icon"></i>
                                    <span><?= htmlspecialchars($module['subject_name']); ?></span>
                                    <br>
                                    <i class="fas fa-code-branch text-icon"></i>
                                    <span>Module Code: <?= htmlspecialchars($module['module_code']); ?></span>
                                </div>
                                <a href="set_assignments.php?module_id=<?= $module['module_id']; ?>"
                                   class="px-4 py-2 bg-blue-500 btn-assign text-white font-bold rounded-full">
                                    <i class="fas fa-plus-circle"></i> Set Assignments
                                </a>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
