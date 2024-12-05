<?php
session_start();
require_once '../../connections/db_school_data.php';  // Adjust this path as needed

// Check if the user is logged in and has the correct role
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["teacher_id"]) || !isset($_SESSION["school_id"])) {
    header("location: login_teacher.php");
    exit;
}

// Handle the book request submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $book_name = $_POST["book_name"];
    $author = $_POST["author"];
    $genre = $_POST["genre"];
    $teacher_id = $_SESSION["teacher_id"];  // Get teacher ID from session
    $school_id = $_SESSION["school_id"];    // Get school ID from session

    // Prepare an insert statement
    $sql = "INSERT INTO book_requests (book_name, author, genre, requester_id, school_id, status) VALUES (?, ?, ?, ?, ?, 'Pending')";
    
    if ($stmt = $schoolDataConn->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("sssii", $book_name, $author, $genre, $teacher_id, $school_id);
        
        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            echo "<p>Request submitted successfully.</p>";
        } else {
            echo "<p>Error submitting request: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p>Error preparing the statement: " . $schoolDataConn->error . "</p>";
    }

    $schoolDataConn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request a Book</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
</head>
<body class="bg-blue-50 font-sans">
    <div class="container mx-auto p-8">
        <aside class="bg-blue-800 text-white p-5 sticky top-0">
            <h2 class="text-xl font-semibold mb-6">Library Dashboard</h2>
            <ul>
                <li><a href="request_status.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700">Request Status</a></li>
                <li><a href="../dashboard.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700">Dashboard</a></li>
            </ul>
        </aside>
        <div class="flex-1 p-6">
            <h1 class="text-xl font-bold mb-6">Request a Book</h1>
            <form action="" method="post">
                <div class="mb-4">
                    <label for="book_name" class="block text-sm font-medium text-gray-700">Book Name</label>
                    <input type="text" name="book_name" id="book_name" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                </div>
                <div class="mb-4">
                    <label for="author" class="block text-sm font-medium text-gray-700">Author</label>
                    <input type="text" name="author" id="author" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                </div>
                <div class="mb-4">
                    <label for="genre" class="block text-sm font-medium text-gray-700">Genre</label>
                    <input type="text" name="genre" id="genre" required class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm">
                </div>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Submit Request</button>
            </form>
        </div>
    </div>
</body>
</html>
