<?php
session_start();
require_once '../../connections/db.php'; // Ensure this path is correct

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

$student_id = $_GET['id'] ?? null;
if (!$student_id) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Student ID is required.'];
    header("Location: student_list.php"); // Redirect to a student list page or an appropriate page
    exit;
}

// Fetching existing student details for specific fields only
$stmt = $userInfoConn->prepare("SELECT fullname, dob, gender, phone, email, parentName, parentPhone, parentEmail, address, emergencyContact, emergencyPhone, medicalInfo, previousSchools, admissionDate, ethnicity, language, sen, religion, diet, extracurricular, photo FROM student_info WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if (!$student) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'No student found.'];
    header("Location: student_list.php"); // Redirect to a student list page or an appropriate page
    exit;
}
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collecting form data for the listed fields only
    $fields = ['fullname', 'dob', 'gender', 'phone', 'email', 'parentName', 'parentPhone', 'parentEmail', 'address', 'emergencyContact', 'emergencyPhone', 'medicalInfo', 'previousSchools', 'admissionDate', 'ethnicity', 'language', 'sen', 'religion', 'diet', 'extracurricular'];
    $data = [];
    foreach ($fields as $field) {
        $data[$field] = $_POST[$field] ?? $student[$field];
    }

    // Handling the photo file upload
    $photo = $student['photo'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['photo']['tmp_name'];
        $fileName = $_FILES['photo']['name'];
        $fileNameCmps = explode('.', $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = './uploaded_files/';
            $dest_path = $uploadFileDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $photo = $dest_path;
            } else {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'Error uploading the photo.'];
                header("Location: " . htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $student_id);
                exit;
            }
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Invalid file type. Only JPG, GIF, PNG, and JPEG are allowed.'];
            header("Location: " . htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $student_id);
            exit;
        }
    }

    // Preparing the update statement with only the listed fields
    $updateStmt = $userInfoConn->prepare("UPDATE student_info SET fullname=?, dob=?, gender=?, phone=?, email=?, parentName=?, parentPhone=?, parentEmail=?, address=?, emergencyContact=?, emergencyPhone=?, medicalInfo=?, previousSchools=?, admissionDate=?, ethnicity=?, language=?, sen=?, religion=?, diet=?, extracurricular=?, photo=? WHERE id=?");
    $params = array_merge(array_values($data), [$photo, $student_id]);
    $types = str_repeat('s', count($data)) . 'si'; // Create a type string for binding parameters
    $updateStmt->bind_param($types, ...$params);
    $updateStmt->execute();

    if ($updateStmt->affected_rows > 0) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Student profile updated successfully.'];
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'No changes were made or update failed.'];
    }
    $updateStmt->close();
    header("Location: " . htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $student_id);
    exit;
}

$userInfoConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student Profile | <?= htmlspecialchars($student['fullname']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(135deg, #1f2937, #3b82f6);
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
            color: #f8fafc;
            font-family: 'Roboto', sans-serif;
        }
        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .card {
            background-color: #2d3748;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 10px;
            padding: 20px;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        .input-field {
            background-color: #1a202c;
            color: #a0aec0;
            border: 1px solid #4a5568;
        }
        .input-field:focus {
            border-color: #63b3ed;
            box-shadow: 0 0 0 1px #63b3ed;
        }
        .button-blue {
            background-color: #63b3ed;
            color: #fff;
            transition: background-color 0.2s ease-in-out, transform 0.2s ease-in-out;
        }
        .button-blue:hover {
            background-color: #3182ce;
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gray-900">
    <div class="max-w-4xl mx-auto py-10 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-200 mb-6 text-center">Edit Student Profile</h1>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="p-4 mb-4 text-sm <?php echo $_SESSION['message']['type'] === 'success' ? 'text-green-700 bg-green-100' : 'text-red-700 bg-red-100'; ?> rounded-lg" role="alert">
                <?= htmlspecialchars($_SESSION['message']['text']) ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" class="card">
            <?php foreach ($student as $key => $value): ?>
            <div class="mb-4">
                <label class="block font-medium text-gray-400"><?= ucwords(str_replace('_', ' ', $key)) ?></label>
                <?php if ($key === 'photo'): ?>
                    Current: <img src="<?= htmlspecialchars($value) ?>" alt="Current Photo" style="width: 100px; height: auto;"><br>
                    <input type="file" name="<?= $key ?>" class="input-field mt-2">
                <?php else: ?>
                    <input type="text" name="<?= $key ?>" value="<?= htmlspecialchars($value) ?>" class="input-field mt-2">
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <button type="submit" class="button-blue w-full py-2 px-4 mt-4 rounded-md">Update</button>
        </form>
    </div>
</body>
</html>
