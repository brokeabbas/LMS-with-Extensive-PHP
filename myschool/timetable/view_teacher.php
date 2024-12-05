<?php
session_start();
require_once '../../connections/db_school_data.php';

// Redirect if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

// Get the teacher number from the URL parameter
$teacher_number = $_GET['teacher_number'] ?? null;

if (!$teacher_number) {
    echo "No teacher number provided.";
    exit;
}

// Fetch the teacher's information
$teacherQuery = "SELECT * FROM schoolhu_userinfo.teacher_info WHERE teacher_number = ?";
$teacherInfo = [];

if ($stmt = $schoolDataConn->prepare($teacherQuery)) {
    $stmt->bind_param("i", $teacher_number);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($teacher = $result->fetch_assoc()) {
        $teacherInfo = $teacher;
    }
    $stmt->close();
}

$schoolDataConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(135deg, #1f2937, #3b82f6);
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
            color: #f8fafc;
            font-family: 'Roboto', sans-serif;
        }
        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .card {
            background-color: #2d3748;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        .btn-primary {
            background-color: #63b3ed;
            color: #fff;
            transition: background-color 0.2s ease-in-out, transform 0.2s ease-in-out;
        }
        .btn-primary:hover {
            background-color: #3182ce;
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gray-900">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center text-gray-200 mb-6">Teacher Details</h1>
        <?php if ($teacherInfo): ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="text-2xl font-semibold text-gray-200 mb-4"><?= htmlspecialchars($teacherInfo['name']); ?></h5>
                    <img src="<?= '../../uploads/' . htmlspecialchars($teacherInfo['photo']); ?>" alt="Teacher Photo" class="rounded-full mb-4" style="width: 100px; height: 100px; object-fit: cover;">
                    <p class="text-gray-400">
                        Email: <?= htmlspecialchars($teacherInfo['email']); ?><br>
                        Phone: <?= htmlspecialchars($teacherInfo['phone']); ?><br>
                        Department: <?= htmlspecialchars($teacherInfo['department']); ?>
                    </p>
                </div>
            </div>
        <?php else: ?>
            <p class="text-red-600">Teacher details not found.</p>
        <?php endif; ?>
        <a href="manage_assigned_teachers.php" class="btn-primary inline-block mt-4 py-2 px-4 rounded focus:outline-none focus:shadow-outline">Back to Management</a>
    </div>
</body>
</html>
