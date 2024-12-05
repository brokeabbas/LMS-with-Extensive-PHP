<?php
session_start();
require_once '../../connections/db.php';  // Connection to the user info database
require_once '../../connections/db_school_data.php'; // Connection to the school data database

// Authentication check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_teacher.php");
    exit;
}

if (!isset($_GET['module_id'])) {
    exit('Module not specified.');
}

$module_id = $_GET['module_id'];

// Handle delete request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_assignment'])) {
    $assignment_id = $_POST['assignment_id'];

    // First delete all related submissions
    $delete_submissions_sql = "DELETE FROM assignment_submissions WHERE assignment_id = ?";
    if ($delete_submissions_stmt = $schoolDataConn->prepare($delete_submissions_sql)) {
        $delete_submissions_stmt->bind_param("i", $assignment_id);
        if (!$delete_submissions_stmt->execute()) {
            echo "Error deleting submissions: " . $schoolDataConn->error;
            $delete_submissions_stmt->close();
            exit;
        }
        $delete_submissions_stmt->close();
    } else {
        echo "Error preparing deletion of submissions: " . $schoolDataConn->error;
        exit;
    }

    // Now delete the assignment
    $delete_sql = "DELETE FROM assignments WHERE id = ? AND module_id = ? AND school_id = ?";
    if ($delete_stmt = $schoolDataConn->prepare($delete_sql)) {
        $delete_stmt->bind_param("iii", $assignment_id, $module_id, $_SESSION['school_id']);
        if ($delete_stmt->execute()) {
            // Refresh the page to show updated list
            header("Location: " . $_SERVER['PHP_SELF'] . "?module_id=" . $module_id);
            exit;
        } else {
            echo "SQL Error: " . $schoolDataConn->error;
        }
        $delete_stmt->close();
    } else {
        echo "Error preparing deletion of assignment: " . $schoolDataConn->error;
        exit;
    }
}

// Fetch all assignments for the specified module
$sql = "SELECT id, assignment_name, due_date, description, file_path 
        FROM assignments 
        WHERE module_id = ? AND school_id = ?";
$assignments = [];

if ($stmt = $schoolDataConn->prepare($sql)) {
    $stmt->bind_param("ii", $module_id, $_SESSION['school_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }
    $stmt->close();
} else {
    echo "SQL Error: " . $schoolDataConn->error;
    exit;
}

$schoolDataConn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Assignments</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
</head>
<body class="bg-blue-50">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-blue-800 text-white shadow-md">
            <div class="px-6 py-8">
                <h1 class="text-xl font-semibold">Teaching Dashboard</h1>
                <nav class="mt-10">
                    <a href="../dashboard.php" class="flex items-center py-2.5 px-4 rounded hover:bg-blue-700">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                    <a href="/logout" class="flex items-center py-2.5 px-4 rounded hover:bg-blue-700">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </nav>
            </div>
        </div>
        <!-- Main content area -->
        <div class="flex-1 p-6">
            <header class="bg-white shadow p-6 rounded-lg mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Assignments for Module</h2>
            </header>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php if (!empty($assignments)): ?>
                    <?php foreach ($assignments as $assignment): ?>
                        <div class="bg-white p-6 rounded-lg shadow-md flex flex-col items-center justify-center text-center">
                            <h2 class="text-2xl font-semibold text-gray-800 mb-2"><?= htmlspecialchars($assignment['assignment_name']); ?></h2>
                            <p class="text-gray-600 mb-3">Due: <?= htmlspecialchars(date("F j, Y", strtotime($assignment['due_date']))); ?></p>
                            <p class="mb-4"><?= nl2br(htmlspecialchars($assignment['description'])); ?></p>
                            <?php if (!empty($assignment['file_path'])): ?>
                                <a href="<?= htmlspecialchars($assignment['file_path']); ?>" class="inline-flex items-center text-blue-600 hover:text-blue-800 transition-colors duration-300 mb-4">
                                    <i class="fas fa-download mr-2"></i>
                                    Download Attachment
                                </a>
                            <?php endif; ?>
                            <form method="post" onsubmit="return confirm('Are you sure you want to delete this assignment?');" class="mt-4">
                                <input type="hidden" name="assignment_id" value="<?= $assignment['id']; ?>">
                                <button type="submit" name="delete_assignment" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-700">
                                    <i class="fas fa-trash-alt mr-2"></i>
                                    Delete
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-2">
                        <p class="text-gray-600 text-center">No assignments found for this module.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
