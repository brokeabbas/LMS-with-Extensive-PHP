<?php
session_start();
require_once '../../connections/db_school_data.php'; // Path to school_data DB connection script
require_once '../../connections/db.php'; // Path to userinfo DB connection script

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_teacher.php");
    exit;
}

if (!isset($_SESSION["school_id"], $_GET['module_id'])) {
    exit('Necessary information not available.');
}

$module_id = $_GET['module_id'];
$school_id = $_SESSION['school_id'];

// Fetch students enrolled in the specific module using school_data connection
$sql = "SELECT DISTINCT si.fullname, si.student_number, si.id as student_id
        FROM schoolhu_school_data.student_modules sm
        JOIN schoolhu_userinfo.student_info si ON sm.student_id = si.id
        WHERE sm.module_id = ? AND sm.school_id = ?";

$students = [];

if ($stmt = $schoolDataConn->prepare($sql)) {
    $stmt->bind_param("ii", $module_id, $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
} else {
    die("SQL Error: " . $schoolDataConn->error);
}

$schoolDataConn->close();
$userInfoConn->close(); // Ensure both connections are closed after the operation
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Module Grades</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
        .custom-header {
            background: linear-gradient(to right, #667eea, #764ba2);
            color: white;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar for navigation -->
        <aside class="w-64 bg-gradient-to-b from-gray-800 to-gray-900 text-white p-5 shadow-md overflow-auto">
            <div class="px-6 py-8">
                <h1 class="text-xl font-semibold">Grading System</h1>
                <nav class="mt-10 space-y-4">
                    <a href="../myteach.php" class="flex items-center p-2 rounded hover:bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-tachometer-alt mr-3 icon"></i>Dashboard
                    </a>
                    <a href="grading.php" class="flex items-center p-2 rounded hover:bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-eye mr-3 icon"></i>Set Grades
                    </a>
                    <a href="/logout" class="flex items-center p-2 rounded hover:bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-sign-out-alt mr-3 icon"></i>Logout
                    </a>
                </nav>
            </div>
        </aside>
        <!-- Main content area -->
        <div class="flex-1 flex flex-col">
            <header class="custom-header p-6 shadow-lg">
                <div class="max-w-7xl mx-auto flex justify-between items-center">
                    <h2 class="text-3xl font-bold">Students in Module</h2>
                    <i class="fas fa-chalkboard-teacher text-lg"></i>
                </div>
            </header>
            <main class="p-6 bg-white text-gray-800 flex-1">
                <div class="container mx-auto px-4">
                    <div class="bg-white p-6 rounded-lg shadow-lg hover-rise">
                        <?php if (empty($students)): ?>
                            <p class="text-gray-800">No students enrolled or grades set yet.</p>
                        <?php else: ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full leading-normal">
                                    <thead>
                                        <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                                            <th class="py-3 px-6 text-left">Student Name</th>
                                            <th class="py-3 px-6 text-left">Student Number</th>
                                            <th class="py-3 px-6 text-left">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-gray-600 text-sm font-light">
                                        <?php foreach ($students as $student): ?>
                                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                                <td class="py-3 px-6 text-left"><?= htmlspecialchars($student['fullname']); ?></td>
                                                <td class="py-3 px-6 text-left"><?= htmlspecialchars($student['student_number']); ?></td>
                                                <td class="py-3 px-6 text-left">
                                                    <a href="view_student_grades.php?student_id=<?= htmlspecialchars($student['student_id']); ?>&module_id=<?= htmlspecialchars($module_id); ?>" class="text-blue-500 hover:text-blue-800">View Grades</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
