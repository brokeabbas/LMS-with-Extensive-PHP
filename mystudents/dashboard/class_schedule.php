<?php
session_start();
require_once '../../connections/db.php'; // Ensure the path is correctly pointing to your DB connection setup

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_student.php");
    exit;
}

if (!isset($_SESSION["student_id"], $_SESSION["school_id"])) {
    echo "<p>No Student ID provided or you are not logged in with a valid session.</p>";
    exit;
}

$student_id = $_SESSION['student_id'];
$school_id = $_SESSION['school_id'];

// Prepare the SQL to fetch class schedule
$sql = "SELECT ss.subject_name, cl.class_name, mt.module_code FROM schoolhu_school_data.student_modules sm
        JOIN schoolhu_school_data.modules_taught mt ON sm.module_id = mt.module_id
        JOIN schoolhu_school_data.class_subject cs ON mt.module_id = cs.module_id
        JOIN schoolhu_school_data.classes cl ON cs.class_id = cl.class_id
        JOIN schoolhu_school_data.school_subjects ss ON cs.subject_id = ss.subject_id
        WHERE sm.student_id = ? AND sm.school_id = ?";

$schedule = [];

if ($stmt = $userInfoConn->prepare($sql)) {
    $stmt->bind_param("ii", $student_id, $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $schedule[] = $row;
    }
    $stmt->close();
} else {
    die("SQL Error: " . $userInfoConn->error);
}

$userInfoConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Class Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.3/js/all.js"></script> <!-- FontAwesome Icons -->
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(to right, #6ee7b7, #3b82f6);
        }
        .menu:hover {
            transform: scale(1.05);
            transition: transform 0.3s ease;
        }
    </style>
</head>
<body class="font-sans leading-normal tracking-normal">
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 min-h-screen flex flex-col bg-blue-800 text-white shadow-lg">
            <div class="p-5 text-center text-2xl font-bold border-b-2 border-blue-700">
                Navigation
            </div>
            <ul class="flex-1 p-5">
                <a href="../mystudy.php">
                <li class="menu mb-4 text-lg hover:bg-blue-700 p-2 rounded cursor-pointer">
                    <i class="fas fa-home"></i> Dashboard
                </li>
                </a>
                <a href="../profile.php">
                <li class="menu mb-4 text-lg hover:bg-blue-700 p-2 rounded cursor-pointer">
                    <i class="fas fa-user"></i> Profile
                </li>
                </a>
                <a href="gradebook.php">
                <li class="menu mb-4 text-lg hover:bg-blue-700 p-2 rounded cursor-pointer">
                    <i class="fas fa-cog"></i> Grades
                </li>
                </a>
            </ul>
        </div>

        <!-- Main content -->
        <div class="flex-1 p-10 text-gray-800">
            <div class="bg-white shadow-xl rounded-lg p-6 opacity-95">
                <h1 class="text-4xl font-bold text-gray-800 mb-4">Your Class Schedule</h1>
                <?php if (!empty($schedule)): ?>
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full">
                            <thead class="text-xs font-semibold uppercase text-gray-600 bg-blue-200">
                                <tr>
                                    <th class="p-2">
                                        <div class="font-bold text-left"><i class="fas fa-book-reader text-blue-500"></i> Subject</div>
                                    </th>
                                    <th class="p-2">
                                        <div class="font-bold text-left"><i class="fas fa-chalkboard-teacher text-green-500"></i> Class Name</div>
                                    </th>
                                    <th class="p-2">
                                        <div class="font-bold text-left"><i class="fas fa-code text-red-500"></i> Module Code</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                <?php foreach ($schedule as $class): ?>
                                    <tr class="hover:bg-gray-100 text-gray-700">
                                        <td class="p-3"><?= htmlspecialchars($class['subject_name']) ?></td>
                                        <td class="p-3"><?= htmlspecialchars($class['class_name']) ?></td>
                                        <td class="p-3"><?= htmlspecialchars($class['module_code']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-lg text-gray-700 mt-4">You are currently not enrolled in any classes.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
