<?php
session_start();
require_once '../../connections/db.php'; // Connection to the user info database
require_once '../../connections/db_school_data.php'; // Connection to the school data database
require '../../connections/vendor/autoload.php'; // Include PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Authentication check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["teacher_id"], $_SESSION["school_id"])) {
    header("location: ../login_teacher.php");
    exit;
}

// Initialization of variables
$module_id = $_POST['module_id'] ?? null;
$school_id = $_SESSION['school_id'];
$attendance_date = $_POST['attendance_date'] ?? date('Y-m-d'); // Default to today if not provided
$search_term = $_POST['search_term'] ?? '';

// Redirect if module ID is not provided
if ($module_id === null) {
    $_SESSION['error'] = "Module ID not provided. Please go back and try again.";
    header("location: ../attendance.php");
    exit;
}

// Function to fetch existing attendance
function getExistingAttendance($module_id, $school_id, $attendance_date, $schoolDataConn) {
    $sql = "SELECT student_id, attendance_status FROM attendance_records WHERE module_id = ? AND school_id = ? AND attendance_date = ?";
    if ($stmt = $schoolDataConn->prepare($sql)) {
        $stmt->bind_param("iis", $module_id, $school_id, $attendance_date);
        $stmt->execute();
        $result = $stmt->get_result();
        $attendance = [];
        while ($row = $result->fetch_assoc()) {
            $attendance[$row['student_id']] = $row['attendance_status'];
        }
        $stmt->close();
        return $attendance;
    } else {
        $_SESSION['error'] = "SQL Error: " . htmlspecialchars($schoolDataConn->error);
        return [];
    }
}

$existingAttendance = getExistingAttendance($module_id, $school_id, $attendance_date, $schoolDataConn);

// Check if there's any attendance already recorded for the date
$isAttendanceRecorded = !empty($existingAttendance);

// Function to fetch attendance dates
function fetchAttendanceDates($module_id, $school_id, $schoolDataConn) {
    $sql = "SELECT DISTINCT attendance_date FROM attendance_records WHERE module_id = ? AND school_id = ?";
    $dates = [];
    if ($stmt = $schoolDataConn->prepare($sql)) {
        $stmt->bind_param("ii", $module_id, $school_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $dates[] = $row['attendance_date'];
        }
        $stmt->close();
    }
    return $dates;
}

$attendanceDates = fetchAttendanceDates($module_id, $school_id, $schoolDataConn);

// Function to fetch student details including parent email
function getStudentDetails($student_id, $userInfoConn) {
    $fullname = $student_number = $parentEmail = ''; // Initialize the variables
    $sql = "SELECT fullname, student_number, parentEmail FROM student_info WHERE id = ?";
    if ($stmt = $userInfoConn->prepare($sql)) {
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $stmt->bind_result($fullname, $student_number, $parentEmail);
        $stmt->fetch();
        $stmt->close();
        return compact('fullname', 'student_number', 'parentEmail');
    } else {
        return null;
    }
}

// Function to fetch class and module details
function getClassAndModuleDetails($module_id, $schoolDataConn) {
    $module_code = $class_name = $subject_name = ''; // Initialize the variables
    $sql = "SELECT cs.module_code, cl.class_name, ss.subject_name
            FROM class_subject cs
            JOIN classes cl ON cs.class_id = cl.class_id
            JOIN school_subjects ss ON cs.subject_id = ss.subject_id
            WHERE cs.module_id = ?";
    if ($stmt = $schoolDataConn->prepare($sql)) {
        $stmt->bind_param("i", $module_id);
        $stmt->execute();
        $stmt->bind_result($module_code, $class_name, $subject_name);
        $stmt->fetch();
        $stmt->close();
        return compact('module_code', 'class_name', 'subject_name');
    } else {
        return null;
    }
}


// Function to send absence email to parent
function sendAbsenceEmail($parentEmail, $studentName, $studentNumber, $className, $subjectName, $moduleCode) {
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'mail.schoolhub.ng'; // Set the SMTP server to send through
        $mail->SMTPAuth   = true;
        $mail->Username   = 'admin@schoolhub.ng'; // SMTP username
        $mail->Password   = '4]?[-C$_l9BR'; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        //Recipients
        $mail->setFrom('admin@schoolhub.ng', 'School Admin');
        $mail->addAddress($parentEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Absence Notification';
        $mail->Body    = "Dear Parent,<br><br>Your child, $studentName ($studentNumber), has missed his $className ($subjectName) class today.<br><br>Regards,<br>School Admin";

        $mail->send();
    } catch (Exception $e) {
        // Handle email sending error
    }
}

// Insert or update attendance data into the database if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['attendance']) && !$isAttendanceRecorded) {
    $errors = [];
    foreach ($_POST['attendance'] as $student_id => $status) {
        $attendance_status = null;
        if (isset($status['present']) && $status['present'] == 1) {
            $attendance_status = 'present';
        } elseif (isset($status['absent']) && $status['absent'] == 1) {
            $attendance_status = 'absent';
            // Fetch student details and send notification
            $studentDetails = getStudentDetails($student_id, $userInfoConn);
            $classAndModuleDetails = getClassAndModuleDetails($module_id, $schoolDataConn);
            if ($studentDetails && $classAndModuleDetails) {
                sendAbsenceEmail(
                    $studentDetails['parentEmail'],
                    $studentDetails['fullname'],
                    $studentDetails['student_number'],
                    $classAndModuleDetails['class_name'],
                    $classAndModuleDetails['subject_name'],
                    $classAndModuleDetails['module_code']
                );
            }
        }

        if ($attendance_status !== null) {
            $sql = "INSERT INTO attendance_records (student_id, module_id, school_id, attendance_date, attendance_status)
                    VALUES (?, ?, ?, ?, ?)";
            if ($stmt = $schoolDataConn->prepare($sql)) {
                $stmt->bind_param("iiiss", $student_id, $module_id, $school_id, $attendance_date, $attendance_status);
                if (!$stmt->execute()) {
                    $errors[] = "SQL Error: " . htmlspecialchars($stmt->error);
                }
                $stmt->close();
            } else {
                $errors[] = "SQL Error: " . htmlspecialchars($schoolDataConn->error);
            }
        }
    }

    if (empty($errors)) {
        $_SESSION['success'] = "Attendance has been successfully recorded.";
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }

    $existingAttendance = getExistingAttendance($module_id, $school_id, $attendance_date, $schoolDataConn);
    $isAttendanceRecorded = true;
}

