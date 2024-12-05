<?php
session_start(); // Start or resume the session

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

require_once '../../connections/db.php';  // userInfoConn for user data
require_once '../../connections/db_school_data.php';  // schoolDataConn for school data

$studentInfo = [];
$classSchedule = [];
$allGrades = [];
$attendanceRecords = [];
$disciplinaryRecords = [];
$attendance_date = date('Y-m-d');  // Default to today

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['student_number'])) {
    $student_number = $_POST['student_number'];
    $school_id = $_SESSION['school_id'];
    $attendance_date = $_POST['attendance_date'] ?? date('Y-m-d'); // Default to today if not provided

    // Fetch student information
    $stmt = $userInfoConn->prepare("SELECT * FROM student_info WHERE student_number = ? AND school_id = ?");
    $stmt->bind_param("si", $student_number, $school_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $studentInfo = $result->fetch_assoc();

        // Fetch class schedule
        $stmt = $schoolDataConn->prepare("
            SELECT 
                cs.module_id,
                cs.module_code,
                ss.subject_name,
                sm.enrollment_date,
                c.class_name
            FROM 
                student_modules sm
            JOIN 
                class_subject cs ON sm.module_id = cs.module_id
            JOIN 
                school_subjects ss ON cs.subject_id = ss.subject_id
            JOIN 
                classes c ON cs.class_id = c.class_id
            WHERE 
                sm.student_id = ? AND 
                sm.school_id = ? AND 
                cs.school_id = ? AND 
                ss.school_id = ?");
        $stmt->bind_param("iiii", $studentInfo['id'], $school_id, $school_id, $school_id);
        $stmt->execute();
        $scheduleResult = $stmt->get_result();
        while ($schedule = $scheduleResult->fetch_assoc()) {
            $classSchedule[] = $schedule;

            // Fetch grades for each module
            $gradeStmt = $schoolDataConn->prepare("
                SELECT grade, assessment_type, assessment_name, overall_score, teacher_id, term
                FROM grades
                WHERE module_id = ? AND student_id = ? AND school_id = ?");
            $gradeStmt->bind_param("iii", $schedule['module_id'], $studentInfo['id'], $school_id);
            $gradeStmt->execute();
            $gradesResult = $gradeStmt->get_result();
            while ($grade = $gradesResult->fetch_assoc()) {
                $allGrades[$schedule['class_name']][$schedule['module_code']][$schedule['subject_name']][$grade['term']][] = $grade;
            }
            $gradeStmt->close();
        }

        // Fetch attendance records
        $attendanceStmt = $schoolDataConn->prepare("
            SELECT ar.attendance_date, ar.attendance_status, ar.remarks, cs.module_code
            FROM attendance_records ar
            JOIN class_subject cs ON ar.module_id = cs.module_id
            WHERE ar.student_id = ? AND ar.school_id = ? AND ar.attendance_date = ?");
        $attendanceStmt->bind_param("iis", $studentInfo['id'], $school_id, $attendance_date);
        $attendanceStmt->execute();
        $attendanceResult = $attendanceStmt->get_result();
        while ($record = $attendanceResult->fetch_assoc()) {
            $attendanceRecords[] = $record;
        }
        $attendanceStmt->close();

        $disciplinaryStmt = $schoolDataConn->prepare("
            SELECT dr.strike_title, dr.strike_number, dr.strike_description, dr.strike_consequence, dr.recorded_on
            FROM disciplinary_records dr
            WHERE dr.student_id = ? AND dr.school_id = ?");
        $disciplinaryStmt->bind_param("ii", $studentInfo['id'], $school_id);
        $disciplinaryStmt->execute();
        $disciplinaryResult = $disciplinaryStmt->get_result();
        while ($record = $disciplinaryResult->fetch_assoc()) {
            $disciplinaryRecords[] = $record;
        }
        $disciplinaryStmt->close();
    } else {
        echo "<p>No student found with that number at your school.</p>";
    }
    $stmt->close();
}

$userInfoConn->close();
$schoolDataConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspect Student</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
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
        .collapsible {
            background-color: #374151;
            color: #f8fafc;
            cursor: pointer;
            padding: 18px;
            width: 100%;
            border: none;
            text-align: left;
            outline: none;
            font-size: 15px;
            border-bottom: 1px solid #4b5563;
        }
        .active, .collapsible:hover {
            background-color: #4b5563;
        }
        .content {
            padding: 0 18px;
            display: none;
            overflow: hidden;
            background-color: #1f2937;
            transition: max-height 0.2s ease-out;
        }
        .table-header {
            background-color: #1e3a8a;
        }
        .table-cell {
            background-color: #1e293b;
        }
    </style>
</head>
<body class="bg-gray-900 flex items-center justify-center h-screen">
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-3xl font-bold mb-6 text-center">Inspect Student</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="mb-6 mx-auto max-w-lg bg-gray-800 p-6 rounded shadow-lg">
            <div class="flex flex-col">
                <label for="student_number" class="block text-sm font-medium text-gray-300">Student Number:</label>
                <input type="text" name="student_number" id="student_number" required class="mt-1 px-4 py-2 rounded bg-gray-700 text-white">
                <label for="attendance_date" class="block text-sm font-medium text-gray-300 mt-4">Attendance Date:</label>
                <input type="date" name="attendance_date" id="attendance_date" class="mt-1 px-4 py-2 rounded bg-gray-700 text-white">
                <button type="submit" class="mt-4 px-4 py-2 bg-blue-500 text-white font-semibold rounded hover:bg-blue-600 transition">Search</button>
            </div>
        </form>

        <?php if (!empty($studentInfo)): ?>
            <div class="bg-gray-800 p-6 rounded shadow-lg card">
                <h2 class="text-2xl font-semibold mb-4"><?php echo htmlspecialchars($studentInfo['fullname']); ?></h2>
                <p>Email: <?php echo htmlspecialchars($studentInfo['email']); ?></p>
                <p>Date of Birth: <?php echo htmlspecialchars($studentInfo['dob']); ?></p>
                <p>Parent Name: <?php echo htmlspecialchars($studentInfo['parentName']); ?></p>
                <p>Parent Contact: <?php echo htmlspecialchars($studentInfo['parentPhone']); ?></p>
                <p>Home Address: <?php echo htmlspecialchars($studentInfo['address']); ?></p>

                <button class="collapsible mt-4">Class Schedule</button>
                <div class="content">
                    <ul class="mt-2">
                        <?php foreach ($classSchedule as $schedule): ?>
                            <li><?php echo htmlspecialchars($schedule['module_code']) . " - " . htmlspecialchars($schedule['subject_name']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <button class="collapsible mt-4">Grades Overview</button>
                <div class="content">
                    <?php foreach ($allGrades as $class_name => $modules): ?>
                        <button class="collapsible mt-2"><?php echo htmlspecialchars($class_name); ?></button>
                        <div class="content">
                            <?php foreach ($modules as $module_code => $subjects): ?>
                                <button class="collapsible mt-2"><?php echo htmlspecialchars($module_code); ?></button>
                                <div class="content">
                                    <?php foreach ($subjects as $subject_name => $terms): ?>
                                        <button class="collapsible mt-2"><?php echo htmlspecialchars($subject_name); ?></button>
                                        <div class="content">
                                            <?php foreach ($terms as $term => $grades): ?>
                                                <h4 class="text-md font-semibold mt-2"><?php echo htmlspecialchars($term); ?></h4>
                                                <table class="min-w-full border-collapse border border-gray-700 mt-2">
                                                    <thead>
                                                        <tr class="table-header">
                                                            <th class="border border-gray-600 px-4 py-2 text-left">Assessment Name</th>
                                                            <th class="border border-gray-600 px-4 py-2 text-left">Type</th>
                                                            <th class="border border-gray-600 px-4 py-2 text-left">Score</th>
                                                            <th class="border border-gray-600 px-4 py-2 text-left">Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($grades as $grade): ?>
                                                            <tr>
                                                                <td class="border border-gray-600 px-4 py-2 table-cell"><?php echo htmlspecialchars($grade['assessment_name']); ?></td>
                                                                <td class="border border-gray-600 px-4 py-2 table-cell"><?php echo htmlspecialchars($grade['assessment_type']); ?></td>
                                                                <td class="border border-gray-600 px-4 py-2 table-cell"><?php echo htmlspecialchars($grade['grade']); ?></td>
                                                                <td class="border border-gray-600 px-4 py-2 table-cell"><?php echo htmlspecialchars($grade['overall_score']); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <button class="collapsible mt-4">Attendance Records</button>
                <div class="content">
                    <table class="min-w-full border-collapse border border-gray-700 mt-2">
                        <thead>
                            <tr class="table-header">
                                <th class="border border-gray-600 px-4 py-2 text-left">Date</th>
                                <th class="border border-gray-600 px-4 py-2 text-left">Status</th>
                                <th class="border border-gray-600 px-4 py-2 text-left">Module Code</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($attendanceRecords as $record): ?>
                                <tr>
                                    <td class="border border-gray-600 px-4 py-2 table-cell"><?php echo htmlspecialchars($record['attendance_date']); ?></td>
                                    <td class="border border-gray-600 px-4 py-2 table-cell"><?php echo htmlspecialchars($record['attendance_status']); ?></td>
                                    <td class="border border-gray-600 px-4 py-2 table-cell"><?php echo htmlspecialchars($record['module_code']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <button class="collapsible mt-4">Disciplinary Records</button>
                <div class="content">
                    <table class="min-w-full border-collapse border border-gray-700 mt-2">
                        <thead>
                            <tr class="table-header">
                                <th class="border border-gray-600 px-4 py-2 text-left">Strike Title</th>
                                <th class="border border-gray-600 px-4 py-2 text-left">Strike Number</th>
                                <th class="border border-gray-600 px-4 py-2 text-left">Description</th>
                                <th class="border border-gray-600 px-4 py-2 text-left">Consequence</th>
                                <th class="border border-gray-600 px-4 py-2 text-left">Recorded On</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($disciplinaryRecords as $record): ?>
                                <tr>
                                    <td class="border border-gray-600 px-4 py-2 table-cell"><?php echo htmlspecialchars($record['strike_title']); ?></td>
                                    <td class="border border-gray-600 px-4 py-2 table-cell"><?php echo htmlspecialchars($record['strike_number']); ?></td>
                                    <td class="border border-gray-600 px-4 py-2 table-cell"><?php echo htmlspecialchars($record['strike_description']); ?></td>
                                    <td class="border border-gray-600 px-4 py-2 table-cell"><?php echo htmlspecialchars($record['strike_consequence']); ?></td>
                                    <td class="border border-gray-600 px-4 py-2 table-cell"><?php echo htmlspecialchars($record['recorded_on']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        var coll = document.getElementsByClassName("collapsible");
        for (var i = 0; i < coll.length; i++) {
            coll[i].addEventListener("click", function() {
                this.classList.toggle("active");
                var content = this.nextElementSibling;
                if (content.style.display === "block") {
                    content.style.display = "none";
                } else {
                    content.style.display = "block";
                }
            });
        }
    </script>
</body>
</html>
