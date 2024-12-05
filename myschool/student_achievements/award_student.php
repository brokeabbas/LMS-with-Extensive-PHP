<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

require_once '../../connections/db_school_data.php'; // Database connection file

$student_id = $_GET['id'] ?? null; // Get student ID from URL
$school_id = $_SESSION['school_id'] ?? null; // Get school ID from session
$award_added = false;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['award'])) {
    // Prepare an insert statement
    $sql = "INSERT INTO awards (student_id, school_id, award_type, award_description) VALUES (?, ?, ?, ?)";
    
    if ($stmt = $schoolDataConn->prepare($sql)) {
        // Bind variables to the prepared statement as parameters
        $stmt->bind_param("iiss", $param_student_id, $param_school_id, $param_award_type, $param_description);

        // Set parameters
        $param_student_id = $student_id;
        $param_school_id = $school_id;
        $param_award_type = $_POST['award_type'];
        $param_description = $_POST['description'];

        // Attempt to execute the prepared statement
        if ($stmt->execute()) {
            $award_added = true; // Set a flag to show success message
        }
        $stmt->close();
    }
}

$schoolDataConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Award Student</title>
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
            background-color: #3b82f6;
            color: #fff;
            transition: background-color 0.2s ease-in-out, transform 0.2s ease-in-out;
        }
        .btn-primary:hover {
            background-color: #2563eb;
            transform: translateY(-2px);
        }
        .form-label {
            color: #e5e7eb;
        }
        .form-input, .textarea-field {
            color: #000;
            padding: 1rem;
            font-size: 1.125rem; /* 18px */
            border: 2px solid #3b82f6;
        }
        .container {
            max-width: 40rem; /* 640px */
        }
    </style>
</head>
<body class="bg-gray-900 flex items-center justify-center h-screen">
    <div class="container mx-auto py-10 px-6 lg:px-8">
        <div class="max-w-xl mx-auto bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="p-6 border-b border-blue-500">
                <h1 class="text-3xl font-bold text-center text-gray-100">Award Student</h1>
            </div>
            <?php if ($award_added): ?>
            <p class="text-green-500 p-6 text-center">Award has been added successfully.</p>
            <?php endif; ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=$student_id"); ?>" method="post" class="p-6">
                <div class="mb-6">
                    <label for="award_type" class="block text-lg font-medium form-label">Award Type:</label>
                    <input type="text" id="award_type" name="award_type" required class="form-input mt-2 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm border-gray-300 rounded-md">
                </div>
                <div class="mb-6">
                    <label for="description" class="block text-lg font-medium form-label">Description:</label>
                    <textarea id="description" name="description" rows="4" required class="textarea-field mt-2 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm border-gray-300 rounded-md"></textarea>
                </div>
                <button type="submit" name="award" class="w-full inline-flex items-center justify-center px-4 py-3 border border-transparent text-lg font-medium rounded-md shadow-sm text-white btn-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Submit
                </button>
            </form>
        </div>
    </div>
</body>
</html>
