<?php
session_start();
require_once '../../connections/db_school_data.php'; // Adjust the path as needed

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

$school_id = $_SESSION['school_id'] ?? null;
$message = '';

// Fetch module details if requested
if (isset($_GET['fetch_module_details'])) {
    $module_code = $_GET['module_code'];
    $moduleDetailsQuery = "SELECT cs.module_code, c.class_name, ss.subject_name
                           FROM class_subject cs
                           JOIN classes c ON cs.class_id = c.class_id
                           JOIN school_subjects ss ON cs.subject_id = ss.subject_id
                           WHERE cs.module_code = ? AND cs.school_id = ?";
    $stmt = $schoolDataConn->prepare($moduleDetailsQuery);
    $stmt->bind_param("si", $module_code, $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();
    echo json_encode($data);
    exit;
}

// Handle form submission for student enrollment
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $module_code = $_POST['module_code'];
    $student_number = $_POST['student_number'];

    // Enroll student into the module
    $enrollmentQuery = "INSERT INTO schoolhu_school_data.student_modules (student_id, module_id, school_id) 
                        SELECT id, (SELECT module_id FROM class_subject WHERE module_code = ?), ? 
                        FROM schoolhu_userinfo.student_info 
                        WHERE student_number = ? AND school_id = ?";

    if ($enrollmentStmt = $schoolDataConn->prepare($enrollmentQuery)) {
        $enrollmentStmt->bind_param("siis", $module_code, $_SESSION['school_id'], $student_number, $_SESSION['school_id']);
        $enrollmentStmt->execute();
        if ($enrollmentStmt->affected_rows === 0) {
            $message = '<div class="bg-red-500 text-white font-bold py-2 px-4 rounded">Student enrollment failed or student does not exist.</div>';
        } else {
            $message = '<div class="bg-green-500 text-white font-bold py-2 px-4 rounded">Student successfully enrolled.</div>';
        }
        $enrollmentStmt->close();
    } else {
        $message = '<div class="bg-red-500 text-white font-bold py-2 px-4 rounded">Error: ' . htmlspecialchars($schoolDataConn->error) . '</div>';
    }
}

$schoolDataConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll Students</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(135deg, #1f2937, #3b82f6);
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
            color: #f8fafc;
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
            background-color: #3b82f6;
            color: #fff;
            transition: background-color 0.2s ease-in-out, transform 0.2s ease-in-out;
        }
        .btn-primary:hover {
            background-color: #2563eb;
            transform: translateY(-2px);
        }
        .form-label {
            color: #000;
        }
        .form-input {
            color: #000;
            padding: 1rem;
            font-size: 1.125rem; /* 18px */
        }
        .container {
            max-width: 40rem; /* 640px */
        }
    </style>
    <script>
        function fetchModuleDetails() {
            var moduleCode = document.getElementById('module_code').value;
            $.ajax({
                url: '?fetch_module_details=1&module_code=' + moduleCode,
                type: 'GET',
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data) {
                        document.getElementById('class_name').textContent = data.class_name;
                        document.getElementById('subject_name').textContent = data.subject_name;
                    } else {
                        document.getElementById('class_name').textContent = 'No data found';
                        document.getElementById('subject_name').textContent = 'No data found';
                    }
                }
            });
        }
    </script>
</head>
<body class="bg-gray-900 flex items-center justify-center h-screen">
    <div class="container mx-auto py-10 px-6 lg:px-8">
        <h1 class="text-4xl font-bold text-center text-gray-200 mb-10">Enroll Student into a Module</h1>
        <?= $message ?>
        <div class="bg-gray-800 p-8 rounded-lg shadow-lg">
            <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-6">
                    <label for="module_code" class="block text-lg font-medium form-label">Module Code:</label>
                    <input type="text" id="module_code" name="module_code" oninput="fetchModuleDetails()" required class="mt-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-lg border-gray-300 rounded-md form-input">
                    <p class="mt-2 text-lg text-gray-400">Class: <span id="class_name" class="font-semibold text-gray-300"></span></p>
                    <p class="mt-1 text-lg text-gray-400">Subject: <span id="subject_name" class="font-semibold text-gray-300"></span></p>
                </div>
                <div class="mb-6">
                    <label for="student_number" class="block text-lg font-medium form-label">Student Number:</label>
                    <input type="text" id="student_number" name="student_number" required class="mt-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-lg border-gray-300 rounded-md form-input">
                </div>
                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 border border-transparent text-lg font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Enroll Student
                </button>
            </form>
        </div>
    </div>
</body>
</html>
