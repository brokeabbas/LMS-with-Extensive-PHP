<?php
session_start();
require_once '../../connections/db_school_data.php'; // Load school-specific data operations

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_teacher.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];
$school_id = $_SESSION['school_id'];

$search = $_GET['search'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// SQL query with search functionality including date range
$sql = "SELECT m.message_title, m.message_body, si.fullname as student_name, si.student_number, mt.module_code, m.created_at 
        FROM messages m 
        JOIN schoolhu_userinfo.student_info si ON m.student_id = si.id
        JOIN schoolhu_school_data.modules_taught mt ON m.module_id = mt.module_id
        WHERE m.teacher_id = ? AND m.school_id = ? 
        AND (si.fullname LIKE CONCAT('%', ?, '%') OR si.student_number LIKE CONCAT('%', ?, '%'))
        AND (m.created_at BETWEEN ? AND ?)
        ORDER BY m.created_at DESC";

$messages = [];
if ($stmt = $schoolDataConn->prepare($sql)) {
    // Add date conditions to SQL query if both dates are set
    $endDate = date('Y-m-d', strtotime($end_date . ' +1 day')); // Adjust the end date to include the full day
    $stmt->bind_param("iissss", $teacher_id, $school_id, $search, $search, $start_date, $endDate);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
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
    <title>Outbox - Sent Messages</title>
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
        .fixed-sidebar {
            position: fixed;
            width: 256px; /* Same as the width of the sidebar */
            height: 100%;
            overflow-y: auto;
        }
        .main-content {
            margin-left: 256px; /* Same as the width of the sidebar */
            width: calc(100% - 256px); /* Adjust width to account for the sidebar */
            height: 100vh;
            overflow-y: scroll;
        }
        .icon {
            width: 18px;
            height: 18px;
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
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Static Sidebar -->
        <aside class="fixed-sidebar bg-gradient-to-b from-gray-800 to-gray-900 text-white p-6 shadow-md">
            <h2 class="text-xl font-semibold text-white mb-6"><i class="fas fa-bars mr-2"></i>Navigation</h2>
            <nav>
                <a href="../myteach.php" class="flex items-center p-2 mb-2 rounded hover:bg-gray-700 transition-colors duration-200">
                    <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                </a>
                <a href="inbox.php" class="flex items-center p-2 mb-2 rounded hover:bg-gray-700 transition-colors duration-200">
                    <i class="fas fa-inbox mr-2"></i>Inbox
                </a>
                <a href="/logout" class="flex items-center p-2 mb-2 rounded hover:bg-red-600 transition-colors duration-200">
                    <i class="fas fa-sign-out-alt mr-2"></i>Logout
                </a>
            </nav>
        </aside>
        <!-- Main content -->
        <div class="main-content p-10">
            <h1 class="text-3xl font-bold text-gray-900 mb-6">Outbox - Sent Messages</h1>
            <div class="mb-6">
                <form action="" method="get" class="bg-white p-6 rounded-lg shadow hover-rise">
                    <div class="grid grid-cols-4 gap-4 mb-4">
                        <input type="text" name="search" placeholder="Search by name or number" class="border p-2 rounded col-span-2" value="<?= htmlspecialchars($search); ?>">
                        <input type="date" name="start_date" class="border p-2 rounded" value="<?= htmlspecialchars($start_date); ?>">
                        <input type="date" name="end_date" class="border p-2 rounded" value="<?= htmlspecialchars($end_date); ?>">
                    </div>
                    <button type="submit" class="button-style">Search</button>
                </form>
            </div>
            <div class="bg-white p-6 rounded-lg shadow hover-rise">
                <?php if (!empty($messages)): ?>
                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($messages as $message): ?>
                            <li class="p-4 hover:bg-gray-50">
                                <h3 class="font-semibold text-blue-800 mb-2"><?= htmlspecialchars($message['message_title']); ?> <i class="fas fa-paper-plane text-blue-500"></i></h3>
                                <p class="mb-4"><?= nl2br(htmlspecialchars($message['message_body'])); ?></p>
                                <div class="text-sm text-gray-500">
                                    <p><i class="fas fa-user-graduate icon"></i> To: <?= htmlspecialchars($message['student_name']); ?> (<?= htmlspecialchars($message['student_number']); ?>)</p>
                                    <p><i class="fas fa-code icon"></i> Module: <?= htmlspecialchars($message['module_code']); ?></p>
                                    <p><i class="fas fa-clock icon"></i> Sent on: <?= date("F j, Y, g:i a", strtotime($message['created_at'])); ?></p>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-gray-600">No messages found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
