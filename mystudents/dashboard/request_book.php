<?php
session_start();
require_once '../../connections/db_school_data.php';  // Adjust this path as needed

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["student_id"]) || !isset($_SESSION["school_id"])) {
    header("location: ../login_student.php");
    exit;
}

$successMessage = "";

// Handle the book request submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    $book_name = $_POST["book_name"];
    $author = $_POST["author"];
    $genre = $_POST["genre"];
    $student_id = $_SESSION["student_id"];  // Get student ID from session
    $school_id = $_SESSION["school_id"];    // Get school ID from session

    // Prepare an insert statement
    $sql = "INSERT INTO book_requests (book_name, author, genre, requester_id, school_id, status) VALUES (?, ?, ?, ?, ?, 'Pending')";
    
    if ($stmt = $schoolDataConn->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("sssii", $book_name, $author, $genre, $student_id, $school_id);
        
        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            $successMessage = "Request submitted successfully.";
        } else {
            $successMessage = "Error submitting request: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $successMessage = "Error preparing the statement: " . $schoolDataConn->error;
    }
}

$schoolDataConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request a Book</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        aside {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 64;
            background: #1a202c;
            color: white;
            padding: 20px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-blue-50 font-sans">
    <div class="container mx-auto p-8">
        <aside class="bg-blue-800 p-5">
            <h2 class="text-xl font-semibold mb-6">Library Dashboard</h2>
            <ul>
                <li><a href="request_status.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700">Request Status</a></li>
                <li><a href="../mystudy.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700">Dashboard</a></li>
            </ul>
        </aside>
        <div class="flex-1 p-6" style="margin-left: 256px;">
            <h1 class="text-xl font-bold mb-6">Request a Book</h1>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
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
                <button type="submit" name="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Submit Request</button>
            </form>
            <?php if ($successMessage): ?>
                <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
