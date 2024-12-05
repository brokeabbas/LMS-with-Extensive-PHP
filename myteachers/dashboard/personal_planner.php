<?php
session_start();
require_once '../../connections/db.php';
require_once '../../connections/db_school_data.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login_teacher.php");
    exit;
}

$school_name = "School Not Found";
if (isset($_SESSION["school_id"]) && $stmt = $userInfoConn->prepare("SELECT school_name FROM schools WHERE school_id = ?")) {
    $stmt->bind_param("i", $_SESSION["school_id"]);
    $stmt->execute();
    $stmt->bind_result($school_name);
    $stmt->fetch();
    $stmt->close();
}

$teacher_name = "Teacher Not Found";
if (isset($_SESSION["teacher_id"]) && $stmt = $userInfoConn->prepare("SELECT name FROM teacher_info WHERE id = ?")) {
    $stmt->bind_param("i", $_SESSION["teacher_id"]);
    $stmt->execute();
    $stmt->bind_result($teacher_name);
    $stmt->fetch();
    $stmt->close();
}

// Functions to fetch data
function fetch_tasks($teacher_id, $school_id) {
    global $schoolDataConn;
    $tasks = [];

    $sql = "SELECT task_id, task_name, task_description, due_date FROM tasks WHERE teacher_id = ? AND school_id = ?";
    if ($stmt = $schoolDataConn->prepare($sql)) {
        $stmt->bind_param("ii", $teacher_id, $school_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $tasks[] = $row;
        }

        $stmt->close();
    }
    return $tasks;
}

function fetch_events($teacher_id, $school_id) {
    global $schoolDataConn;
    $events = [];

    $sql = "SELECT event_id, event_name, event_description, event_date FROM events WHERE teacher_id = ? AND school_id = ?";
    if ($stmt = $schoolDataConn->prepare($sql)) {
        $stmt->bind_param("ii", $teacher_id, $school_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }

        $stmt->close();
    }
    return $events;
}

$tasks = fetch_tasks($_SESSION['teacher_id'], $_SESSION['school_id']);
$events = fetch_events($_SESSION['teacher_id'], $_SESSION['school_id']);

$userInfoConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Planner</title>
    <link rel="stylesheet" href="path/to/your/css/file.css"> <!-- Update with your actual CSS file path -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
</head>
<body>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Personal Planner for <?php echo htmlspecialchars($teacher_name); ?></h1>
        <p class="text-lg mb-4">School: <?php echo htmlspecialchars($school_name); ?></p>

        <!-- Task Management Section -->
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-2">Task Management</h2>
            <ul class="list-disc pl-5">
                <?php foreach ($tasks as $task): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($task['task_name']); ?></strong><br>
                        <?php echo htmlspecialchars($task['task_description']); ?><br>
                        <em>Due: <?php echo htmlspecialchars($task['due_date']); ?></em>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Calendar Integration Section -->
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-2">Upcoming Events</h2>
            <ul class="list-disc pl-5">
                <?php foreach ($events as $event): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($event['event_name']); ?></strong><br>
                        <?php echo htmlspecialchars($event['event_description']); ?><br>
                        <em>Date: <?php echo htmlspecialchars($event['event_date']); ?></em>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Links to Other Features -->
        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-2">More Features</h2>
            <ul class="list-disc pl-5">
                <li><a href="professional_portfolio.php" class="text-blue-500 hover:underline">Professional Portfolio</a></li>
                <li><a href="resource_management.php" class="text-blue-500 hover:underline">Resource Management</a></li>
                <li><a href="wellness_and_selfcare.php" class="text-blue-500 hover:underline">Wellness and Self-Care</a></li>
                <li><a href="professional_networking.php" class="text-blue-500 hover:underline">Professional Networking</a></li>
            </ul>
        </div>
    </div>
</body>
</html>
