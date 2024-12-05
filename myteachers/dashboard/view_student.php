<?php
require_once '../../connections/db.php'; // Assumes you have a db.php that sets up $userInfoConn

if (!isset($_GET['student_number'])) {
    die('Student ID not provided!');
}

$student_id = $_GET['student_number'];
$sql = "SELECT * FROM student_info WHERE student_number = ?";

if ($stmt = $userInfoConn->prepare($sql)) {
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $stmt->close();
} else {
    die("SQL Error: " . $userInfoConn->error);
}

$userInfoConn->close();

// Display student details in HTML below
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student Details</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-md mx-auto bg-white p-5 rounded shadow">
        <h1 class="text-xl font-bold mb-4">Student Details</h1>
        <p><strong>Full Name:</strong> <?= htmlspecialchars($student['fullname']); ?></p>
        <!-- Add more fields as needed -->
    </div>
</body>
</html>
