<?php
session_start();
require_once '../../connections/db_school_data.php'; // Ensure this path is correct

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_student.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// Fetch modules and teacher details
$modules = [];
$sql = "SELECT mt.module_code, ss.subject_name, t.name as teacher_name, t.id as teacher_id
        FROM student_modules sm
        JOIN modules_taught mt ON sm.module_id = mt.module_id
        JOIN schoolhu_userinfo.teacher_info t ON mt.teacher_id = t.id
        JOIN class_subject cs ON mt.module_id = cs.module_id
        JOIN school_subjects ss ON cs.subject_id = ss.subject_id
        WHERE sm.student_id = ? AND mt.school_id = sm.school_id AND ss.school_id = mt.school_id";

if ($stmt = $schoolDataConn->prepare($sql)) {
    $stmt->bind_param("i", $student_id);
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
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script> <!-- FontAwesome for icons -->
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to left, #4b6cb7, #182848); /* Darker blue gradient */
            color: #ffffff;
            overflow-x: hidden;
        }

        aside {
            background: linear-gradient(145deg, rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.95));
            backdrop-filter: blur(10px);
            color: #fff;
            width: 250px; /* Fixed sidebar width */
            height: 100vh;
            position: fixed; /* Fixed position */
            overflow-y: auto; /* Scrollable */
            box-shadow: 5px 0 15px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            align-items: center;
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            z-index: 1000;
            padding: 20px;
        }

        .content {
            margin-left: 250px; /* Space for sidebar */
            padding: 20px;
            min-height: 100vh;
            background: linear-gradient(to right, #0f0c29, #302b63, #24243e); /* Futuristic gradient background */
            transition: margin-left 0.3s;
        }

        .card {
            background: #ffffff; /* White background for cards */
            border-radius: 12px;
            transition: all 0.3s ease-in-out;
            color: #333;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
            padding: 20px;
        }

        .card-header {
            background: #f0f4f8; /* Light background for headers */
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .card-link {
            display: inline-block;
            background: #0288d1; /* Light blue background for links */
            padding: 8px 16px;
            border-radius: 20px;
            color: white;
            box-shadow: 0 2px 2px rgba(0, 0, 0, 0.1);
            transition: background 0.3s, transform 0.3s;
        }

        .card-link:hover {
            background: #0277bd;
            transform: scale(1.05);
        }

        .sidebar a {
            color: #aad3ea;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            display: block;
            width: 100%;
            text-align: center;
            transition: background-color 300ms, transform 300ms;
        }

        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(10px);
        }

        .hover-effect:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4);
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }

        .page-title {
            background: linear-gradient(90deg, #4b6cb7, #182848); /* Gradient background */
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .page-title:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body class="font-sans leading-normal tracking-normal">
    <aside>
        <h2 class="text-xl font-bold mb-4">Navigation</h2>
        <a href="messages.php"><i class="fas fa-inbox icon"></i> Inbox</a>
        <a href="outbox.php"><i class="fas fa-paper-plane icon"></i> Outbox</a>
        <a href="../logout_student.php"><i class="fas fa-sign-out-alt icon"></i> Logout</a>
    </aside>
    <div class="content">
        <h1 class="text-3xl font-bold text-white mb-4 page-title">Communication Panel</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($modules as $module): ?>
            <div class="card hover-effect shadow-lg p-6 transition duration-300 ease-in-out">
                <div class="card-header p-4">
                    <h2 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($module['subject_name']); ?></h2>
                    <p class="text-gray-600">Module Code: <?= htmlspecialchars($module['module_code']); ?></p>
                    <p class="text-gray-600">Teacher: <?= htmlspecialchars($module['teacher_name']); ?></p>
                </div>
                <div class="p-4">
                    <a href="stu_chat.php?module=<?= urlencode($module['module_code']) ?>" class="card-link">Communicate</a>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($modules)): ?>
            <p class="text-center text-white">You are not enrolled in any modules.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
