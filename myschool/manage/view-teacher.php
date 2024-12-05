<?php
session_start();
require_once '../../connections/db.php'; // Ensure this path is correct

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

$teacher_id = $_GET['id'] ?? null;
if (!$teacher_id) {
    echo "Teacher ID is required.";
    exit;
}

$stmt = $userInfoConn->prepare("SELECT name, email, username, phone, address, dob, ssn, department, subjects, classes, hire_date, education_background, previous_employment, professional_references, contract_type, photo, biography, teaching_philosophy, role, teacher_number FROM teacher_info WHERE id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();

if (!$teacher) {
    echo "No teacher found.";
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
    <title>Teacher Profile | <?php echo htmlspecialchars($teacher['name']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body { background-color: #f9fafb; font-family: 'Nunito', sans-serif; }
        .profile-header { background-color: #ffffff; padding: 2rem; border-bottom: 2px solid #e5e7eb; display: flex; flex-direction: column; align-items: center; }
        .profile-image { width: 150px; height: 150px; border-radius: 9999px; overflow: hidden; box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
        .profile-content { padding: 2rem; background-color: #ffffff; margin: 1rem; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; }
        .profile-detail { display: flex; flex-direction: column; }
        .profile-detail dt { color: #4a5568; font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem; }
        .profile-detail dd { font-size: 1.25rem; color: #2d3748; }
    </style>
</head>
<body>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="profile-header">
            <div class="profile-image">
                <!-- Adding the relative path prefix to the photo URL -->
                <img src="<?php echo htmlspecialchars($teacher['photo']); ?>" alt="Teacher Photo">
            </div>
            <h1 class="mt-4 text-4xl font-bold"><?php echo htmlspecialchars($teacher['name']); ?></h1>
        </div>
        <div class="profile-content">
            <?php foreach ($teacher as $key => $value): ?>
            <div class="profile-detail">
                <dt><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $key))); ?>:</dt>
                <dd><?php echo htmlspecialchars($value); ?></dd>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>

