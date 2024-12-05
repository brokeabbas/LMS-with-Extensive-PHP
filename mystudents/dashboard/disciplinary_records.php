<?php
session_start();
require_once '../../connections/db.php';  // Adjust the path as necessary

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["student_id"])) {
    header("location: ../login_student.php");
    exit;
}

$student_id = $_SESSION["student_id"];

// Database connection
require_once '../../connections/db_school_data.php';

// Fetch disciplinary records including the teacher number
$sql = "SELECT dr.recorded_on, ti.teacher_number, dr.strike_description, dr.strike_consequence FROM disciplinary_records dr
        JOIN schoolhu_userinfo.teacher_info ti ON dr.teacher_id = ti.id
        WHERE dr.student_id = ?";
$records = [];

if ($stmt = $schoolDataConn->prepare($sql)) {
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    $stmt->close();
}
$schoolDataConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disciplinary Records</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(to right, #e53935, #b71c1c); /* Reddish gradient */
            color: #fff;
        }
        .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.3); /* Lighter border for better contrast */
        }
        .card-hover:hover {
            background: rgba(255, 255, 255, 0.2); /* Light hover effect for interactivity */
        }
        .container {
            transition: color 0.3s ease;
        }
        .text-center p {
            color: #ffb2a7; /* Soft red for text */
        }
    </style>
</head>
<body>
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold text-center mb-4">Disciplinary Records</h1>
        <?php if (!empty($records)): ?>
            <div class="max-w-4xl mx-auto">
                <?php foreach ($records as $record): ?>
                    <div class="card p-4 mb-4 card-hover">
                        <h3 class="text-lg font-semibold">Date: <?= htmlspecialchars($record['recorded_on']); ?></h3>
                        <p>Teacher Number: <?= htmlspecialchars($record['teacher_number']); ?></p>
                        <p>Description: <?= htmlspecialchars($record['strike_description']); ?></p>
                        <p>Consequence: <?= htmlspecialchars($record['strike_consequence']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center">
                <p>No disciplinary records found.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
