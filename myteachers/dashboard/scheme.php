<?php
session_start();
require_once '../../connections/db_school_data.php'; // Ensure this path is correct

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_teacher.php");
    exit;
}

// Retrieve module code from the session or request
$module_code = $_GET['module'] ?? '';
if (!$module_code) {
    die("Module code not specified.");
}

// Fetch school_id and teacher_id from session
$teacher_id = $_SESSION['teacher_id'];
$school_id = $_SESSION['school_id'];
$module_id = null;

// Fetch module_id using module_code
if ($module_code) {
    if ($stmt = $schoolDataConn->prepare("SELECT module_id FROM modules_taught WHERE module_code = ?")) {
        $stmt->bind_param("s", $module_code);
        $stmt->execute();
        $stmt->bind_result($module_id);
        $stmt->fetch();
        $stmt->close();
    }
}

// Function to handle file uploads
function handleFileUpload($files, $target_dir = "../../scheme_files/") {
    $paths = [];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    $maxFileSize = 5000000; // 5 MB

    if (!empty($files['name'][0])) { // Check if at least one file was uploaded
        foreach ($files['name'] as $key => $filename) {
            if ($filename == '') continue; // Skip file slots without files
            $unique_name = uniqid() . '-' . basename($filename);
            $target_file = $target_dir . $unique_name;
            $fileType = mime_content_type($files["tmp_name"][$key]);
            $fileSize = $files["size"][$key];

            if ($fileSize > $maxFileSize) {
                $_SESSION['message'] = ['type' => 'error', 'content' => "Sorry, your file is too large."];
                continue;
            }
            if (!in_array($fileType, $allowedTypes)) {
                $_SESSION['message'] = ['type' => 'error', 'content' => "Sorry, only JPG, JPEG, PNG, GIF, & PDF files are allowed."];
                continue;
            }
            if (!move_uploaded_file($files["tmp_name"][$key], $target_file)) {
                $_SESSION['message'] = ['type' => 'error', 'content' => "Sorry, there was an error uploading your file."];
                continue;
            }
            $_SESSION['message'] = ['type' => 'success', 'content' => "The file ". htmlspecialchars(basename($filename)). " has been uploaded."];
            $paths[] = $target_file;
        }
    }
    return $paths;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $week = $_POST['week'];
    $topic = $_POST['topic'];
    $notes = $_POST['notes'];
    $assignments = $_POST['assignments'];
    $materials = $_POST['materials'];

    // Handle file uploads
    $note_files_paths = isset($_FILES['notes_files']) ? handleFileUpload($_FILES['notes_files']) : [];
    $assignment_files_paths = isset($_FILES['assignments_files']) ? handleFileUpload($_FILES['assignments_files']) : [];
    $material_files_paths = isset($_FILES['materials_files']) ? handleFileUpload($_FILES['materials_files']) : [];

    $note_files_path_str = implode(';', $note_files_paths);
    $assignment_files_path_str = implode(';', $assignment_files_paths);
    $material_files_path_str = implode(';', $material_files_paths);

    $sql = "INSERT INTO school_data.schemes (school_id, teacher_id, module_id, week, topic, notes, assignments, materials, notes_files, assignments_files, materials_files) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = $schoolDataConn->prepare($sql)) {
        $stmt->bind_param("iiissssssss", $school_id, $teacher_id, $module_id, $week, $topic, $notes, $assignments, $materials, $note_files_path_str, $assignment_files_path_str, $material_files_path_str);
        if ($stmt->execute()) {
            $_SESSION['message'] = ['type' => 'success', 'content' => "Scheme added successfully!"];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'content' => "Error adding scheme: " . $stmt->error];
        }
        $stmt->close();
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
    <title>Scheme Editor - <?= htmlspecialchars($module_code); ?></title>
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
        input:focus, textarea:focus, .file-input:focus {
            outline: 2px solid #6366f1;
            color: black;
            outline-offset: 2px;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 250px;
            background-color: #2b6cb0;
            color: white;
            padding-top: 20px;
        }
        .sidebar a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 18px;
            color: white;
            display: block;
        }
        .sidebar a:hover {
            background-color: #4f46e5;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
    </style>
</head>
<body class="bg-gray-800 font-sans leading-normal tracking-normal">
    <div class="sidebar">
        <div class="p-5 text-xl font-medium border-b border-gray-700">
            Teaching Dashboard
        </div>
        <a href="../myteach.php" class="flex items-center space-x-2">
            <i class="fas fa-home"></i><span>Dashboard</span>
        </a>
        <a href="view_schemes.php" class="flex items-center space-x-2">
            <i class="fas fa-tasks"></i><span>View Schemes</span>
        </a>
        <a href="/logout" class="flex items-center space-x-2">
            <i class="fas fa-sign-out-alt"></i><span>Logout</span>
        </a>
    </div>
    <div class="main-content">
        <div class="header">
            <h2 class="text-lg font-bold">Scheme Editor Portal</h2>
            <p>Create and manage schemes for your module efficiently.</p>
        </div>
        <div class="form-container">
            <h1 class="text-3xl font-bold text-center mb-6">Scheme Editor for <?= htmlspecialchars($module_code); ?></h1>

            <!-- Display session messages -->
            <?php if ($session_message): ?>
                <div class="<?= $session_message['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?> p-4 mb-4 rounded">
                    <?= htmlspecialchars($session_message['content']) ?>
                </div>
            <?php endif; ?>

            <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) . '?module=' . urlencode($module_code); ?>" method="post" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label for="week" class="block text-sm font-medium">Week Number:</label>
                    <input type="number" id="week" name="week" min="1" required class="mt-1 p-2 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="topic" class="block text-sm font-medium">Topic:</label>
                    <input type="text" id="topic" name="topic" required class="mt-1 p-2 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="notes" class="block text-sm font-medium">Class Notes:</label>
                    <textarea id="notes" name="notes" rows="3" class="mt-1 p-2 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                    <input type="file" id="notes_files" name="notes_files[]" multiple class="mt-2 block w-full file:mr-4 file:py-2 file:px-4 file:rounded file:border file:border-gray-300 file:text-sm file:font-medium file:bg-white file:text-gray-700 hover:file:bg-gray-100">
                </div>
                <div>
                    <label for="assignments" class="block text-sm font-medium">Assignments:</label>
                    <textarea id="assignments" name="assignments" rows="3" class="mt-1 p-2 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                    <input type="file" id="assignments_files" name="assignments_files[]" multiple class="mt-2 block w-full file:mr-4 file:py-2 file:px-4 file:rounded file:border file:border-gray-300 file:text-sm file:font-medium file:bg-white file:text-gray-700 hover:file:bg-gray-100">
                </div>
                <div>
                    <label for="materials" class="block text-sm font-medium">Class Materials:</label>
                    <textarea id="materials" name="materials" rows="3" class="mt-1 p-2 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                    <input type="file" id="materials_files" name="materials_files[]" multiple class="mt-2 block w-full file:mr-4 file:py-2 file:px-4 file:rounded file:border file:border-gray-300 file:text-sm file:font-medium file:bg-white file:text-gray-700 hover:file:bg-gray-100">
                </div>
                <button type="submit" class="px-4 py-2 font-bold text-white bg-blue-600 rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-opacity-50 w-full">Submit Scheme</button>
            </form>
        </div>
    </div>
</body>
</html>
