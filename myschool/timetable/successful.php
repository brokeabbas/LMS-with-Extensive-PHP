<?php
session_start();

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
    <title>Curriculum Setup Complete</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(135deg, #1f2937, #3b82f6);
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
            color: #f8fafc;
            font-family: 'Roboto', sans-serif;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
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
        .btn-secondary {
            background-color: #4a5568;
            color: #fff;
            transition: background-color 0.2s ease-in-out, transform 0.2s ease-in-out;
        }
        .btn-secondary:hover {
            background-color: #2d3748;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="max-w-xl mx-auto bg-gray-800 p-8 rounded-lg shadow-lg card text-center">
        <h1 class="text-3xl font-semibold text-gray-200 mb-4">School Curriculum Setup Complete!</h1>
        <p class="text-md text-gray-300 mb-6">All classes and modules have been successfully configured.</p>
        <a href="assign_teacher.php" class="btn-primary inline-block mb-4 py-2 px-4 rounded focus:outline-none focus:shadow-outline">Assign Teachers to Modules</a>
        <a href="../access.php" class="btn-secondary inline-block py-2 px-4 rounded focus:outline-none focus:shadow-outline">Back to Dashboard</a>
    </div>
</body>
</html>
