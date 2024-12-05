<?php
session_start();
require_once '../../connections/db_school_data.php';  // Adjust this path as needed

// Authentication checks
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["school_id"])) {
    header("location: login_teacher.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $genre = $_POST['genre'] ?? 'General'; // Default genre if not specified

    // Process cover image
    $coverPath = null;
    if (!empty($_FILES['cover']['name'])) {
        $coverPath = '../../book_upload/covers/' . basename($_FILES['cover']['name']);
        if (!move_uploaded_file($_FILES['cover']['tmp_name'], $coverPath)) {
            echo "Failed to upload cover image.";
        }
    }

    // Process book file
    $bookPdfPath = null;
    if (!empty($_FILES['book_pdf']['name'])) {
        $bookPdfPath = '../../book_upload/books/' . basename($_FILES['book_pdf']['name']);
        if (!move_uploaded_file($_FILES['book_pdf']['tmp_name'], $bookPdfPath)) {
            echo "Failed to upload book PDF.";
        }
    }

    // Insert book into database (Pending approval)
    $sql = "INSERT INTO library (title, author, genre, cover, book_pdf, school_id, is_approved) VALUES (?, ?, ?, ?, ?, ?, 0)";
    if ($stmt = $schoolDataConn->prepare($sql)) {
        $stmt->bind_param("sssssi", $title, $author, $genre, $coverPath, $bookPdfPath, $_SESSION['school_id']);
        $stmt->execute();
        echo "Book submitted for approval.";
        $stmt->close();
    } else {
        echo "Error preparing the statement: " . $schoolDataConn->error;
    }

    $schoolDataConn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Book</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
</head>
<body class="bg-blue-50 font-sans">
    <div class="flex">
        <!-- Sidebar for navigation -->
        <aside class="bg-blue-800 p-5 text-white" style="width: 250px;">
            <h2 class="text-xl font-semibold mb-6">Library Dashboard</h2>
            <ul>
                <li><a href="../myteach.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700">Dashboard</a></li>
            </ul>
        </aside>

        <!-- Main content area -->
        <div class="flex-1 p-6" style="margin-left: 250px;"> <!-- Adjust margin to match the width of the sidebar -->
            <h1 class="text-4xl font-bold mb-6">Upload a Book</h1>
            <form action="" method="post" enctype="multipart/form-data" class="bg-white p-8 rounded shadow">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="title">Title</label>
                    <input type="text" id="title" name="title" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="author">Author</label>
                    <input type="text" id="author" name="author" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="genre">Genre</label>
                    <input type="text" id="genre" name="genre" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="cover">Cover Image</label>
                    <input type="file" id="cover" name="cover" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="book_pdf">Book File (PDF)</label>
                    <input type="file" id="book_pdf" name="book_pdf" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Upload
                </button>
            </form>
        </div>
    </div>
</body>
</html>
