<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

// Database connection for school_data
require_once '../../../connections/db_school_data.php'; // Adjust path as necessary

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $genre = $_POST['genre'];
    $school_id = $_SESSION['school_id']; // Assuming school_id is stored in session

    $cover_path = '../../../book_upload/covers/';
    $book_path = '../../../book_upload/books/';
    
    // Get original file names
    $cover_name = basename($_FILES['cover']['name']);
    $book_name = basename($_FILES['book_pdf']['name']);
    
    // Generate unique file names
    $unique_cover_name = $cover_path . time() . '_' . $cover_name;
    $unique_book_name = $book_path . time() . '_' . $book_name;

    $maxFileSize = 150 * 1024 * 1024; // 150MB in bytes

    if ($_FILES['cover']['size'] > $maxFileSize || $_FILES['book_pdf']['size'] > $maxFileSize) {
        $message = "Error: File size exceeds 150MB limit.";
    } else {
        if (move_uploaded_file($_FILES['cover']['tmp_name'], $unique_cover_name) && move_uploaded_file($_FILES['book_pdf']['tmp_name'], $unique_book_name)) {
            $sql = "INSERT INTO library (title, author, genre, cover, book_pdf, school_id) VALUES (?, ?, ?, ?, ?, ?)";
            if ($stmt = $schoolDataConn->prepare($sql)) {
                $stmt->bind_param("sssssi", $title, $author, $genre, $unique_cover_name, $unique_book_name, $school_id);
                if ($stmt->execute()) {
                    $message = "Book added successfully!";
                } else {
                    $message = "Error adding book.";
                }
                $stmt->close();
            }
        } else {
            $message = "Failed to upload files.";
        }
    }
}

$schoolDataConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Book to Library</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(135deg, #f3f4f6, #f9fafb);
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
            color: #1f2937;
            font-family: 'Roboto', sans-serif;
        }
        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .card {
            background-color: #ffffff; /* White background */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        .btn-primary {
            background-color: #3b82f6; /* Blue color */
            color: #fff;
            transition: background-color 0.2s ease-in-out, transform 0.2s ease-in-out;
        }
        .btn-primary:hover {
            background-color: #2563eb; /* Darker blue */
            transform: translateY(-2px);
        }
        .form-label {
            color: #4b5563; /* Gray color */
        }
        .form-input {
            color: #000;
            padding: 1rem;
            font-size: 1.125rem; /* 18px */
            border: 2px solid #3b82f6; /* Blue border */
        }
        .container {
            max-width: 40rem; /* 640px */
        }
    </style>
</head>
<body class="bg-gray-900 flex items-center justify-center h-screen">
    <div class="container mx-auto py-10 px-6 lg:px-8">
        <div class="max-w-xl mx-auto bg-white rounded-lg shadow overflow-hidden card">
            <div class="p-6 border-b border-blue-500">
                <h1 class="text-3xl font-bold text-center text-gray-900">Add a New Book</h1>
            </div>
            <?php if ($message): ?>
            <p class="text-center p-4 text-red-500"><?php echo $message; ?></p>
            <?php endif; ?>
            <form action="add-book.php" method="post" enctype="multipart/form-data" class="p-6 space-y-4">
                <div class="mb-6">
                    <label for="title" class="block text-lg font-medium form-label">Book Title</label>
                    <input type="text" name="title" id="title" placeholder="Enter book title" required class="form-input mt-2 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm border-gray-300 rounded-md">
                </div>
                <div class="mb-6">
                    <label for="author" class="block text-lg font-medium form-label">Author</label>
                    <input type="text" name="author" id="author" placeholder="Author's name" required class="form-input mt-2 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm border-gray-300 rounded-md">
                </div>
                <div class="mb-6">
                    <label for="genre" class="block text-lg font-medium form-label">Genre/Subject</label>
                    <input type="text" name="genre" id="genre" placeholder="Genre or subject" required class="form-input mt-2 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm border-gray-300 rounded-md">
                </div>
                <div class="mb-6">
                    <label for="cover" class="block text-lg font-medium form-label">Book Cover</label>
                    <input type="file" name="cover" id="cover" accept="image/*" required class="form-input mt-2 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm border-gray-300 rounded-md">
                </div>
                <div class="mb-6">
                    <label for="book_pdf" class="block text-lg font-medium form-label">Book File (PDF)</label>
                    <input type="file" name="book_pdf" id="book_pdf" accept="application/pdf" required class="form-input mt-2 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm border-gray-300 rounded-md">
                </div>
                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 border border-transparent text-lg font-medium rounded-md shadow-sm text-white btn-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Submit
                </button>
            </form>
        </div>
    </div>
</body>
</html>
