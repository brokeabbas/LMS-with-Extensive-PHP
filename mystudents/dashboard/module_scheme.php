<?php
session_start();
require_once '../../connections/db.php';  // Adjust path as necessary for proper DB connection

// Check user authentication and session validity
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_student.php");
    exit;
}

// Check necessary session variables
if (!isset($_SESSION["student_id"], $_SESSION["school_id"])) {
    echo "Required information not available in session.";
    exit;
}

$student_id = $_SESSION['student_id'];
$school_id = $_SESSION['school_id'];

// SQL to fetch the student's enrolled modules
$sql = "SELECT ss.subject_name, cl.class_name, mt.module_code FROM schoolhu_school_data.student_modules sm
        JOIN schoolhu_school_data.modules_taught mt ON sm.module_id = mt.module_id
        JOIN schoolhu_school_data.class_subject cs ON mt.module_id = cs.module_id
        JOIN schoolhu_school_data.classes cl ON cs.class_id = cl.class_id
        JOIN schoolhu_school_data.school_subjects ss ON cs.subject_id = ss.subject_id
        WHERE sm.student_id = ? AND sm.school_id = ?";

$modules = [];

if ($stmt = $userInfoConn->prepare($sql)) {  // Assume $userInfoConn is your database connection variable
    $stmt->bind_param("ii", $student_id, $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $modules[] = $row;
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
    <title>Your Modules</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script> <!-- FontAwesome for icons -->
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(to right, #6ee7b7, #3b82f6); /* Green to blue gradient */
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        .hover-effect:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4);
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }
        .card {
            background: #f1f5f9; /* Light gray background for cards */
            border-radius: 12px;
            transition: all 0.3s ease-in-out;
            color: #333;
        }
        .card-header {
            background: #e2e8f0; /* Light background for headers */
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
        .sidebar {
            background: linear-gradient(145deg, rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.95));
            backdrop-filter: blur(10px);
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            z-index: 1000;
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
        .icon {
            margin-right: 5px;
            color: #4facfe; /* Matching the color theme for consistency */
        }
        .content {
            margin-left: 250px; /* Same as the sidebar width */
            padding: 20px;
            min-height: 100vh; /* Full height */
            transition: margin-left 0.3s;
        }
        .page-title {
            background: linear-gradient(90deg, #6ee7b7, #3b82f6); /* Gradient background */
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
        .submit-button {
            background: linear-gradient(to right, #4e54c8, #8f94fb);
            border: none;
            color: white;
            cursor: pointer;
            transition: background 0.3s;
        }
        .submit-button:hover {
            background: linear-gradient(to right, #8f94fb, #4e54c8);
        }
    </style>
</head>
<body class="font-sans leading-normal tracking-normal">
    <div class="sidebar">
        <h2 class="text-xl font-bold mb-4">Navigation</h2>
        <a href="../mystudy.php"><i class="fas fa-home icon"></i> Home</a>
        <a href="class_schedule.php"><i class="fas fa-user-circle icon"></i> Classes</a>
        <a href="homework.php"><i class="fas fa-cog icon"></i> Homework</a>
        <a href="gradebook.php"><i class="fas fa-cog icon"></i> Grades</a>
    </div>
    <div class="content">
        <h1 class="text-3xl font-bold text-white mb-4 page-title">Your Enrolled Modules</h1>
        <?php if (empty($modules)): ?>
            <p class="text-center text-white">You are currently not enrolled in any modules.</p>
        <?php else: ?>
            <div class="mt-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($modules as $module): ?>
                    <div class="card hover-effect shadow-lg p-6 transition duration-300 ease-in-out">
                        <div class="card-header p-4">
                            <h3 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($module['subject_name']) ?> - <?= htmlspecialchars($module['class_name']) ?></h3>
                            <p class="text-gray-600">Module Code: <?= htmlspecialchars($module['module_code']) ?></p>
                        </div>
                        <div class="p-4">
                            <a href="view_scheme.php?module_code=<?= urlencode($module['module_code']) ?>" class="card-link">
                                View Module Scheme
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
