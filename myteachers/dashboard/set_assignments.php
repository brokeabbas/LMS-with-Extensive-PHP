<?php
session_start();
require_once '../../connections/db.php';  // Connection to userinfo database
require_once '../../connections/db_school_data.php'; // Connection to school_data database

// Authentication and session validation
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_teacher.php");
    exit;
}

if (!isset($_GET['module_id']) || !isset($_SESSION["school_id"])) {
    echo "Module or school information not specified.";
    exit;
}

$module_id = $_GET['module_id'];
$school_id = $_SESSION['school_id'];

// Handle assignment submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $assignment_name = $_POST['assignment_name'];
    $due_date = $_POST['due_date'];
    $description = $_POST['description'];
    $file_path = '';

    // Handle file upload
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] == 0) {
        $file_tmp = $_FILES['assignment_file']['tmp_name'];
        $file_name = $_FILES['assignment_file']['name'];
        $file_path = '../../assessments_uploads/' . $file_name; // Ensure this directory is writable
        move_uploaded_file($file_tmp, $file_path);
    }

    // Insert into database
    $sql = "INSERT INTO assignments (module_id, school_id, assignment_name, due_date, description, file_path) VALUES (?, ?, ?, ?, ?, ?)";
    if ($stmt = $schoolDataConn->prepare($sql)) {
        $stmt->bind_param("iissss", $module_id, $school_id, $assignment_name, $due_date, $description, $file_path);
        $stmt->execute();
        header("location: assignment_created.php");
        echo "<p>Assignment set successfully!</p>";
        $stmt->close();
    } else {
        echo "<p>SQL Error: " . $schoolDataConn->error . "</p>";
    }
}

$schoolDataConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Assignments</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        /* Enhance file input appearance */
        .file-input {
            transition: background-color 0.3s, border-color 0.3s;
            cursor: pointer;
        }
        .file-input:hover {
            background-color: #dbeafe;
        }
        /* Adjust main content colors and layout */
        .form-container {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            color: #334155; /* Dark gray-blue for text */
        }
        .header {
            background-color: #2b6cb0; /* Deep blue */
            padding: 1rem;
            color: #ffffff;
            margin-bottom: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-800 font-sans leading-normal tracking-normal">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-blue-800 text-white flex flex-col">
            <div class="p-5 text-xl font-medium border-b border-gray-700">
                Teaching Dashboard
            </div>
            <ul class="flex-grow">
                <li class="p-4 hover:bg-blue-700">
                    <a href="../myteach.php" class="flex items-center space-x-2">
                        <i class="fas fa-home"></i><span>Dashboard</span>
                    </a>
                </li>
                <li class="p-4 hover:bg-blue-700">
                    <a href="view_schemes.php" class="flex items-center space-x-2">
                        <i class="fas fa-tasks"></i><span>View Scheme</span>
                    </a>
                </li>
                <li class="p-4 hover:bg-blue-700">
                    <a href="/logout" class="flex items-center space-x-2">
                        <i class="fas fa-sign-out-alt"></i><span>Logout</span>
                    </a>
                </li>
            </ul>
        </div>
        <!-- Main Content Area -->
        <div class="flex-1 p-10">
            <div class="header">
                    <h2 class="text-lg font-bold">Assignment Creation Portal</h2>
                    <p>Create and manage assignments for your module efficiently.</p>
                </div>
            <div class="form-container">
                <h1 class="text-3xl font-bold text-center mb-6">Set Assignment</h1>
                <form action="set_assignments.php?module_id=<?= htmlspecialchars($module_id); ?>" method="post" enctype="multipart/form-data" class="space-y-4">
                    <div>
                        <label for="assignment_name" class="block text-sm font-medium">Assignment Name:</label>
                        <input type="text" id="assignment_name" name="assignment_name" required class="mt-1 p-2 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="due_date" class="block text-sm font-medium">Due Date:</label>
                        <input type="date" id="due_date" name="due_date" required class="mt-1 p-2 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium">Description:</label>
                        <textarea id="description" name="description" rows="4" required class="mt-1 p-2 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                    <div>
                        <label for="assignment_file" class="block text-sm font-medium">Upload File:</label>
                        <input type="file" id="assignment_file" name="assignment_file" class="mt-1 block w-full file:mr-4 file:py-2 file:px-4 file:rounded file:border file:border-gray-300 file:text-sm file:font-medium file:bg-white file:text-gray-700 hover:file:bg-gray-100">
                    </div>
                    <button type="submit" class="px-4 py-2 font-bold text-white bg-blue-600 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-opacity-50 w-full">Set Assignment</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
