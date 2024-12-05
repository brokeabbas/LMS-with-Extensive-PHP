<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_student.php");
    exit;
}

require_once '../../connections/db_school_data.php'; // Ensure the database connection is available

// Fetch only the logged-in student's book requests from the database
$student_id = $_SESSION['student_id']; // Assuming student_id is stored in session
$school_id = $_SESSION['school_id']; // Assuming school_id is stored in session
$requests = [];
$query = "SELECT book_request_id, book_name, author, genre, requester_id, status 
          FROM book_requests 
          WHERE requester_id = ? AND school_id = ? 
          ORDER BY created_at DESC";

if ($stmt = $schoolDataConn->prepare($query)) {
    $stmt->bind_param("ii", $student_id, $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
    $stmt->close();
} else {
    echo "Error preparing the statement: " . $schoolDataConn->error;
}

$schoolDataConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Book Requests</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-xl font-bold mb-4">Your Book Requests</h1>
        <div class="overflow-x-auto relative">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="py-3 px-6">Book Name</th>
                        <th scope="col" class="py-3 px-6">Author</th>
                        <th scope="col" class="py-3 px-6">Genre</th>
                        <th scope="col" class="py-3 px-6">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $request): ?>
                    <tr class="bg-white border-b">
                        <td class="py-4 px-6"><?= htmlspecialchars($request['book_name']); ?></td>
                        <td class="py-4 px-6"><?= htmlspecialchars($request['author']); ?></td>
                        <td class="py-4 px-6"><?= htmlspecialchars($request['genre']); ?></td>
                        <td class="py-4 px-6"><?= htmlspecialchars($request['status']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (empty($requests)): ?>
                <p class="text-center py-4">No book requests found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
