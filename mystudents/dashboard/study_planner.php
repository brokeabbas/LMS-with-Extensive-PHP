<?php
session_start();
require_once '../../connections/db.php';  // Database connection file
require_once '../../connections/db_school_data.php';  // School data database connection

// Authentication and session checks
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login_student.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$school_id = $_SESSION['school_id'];

// Initialize arrays to hold data
$schedules = [];
$grades = [];
$assignments = [];

// Fetch class schedules
$scheduleQuery = "SELECT ss.subject_name, cl.class_name, mt.module_code 
                  FROM school_data.student_modules sm
                  JOIN school_data.modules_taught mt ON sm.module_id = mt.module_id
                  JOIN school_data.class_subject cs ON mt.module_id = cs.module_id
                  JOIN school_data.classes cl ON cs.class_id = cl.class_id
                  JOIN school_data.school_subjects ss ON cs.subject_id = ss.subject_id
                  WHERE sm.student_id = ? AND sm.school_id = ?";
if ($stmt = $schoolDataConn->prepare($scheduleQuery)) {
    $stmt->bind_param("ii", $student_id, $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $schedules = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Fetch grades
$gradesQuery = "SELECT ss.subject_name, g.grade, g.assessment_type, g.assessment_name, g.overall_score
                FROM school_data.grades g
                JOIN school_data.modules_taught mt ON g.module_id = mt.module_id
                JOIN school_data.class_subject cs ON mt.module_id = cs.module_id
                JOIN school_data.school_subjects ss ON cs.subject_id = ss.subject_id
                WHERE g.student_id = ? AND g.school_id = ?
                ORDER BY ss.subject_name";
if ($stmt = $schoolDataConn->prepare($gradesQuery)) {
    $stmt->bind_param("ii", $student_id, $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $grades = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Fetch upcoming assignments
$assignmentsQuery = "SELECT a.assignment_name, a.due_date, a.description 
                     FROM school_data.assignments a
                     JOIN school_data.modules_taught mt ON a.module_id = mt.module_id
                     WHERE mt.module_id IN 
                         (SELECT module_id FROM school_data.student_modules WHERE student_id = ?)
                     AND a.due_date > NOW() 
                     ORDER BY a.due_date ASC";
if ($stmt = $schoolDataConn->prepare($assignmentsQuery)) {
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $assignments = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$schoolDataConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Planner</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css' rel='stylesheet' />
    <script src='https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js'></script>
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold text-gray-900">Study Planner</h1>
        <div id="calendar" class="mt-6"></div>
        <script>
            $(document).ready(function() {
                $('#calendar').fullCalendar({
                    header: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'month,agendaWeek,agendaDay'
                    },
                    defaultDate: '<?= date("Y-m-d") ?>',
                    editable: false,
                    events: [
                        <?php foreach ($assignments as $assignment): ?>
                        {
                            title: '<?= addslashes($assignment['assignment_name']) ?>',
                            start: '<?= $assignment['due_date'] ?>',
                            description: '<?= addslashes($assignment['description']) ?>',
                            allDay: true
                        }<?= !$loop->last ? ',' : '' ?>
                        <?php endforeach; ?>
                    ]
                });
            });
        </script>
    </div>
</body>
</html>
