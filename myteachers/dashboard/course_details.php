<?php
session_start();
require_once '../../connections/db_school_data.php'; // Load school-specific data operations

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_teacher.php");
    exit;
}

if (!isset($_SESSION["teacher_id"], $_SESSION["school_id"])) {
    exit('Session information not available.');
}

if (!isset($_GET['module']) && !isset($_POST['module_code'])) {
    $_SESSION['message'] = [
        'type' => 'error',
        'content' => 'No module specified. Please select a module to view details.'
    ];
}

$module_code = $_GET['module'] ?? $_POST['module_code'];

$class_details = [];
$students = [];

// Fetch class name, subject name, and enrolled students
$sql = "SELECT cl.class_name, ss.subject_name, si.fullname, si.student_number
        FROM schoolhu_school_data.classes cl
        JOIN schoolhu_school_data.class_subject cs ON cl.class_id = cs.class_id
        JOIN schoolhu_school_data.modules_taught mt ON cs.module_id = mt.module_id
        JOIN schoolhu_school_data.school_subjects ss ON cs.subject_id = ss.subject_id
        LEFT JOIN schoolhu_school_data.student_modules sm ON sm.module_id = mt.module_id
        LEFT JOIN schoolhu_userinfo.student_info si ON sm.student_id = si.id AND sm.school_id = si.school_id
        WHERE mt.module_code = ? AND mt.school_id = ? AND cl.school_id = mt.school_id";

if ($stmt = $schoolDataConn->prepare($sql)) {
    $stmt->bind_param("si", $module_code, $_SESSION['school_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $class_details = ['class_name' => $row['class_name'], 'subject_name' => $row['subject_name']];
        if (!empty($row['fullname'])) {
            $students[] = ['name' => $row['fullname'], 'student_number' => $row['student_number']];
        }
    }
    $stmt->close();
} else {
    $_SESSION['message'] = [
        'type' => 'error',
        'content' => 'SQL Error: ' . $schoolDataConn->error
    ];
    header("location: some_redirect_page.php"); // Change to an appropriate page
    exit;
}

// Fetch teacher_code from userinfo database based on logged-in teacher_id
$teacher_code_sql = "SELECT teacher_code FROM schoolhu_userinfo.teacher_users WHERE teacher_id = ? AND school_id = ?";
$actual_teacher_code = null; // Variable to store the actual teacher code from the database

if ($code_stmt = $schoolDataConn->prepare($teacher_code_sql)) {
    $code_stmt->bind_param("ii", $_SESSION['teacher_id'], $_SESSION['school_id']);
    $code_stmt->execute();
    $code_result = $code_stmt->get_result();
    if ($code_row = $code_result->fetch_assoc()) {
        $actual_teacher_code = $code_row['teacher_code'];
    }
    $code_stmt->close();
} else {
    $_SESSION['message'] = [
        'type' => 'error',
        'content' => 'SQL Error: ' . $schoolDataConn->error
    ];
    header("location: some_redirect_page.php"); // Change to an appropriate page
    exit;
}

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_number = $_POST['student_number'];
    $submitted_teacher_code = $_POST['teacher_code'];

    // Validate the submitted teacher_code against the fetched teacher_code
    if ($submitted_teacher_code === $actual_teacher_code) {
        $sql = "SELECT id, fullname, student_number FROM schoolhu_userinfo.student_info WHERE student_number = ? AND school_id = ?";
        if ($stmt = $schoolDataConn->prepare($sql)) {
            $stmt->bind_param("si", $student_number, $_SESSION['school_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                // Assuming we're directly inserting upon validation here
                $insert_sql = "INSERT INTO schoolhu_school_data.student_modules (student_id, module_id, school_id, enrollment_date) VALUES (?, (SELECT module_id FROM schoolhu_school_data.modules_taught WHERE module_code = ?), ?, NOW())";
                if ($insert_stmt = $schoolDataConn->prepare($insert_sql)) {
                    $insert_stmt->bind_param("isi", $row['id'], $module_code, $_SESSION['school_id']);
                    $insert_stmt->execute();
                    $insert_stmt->close();
                    $_SESSION['message'] = [
                        'type' => 'success',
                        'content' => 'Enrollment Successful for ' . htmlspecialchars($row['fullname'])
                    ];
                }
            } else {
                $_SESSION['message'] = [
                    'type' => 'error',
                    'content' => 'No student found with that number in this school.'
                ];
            }
            $stmt->close();
        } else {
            $_SESSION['message'] = [
                'type' => 'error',
                'content' => 'SQL Error: ' . $schoolDataConn->error
            ];
        }
    } else {
        $_SESSION['message'] = [
            'type' => 'error',
            'content' => 'Invalid teacher code.'
        ];
    }
}

// Continue with GET request handling
$sql = "SELECT cl.class_name, ss.subject_name, si.fullname, si.student_number FROM schoolhu_school_data.classes cl
        INNER JOIN schoolhu_school_data.class_subject cs ON cl.class_id = cs.class_id
        INNER JOIN schoolhu_school_data.modules_taught mt ON cs.module_id = mt.module_id
        INNER JOIN schoolhu_school_data.school_subjects ss ON cs.subject_id = ss.subject_id
        LEFT JOIN schoolhu_school_data.student_modules sm ON sm.module_id = mt.module_id
        LEFT JOIN schoolhu_userinfo.student_info si ON sm.student_id = si.id AND sm.school_id = si.school_id
        WHERE mt.module_code = ? AND mt.school_id = ? AND cl.school_id = mt.school_id";

$class_details = [];
$students = [];

if ($stmt = $schoolDataConn->prepare($sql)) {
    $stmt->bind_param("si", $module_code, $_SESSION['school_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if (empty($class_details)) {
            $class_details = ['class_name' => $row['class_name'], 'subject_name' => $row['subject_name']];
        }
        if (!empty($row['fullname'])) {
            $students[] = ['name' => $row['fullname'], 'student_number' => $row['student_number']];
        }
    }
    $stmt->close();
} else {
    $_SESSION['message'] = [
        'type' => 'error',
        'content' => 'SQL Error: ' . $schoolDataConn->error
    ];
}

$schoolDataConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Enrollment Table</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7fafc;
        }
        .dataTables_wrapper {
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            background: white;
        }
        #studentsTable_wrapper .dataTables_filter input {
            padding: 0.5rem;
            border-radius: 0.375rem;
            border: 1px solid #d2d6dc;
        }
        #studentsTable_wrapper .dataTables_length select {
            padding: 0.5rem;
            border-radius: 0.375rem;
            border: 1px solid #d2d6dc;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            font-size: 0.875rem; /* 14px */
            line-height: 1.25rem; /* 20px */
            border-radius: 0.375rem; /* 6px */
            transition: all 0.2s;
        }
        .btn-blue {
            background-color: #3182ce;
            color: white;
        }
        .btn-blue:hover {
            background-color: #2b6cb0;
        }
        .btn-red {
            background-color: #e53e3e;
            color: white;
        }
        .btn-red:hover {
            background-color: #c53030;
        }
        .icon {
            margin-right: 0.5rem;
        }
        .container {
            max-width: 1280px;
            margin: 0 auto;
        }
        .module-header {
            background-color: #ffffff;
            padding: 1rem 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }
        .btn {
            background-color: #3182ce;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #2b6cb0;
        }
        .form-input {
            width: 100%;
            padding: 8px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            margin-top: 0.5rem;
            box-sizing: border-box;
        }
    </style>
    <script>
        $(document).ready(function() {
            $('#studentsTable').DataTable({
                "paging": true,
                "info": false,
                "pageLength": 5
            });
        });
    </script>
