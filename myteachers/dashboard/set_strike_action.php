<?php
session_start();
require_once '../../connections/db_school_data.php';  // Ensure this path is correct

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_teacher.php");
    exit;
}

if (!isset($_SESSION["teacher_id"], $_SESSION["school_id"])) {
    $_SESSION['message'] = ['type' => 'error', 'content' => 'No Teacher ID or School ID provided, or you are not logged in with a valid session.'];
}

$teacher_id = $_SESSION['teacher_id'];
$school_id = $_SESSION['school_id'];
$module_code = $_GET['module_code'] ?? '';
$student_number = $_GET['student_number'] ?? '';

if (!$module_code || !$student_number) {
    $_SESSION['message'] = ['type' => 'error', 'content' => 'Required information is missing.'];
}

// Fetch module_id and student_id
$module_id = null;
$student_id = null;

// Prepare SQL to fetch module_id and student_id
$sql = "SELECT mt.module_id, si.id AS student_id FROM modules_taught mt
        JOIN student_modules sm ON mt.module_id = sm.module_id
        JOIN schoolhu_userinfo.student_info si ON sm.student_id = si.id
        WHERE mt.module_code = ? AND si.student_number = ? AND mt.school_id = ?";
if ($stmt = $schoolDataConn->prepare($sql)) {
    $stmt->bind_param("ssi", $module_code, $student_number, $school_id);
    $stmt->execute();
    $stmt->bind_result($module_id, $student_id);
    if (!$stmt->fetch()) {
        $_SESSION['message'] = ['type' => 'error', 'content' => 'Failed to retrieve module or student information.'];
    }
    $stmt->close();
} else {
    $_SESSION['message'] = ['type' => 'error', 'content' => 'SQL Error: ' . $schoolDataConn->error];
}

// Handle POST request when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $strike_title = $_POST['strike_title'] ?? '';
    $strike_number = $_POST['strike_number'] ?? '';
    $strike_description = $_POST['strike_description'] ?? '';
    $strike_consequence = $_POST['strike_consequence'] ?? '';

    // Insert the disciplinary record
    $sql = "INSERT INTO disciplinary_records (student_id, teacher_id, module_id, school_id, strike_title, strike_number, strike_description, strike_consequence)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = $schoolDataConn->prepare($sql)) {
         $stmt->bind_param("iiiissss", $student_id, $teacher_id, $module_id, $school_id, $strike_title, $strike_number, $strike_description, $strike_consequence);
         if ($stmt->execute()) {
             $_SESSION['message'] = ['type' => 'success', 'content' => 'Disciplinary action recorded successfully.'];
         } else {
             $_SESSION['message'] = ['type' => 'error', 'content' => 'Error recording disciplinary action: ' . $stmt->error];
         }
         $stmt->close();
     } else {
        $_SESSION['message'] = ['type' => 'error', 'content' => 'SQL Error: ' . $schoolDataConn->error];
    }
    $schoolDataConn->close();
}

// Retrieve any session messages and then clear them
$session_message = null;
if (isset($_SESSION['message'])) {
    $session_message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Disciplinary Action</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        /* Custom styles for form elements */
        .input-field, .textarea-field {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            transition: border-color 0.3s ease;
        }
        .input-field:focus, .textarea-field:focus {
            border-color: #3182ce;
            outline: none;
        }
        .btn-submit {
            transition: background-color 0.3s ease;
        }
        .btn-submit:hover {
            background-color: #2c5282;
        }
    </style>
</head>
<body class="bg-gray-800 font-sans leading-normal tracking-normal">
    <div class="container mx-auto p-8">
        <div class="max-w-xl mx-auto bg-white rounded-lg shadow overflow-hidden">
            <div class="p-5 border-b">
                <h1 class="text-2xl font-bold text-gray-900">Record Disciplinary Action</h1>
                <p>For Student: <?= htmlspecialchars($student_number); ?></p>
            </div>

            <!-- Display session messages -->
            <?php if ($session_message): ?>
                <div class="<?= $session_message['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?> p-4 mb-4 rounded">
                    <?= htmlspecialchars($session_message['content']) ?>
                </div>
            <?php endif; ?>

            <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) . '?student_number=' . urlencode($student_number) . '&module_code=' . urlencode($module_code); ?>" method="post" class="p-5">
                <div class="mb-6">
                    <label for="strike_title" class="block text-gray-700 font-bold mb-2">
                        <i class="fas fa-exclamation-circle mr-2"></i>Strike Title:
                    </label>
                    <input type="text" id="strike_title" name="strike_title" required class="input-field w-full p-3 rounded text-gray-700">
                </div>
                <div class="mb-6">
                    <label for="strike_number" class="block text-gray-700 font-bold mb-2">
                        <i class="fas fa-hashtag mr-2"></i>Strike Number:
                    </label>
                    <input type="number" id="strike_number" name="strike_number" required class="input-field w-full p-3 rounded text-gray-700">
                </div>
                <div class="mb-6">
                    <label for="strike_description" class="block text-gray-700 font-bold mb-2">
                        <i class="fas fa-align-left mr-2"></i>Strike Description:
                    </label>
                    <textarea id="strike_description" name="strike_description" rows="4" required class="textarea-field w-full p-3 rounded text-gray-700"></textarea>
                </div>
                <div class="mb-6">
                    <label for="strike_consequence" class="block text-gray-700 font-bold mb-2">
                        <i class="fas fa-gavel mr-2"></i>Consequences:
                    </label>
                    <textarea id="strike_consequence" name="strike_consequence" rows="4" required class="textarea-field w-full p-3 rounded text-gray-700"></textarea>
                </div>
                <button type="submit" class="btn-submit bg-red-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none w-full">
                    <i class="fas fa-save mr-2"></i>Record Action
                </button>
            </form>
        </div>
    </div>
</body>
</html>
