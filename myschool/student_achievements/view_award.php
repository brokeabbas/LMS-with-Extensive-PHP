<?php
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

require_once '../../connections/db_school_data.php'; // Include the database connection

$student_id = $_GET['id'] ?? null; // Get the student ID from URL
$awards = [];

// Fetch all awards for the student
if ($student_id) {
    $query = "SELECT award_type, award_description FROM awards WHERE student_id = ?";
    if ($stmt = $schoolDataConn->prepare($query)) {
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $awards[] = $row;
        }
        $stmt->close();
    }
    $schoolDataConn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student Awards</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(135deg, #fef9c3, #fef3c7);
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
            background-color: #fef3c7; /* Light gold background */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        .text-gold {
            color: #b45309; /* Gold text color */
        }
        .bg-gold {
            background-color: #f59e0b; /* Gold background */
            color: #fff;
        }
        .container {
            max-width: 40rem; /* 640px */
        }
    </style>
</head>
<body class="flex items-center justify-center h-screen">
    <div class="container mx-auto py-10 px-6 lg:px-8">
        <div class="max-w-xl mx-auto bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b border-gold">
                <h1 class="text-3xl font-bold text-center text-gray-900">Student Awards</h1>
            </div>
            <?php if (empty($awards)): ?>
                <p class="text-red-500 p-6 text-center">No awards found for this student.</p>
            <?php else: ?>
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <ul class="divide-y divide-gray-200">
                        <?php foreach ($awards as $award): ?>
                            <li class="px-6 py-4 card">
                                <strong class="text-gold">Award Type:</strong> <?php echo htmlspecialchars($award['award_type']); ?><br>
                                <strong class="text-gold">Description:</strong> <?php echo htmlspecialchars($award['award_description']); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
