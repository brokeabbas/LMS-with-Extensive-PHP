<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

require_once '../../../connections/db_school_data.php'; // Adjust path as necessary

$school_id = $_SESSION['school_id']; // Retrieve the school ID from session

// Handle the publishing of a book
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['publish_id'])) {
    $publishId = $_POST['publish_id'];
    $updateQuery = "UPDATE library SET is_approved = 1 WHERE id = ? AND school_id = ?";
    if ($stmt = $schoolDataConn->prepare($updateQuery)) {
        $stmt->bind_param("ii", $publishId, $school_id);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "Error updating record: " . $schoolDataConn->error;
    }
}

// Fetch all books that are not yet approved from the same school
$books = [];
$query = "SELECT id, title, author, genre, cover, book_pdf FROM library WHERE is_approved = 0 AND school_id = ?";
if ($stmt = $schoolDataConn->prepare($query)) {
    $stmt->bind_param("i", $school_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Adjust the paths by adding '../' to the beginning of each path
            $row['cover'] = '../' . $row['cover'];
            $row['book_pdf'] = '../' . $row['book_pdf'];
            $books[] = $row;
        }
    }
    $stmt->close();
}

$schoolDataConn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Book Approvals</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-xl font-bold mb-4">Book Approval Dashboard</h1>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <?php foreach ($books as $book): ?>
                <div class="bg-white p-4 shadow rounded-lg text-center">
                    <img src="<?= htmlspecialchars($book['cover']); ?>" alt="Cover" style="height: 200px;">
                    <h2 class="text-lg font-semibold mt-2"><?= htmlspecialchars($book['title']); ?></h2>
                    <p class="text-gray-600"><?= htmlspecialchars($book['author']); ?></p>
                    <form method="post">
                        <input type="hidden" name="publish_id" value="<?= $book['id']; ?>">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Publish</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
