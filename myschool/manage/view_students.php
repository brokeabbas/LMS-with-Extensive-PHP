<?php
session_start();
require_once '../../connections/db.php'; // Make sure this path is correct

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

$student_id = $_GET['id'] ?? null;
if (!$student_id) {
    echo "Student ID is required.";
    exit;
}

$stmt = $userInfoConn->prepare("SELECT fullname, dob, gender, phone, email, parentName, parentPhone, parentEmail, address, emergencyContact, emergencyPhone, medicalInfo, previousSchools, admissionDate, ethnicity, language, sen, religion, diet, extracurricular, photo, username, student_number FROM student_info WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    echo "No student found.";
    exit;
}

$stmt->close();
$userInfoConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile | <?php echo htmlspecialchars($student['fullname']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background-color: #f9fafb;
            font-family: 'Nunito', sans-serif;
        }
        .profile-header {
            background-color: #ffffff;
            padding: 2rem;
            border-bottom: 2px solid #e5e7eb;
            display: flex;
            flex-direction: column;
            align-items: center;
            animation: fadeIn 0.5s ease-in-out;
        }
        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 9999px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .profile-content {
            padding: 2rem;
            background-color: #ffffff;
            margin: 1rem;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
            animation: slideUp 0.5s ease-in-out;
        }
        .profile-detail {
            display: flex;
            flex-direction: column;
        }
        .profile-detail dt {
            color: #4a5568;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .profile-detail dd {
            font-size: 1.25rem;
            color: #2d3748;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="profile-header">
            <div class="profile-image">
                <img src="<?php echo htmlspecialchars($student['photo']); ?>" alt="Student Photo">
            </div>
            <h1 class="mt-4 text-4xl font-bold"><?php echo htmlspecialchars($student['fullname']); ?></h1>
        </div>
        <div class="profile-content">
            <?php foreach ($student as $key => $value): ?>
                <?php if ($key !== 'photo'): ?>
                <div class="profile-detail">
                    <dt><i class="fas fa-chevron-right"></i> <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $key))); ?>:</dt>
                    <dd><?php echo htmlspecialchars($value); ?></dd>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>