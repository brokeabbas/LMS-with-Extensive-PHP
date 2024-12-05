<?php
session_start();
require_once '../../connections/db_school_data.php'; // Assuming this is the path to your database connections

// Ensure the user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_teacher.php");
    exit;
}

// Validate presence of student_number and module_code in the request
$student_number = isset($_GET['student_number']) ? $_GET['student_number'] : null;
$module_code = isset($_GET['module_code']) ? $_GET['module_code'] : '';

// Get school_id and teacher_id from the session
$teacher_id = $_SESSION['teacher_id'];
$school_id = $_SESSION['school_id'];

// Initialize variables
$student_id = null;
$module_id = null;

// Fetch student_id from student_number
if ($student_number) {
    if ($stmt = $schoolDataConn->prepare("SELECT id FROM schoolhu_userinfo.student_info WHERE student_number = ?")) {
        $stmt->bind_param("s", $student_number);
        $stmt->execute();
        $stmt->bind_result($student_id);
        $stmt->fetch();
        $stmt->close();
    }
}

// Fetch module_id using module_code
if ($module_code) {
    if ($stmt = $schoolDataConn->prepare("SELECT module_id FROM schoolhu_school_data.modules_taught WHERE module_code = ?")) {
        $stmt->bind_param("s", $module_code);
        $stmt->execute();
        $stmt->bind_result($module_id);
        $stmt->fetch();
        $stmt->close();
    }
}

// Process message submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && $student_id && $module_id) {
    $title = $_POST['title'];
    $message = $_POST['message'];

    // Insert message into database
    $sql = "INSERT INTO messages (teacher_id, student_id, school_id, module_id, message_title, message_body) VALUES (?, ?, ?, ?, ?, ?)";
    if ($stmt = $schoolDataConn->prepare($sql)) {
        $stmt->bind_param("iiiiss", $teacher_id, $student_id, $school_id, $module_id, $title, $message);
        if ($stmt->execute()) {
            $_SESSION['message'] = [
                'type' => 'success',
                'content' => 'Message sent successfully!'
            ];
        } else {
            $_SESSION['message'] = [
                'type' => 'error',
                'content' => 'Error sending message: ' . $stmt->error
            ];
        }
        $stmt->close();
    }
    $schoolDataConn->close();
    header("Location: chat.php?student_number=" . urlencode($student_number) . "&module_code=" . urlencode($module_code));
    exit;
}

// Retrieve any session messages and then clear them
$session_message = null;
if (isset($_SESSION['message'])) {
    $session_message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Message</title>
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
        .sidebar {
            background: linear-gradient(to bottom, #4f46e5, #3b82f6);
        }
        .sidebar a {
            transition: background-color 0.3s ease;
        }
        .sidebar a:hover {
            background-color: #3b82f6;
        }
        .custom-header {
            background: linear-gradient(to right, #667eea, #764ba2);
            color: white;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-gradient-to-b from-gray-800 to-gray-900 text-white p-5 shadow-md overflow-auto">
            <div class="px-6 py-8">
                <h1 class="text-xl font-semibold mb-4 flex items-center">
                    <i class="fas fa-bars mr-2"></i>Navigation
                </h1>
                <ul>
                    <li class="mb-2">
                        <a href="../myteach.php" class="flex items-center p-2 rounded hover:bg-gray-700 transition-colors duration-200">
                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="inbox.php" class="flex items-center p-2 rounded hover:bg-gray-700 transition-colors duration-200">
                            <i class="fas fa-envelope mr-2"></i>Messages
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="/logout" class="flex items-center p-2 rounded hover:bg-red-600 transition-colors duration-200">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 p-10">
            <div class="w-full max-w-3xl mx-auto bg-white rounded-lg shadow-xl p-6">
                <h1 class="text-3xl font-semibold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-paper-plane mr-2"></i>Send Message to Student
                </h1>

                <!-- Display session messages -->
                <?php if ($session_message): ?>
                    <div class="<?= $session_message['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?> p-4 mb-4 rounded">
                        <?= htmlspecialchars($session_message['content']) ?>
                    </div>
                <?php endif; ?>

                <form action="chat.php?student_number=<?= htmlspecialchars($student_number) ?>&module_code=<?= htmlspecialchars($module_code) ?>" method="post">
                    <div class="mb-4">
                        <label for="title" class="block text-sm font-medium text-gray-700">Title:</label>
                        <input type="text" name="title" id="title" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="mb-6">
                        <label for="message" class="block text-sm font-medium text-gray-700">Message:</label>
                        <textarea name="message" id="message" rows="10" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                    <button type="submit" class="w-full button-style">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
