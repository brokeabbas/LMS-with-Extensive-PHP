<?php
session_start();
require_once '../connections/db.php'; // Ensure this file contains the appropriate mysqli connection setup

// Authentication and session checks
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["teacher_id"])) {
    header("location: login_teacher.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];

// Prepare and execute query to fetch teacher data
$sql = "SELECT name, email, username, phone, address, dob, ssn, department, subjects, classes, 
        hire_date, education_background, previous_employment, professional_references, 
        contract_type, photo, biography, teaching_philosophy, role, school_id, teacher_number, active
        FROM teacher_info
        WHERE id = ?";

if ($stmt = $userInfoConn->prepare($sql)) {
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $teacherData = $result->fetch_assoc();
    $stmt->close();
} else {
    echo "SQL Error: " . $userInfoConn->error;
    exit;
}

$userInfoConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(to right, #667eea, #764ba2);
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .profile-container {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 900px;
            width: 100%;
        }
        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .profile-header img {
            border-radius: 9999px;
        }
        .profile-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-top: 1rem;
        }
        .profile-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        .profile-info p {
            margin: 0.5rem 0;
        }
        .profile-info strong {
            font-weight: 700;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="profile-container">
        <div class="profile-header">
            <?php if ($teacherData): ?>
                <img src="<?= htmlspecialchars(substr($teacherData['photo'], 3)) ?>" alt="Profile Picture" class="h-32 w-32 mx-auto">
                <h1><?= htmlspecialchars($teacherData['name']) ?></h1>
            <?php else: ?>
                <p class="text-red-500">No profile data found for the provided ID.</p>
            <?php endif; ?>
        </div>
        <?php if ($teacherData): ?>
            <div class="profile-info">
                <p><strong>Email:</strong> <?= htmlspecialchars($teacherData['email']) ?></p>
                <p><strong>Phone:</strong> <?= htmlspecialchars($teacherData['phone']) ?></p>
                <p><strong>Address:</strong> <?= htmlspecialchars($teacherData['address']) ?></p>
                <p><strong>Date of Birth:</strong> <?= htmlspecialchars($teacherData['dob']) ?></p>
                <p><strong>Department:</strong> <?= htmlspecialchars($teacherData['department']) ?></p>
                <p><strong>Subjects Taught:</strong> <?= htmlspecialchars($teacherData['subjects']) ?></p>
                <p><strong>Hire Date:</strong> <?= htmlspecialchars($teacherData['hire_date']) ?></p>
                <p><strong>Educational Background:</strong> <?= nl2br(htmlspecialchars($teacherData['education_background'])) ?></p>
                <p><strong>Previous Employment:</strong> <?= nl2br(htmlspecialchars($teacherData['previous_employment'])) ?></p>
                <p><strong>Professional References:</strong> <?= nl2br(htmlspecialchars($teacherData['professional_references'])) ?></p>
                <p><strong>Contract Type:</strong> <?= htmlspecialchars($teacherData['contract_type']) ?></p>
                <p><strong>Biography:</strong> <?= nl2br(htmlspecialchars($teacherData['biography'])) ?></p>
                <p><strong>Teaching Philosophy:</strong> <?= nl2br(htmlspecialchars($teacherData['teaching_philosophy'])) ?></p>
                <p><strong>Role:</strong> <?= htmlspecialchars($teacherData['role']) ?></p>
                <p><strong>Teacher Number:</strong> <?= htmlspecialchars($teacherData['teacher_number']) ?></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
