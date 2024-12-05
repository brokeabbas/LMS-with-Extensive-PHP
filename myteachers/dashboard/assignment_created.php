<?php
session_start();
require_once '../../connections/db.php';  // Connection to userinfo database
require_once '../../connections/db_school_data.php'; // Connection to school_data database

// Authentication and session validation
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_teacher.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignment Set Successfully</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap');

        body {
            font-family: 'Nunito', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-r from-blue-400 via-blue-500 to-blue-600 min-h-screen flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg shadow-lg max-w-md mx-auto text-center">
        <h1 class="text-2xl font-bold text-green-500 mb-4">
            <i class="fas fa-check-circle"></i> Assignment Set Successfully!
        </h1>
        <p class="text-gray-700 mb-4">Your assignment has been successfully created and is now available for students.</p>
        <div class="space-y-4">
            <a href="manage_assignments.php" class="inline-block bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 transition duration-300">
                <i class="fas fa-tasks"></i> Manage Assignments
            </a>
            <a href="../myteach.php" class="inline-block bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600 transition duration-300">
                <i class="fas fa-home"></i> Home
            </a>
        </div>
    </div>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>
