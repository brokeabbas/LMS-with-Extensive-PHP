<?php
session_start();
require_once '../connections/db.php'; // Ensure this file contains the appropriate mysqli connection setup

// Authentication and session checks
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["student_id"])) {
    header("location: login_student.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// Prepare and execute query to fetch student data
$sql = "SELECT *
        FROM student_info
        WHERE id = ?";

if ($stmt = $userInfoConn->prepare($sql)) {
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $studentData = $result->fetch_assoc();
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
    <title>Student Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #f0f4f8;
        }
        .profile-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .profile-container h1 {
            color: #f0f4f8;
            font-size: 2rem;
            margin-bottom: 1rem;
            text-align: center;
        }
        .profile-container p {
            color: #e2e8f0;
            margin-bottom: 1rem;
        }
        .profile-container .info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        @media (min-width: 768px) {
            .profile-container .info-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
        .profile-picture {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }
        .profile-picture img {
            border-radius: 50%;
            height: 128px;
            width: 128px;
            object-fit: cover;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        .alert {
            color: #ffb3b3;
            margin-top: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="max-w-4xl mx-auto p-5">
        <div class="profile-container">
            <h1>Student Profile</h1>
            <?php if ($studentData): ?>
                <div class="profile-picture">
                    <img src="<?= htmlspecialchars(substr($studentData['photo'], 3)) ?>" alt="Profile Picture">
                </div>
                <div class="info-grid">
                    <!-- Display each piece of student information -->
                    <p><strong>Full Name:</strong> <?= htmlspecialchars($studentData['fullname']) ?></p>
                    <p><strong>Date of Birth:</strong> <?= htmlspecialchars($studentData['dob']) ?></p>
                    <p><strong>Gender:</strong> <?= htmlspecialchars($studentData['gender']) ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($studentData['phone']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($studentData['email']) ?></p>
                    <p><strong>Parent Name:</strong> <?= htmlspecialchars($studentData['parentName']) ?></p>
                    <p><strong>Parent Phone:</strong> <?= htmlspecialchars($studentData['parentPhone']) ?></p>
                    <p><strong>Parent Email:</strong> <?= htmlspecialchars($studentData['parentEmail']) ?></p>
                    <p><strong>Address:</strong> <?= htmlspecialchars($studentData['address']) ?></p>
                    <p><strong>Emergency Contact:</strong> <?= htmlspecialchars($studentData['emergencyContact']) ?></p>
                    <p><strong>Emergency Phone:</strong> <?= htmlspecialchars($studentData['emergencyPhone']) ?></p>
                    <p><strong>Medical Information:</strong> <?= htmlspecialchars($studentData['medicalInfo']) ?></p>
                    <p><strong>Admission Date:</strong> <?= htmlspecialchars($studentData['admissionDate']) ?></p>
                    <p><strong>Language:</strong> <?= htmlspecialchars($studentData['language']) ?></p>
                    <p><strong>SEN:</strong> <?= htmlspecialchars($studentData['sen']) ?></p>
                    <p><strong>Student Number:</strong> <?= htmlspecialchars($studentData['student_number']) ?></p>
                </div>
                <!-- Notice for incorrect information -->
                <p class="alert">
                    If this information does not appear to be correct, please check with your school administration.
                </p>
            <?php else: ?>
                <p class="alert">No profile data found for the provided ID.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
