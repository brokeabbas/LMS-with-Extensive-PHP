<?php
session_start();
require_once '../../connections/db_school_data.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_teacher.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];
$modules = [];

$sql = "SELECT mt.module_code, ss.subject_name
        FROM modules_taught mt
        JOIN class_subject cs ON mt.module_id = cs.module_id
        JOIN school_subjects ss ON cs.subject_id = ss.subject_id
        WHERE mt.teacher_id = ?";

if ($stmt = $schoolDataConn->prepare($sql)) {
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $modules[] = $row;
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
    <title>Work Scheme Planner</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f3f4f6;
        }
        .slide-in-left { 
            animation: slideInFromLeft 0.5s ease-out forwards; 
        }
        @keyframes slideInFromLeft { 
            0% { transform: translateX(-100%); } 
            100% { transform: translateX(0); } 
        }
        .fade-in { 
            animation: fadeIn 1s ease-out; 
        }
        @keyframes fadeIn { 
            from { opacity: 0; transform: translateY(20px); } 
            to { opacity: 1; transform: translateY(0); } 
        }
        .scale-in { 
            animation: scaleIn 0.5s ease-out; 
        }
        @keyframes scaleIn { 
            from { transform: scale(0.8); opacity: 0; } 
            to { transform: scale(1); opacity: 1; } 
        }
        .hover-rise:hover { 
            transform: translateY(-5px); 
            transition: transform 0.3s ease; 
        }
        .custom-font { 
            font-family: 'Nunito', sans-serif; 
        }
        .custom-header { 
            background: linear-gradient(to right, #667eea, #764ba2); 
            color: white; 
        }
        .btn-assign { 
            transition: background-color 0.3s ease-in-out, transform 0.2s ease; 
        }
        .btn-assign:hover { 
            background-color: #3b82f6; 
            transform: scale(1.05); 
        }
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
        <!-- Sidebar for navigation -->
        <aside class="w-64 bg-gradient-to-b from-gray-800 to-gray-900 text-white p-5 slide-in-left">
            <div class="px-6 py-8">
                <h1 class="text-xl font-semibold">Teaching Dashboard</h1>
                <nav class="mt-10 space-y-4">
                    <a href="../myteach.php" class="flex items-center p-2 rounded hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                    </a>
                    <a href="view_schemes.php" class="flex items-center p-2 rounded hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-stream mr-3"></i>View Schemes
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
                    <h2 class="text-3xl font-bold">Courses and Schemes</h2>
                    <i class="fas fa-chalkboard-teacher text-lg"></i>
                </div>
            </header>
            <main class="p-6 bg-white text-gray-800 flex-1">
                <h3 class="text-lg leading-6 font-medium custom-font">Set Schemes for Your Courses <i class="fas fa-tasks"></i></h3>
                <div class="mt-6">
                    <ul role="list" class="divide-y divide-gray-200">
                        <?php if (!empty($modules)): ?>
                            <?php foreach ($modules as $module): ?>
                                <li class="py-4 hover-rise fade-in">
                                    <div class="flex justify-between items-center custom-font">
                                        <div>
                                            <i class="fas fa-book text-icon"></i>
                                            <span><?= htmlspecialchars($module['subject_name']); ?></span>
                                            <br>
                                            <i class="fas fa-code-branch text-icon"></i>
                                            <span>Module Code: <?= htmlspecialchars($module['module_code']); ?></span>
                                        </div>
                                        <a href="scheme.php?module=<?= urlencode($module['module_code']); ?>" class="px-4 py-2 bg-blue-500 btn-assign text-white font-bold rounded-full">
                                            <i class="fas fa-plus-circle"></i> Create Scheme
                                        </a>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No courses assigned yet.</p>
                        <?php endif; ?>
                    </ul>
                </div>
            </main>
        </div>
    </div>
</body>
</html>