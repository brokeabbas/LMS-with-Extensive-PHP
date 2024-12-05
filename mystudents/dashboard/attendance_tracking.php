<?php
session_start();
require_once '../../connections/db.php'; // Connection to the user info database
require_once '../../connections/db_school_data.php'; // Connection to the school data database

// Authentication check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["student_id"], $_SESSION["school_id"])) {
    header("location: ../login_student.php");
    exit;
}

// Variables
$school_id = $_SESSION['school_id'];
$student_id = $_SESSION['student_id'];
$attendance_date = $_GET['attendance_date'] ?? date('Y-m-d'); // Default to today if not provided

// SQL to fetch attendance records along with subject and class names
$sql = "SELECT ar.attendance_date, ar.attendance_status, mt.module_code, ss.subject_name, cl.class_name
        FROM attendance_records ar
        JOIN modules_taught mt ON ar.module_id = mt.module_id
        JOIN class_subject cs ON mt.module_id = cs.module_id
        JOIN school_subjects ss ON cs.subject_id = ss.subject_id
        JOIN classes cl ON cs.class_id = cl.class_id
        WHERE ar.student_id = ? AND ar.school_id = ? AND ar.attendance_date = ?";

$records = [];

if ($stmt = $schoolDataConn->prepare($sql)) {
    $stmt->bind_param("iis", $student_id, $school_id, $attendance_date);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    $stmt->close();
} else {
    echo "SQL Error: " . $schoolDataConn->error;
}

$schoolDataConn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Tracking</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(to right, #0f2027, #203a43, #2c5364); /* Cool blue gradient */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #ffffff;
            overflow-x: hidden;
        }
        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
        }
        .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        .card:hover {
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5);
        }
        .btn {
            background: linear-gradient(145deg, #22d1ee, #3f5efb);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        th {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }
        tr:nth-child(even) {
            background: rgba(255, 255, 255, 0.1);
        }
        tr:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        input[type="date"] {
            background: rgba(255, 255, 255, 0.9);
            color: #000;
            padding: 8px;
            border-radius: 5px;
            border: none;
            outline: none;
        }
        input[type="date"]:focus {
            box-shadow: 0 0 10px rgba(34, 209, 238, 0.5);
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-3xl font-bold text-center mt-4">Attendance Records</h1>
        <div class="card">
            <form action="" method="get" class="flex justify-between items-center">
                <label for="attendance_date" class="block text-sm font-medium text-white">Select Date:</label>
                <input type="date" id="attendance_date" name="attendance_date" value="<?= htmlspecialchars($attendance_date); ?>" class="mt-1 block pl-3 pr-10 py-2 border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md">
                <button type="submit" class="btn">Show Records</button>
            </form>
            <div class="mt-4">
                <?php if (count($records) > 0): ?>
                    <table>
                        <thead class="text-xs font-medium uppercase text-gray-400">
                            <tr>
                                <th>Date</th>
                                <th>Module</th>
                                <th>Subject</th>
                                <th>Class</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-white">
                            <?php foreach ($records as $record): ?>
                                <tr>
                                    <td><?= htmlspecialchars($record['attendance_date']); ?></td>
                                    <td><?= htmlspecialchars($record['module_code']); ?></td>
                                    <td><?= htmlspecialchars($record['subject_name']); ?></td>
                                    <td><?= htmlspecialchars($record['class_name']); ?></td>
                                    <td><?= htmlspecialchars($record['attendance_status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-gray-400 mt-4">No attendance records found for the selected date.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
