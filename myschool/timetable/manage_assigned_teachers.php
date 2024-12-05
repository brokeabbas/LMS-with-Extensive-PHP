<?php
session_start();
require_once '../../connections/db_school_data.php';

// Redirect unauthenticated users
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

$school_id = $_SESSION['school_id'] ?? null;

// Fetch all teachers for the dropdown
$teachers = [];
$teacherQuery = "SELECT id, name FROM schoolhu_userinfo.teacher_info WHERE school_id = ? ORDER BY name ASC";
if ($stmt = $schoolDataConn->prepare($teacherQuery)) {
    $stmt->bind_param("i", $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($teacher = $result->fetch_assoc()) {
        $teachers[$teacher['id']] = $teacher['name'];
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $module_id = $_POST['module_id'];
    if (isset($_POST['unassign_teacher'])) {
        $updateQuery = "UPDATE modules_taught SET assigned = 0, teacher_id = NULL WHERE module_id = ?";
    } elseif (isset($_POST['replace_teacher'])) {
        $new_teacher_id = $_POST['new_teacher_id'];
        $updateQuery = "UPDATE modules_taught SET teacher_id = ? WHERE module_id = ?";
    }

    if ($stmt = $schoolDataConn->prepare($updateQuery)) {
        if (isset($_POST['replace_teacher'])) {
            $stmt->bind_param("ii", $new_teacher_id, $module_id);
        } else {
            $stmt->bind_param("i", $module_id);
        }
        if (!$stmt->execute()) {
            echo "Error updating record: " . $schoolDataConn->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $schoolDataConn->error;
    }
}

// Fetching classes and their modules with assigned teachers
$query = "SELECT c.class_name, ss.subject_name, cs.module_code, mt.module_id, ti.name AS teacher_name, ti.teacher_number, mt.assigned
          FROM classes c
          JOIN class_subject cs ON c.class_id = cs.class_id
          JOIN school_subjects ss ON cs.subject_id = ss.subject_id
          JOIN modules_taught mt ON cs.module_id = mt.module_id
          JOIN schoolhu_userinfo.teacher_info ti ON mt.teacher_id = ti.id
          WHERE c.school_id = ? AND mt.teacher_id IS NOT NULL AND mt.assigned = 1
          ORDER BY c.class_name, ss.subject_name";

$classes = [];
if ($stmt = $schoolDataConn->prepare($query)) {
    $stmt->bind_param("i", $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $classes[$row['class_name']][$row['subject_name']][] = $row;
    }
    $stmt->close();
} else {
    echo "SQL Error: " . $schoolDataConn->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assigned Teachers</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
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
        function confirmAction(message) {
            return confirm(message);
        }

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
        <h1 class="text-3xl font-bold text-center text-gray-200 mb-6">Manage Assigned Teachers</h1>
        <?php foreach ($classes as $class_name => $subjects): ?>
            <button type="button" class="collapsible"><?= htmlspecialchars($class_name); ?></button>
            <div class="content">
                <?php foreach ($subjects as $subject_name => $modules): ?>
                    <div class="card mb-3">
                        <div class="card-header">
                            <h2 class="text-xl font-semibold text-gray-200"><?= htmlspecialchars($subject_name); ?></h2>
                        </div>
                        <ul class="list-group list-group-flush bg-gray-800 p-4 rounded-lg">
                            <?php foreach ($modules as $module): ?>
                                <li class="list-group-item bg-gray-800 text-gray-300 d-flex justify-content-between align-items-center">
                                    Module: <?= htmlspecialchars($module['module_code']); ?>
                                    <span class="badge bg-primary rounded-pill">
                                        <?= htmlspecialchars($module['teacher_name']); ?> (Code: <?= htmlspecialchars($module['teacher_number']); ?>)
                                    </span>
                                    <div class="flex items-center">
                                        <form method="post" action="" class="d-inline">
                                            <input type="hidden" name="module_id" value="<?= $module['module_id']; ?>">
                                            <select name="new_teacher_id" class="form-select form-select-sm d-inline w-auto text-gray-900">
                                                <?php foreach ($teachers as $id => $name): ?>
                                                    <option value="<?= $id; ?>"><?= htmlspecialchars($name); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="replace_teacher" class="btn-primary inline-block mb-2 py-2 px-4 rounded focus:outline-none focus:shadow-outline ml-2" onclick="return confirmAction('Are you sure you want to replace this teacher?');">Replace</button>
                                        </form>
                                        <a href="view_teacher.php?teacher_number=<?= $module['teacher_number']; ?>" class="btn btn-info btn-sm ml-2">View</a>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
