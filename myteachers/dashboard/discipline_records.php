<?php
session_start();
require_once '../../connections/db_school_data.php';  // Ensure this path is correct

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_teacher.php");
    exit;
}

if (!isset($_SESSION["teacher_id"], $_SESSION["school_id"])) {
    echo "No Teacher ID or School ID provided, or you are not logged in with a valid session.";
    exit;
}

$teacher_id = $_SESSION['teacher_id'];
$school_id = $_SESSION['school_id'];
$module_code = $_GET['module_code'] ?? '';

if (!$module_code) {
    die("Module code not specified.");
}

// Fetch students in the module
$students = [];
$sql = "SELECT si.fullname, si.student_number FROM schoolhu_userinfo.student_info si
        JOIN schoolhu_school_data.student_modules sm ON si.id = sm.student_id
        JOIN schoolhu_school_data.modules_taught mt ON sm.module_id = mt.module_id
        WHERE mt.module_code = ? AND mt.teacher_id = ? AND mt.school_id = ?";
if ($stmt = $schoolDataConn->prepare($sql)) {
    $stmt->bind_param("sii", $module_code, $teacher_id, $school_id);
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disciplinary Actions for <?= htmlspecialchars($module_code); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #4a5568, #2d3748);
            color: #f7f7f7;
        }
        .sidebar {
            background-color: #1a202c;
            color: #cbd5e0;
        }
        .sidebar a {
            color: #e2e8f0;
        }
        .sidebar a:hover {
            background-color: #4a5568;
        }
        .card {
            background: white;
            color: #2d3748;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.25);
        }
        .button-style {
            background-color: #c53030;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.2s ease;
            display: inline-block;
        }
        .button-style:hover {
            background-color: #9b2c2c;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        .icon {
            color: #c53030;
        }
        .hover-rise:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
    </style>
</head>
<body class="bg-gradient-to-r from-red-500 to-yellow-500 text-white">
    <div class="flex min-h-screen">
        <!-- Sidebar for navigation -->
        <aside class="w-64 bg-gradient-to-b from-gray-800 to-gray-900 text-white p-5 shadow-md overflow-auto">
            <div class="px-6 py-8">
                <h1 class="text-xl font-semibold">Disciplinary Panel</h1>
                <nav class="mt-10 space-y-4">
                    <a href="../myteach.php" class="flex items-center p-2 rounded hover:bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-tachometer-alt mr-3 icon"></i>Dashboard
                    </a>
                    <a href="/logout" class="flex items-center p-2 rounded hover:bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-sign-out-alt mr-3 icon"></i>Logout
                    </a>
                </nav>
            </div>
        </aside>
        <!-- Main content area -->
        <div class="flex-1 flex flex-col">
            <header class="bg-gradient-to-r from-red-600 to-yellow-600 p-6 shadow-lg">
                <div class="max-w-7xl mx-auto flex justify-between items-center">
                    <h2 class="text-3xl font-bold">Disciplinary Actions for Module: <?= htmlspecialchars($module_code); ?></h2>
                    <i class="fas fa-gavel text-lg"></i>
                </div>
            </header>
            <main class="p-6 bg-white text-gray-800 flex-1">
                <div class="container mx-auto px-4">
                    <!-- Search Bar -->
                    <input type="text" id="searchInput" onkeyup="searchStudents()" placeholder="Search by name or student number..." class="mb-4 p-2 w-full rounded border border-gray-300 shadow-inner">

                    <?php if (empty($students)): ?>
                        <div class="bg-white shadow overflow-hidden rounded-md p-4 text-center text-gray-500">
                            No students found in this module.
                        </div>
                    <?php else: ?>
                        <div id="studentList" class="grid md:grid-cols-2 gap-4">
                            <?php foreach ($students as $student): ?>
                                <div class="student-card bg-white shadow-lg rounded-lg p-6 hover:shadow-xl transition-shadow duration-300 ease-in-out" data-name="<?= strtolower($student['fullname']); ?>" data-number="<?= $student['student_number']; ?>">
                                    <h3 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($student['fullname']); ?></h3>
                                    <p class="text-gray-600">Student Number: <?= htmlspecialchars($student['student_number']); ?></p>
                                    <a href="set_strike_action.php?student_number=<?= urlencode($student['student_number']); ?>&module_code=<?= urlencode($module_code); ?>"
                                       class="mt-3 inline-block bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded transition-colors duration-300">
                                        Record Action
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script>
        function searchStudents() {
            var input = document.getElementById("searchInput");
            var filter = input.value.toLowerCase();
            var nodes = document.getElementsByClassName('student-card');

            Array.from(nodes).forEach(function(node) {
                var name = node.getAttribute('data-name');
                var number = node.getAttribute('data-number');
                if (name.includes(filter) || number.includes(filter)) {
                    node.style.display = "block";
                } else {
                    node.style.display = "none";
                }
            });
        }
    </script>
</body>
</html>
