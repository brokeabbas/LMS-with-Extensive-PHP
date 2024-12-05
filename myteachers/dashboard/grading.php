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

// Prepare SQL to fetch all courses this teacher is assigned to, where assigned = 1
$sql = "SELECT ss.subject_name, mt.module_code, mt.module_id FROM modules_taught mt
        JOIN class_subject cs ON mt.module_id = cs.module_id
        JOIN school_subjects ss ON cs.subject_id = ss.subject_id
        WHERE mt.teacher_id = ? AND mt.school_id = ? AND mt.assigned = 1"; // Only select modules that are assigned
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
    <title>Grading System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            font-family: 'Nunito', sans-serif; /* Consistent modern font */
        }
        .hover-rise:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .button-style {
            display: inline-block;
            background-color: #4f46e5; /* Indigo shade */
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.2s ease;
        }
        .button-style:hover {
            background-color: #4338ca; /* Darker indigo on hover */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        .icon {
            color: #6366f1; /* Tailwind indigo-500 for icons */
        }
    </style>
</head>
<body class="bg-gradient-to-r from-blue-500 to-purple-500 text-white">
    <div class="flex min-h-screen">
        <!-- Sidebar for navigation -->
        <aside class="w-64 bg-gradient-to-b from-gray-800 to-gray-900 text-white p-5 shadow-md overflow-auto">
            <div class="px-6 py-8">
                <h1 class="text-xl font-semibold">Grading System</h1>
                <nav class="mt-10 space-y-4">
                    <a href="../myteach.php" class="flex items-center p-2 rounded hover:bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-tachometer-alt mr-3 icon"></i>Dashboard
                    </a>
                    <a href="view_grades.php" class="flex items-center p-2 rounded hover:bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-eye mr-3 icon"></i>View Grades
                    </a>
                    <a href="/logout" class="flex items-center p-2 rounded hover:bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-sign-out-alt mr-3 icon"></i>Logout
                    </a>
                </nav>
            </div>
        </aside>
        <!-- Main content area -->
        <div class="flex-1 flex flex-col">
            <header class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 shadow-lg">
                <div class="max-w-7xl mx-auto flex justify-between items-center">
                    <h2 class="text-3xl font-bold">Set Grades</h2>
                    <i class="fas fa-graduation-cap text-lg"></i>
                </div>
            </header>
            <main class="p-6 bg-white text-gray-800 flex-1">
                <div class="container mx-auto px-4">
                    <div class="grid grid-cols-1 md:grid-co2 lg:grid-cols-3 gap-6">
                        <?php if (empty($modules)): ?>
                            <p class="text-gray-800">No courses assigned yet.</p>
                        <?php else: ?>
                            <?php foreach ($modules as $module): ?>
                                <div class="bg-white rounded-lg shadow-lg p-6 hover-rise card">
                                    <h3 class="text-lg font-semibold text-gray-800">
                                        <i class="fas fa-book-open mr-2 icon"></i>
                                        <?= htmlspecialchars($module['subject_name']); ?>
                                    </h3>
                                    <p class="text-gray-600">
                                        <i class="fas fa-code mr-2 icon"></i>
                                        Module Code: <?= htmlspecialchars($module['module_code']); ?>
                                    </p>
                                    <a href="set_grades.php?module_id=<?= urlencode($module['module_id']); ?>" class="mt-4 button-style">
                                        <i class="fas fa-edit mr-2"></i>Set Grades
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