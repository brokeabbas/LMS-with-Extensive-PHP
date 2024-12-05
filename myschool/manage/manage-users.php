<?php
session_start();
require_once '../../connections/db.php'; // Ensure this path is correct

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
            color: #1f2937;
        }
        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .card {
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .hover\:grow { transition: all .2s ease-in-out; }
        .hover\:grow:hover { transform: scale(1.05); }
    </style>
</head>
<body class="bg-gray-100">
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-700 mb-6 text-center">Manage Users</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Manage Existing Students -->
            <div class="card p-6 rounded-lg shadow hover:grow flex flex-col items-center text-center">
                <i class="fas fa-user-graduate fa-3x text-blue-500 mb-4"></i>
                <h2 class="text-xl font-semibold text-gray-700">Existing Students</h2>
                <p class="mt-1 text-gray-500">View, edit, or remove existing student profiles.</p>
                <a href="manage-existing-students.php" class="mt-4 inline-block text-blue-500 hover:text-blue-700">Manage Students</a>
            </div>

            <!-- Manage Existing Teachers -->
            <div class="card p-6 rounded-lg shadow hover:grow flex flex-col items-center text-center">
                <i class="fas fa-chalkboard-teacher fa-3x text-blue-500 mb-4"></i>
                <h2 class="text-xl font-semibold text-gray-700">Existing Teachers</h2>
                <p class="mt-1 text-gray-500">Manage teacher accounts including profile updates and role assignments.</p>
                <a href="manage-existing-teachers.php" class="mt-4 inline-block text-blue-500 hover:text-blue-700">Manage Teachers</a>
            </div>

            <!-- Student Account Details -->
            <div class="card p-6 rounded-lg shadow hover:grow flex flex-col items-center text-center">
                <i class="fas fa-user-clock fa-3x text-blue-500 mb-4"></i>
                <h2 class="text-xl font-semibold text-gray-700">Student Account Details</h2>
                <p class="mt-1 text-gray-500">View your student user account details.</p>
                <a href="account_page.php" class="mt-4 inline-block text-blue-500 hover:text-blue-700">View Account</a>
            </div>

            <!-- Teacher Account Details -->
            <div class="card p-6 rounded-lg shadow hover:grow flex flex-col items-center text-center">
                <i class="fas fa-user-plus fa-3x text-blue-500 mb-4"></i>
                <h2 class="text-xl font-semibold text-gray-700">Teacher Account Details</h2>
                <p class="mt-1 text-gray-500">View your teacher account details.</p>
                <a href="teacher_account.php" class="mt-4 inline-block text-blue-500 hover:text-blue-700">View Account</a>
            </div>

            <!-- Inactive Students -->
            <div class="card p-6 rounded-lg shadow hover:grow flex flex-col items-center text-center">
                <i class="fas fa-user-slash fa-3x text-blue-500 mb-4"></i>
                <h2 class="text-xl font-semibold text-gray-700">Inactive Students</h2>
                <p class="mt-1 text-gray-500">View students who are currently inactive.</p>
                <a href="inactive_students.php" class="mt-4 inline-block text-blue-500 hover:text-blue-700">View Inactive Students</a>
            </div>
        </div>
    </div>
</body>
</html>
