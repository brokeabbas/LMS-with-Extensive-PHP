<?php
session_start();

require_once '../../connections/db_school_data.php'; // Adjust the path as needed

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

$school_id = $_SESSION['school_id'] ?? null; // Retrieve the school ID from session

// Fetch classes and subjects from the database
$classesQuery = "SELECT class_id, class_name FROM classes WHERE school_id = ?";
$subjectsQuery = "SELECT subject_id, subject_name FROM school_subjects WHERE school_id = ?";

$classes = [];
$subjects = [];

if ($stmt = $schoolDataConn->prepare($classesQuery)) {
    $stmt->bind_param("i", $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }
    $stmt->close();
}

if ($stmt = $schoolDataConn->prepare($subjectsQuery)) {
    $stmt->bind_param("i", $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
    $stmt->close();
}

// Process the form when it is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_module'])) {
    $schoolDataConn->begin_transaction();
    try {
        $insertQuery = "INSERT INTO class_subject (class_id, subject_id, school_id, module_code) VALUES (?, ?, ?, ?)";
        $stmt = $schoolDataConn->prepare($insertQuery);

        foreach ($_POST['modules'] as $module) {
            $parts = explode('-', $module); // Expected format: class_id-subject_id
            $class_id = (int)$parts[0];
            $subject_id = (int)$parts[1];

            // Generate module code
            $subject_name = array_filter($subjects, function($sub) use ($subject_id) {
                return $sub['subject_id'] == $subject_id;
            });
            $subject_name = array_values($subject_name)[0]['subject_name'] ?? '';
            $module_code = substr($subject_name, 0, 3) . sprintf('%06d', rand(1000, 999999)); // First three letters + 6 digits

            $stmt->bind_param("iiis", $class_id, $subject_id, $school_id, $module_code);
            $stmt->execute();
        }

        $stmt->close();
        $schoolDataConn->commit();
        header("Location: successful.php");
        echo "<p>Modules have been successfully added.</p>";
    } catch (Exception $e) {
        $schoolDataConn->rollback();
        echo "<p>Error adding modules: " . $e->getMessage() . "</p>";
    }
    $schoolDataConn->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Modules to Classes</title>
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
        .dropdown-content {
            display: none;
            background-color: #2d3748;
            padding: 12px;
            border-radius: 10px;
        }
        .dropdown:hover .dropdown-content {
            display: block;
        }
    </style>
    <script>
        function toggleAll(source) {
            document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = source.checked;
            });
        }

        function toggleClass(classId, source) {
            document.querySelectorAll(`.class-${classId}`).forEach(checkbox => {
                checkbox.checked = source.checked;
            });
        }

        function toggleDropdown(classId) {
            const dropdown = document.getElementById(`dropdown-${classId}`);
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        }
    </script>
</head>
<body class="bg-gray-900">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto bg-gray-800 p-8 rounded-lg shadow-lg card">
            <h1 class="text-3xl font-semibold text-gray-200 mb-6 text-center">Assign Modules to Classes</h1>
            <?php if (!empty($successMessage)): ?>
                <div class="alert-success p-4 rounded mb-4">
                    <?= $successMessage; ?>
                    <a href="assign_teachers.php" class="btn-primary mt-4 inline-block">Assign Teachers to Modules</a>
                    <a href="dashboard.php" class="btn-secondary mt-4 inline-block">Back to Dashboard</a>
                </div>
            <?php else: ?>
            <form method="POST" action="">
                <div class="mb-6 flex justify-center">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" onchange="toggleAll(this)" class="form-checkbox rounded h-6 w-6 text-blue-500 transition duration-150 ease-in-out"><span class="ml-2 text-lg text-gray-300">Select All Subjects for All Classes</span>
                    </label>
                </div>
                <?php foreach ($classes as $class): ?>
                <div class="mb-6 border-b border-gray-700 pb-4">
                    <h3 class="text-lg font-semibold mb-2 text-gray-300"><?= htmlspecialchars($class['class_name']); ?></h3>
                    <label class="inline-flex items-center cursor-pointer mb-2">
                        <input type="checkbox" onchange="toggleClass(<?= $class['class_id']; ?>, this)" class="form-checkbox rounded h-6 w-6 text-blue-500 transition duration-150 ease-in-out"><span class="ml-2 text-md text-gray-400">Select All for <?= htmlspecialchars($class['class_name']); ?></span>
                    </label>
                    <div class="relative">
                        <button type="button" onclick="toggleDropdown(<?= $class['class_id']; ?>)" class="btn-secondary mt-2 px-4 py-2 rounded shadow">Toggle Subjects</button>
                        <div id="dropdown-<?= $class['class_id']; ?>" class="dropdown-content mt-2">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <?php foreach ($subjects as $subject): ?>
                                <label class="inline-flex items-center cursor-pointer mb-2">
                                    <input type="checkbox" name="modules[]" value="<?= $class['class_id'] . '-' . $subject['subject_id']; ?>" class="form-checkbox rounded h-6 w-6 text-blue-500 transition duration-150 ease-in-out class-<?= $class['class_id']; ?>">
                                    <span class="ml-2 text-md text-gray-300"><?= htmlspecialchars($subject['subject_name']); ?></span>
                                    <div class="dropdown-content mt-2">
                                        <p class="text-sm text-gray-300">Module Details</p>
                                        <p class="text-xs text-gray-400">Description of <?= htmlspecialchars($subject['subject_name']); ?></p>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <div class="flex justify-center">
                    <button type="submit" name="submit_module" class="btn-primary mt-4 px-6 py-3 rounded shadow">Assign Modules</button>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