// Fetch module code and student list
$students = [];
$module_code = "";
$sql = "SELECT mt.module_code, si.id, si.fullname, si.student_number
        FROM schoolhu_school_data.modules_taught mt
        JOIN schoolhu_school_data.student_modules sm ON mt.module_id = sm.module_id
        JOIN schoolhu_userinfo.student_info si ON sm.student_id = si.id
        WHERE mt.module_id = ? AND si.school_id = ? AND 
        (si.fullname LIKE CONCAT('%', ?, '%') OR si.student_number LIKE CONCAT('%', ?, '%'))";

if ($stmt = $schoolDataConn->prepare($sql)) {
    $stmt->bind_param("iiss", $module_id, $school_id, $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $module_code = $row['module_code'];
        do {
            $students[] = $row;
        } while ($row = $result->fetch_assoc());
    }
    $stmt->close();
} else {
    $_SESSION['error'] = "SQL Error: " . $schoolDataConn->error;
}

$schoolDataConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance - <?= htmlspecialchars($module_code) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/js/all.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            font-family: 'Nunito', sans-serif; /* Consistent modern font */
        }
        .hover-rise:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .button-style {
            display: inline-block;
            background-color: #4f46e5; /* Indigo shade */
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.2s ease;
        }
        .button-style:hover {
            background-color: #4338ca; /* Darker indigo on hover */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        .custom-header {
            background: linear-gradient(to right, #667eea, #764ba2);
            color: white;
        }
        .notification {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .notification-success {
            background-color: #d4edda;
            color: #155724;
        }
        .notification-error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar for navigation -->
        <aside class="w-64 bg-gradient-to-b from-gray-800 to-gray-900 text-white p-5 shadow-md overflow-auto">
            <div class="px-6 py-8">
                <h1 class="text-xl font-semibold">Attendance Management</h1>
                <nav class="mt-10 space-y-4">
                    <a href="../myteach.php" class="flex items-center p-2 rounded hover:bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-tachometer-alt mr-3 icon"></i>Dashboard
                    </a>
                    <a href="attendance.php" class="flex items-center p-2 rounded bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-user-check mr-3 icon"></i>Attendance
                    </a>
                    <a href="logout.php" class="flex items-center p-2 rounded hover:bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-sign-out-alt mr-3 icon"></i>Logout
                    </a>
                </nav>
            </div>
        </aside>
        <!-- Main content area -->
        <div class="flex-1 flex flex-col">
            <header class="custom-header p-6 shadow-lg">
                <div class="max-w-7xl mx-auto flex justify-between items-center">
                    <h2 class="text-3xl font-bold">Attendance Manager for <?= htmlspecialchars($module_code) ?></h2>
                    <i class="fas fa-calendar-check text-lg"></i>
                </div>
            </header>
            <main class="p-6 bg-white text-gray-800 flex-1">
                <div class="container mx-auto px-4">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="notification notification-success">
                            <?= $_SESSION['success'] ?>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="notification notification-error">
                            <?= $_SESSION['error'] ?>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <form action="" method="post" class="mb-6">
                        <input type="hidden" name="module_id" value="<?= htmlspecialchars($module_id); ?>">
                        <input type="text" name="search_term" value="<?= htmlspecialchars($search_term); ?>" placeholder="Search name or number" class="form-input rounded-md shadow-sm block w-full border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 transition duration-150 ease-in-out mb-4">
                        <input type="date" name="attendance_date" value="<?= htmlspecialchars($attendance_date); ?>" class="form-input rounded-md shadow-sm block w-full border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 transition duration-150 ease-in-out mb-4" <?= $isAttendanceRecorded ?: ''; ?>>
                        <button type="submit" class="button-style mb-4" <?= $isAttendanceRecorded ? : ''; ?>>Search</button>
                    </form>
                    <form action="" method="post">
                        <input type="hidden" name="module_id" value="<?= htmlspecialchars($module_id); ?>">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left">Student Name</th>
                                    <th scope="col" class="px-6 py-3 text-left">Student Number</th>
                                    <th scope="col" class="px-6 py-3 text-left">Present</th>
                                    <th scope="col" class="px-6 py-3 text-left">Absent</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($students as $student): ?>
                                <tr class="hover:bg-gray-100">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= htmlspecialchars($student['fullname']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?= htmlspecialchars($student['student_number']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" name="attendance[<?= $student['id']; ?>][present]" value="1" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" <?= isset($existingAttendance[$student['id']]) && $existingAttendance[$student['id']] == 'present' ? 'checked disabled' : ($isAttendanceRecorded ? 'disabled' : ''); ?>>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="checkbox" name="attendance[<?= $student['id']; ?>][absent]" value="1" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" <?= isset($existingAttendance[$student['id']]) && $existingAttendance[$student['id']] == 'absent' ? 'checked disabled' : ($isAttendanceRecorded ? 'disabled' : ''); ?>>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <button type="submit" class="mt-4 button-style" <?= $isAttendanceRecorded ? 'disabled' : ''; ?>>Submit Attendance</button>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
