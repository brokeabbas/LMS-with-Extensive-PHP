<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

require_once '../../connections/db.php'; // Ensure the database connection is available
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Library</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        <h1 class="text-3xl font-bold text-gray-200 mb-6 text-center">Library Management</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Add Book Card -->
            <div class="p-6 rounded-lg shadow hover:grow flex flex-col items-center text-center card">
                <i class="fas fa-book-medical fa-3x text-blue-500 mb-4"></i>
                <h2 class="text-xl font-semibold text-gray-200">Add a Book</h2>
                <p class="mt-1 text-gray-400">Add a new book to the library collection.</p>
                <a href="library_setting/add-book.php" class="mt-4 inline-block text-blue-500 hover:text-blue-300">Add a Book</a>
            </div>

            <!-- View Books Card -->
            <div class="p-6 rounded-lg shadow hover:grow flex flex-col items-center text-center card">
                <i class="fas fa-book-open fa-3x text-blue-500 mb-4"></i>
                <h2 class="text-xl font-semibold text-gray-200">View Books</h2>
                <p class="mt-1 text-gray-400">Explore the library collection.</p>
                <a href="library_setting/view-books.php" class="mt-4 inline-block text-blue-500 hover:text-blue-300">View Books</a>
            </div>

            <!-- Delete Books Card -->
            <div class="p-6 rounded-lg shadow hover:grow flex flex-col items-center text-center card">
                <i class="fas fa-book-dead fa-3x text-blue-500 mb-4"></i>
                <h2 class="text-xl font-semibold text-gray-200">Delete a Book</h2>
                <p class="mt-1 text-gray-400">Remove books from the library collection.</p>
                <a href="library_setting/delete-book.php" class="mt-4 inline-block text-blue-500 hover:text-blue-300">Delete a Book</a>
            </div>

            <!-- Student Request Card -->
            <div class="p-6 rounded-lg shadow hover:grow flex flex-col items-center text-center card">
                <i class="fas fa-user-tag fa-3x text-blue-500 mb-4"></i>
                <h2 class="text-xl font-semibold text-gray-200">Student Requests</h2>
                <p class="mt-1 text-gray-400">View what your students would like to read the most.</p>
                <a href="library_setting/book_requests.php" class="mt-4 inline-block text-blue-500 hover:text-blue-300">Student Requests</a>
            </div>
        </div>
    </div>
</body>
</html>

