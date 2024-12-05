<?php
session_start();
require_once '../../connections/db_school_data.php'; // Load school-specific data operations

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_teacher.php");
    exit;
}

// Validate presence of module_code in the request
$module_code = $_GET['module'] ?? '';

// Get school_id and teacher_id from the session
$teacher_id = $_SESSION['teacher_id'];
$school_id = $_SESSION['school_id'];

// Initialize variables for search and student details
$search_query = $_GET['search'] ?? ''; // Collect search query if any
$students = [];

// Fetching students enrolled in the selected module with optional search
$sql = "SELECT si.fullname, si.student_number FROM schoolhu_userinfo.student_info si
        JOIN schoolhu_school_data.student_modules sm ON si.id = sm.student_id
        JOIN schoolhu_school_data.modules_taught mt ON sm.module_id = mt.module_id
        WHERE mt.module_code = ? AND mt.school_id = ? AND si.school_id = ? 
        AND (si.fullname LIKE CONCAT('%', ?, '%') OR si.student_number LIKE CONCAT('%', ?, '%'))";

if ($stmt = $schoolDataConn->prepare($sql)) {
    $stmt->bind_param("siiss", $module_code, $school_id, $school_id, $search_query, $search_query);
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
    <title>Module Communication</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            font-family: 'Nunito', sans-serif; /* Consistent modern font */
            background-color: #f3f4f6; /* Light background color */
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
        .custom-header {
            background: linear-gradient(to right, #667eea, #764ba2);
            color: white;
        }
        .sidebar {
            background: linear-gradient(to bottom, #4f46e5, #3b82f6);
        }
        .sidebar a {
            transition: background-color 0.3s ease;
        }
        .sidebar a:hover {
            background-color: #3b82f6;
        }
        .search-bar {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .search-bar input {
            width: 100%;
            padding-right: 2.5rem;
        }
        .search-bar i {
            position: absolute;
            top: 50%;
            right: 0.75rem;
            transform: translateY(-50%);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar for navigation -->
        <aside class="w-64 bg-gradient-to-b from-gray-800 to-gray-900 text-white p-5 shadow-md overflow-auto">
            <div class="px-6 py-8">
                <h1 class="text-xl font-semibold"><i class="fas fa-comments mr-2"></i>Communication Panel</h1>
                <nav class="mt-10 space-y-4">
                    <a href="outbox.php" class="flex items-center p-2 rounded hover:bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-paper-plane mr-3"></i>Outbox
                    </a>
                    <a href="inbox.php" class="flex items-center p-2 rounded hover:bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-inbox mr-3"></i>Inbox
                    </a>
                    <a href="../myteach.php" class="flex items-center p-2 rounded hover:bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                    </a>
                    <a href="/logout" class="flex items-center p-2 rounded hover:bg-red-600 transition-colors duration-200">
                        <i class="fas fa-sign-out-alt mr-3"></i>Logout
                    </a>
                </nav>
            </div>
        </aside>
        <!-- Main content area -->
        <div class="flex-1 flex flex-col">
            <header class="custom-header p-6 shadow-lg">
                <div class="max-w-7xl mx-auto flex justify-between items-center">
                    <h2 class="text-3xl font-bold">Communication Panel for Module: <?= htmlspecialchars($module_code); ?></h2>
                    <i class="fas fa-user-graduate text-lg"></i>
                </div>
            </header>
            <main class="p-6 bg-white text-gray-800 flex-1">
                <div class="container mx-auto px-4">
                    <div class="search-bar">
                        <input type="text" id="searchInput" onkeyup="searchStudents()" placeholder="Search for students..." class="border-2 border-gray-300 bg-white h-10 px-5 pr-16 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <h2 class="text-2xl font-semibold text-gray-700 mb-4"><i class="fas fa-users mr-2"></i>Students in this Module</h2>
                        <ul id="studentList" class="list-none space-y-2">
                            <?php if (!empty($students)): ?>
                                <?php foreach ($students as $student): ?>
                                    <li class="flex justify-between items-center bg-gray-50 p-3 rounded hover-effect">
                                        <?= htmlspecialchars($student['fullname']) ?> (<?= htmlspecialchars($student['student_number']) ?>)
                                        <a href="chat.php?student_number=<?= urlencode($student['student_number']) ?>&module_code=<?= urlencode($module_code) ?>" class="text-blue-600 hover:text-blue-800"><i class="fas fa-comment-dots mr-2"></i>Communicate</a>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-gray-600">No students found.</p>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script>
        function searchStudents() {
            let input = document.getElementById('searchInput');
            let filter = input.value.toLowerCase();
            let nodes = document.getElementById('studentList').getElementsByTagName('li');

            for (let i = 0; i < nodes.length; i++) {
                let textContent = nodes[i].textContent || nodes[i].innerText;
                nodes[i].style.display = textContent.toLowerCase().indexOf(filter) > -1 ? "" : "none";
            }
        }
    </script>
</body>
</html>