</head>
<body>
    <div class="container px-4 py-6">
        <div class="module-header">
            <h1 class="text-3xl font-bold">Enrollment for Module: <?= htmlspecialchars($module_code); ?></h1>
            <p><strong>Class:</strong> <?= htmlspecialchars($class_details['class_name']); ?></p>
            <p><strong>Subject:</strong> <?= htmlspecialchars($class_details['subject_name']); ?></p>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert <?= $_SESSION['message']['type'] === 'error' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?> p-4 mb-4 rounded">
                <?= htmlspecialchars($_SESSION['message']['content']); unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <div>
            <button onclick="document.getElementById('enrollmentForm').classList.toggle('hidden');" class="btn">
                <i class="fas fa-plus mr-2"></i>Add Student
            </button>
            <div id="enrollmentForm" class="hidden mt-4 p-4 bg-white rounded shadow">
                <form action="#" method="post">
                    <label for="student_number" class="block font-medium text-gray-700">Student Number:</label>
                    <input type="text" id="student_number" name="student_number" required class="form-input">
                    
                    <label for="teacher_code" class="block mt-4 font-medium text-gray-700">Teacher Code (for verification):</label>
                    <input type="text" id="teacher_code" name="teacher_code" required class="form-input">
                    
                    <button type="submit" class="btn mt-4"><i class="fas fa-save mr-2"></i>Enroll Student</button>
                </form>
            </div>
        </div>
        <div class="mt-6 bg-white p-5 rounded-lg shadow">
            <h2 class="text-2xl font-bold mb-4">Enrolled Students</h2>
            <table id="studentsTable" class="w-full text-left">
                <thead>
                    <tr>
                        <th class="border-b-2 p-2">Name</th>
                        <th class="border-b-2 p-2">Student Number</th>
                        <th class="border-b-2 p-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td class="p-2"><?= htmlspecialchars($student['name']); ?></td>
                        <td class="p-2"><?= htmlspecialchars($student['student_number']); ?></td>
                        <td class="p-2">
                        <a href="view_student.php?student_number=<?= $student['student_number']; ?>" class="btn">
                            <i class="fas fa-eye icon"></i>View
                        </a>
                        <a href="delete_student.php?student_code=<?= htmlspecialchars($student['student_number']); ?>&module_code=<?= htmlspecialchars($module_code); ?>&school_id=<?= htmlspecialchars($_SESSION['school_id']); ?>" onclick="return confirm('Are you sure you want to remove this student?');" class="btn">
                            <i class="fas fa-times icon"></i> Remove
                        </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
