<?php
session_start();

require_once '../../connections/db_school_data.php'; // Include the database connection file

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

$school_id = $_SESSION['school_id'] ?? null; // Assuming school_id is stored in session
$teacher_id = null; // This could be null if not logged in as a teacher
$module_id = null; // This could be null if not relevant or necessary

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_number = $_POST['student_number'] ?? '';
    $strike_title = $_POST['strike_title'] ?? '';
    $strike_number = $_POST['strike_number'] ?? '';
    $strike_description = $_POST['strike_description'] ?? '';
    $strike_consequence = $_POST['strike_consequence'] ?? '';

    $stmt = $schoolDataConn->prepare("SELECT id FROM schoolhu_userinfo.student_info WHERE student_number = ? AND school_id = ?");
    $stmt->bind_param("si", $student_number, $school_id);
    $stmt->execute();
    $stmt->bind_result($student_id);
    if ($stmt->fetch()) {
        $stmt->close();

        // Insert the disciplinary record, handling NULL for teacher_id and module_id
        $sql = "INSERT INTO disciplinary_records (student_id, teacher_id, school_id, module_id, strike_title, strike_number, strike_description, strike_consequence) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $schoolDataConn->prepare($sql);
        $insertStmt->bind_param("iiisssss", $student_id, $teacher_id, $school_id, $module_id, $strike_title, $strike_number, $strike_description, $strike_consequence);
        if ($insertStmt->execute()) {
            echo "<script>alert('Disciplinary action recorded successfully.');</script>";
        } else {
            echo "Error: " . $insertStmt->error;
        }
        $insertStmt->close();
    } else {
        echo "<script>alert('No student found with that number.');</script>";
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
    <title>Add Disciplinary Record</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
            background-color: #1e293b;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        .btn-primary {
            background-color: #ef4444; /* Red color */
            color: #fff;
            transition: background-color 0.2s ease-in-out, transform 0.2s ease-in-out;
        }
        .btn-primary:hover {
            background-color: #dc2626; /* Darker red */
            transform: translateY(-2px);
        }
        .form-label {
            color: #ef4444; /* Red color */
        }
        .form-input, .textarea-field {
            color: #000;
            padding: 1rem;
            font-size: 1.125rem; /* 18px */
            border: 2px solid #ef4444; /* Red border */
        }
        .container {
            max-width: 40rem; /* 640px */
        }
    </style>
</head>
<body class="bg-gray-900 flex items-center justify-center h-screen">
    <div class="container mx-auto py-10 px-6 lg:px-8">
        <div class="max-w-xl mx-auto bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b border-red-500">
                <h1 class="text-3xl font-bold text-center text-gray-900">Add Disciplinary Record</h1>
            </div>
            <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="p-6">
                <div class="mb-6">
                    <label for="student_number" class="block text-lg font-medium form-label">Student Number:</label>
                    <input type="text" id="student_number" name="student_number" required class="form-input mt-2 focus:ring-red-500 focus:border-red-500 block w-full shadow-sm border-gray-300 rounded-md">
                </div>
                <div class="mb-6">
                    <label for="strike_title" class="block text-lg font-medium form-label">Strike Title:</label>
                    <input type="text" id="strike_title" name="strike_title" required class="form-input mt-2 focus:ring-red-500 focus:border-red-500 block w-full shadow-sm border-gray-300 rounded-md">
                </div>
                <div class="mb-6">
                    <label for="strike_number" class="block text-lg font-medium form-label">Strike Number:</label>
                    <input type="number" id="strike_number" name="strike_number" required class="form-input mt-2 focus:ring-red-500 focus:border-red-500 block w-full shadow-sm border-gray-300 rounded-md">
                </div>
                <div class="mb-6">
                    <label for="strike_description" class="block text-lg font-medium form-label">Strike Description:</label>
                    <textarea id="strike_description" name="strike_description" rows="4" required class="textarea-field mt-2 focus:ring-red-500 focus:border-red-500 block w-full shadow-sm border-gray-300 rounded-md"></textarea>
                </div>
                <div class="mb-6">
                    <label for="strike_consequence" class="block text-lg font-medium form-label">Consequences:</label>
                    <textarea id="strike_consequence" name="strike_consequence" rows="4" required class="textarea-field mt-2 focus:ring-red-500 focus:border-red-500 block w-full shadow-sm border-gray-300 rounded-md"></textarea>
                </div>
                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 border border-transparent text-lg font-medium rounded-md shadow-sm text-white btn-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Submit Record
                </button>
            </form>
        </div>
    </div>
</body>
</html>
