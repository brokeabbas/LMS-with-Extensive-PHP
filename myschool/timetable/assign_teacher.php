<?php
session_start();
require_once '../../connections/db_school_data.php'; // Adjust the path as needed

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

$school_id = $_SESSION['school_id'] ?? null;
$message = '';

// Fetch classes and their modules
$sql = "SELECT c.class_id, c.class_name, ss.subject_id, ss.subject_name, cs.module_code, cs.module_id
        FROM classes c
        LEFT JOIN class_subject cs ON c.class_id = cs.class_id AND c.school_id = cs.school_id AND cs.assigned = 0
        LEFT JOIN school_subjects ss ON cs.subject_id = ss.subject_id AND ss.school_id = cs.school_id
        WHERE c.school_id = ?";
$classes = [];

if ($stmt = $schoolDataConn->prepare($sql)) {
    $stmt->bind_param("i", $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $classes[$row['class_id']]['class_name'] = $row['class_name'];
        $classes[$row['class_id']]['modules'][] = [
            'subject_id' => $row['subject_id'],
            'subject_name' => $row['subject_name'],
            'module_code' => $row['module_code'],
            'module_id' => $row['module_id']
        ];
    }
    $stmt->close();
} else {
    $message = '<div class="bg-red-500 text-white font-bold py-2 px-4 rounded">SQL Error: ' . htmlspecialchars($schoolDataConn->error) . '</div>';
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_assignment'])) {
    $teacher_number = $_POST['teacher_number'];
    $module_code = $_POST['module_code'];
    $module_id = $_POST['module_id'];

    // Validate teacher number
    $validateTeacherQuery = "SELECT id FROM schoolhu_userinfo.teacher_info WHERE teacher_number = ? AND school_id = ?";
    if ($stmt = $schoolDataConn->prepare($validateTeacherQuery)) {
        $stmt->bind_param("ii", $teacher_number, $school_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 1) {
            $teacher = $result->fetch_assoc();
            $teacher_id = $teacher['id'];

            // Insert or update module assignment and mark as assigned
            $insertAssignmentQuery = "INSERT INTO modules_taught (module_id, teacher_id, school_id, module_code, assigned) VALUES (?, ?, ?, ?, 1)
                                      ON DUPLICATE KEY UPDATE teacher_id = VALUES(teacher_id), school_id = VALUES(school_id), assigned = 1";
            if ($stmt = $schoolDataConn->prepare($insertAssignmentQuery)) {
                $stmt->bind_param("iiis", $module_id, $teacher_id, $school_id, $module_code);
                if ($stmt->execute()) {
                    // Additional SQL to update 'assigned' status in class_subject
                    $updateClassSubjectQuery = "UPDATE class_subject SET assigned = 1 WHERE module_code = ? AND school_id = ?";
                    if ($updateStmt = $schoolDataConn->prepare($updateClassSubjectQuery)) {
                        $updateStmt->bind_param("si", $module_code, $school_id);
                        $updateStmt->execute();
                        $updateStmt->close();
                    }
                    $message = '<div class="bg-green-500 text-white font-bold py-2 px-4 rounded">Teacher assigned successfully to the module.</div>';
                } else {
                    $message = '<div class="bg-red-500 text-white font-bold py-2 px-4 rounded">Error assigning teacher: ' . htmlspecialchars($stmt->error) . '</div>';
                }
            } else {
                $message = '<div class="bg-red-500 text-white font-bold py-2 px-4 rounded">SQL Error: ' . htmlspecialchars($schoolDataConn->error) . '</div>';
            }
        } else {
            $message = '<div class="bg-red-500 text-white font-bold py-2 px-4 rounded">Teacher number does not exist or does not match school ID.</div>';
        }
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
    <title>Manage School Modules and Teachers</title>
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
            margin-bottom: 1rem;
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
        .collapsible {
            background-color: #4a5568;
            color: #f8fafc;
            cursor: pointer;
            padding: 18px;
            width: 100%;
            border: none;
            text-align: left;
            outline: none;
            font-size: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            transition: background-color 0.2s ease-in-out;
        }
        .collapsible:hover {
            background-color: #3b82f6;
        }
        .active, .collapsible:focus {
            background-color: #3b82f6;
        }
        .content {
            padding: 0 18px;
            display: none;
            overflow: hidden;
            background-color: #1f2937;
            border-radius: 10px;
        }
        .input-group {
            display: flex;
            align-items: center;
        }
        .input-group input[type="number"] {
            -webkit-appearance: none;
            -moz-appearance: textfield;
            margin: 0;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const collapsibles = document.querySelectorAll('.collapsible');
            collapsibles.forEach(collapsible => {
                collapsible.addEventListener('click', function () {
                    this.classList.toggle('active');
                    const content = this.nextElementSibling;
                    if (content.style.display === 'block') {
                        content.style.display = 'none';
                    } else {
                        content.style.display = 'block';
                    }
                });
            });
        });
    </script>
</head>
<body class="bg-gray-900">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center text-gray-200 mb-6">Manage Classes and Modules</h1>
        <?php if ($message) echo $message; ?>
        <?php foreach ($classes as $class_id => $class_info): ?>
            <button type="button" class="collapsible"><?= htmlspecialchars($class_info['class_name']) ?></button>
            <div class="content">
                <?php if (isset($class_info['modules'])): ?>
                    <ul class="list-group list-group-flush bg-gray-800 p-4 rounded-lg">
                        <?php foreach ($class_info['modules'] as $module): ?>
                            <li class="list-group-item bg-gray-800 text-gray-300">
                                <?= htmlspecialchars($module['subject_name']) . " (Module Code: " . htmlspecialchars($module['module_code']) . ")" ?>
                                <form method="post" class="form-inline mt-2">
                                    <input type="hidden" name="module_code" value="<?= htmlspecialchars($module['module_code']) ?>">
                                    <input type="hidden" name="module_id" value="<?= htmlspecialchars($module['module_id']) ?>">
                                    <div class="input-group">
                                        <input type="number" name="teacher_number" class="form-input px-4 py-2 rounded-md text-gray-900" placeholder="Enter teacher number" required>
                                        <button type="submit" name="submit_assignment" class="btn-primary inline-block mb-2 py-2 px-4 rounded focus:outline-none focus:shadow-outline ml-2">Assign Teacher</button>
                                    </div>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-gray-300 p-4">No modules assigned to this class yet.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
