<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_student.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Success</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-6">
        <div class="bg-white shadow-lg rounded-lg p-6">
            <h1 class="text-2xl font-bold text-gray-900">Assignment Submitted Successfully!</h1>
            <p class="mt-4 text-gray-600">Your assignment has been successfully submitted. You can review your submissions or continue to work on other assignments.</p>
            <div class="mt-6">
                <a href="homework.php" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-700">Go Back to Assignments</a>
            </div>
        </div>
    </div>
</body>
</html>
