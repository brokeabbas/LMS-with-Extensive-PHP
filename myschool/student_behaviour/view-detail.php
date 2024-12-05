<?php
session_start();

// Include database connection files for school data and user info
require_once '../../connections/db_school_data.php'; // Adjust the path as necessary
require_once '../../connections/db.php'; // Assuming db.php is your user database connection setup

// Check if the user is logged in, otherwise redirect to the login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

// Validate the record ID
$recordId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($recordId <= 0) {
    echo "Invalid Record ID.";
    exit;
}

// Prepare SQL to fetch the record along with student and teacher details
$query = "SELECT dr.*, si.fullname AS student_name, si.student_number, ti.name AS teacher_name, ti.teacher_number AS teacher_number
          FROM disciplinary_records dr
          LEFT JOIN schoolhu_userinfo.student_info si ON dr.student_id = si.id
          LEFT JOIN schoolhu_userinfo.teacher_info ti ON dr.teacher_id = ti.id
          WHERE dr.id = ? AND dr.school_id = ?";

$stmt = $schoolDataConn->prepare($query);
if (!$stmt) {
    echo "Preparation failed: (" . $schoolDataConn->errno . ") " . $schoolDataConn->error;
    exit;
}

// Bind parameters and execute
$stmt->bind_param('ii', $recordId, $_SESSION['school_id']);
$stmt->execute();
$result = $stmt->get_result();
$record = $result->fetch_assoc();

if (!$record) {
    echo "No record found.";
    exit;
}

$stmt->close();
$schoolDataConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Disciplinary Record Details</title>
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
        .text-red-accent {
            color: #f56565;
        }
        .border-red-accent {
            border-color: #f56565;
        }
    </style>
</head>
<body class="bg-gray-900">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-3xl mx-auto bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="p-5 border-b border-red-accent">
                <h1 class="text-2xl font-bold text-red-accent">Disciplinary Record Details</h1>
            </div>
            <div class="p-5">
                <h2 class="text-xl text-gray-300">Student Details</h2>
                <p class="text-gray-400"><strong class="text-red-accent">Name:</strong> <?= htmlspecialchars($record['student_name']) ?></p>
                <p class="text-gray-400"><strong class="text-red-accent">Number:</strong> <?= htmlspecialchars($record['student_number']) ?></p>

                <h2 class="text-xl text-gray-300 mt-6">Teacher Details</h2>
                <p class="text-gray-400"><strong class="text-red-accent">Name:</strong> <?= htmlspecialchars($record['teacher_name'] ?? 'N/A') ?></p>
                <p class="text-gray-400"><strong class="text-red-accent">Number:</strong> <?= htmlspecialchars($record['teacher_number'] ?? 'N/A') ?></p>

                <h2 class="text-xl text-gray-300 mt-6">Incident Details</h2>
                <p class="text-gray-400"><strong class="text-red-accent">Title:</strong> <?= htmlspecialchars($record['strike_title']) ?></p>
                <p class="text-gray-400"><strong class="text-red-accent">Number:</strong> <?= htmlspecialchars($record['strike_number']) ?></p>
                <p class="text-gray-400"><strong class="text-red-accent">Description:</strong> <?= nl2br(htmlspecialchars($record['strike_description'])) ?></p>
                <p class="text-gray-400"><strong class="text-red-accent">Consequence:</strong> <?= nl2br(htmlspecialchars($record['strike_consequence'])) ?></p>
                <p class="text-gray-400"><strong class="text-red-accent">Date:</strong> <?= htmlspecialchars($record['recorded_on']) ?></p>
            </div>
        </div>
    </div>
</body>
</html>
