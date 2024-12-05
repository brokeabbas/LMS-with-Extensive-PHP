<?php
session_start();

// Import the necessary database connection scripts
require_once '../../connections/db_school_data.php';

// Check if the user is logged in, redirect if not
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
    <title>School Management Options</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(135deg, #1f2937, #3b82f6);
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
            color: #f8fafc;
        }
        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .card {
            background-color: #1e293b;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        .hover\:grow {
            transition: all 0.2s ease-in-out;
        }
        .hover\:grow:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body class="bg-gray-900">
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-200 mb-6 text-center">School Modules Options</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Create School Classes and Subjects -->
            <div class="p-6 rounded-lg shadow hover:grow flex flex-col items-center text-center card">
                <i class="fas fa-book-open fa-3x text-blue-500 mb-4"></i>
                <h2 class="text-xl font-semibold text-gray-200">Create Classes and Subjects</h2>
                <p class="mt-1 text-gray-400">Add new classes and subjects to the school curriculum.</p>
                <a href="timetable-management.php" class="mt-4 inline-block text-blue-500 hover:text-blue-300">Create Classes/Subjects</a>
            </div>

            <!-- Manage School Classes and Subjects -->
            <div class="p-6 rounded-lg shadow hover:grow flex flex-col items-center text-center card">
                <i class="fas fa-edit fa-3x text-blue-500 mb-4"></i>
                <h2 class="text-xl font-semibold text-gray-200">Manage Classes and Subjects</h2>
                <p class="mt-1 text-gray-400">Edit or remove existing classes and subjects.</p>
                <a href="manage_curriculum.php" class="mt-4 inline-block text-blue-500 hover:text-blue-300">Manage Classes/Subjects</a>
            </div>

            <!-- Manage Teachers to Classes and Subjects -->
            <div class="p-6 rounded-lg shadow hover:grow flex flex-col items-center text-center card">
                <i class="fas fa-chalkboard-teacher fa-3x text-blue-500 mb-4"></i>
                <h2 class="text-xl font-semibold text-gray-200">Assign Teachers</h2>
                <p class="mt-1 text-gray-400">Assign teachers to specific classes and subjects.</p>
                <a href="assign_teacher.php" class="mt-4 inline-block text-blue-500 hover:text-blue-300">Assign Teachers</a>
            </div>

            <!-- Manage Assigned Teachers -->
            <div class="p-6 rounded-lg shadow hover:grow flex flex-col items-center text-center card">
                <i class="fas fa-users-cog fa-3x text-blue-500 mb-4"></i>
                <h2 class="text-xl font-semibold text-gray-200">Manage Assigned Teachers</h2>
                <p class="mt-1 text-gray-400">Review and adjust teacher assignments to classes and subjects.</p>
                <a href="manage_assigned_teachers.php" class="mt-4 inline-block text-blue-500 hover:text-blue-300">Manage Teachers</a>
            </div>

            <!-- Manage Students to Classes and Subjects -->
            <div class="p-6 rounded-lg shadow hover:grow flex flex-col items-center text-center card">
                <i class="fas fa-user-graduate fa-3x text-blue-500 mb-4"></i>
                <h2 class="text-xl font-semibold text-gray-200">Enroll Students</h2>
                <p class="mt-1 text-gray-400">Manage student enrollments in classes and subjects.</p>
                <a href="enroll_student.php" class="mt-4 inline-block text-blue-500 hover:text-blue-300">Enroll Students</a>
            </div>
        </div>
    </div>
</body>
</html>
