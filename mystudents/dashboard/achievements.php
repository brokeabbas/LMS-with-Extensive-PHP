<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_student.php"); // Redirect to login page if not logged in
    exit;
}

// Database connection for user info
require_once '../../connections/db.php';

// Database connection for school data
require_once '../../connections/db_school_data.php';

$student_id = $_SESSION['student_id'] ?? null; // Student ID from session
$school_id = $_SESSION['school_id'] ?? null; // School ID from session

$awards = [];

// Fetch student's awards
if ($student_id && $school_id) {
    // Corrected SQL query
    $sql = "SELECT award_type, award_description FROM awards WHERE student_id = ? AND school_id = ?";
    if ($stmt = $schoolDataConn->prepare($sql)) {
        $stmt->bind_param("ii", $student_id, $school_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $awards[] = $row;
        }
        $stmt->close();
    } else {
        echo "SQL Error: " . $schoolDataConn->error;
    }
    $schoolDataConn->close();
}

// You might need other information from the userinfo database here
// For example, fetching school or student name, handle those queries here and then close the connection
$userInfoConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Achievements</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script> <!-- FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: #fff8e1;
            background-image: linear-gradient(45deg, rgba(255, 235, 59, 0.7) 25%, rgba(255, 215, 0, 0.7) 25%, rgba(255, 215, 0, 0.7) 50%, rgba(255, 235, 59, 0.7) 50%, rgba(255, 235, 59, 0.7) 75%, rgba(255, 215, 0, 0.7) 75%, rgba(255, 215, 0, 0.7) 100%);
            background-size: 56.57px 56.57px;
            animation: gradientMove 4s linear infinite;
        }

        @keyframes gradientMove {
            0% {
                background-position: 0 0;
            }
            100% {
                background-position: 56.57px 56.57px;
            }
        }

        .award-card {
            background-color: #ffeb3b;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            transition: transform 0.3s ease-in-out;
            max-width: 300px;
            text-align: center;
            position: relative;
        }
        .award-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 12px rgba(0,0,0,0.3);
        }
        .icon-container {
            background-color: #cddc39;
            border-radius: 50%;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 70px;
            height: 70px;
            margin: 0 auto;
        }
        .award-title {
            color: #d4af37;
            font-size: 1.5rem;
            font-weight: bold;
        }
        .award-description {
            color: #8b4513;
        }
        .badge {
            position: absolute;
            top: -20px;
            right: -20px;
            background-color: #d4af37;
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.25rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-4xl font-bold mb-6 text-center text-yellow-600">My Achievements</h1>

        <!-- Display a message if no awards are found -->
        <?php if (empty($awards)): ?>
            <p class="text-center text-gray-700">No achievements to display.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- List of Awards -->
                <?php foreach ($awards as $award): ?>
                    <div class="award-card flex flex-col items-center">
                        <div class="badge">
                            <i class="fas fa-medal"></i>
                        </div>
                        <div class="icon-container">
                            <i class="fas fa-trophy fa-2x text-white"></i>
                        </div>
                        <div>
                            <h3 class="award-title"><?= htmlspecialchars($award['award_type']); ?></h3>
                            <p class="award-description"><?= htmlspecialchars($award['award_description']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
