<?php
session_start();
require_once '../../connections/db_school_data.php'; // Load school-specific data operations

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_teacher.php");
    exit;
}

// Ensure the necessary session variables are set
if (!isset($_SESSION["teacher_id"], $_SESSION["school_id"])) {
    exit('Session information not available.');
}

$teacher_id = $_SESSION['teacher_id'];
$school_id = $_SESSION['school_id'];

// Prepare SQL to fetch all courses this teacher is assigned to
$sql = "SELECT ss.subject_name, mt.module_code FROM modules_taught mt
        JOIN class_subject cs ON mt.module_id = cs.module_id
        JOIN school_subjects ss ON cs.subject_id = ss.subject_id
        WHERE mt.teacher_id = ? AND mt.school_id = ? AND mt.assigned = 1";
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
    <title>Courses Management</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        .slide-in-left { animation: slideInFromLeft 0.5s ease-out forwards; }
        @keyframes slideInFromLeft { 0% { transform: translateX(-100%); } 100% { transform: translateX(0); } }
        .fade-in { animation: fadeIn 1s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .scale-in { animation: scaleIn 0.5s ease-out; }
        @keyframes scaleIn { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        aside {
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }
        /* Custom scrollbar styles */
        aside::-webkit-scrollbar {
            width: 12px;
        }
        aside::-webkit-scrollbar-track {
            background: #1a202c;
            border-radius: 6px;
        }
        aside::-webkit-scrollbar-thumb {
            background: #4a5568;
            border-radius: 6px;
        }
        aside::-webkit-scrollbar-thumb:hover {
            background: #2d3748;
        }
    </style>
</head>
<body class="bg-gradient-to-r from-blue-500 to-purple-500 font-sans leading-normal tracking-normal text-white">
    <div class="flex min-h-screen">
        <aside class="w-64 bg-gradient-to-b from-gray-900 to-black p-5 text-white">
            <h2 class="text-2xl font-semibold mb-10">Navigation Panel</h2>
            <nav class="flex flex-col gap-3">
                <a href="/dashboard" class="flex items-center p-2 rounded hover:bg-purple-700 transition duration-300 ease-in-out">
                    <i class="fas fa-home mr-3"></i> Teacher's Hub
                </a>
                <a href="courses_management.php" class="flex items-center p-2 rounded hover:bg-purple-700 transition duration-300 ease-in-out">
                    <i class="fas fa-chalkboard-teacher mr-3"></i> Course Management
                </a>
                <a href="assignments.php" class="flex items-center p-2 rounded hover:bg-purple-700 transition duration-300 ease-in-out">
                    <i class="fas fa-tasks mr-3"></i> Assignments
                </a>
                <a href="grading.php" class="flex items-center p-2 rounded hover:bg-purple-700 transition duration-300 ease-in-out">
                    <i class="fas fa-graduation-cap mr-3"></i> Grading System
                </a>
                <a href="attendance.php" class="flex items-center p-2 rounded hover:bg-purple-700 transition duration-300 ease-in-out">
                    <i class="fas fa-user-check mr-3"></i> Attendance
                </a>
                <a href="school_calendar.php" class="flex items-center p-2 rounded hover:bg-purple-700 transition duration-300 ease-in-out">
                    <i class="fas fa-calendar-alt mr-3"></i> School Calendar
                </a>
                <a href="library.php" class="flex items-center p-2 rounded hover:bg-purple-700 transition duration-300 ease-in-out">
                    <i class="fas fa-book mr-3"></i> E-Library
                </a>
                <a href="communication.php" class="flex items-center p-2 rounded hover:bg-purple-700 transition duration-300 ease-in-out">
                    <i class="fas fa-comments mr-3"></i> Student Communication
                </a>
                <a href="scheme_manager.php" class="flex items-center p-2 rounded hover:bg-purple-700 transition duration-300 ease-in-out">
                    <i class="fas fa-stream mr-3"></i> Work Scheme Planner
                </a>
                <a href="disciplinary_action.php" class="flex items-center p-2 rounded hover:bg-purple-700 transition duration-300 ease-in-out">
                    <i class="fas fa-gavel mr-3"></i> Disciplinary Action
                </a>
                <a href="suggestion_box.php" class="flex items-center p-2 rounded hover:bg-purple-700 transition duration-300 ease-in-out">
                    <i class="fas fa-inbox mr-3"></i> School's Suggestion Box
                </a>
                <a href="../feedback.php" class="flex items-center p-2 rounded hover:bg-purple-700 transition duration-300 ease-in-out">
                    <i class="fas fa-ellipsis-h mr-3"></i> More Features...
                </a>
            </nav>
        </aside>
        <div class="flex-1 flex flex-col">
            <header class="bg-purple-900 text-white p-6 shadow-lg">
                <div class="max-w-7xl mx-auto flex justify-between items-center">
                    <h1 class="text-4xl font-bold"><i class="fas fa-chalkboard-teacher mr-3"></i> Courses You Teach</h1>
                    <nav class="flex gap-4">
                        <a href="../profile.php" class="text-white hover:text-gray-300"><i class="fas fa-user-circle mr-2"></i>My Profile</a>
                        <a href="../settings.php" class="text-white hover:text-gray-300"><i class="fas fa-cog mr-2"></i>Settings</a>
                        <a href="../logout.php" class="text-white hover:text-gray-300"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
                    </nav>
                </div>
            </header>
            <main class="p-6 bg-white text-gray-800 flex-1">
                <div class="container mx-auto px-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php if (empty($modules)): ?>
                            <p class="text-center col-span-full">No courses assigned yet.</p>
                        <?php else: ?>
                            <?php foreach ($modules as $module): ?>
                                <div class="card bg-white rounded-lg p-4 shadow-lg fade-in">
                                    <h3 class="text-xl font-semibold text-purple-800"><i class="fas fa-book-open icon"></i> <?= htmlspecialchars($module['subject_name']); ?></h3>
                                    <p class="text-gray-600"><i class="fas fa-code icon"></i> Module Code: <?= htmlspecialchars($module['module_code']); ?></p>
                                    <a href="course_details.php?module=<?= urlencode($module['module_code']); ?>" class="details-button mt-4 inline-block text-center bg-purple-600 text-white py-2 px-4 rounded hover:bg-purple-700 transition duration-300 ease-in-out">
                                        <i class="fas fa-info-circle"></i> View Details
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
