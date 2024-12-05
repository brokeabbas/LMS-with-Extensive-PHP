<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Import the necessary database connection scripts
require_once '../../connections/db_school_data.php';
require_once '../../connections/db.php';

// Check if the user is logged in, redirect if not
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

// Establish the database connection
$host = '127.0.0.1';
$user = 'schoolhu_school';
$password = ')SQm.YHFM+w&';
$dbname = 'schoolhu_school_data';
$port = 3306;
$schoolDataConn = new mysqli($host, $user, $password, $dbname, $port);

// Check the database connection
if ($schoolDataConn->connect_error) {
    die("Connection failed: " . $schoolDataConn->connect_error);
}

$successMessage = '';
$errorMessage = '';

// Process the form when it is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';

    // Action for adding a class
    if ($action === 'Add Class') {
        $class_name = $_POST['class_name'] ?? '';
        $school_id = $_SESSION['school_id']; // Assume school_id is stored in session

        if (!empty($class_name)) {
            // Prepare the SQL to check for existing class names similar to the input
            $checkQuery = "SELECT COUNT(*) FROM classes WHERE school_id = ? AND class_name LIKE ?";
            if ($checkStmt = $schoolDataConn->prepare($checkQuery)) {
                $likeName = $class_name . '%'; // Search pattern
                $checkStmt->bind_param("is", $school_id, $likeName);
                $checkStmt->execute();
                $checkStmt->bind_result($count);
                $checkStmt->fetch();
                $checkStmt->close();

                // Append a number if the class name already exists
                $new_class_name = $class_name . ' ' . ($count + 1);

                // Insert the new class name into the database
                $query = "INSERT INTO classes (school_id, class_name) VALUES (?, ?)";
                if ($stmt = $schoolDataConn->prepare($query)) {
                    $stmt->bind_param("is", $school_id, $new_class_name);
                    if ($stmt->execute()) {
                        $successMessage = "Class '$new_class_name' added successfully! Do you want to add another one?";
                    } else {
                        $errorMessage = "Error: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $errorMessage = "Database prepare error: " . $schoolDataConn->error;
                }
            } else {
                $errorMessage = "Error preparing database statement.";
            }
        } else {
            $errorMessage = "Class name cannot be empty.";
        }
    } elseif ($action === 'Proceed') {
        // Logic to proceed without adding a class
        header("Location: proceed.php"); // Redirect to a different page as needed
        exit;
    }
}
$schoolDataConn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Class</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        .background-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background-size: cover;
            filter: brightness(0.5); /* Make the image darker */
            animation: float 10s ease-in-out infinite;
        }
        .card {
            background-color: #2d3748;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 10px;
            padding: 20px;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        .input-field {
            background-color: #1a202c;
            color: #a0aec0;
            border: 1px solid #4a5568;
        }
        .input-field:focus {
            border-color: #63b3ed;
            box-shadow: 0 0 0 1px #63b3ed;
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
            background-color: #4a5568;
            color: #fff;
            transition: background-color 0.2s ease-in-out, transform 0.2s ease-in-out;
        }
        .btn-secondary:hover {
            background-color: #2d3748;
            transform: translateY(-2px);
        }
        .alert-success {
            background-color: #38a169;
            border-color: #2f855a;
            color: #f0fff4;
        }
        .alert-error {
            background-color: #e53e3e;
            border-color: #c53030;
            color: #fff5f5;
        }
    </style>
</head>
<body class="bg-gray-900 flex items-center justify-center min-h-screen">
    <div class="background-animation"></div> <!-- Animated background -->
    <div class="max-w-md mx-auto bg-gray-800 p-8 rounded-lg shadow-md card">
        <h1 class="text-2xl font-semibold text-gray-200 mb-6 text-center"><i class="fas fa-chalkboard"></i> Add New Class</h1>
        <?php if (!empty($successMessage)): ?>
            <div class="alert-success p-4 rounded mb-4">
                <p class="font-bold">Success</p>
                <p><?= $successMessage; ?></p>
                <a href="proceed.php" class="inline-block mt-2 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"><i class="fas fa-arrow-right"></i> Proceed</a>
            </div>
        <?php elseif (!empty($errorMessage)): ?>
            <div class="alert-error p-4 rounded mb-4">
                <p class="font-bold">Error</p>
                <p><?= $errorMessage; ?></p>
            </div>
        <?php endif; ?>
        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="mb-4">
                <label for="class_name" class="block text-sm font-medium text-gray-300">Class Name:</label>
                <select id="class_name" name="class_name" required class="input-field mt-1 block w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="JS1 (Year 7)">JS1 (Year 7)</option>
                    <option value="JS2 (Year 8)">JS2 (Year 8)</option>
                    <option value="JS3 (Year 9)">JS3 (Year 9)</option>
                    <option value="SS1 (Year 10)">SS1 (Year 10)</option>
                    <option value="SS2 (Year 11)">SS2 (Year 11)</option>
                    <option value="SS3 (Year 12)">SS3 (Year 12)</option>
                </select>
            </div>
            <button type="submit" name="action" value="Add Class" class="btn-primary w-full py-2 px-4 rounded shadow">
                <i class="fas fa-plus-circle"></i> Add Class
            </button>
            <button type="submit" name="action" value="Proceed" class="btn-secondary mt-4 w-full py-2 px-4 rounded shadow">
                Proceed Without Adding
            </button>
        </form>
    </div>
</body>
</html>
