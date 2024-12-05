<?php
session_start();

require_once '../../connections/db_school_data.php'; // Include the database connection file

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

$school_id = $_SESSION['school_id'] ?? null; // Retrieve the school ID from session

// Initialize array for classes
$classes = [];

// Prepare and execute query for classes
$classesQuery = $schoolDataConn->prepare("SELECT class_id, class_name FROM classes WHERE school_id = ?");
$classesQuery->bind_param("i", $school_id);
$classesQuery->execute();
$classesResult = $classesQuery->get_result();
while ($class = $classesResult->fetch_assoc()) {
    $classes[] = $class;
}
$classesQuery->close();

// Handle POST request for updating or deleting classes
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle form submission for updates or deletions
    // Remember to check for SQL injection and use prepared statements
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Classes</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
            background-color: #2d3748;
            border-radius: 10px;
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        .btn-primary {
            background-color: #63b3ed;
            color: #fff;
            transition: background-color 0.2s ease-in-out, transform 0.2s ease-in-out;
        }
        .btn-primary:hover {
            background-color: #3182ce;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background-color: #e53e3e;
            color: #fff;
            transition: background-color 0.2s ease-in-out, transform 0.2s ease-in-out;
        }
        .btn-secondary:hover {
            background-color: #c53030;
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gray-900">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center text-gray-200 mb-6">Manage Classes</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($classes as $class): ?>
                <!-- Existing classes display and edit -->
                <div class="bg-gray-800 p-4 rounded-lg shadow card">
                    <h2 class="text-xl font-semibold text-gray-200 mb-2"><?= htmlspecialchars($class['class_name']) ?></h2>
                    <!-- Link for editing class -->
                    <a href="edit_class.php?class_id=<?= urlencode($class['class_id']) ?>" class="btn-primary inline-block mb-2 py-2 px-4 rounded focus:outline-none focus:shadow-outline">Edit</a>
                    <!-- Link for deleting class with confirmation prompt -->
                    <a href="delete_class.php?delete=<?= urlencode($class['class_id']) ?>" class="btn-secondary inline-block py-2 px-4 rounded focus:outline-none focus:shadow-outline" onclick="return confirm('Are you sure you want to delete this class?');">Delete</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
