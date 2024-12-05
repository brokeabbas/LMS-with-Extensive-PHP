<?php
session_start();
require_once '../../connections/db.php'; // Ensure this path is correct

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

$teacher_id = $_GET['id'] ?? null;
if (!$teacher_id) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Teacher ID is required.'];
    header("Location: teacher_list.php"); // Redirect to a teacher list page or an appropriate page
    exit;
}

// Fetching existing teacher details
$stmt = $userInfoConn->prepare("SELECT * FROM teacher_info WHERE id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();

if (!$teacher) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'No teacher found.'];
    header("Location: teacher_list.php"); // Redirect to a teacher list page or an appropriate page
    exit;
}
$stmt->close();

// Handling form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collecting form data
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $dob = $_POST['dob'];
    $ssn = $_POST['ssn'];
    $department = $_POST['department'];
    $subjects = $_POST['subjects'];
    $classes = $_POST['classes'];
    $hire_date = $_POST['hire_date'];
    $education_background = $_POST['education_background'];
    $previous_employment = $_POST['previous_employment'];
    $professional_references = $_POST['professional_references'];
    $contract_type = $_POST['contract_type'];
    $biography = $_POST['biography'];
    $teaching_philosophy = $_POST['teaching_philosophy'];
    $role = $_POST['role'];

    // Handling photo upload
    $photo = $teacher['photo'];
    if (!empty($_FILES['photo']['name'])) {
        $fileTmpPath = $_FILES['photo']['tmp_name'];
        $fileName = $_FILES['photo']['name'];
        $fileNameCmps = explode('.', $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = '../../uploads/';
            $dest_path = $uploadFileDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $photo = $dest_path;
            } else {
                $_SESSION['message'] = ['type' => 'error', 'text' => 'Error uploading the photo.'];
                header("Location: " . htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $teacher_id);
                exit;
            }
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Invalid file type. Only JPG, GIF, PNG, and JPEG are allowed.'];
            header("Location: " . htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $teacher_id);
            exit;
        }
    }

    // Update query
    $sql = "UPDATE teacher_info SET name=?, email=?, phone=?, address=?, dob=?, ssn=?, department=?, subjects=?, classes=?, hire_date=?, education_background=?, previous_employment=?, professional_references=?, contract_type=?, biography=?, teaching_philosophy=?, role=?, photo=? WHERE id=?";
    $stmt = $userInfoConn->prepare($sql);
    $stmt->bind_param("ssssssssssssssssssi", $name, $email, $phone, $address, $dob, $ssn, $department, $subjects, $classes, $hire_date, $education_background, $previous_employment, $professional_references, $contract_type, $biography, $teaching_philosophy, $role, $photo, $teacher_id);
    if ($stmt->execute()) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Teacher updated successfully!'];
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Error updating record: ' . $stmt->error];
    }
    $stmt->close();
    header("Location: " . htmlspecialchars($_SERVER["PHP_SELF"]) . "?id=" . $teacher_id);
    exit;
}

$userInfoConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Teacher Profile | <?php echo htmlspecialchars($teacher['name']); ?></title>
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
        <h1 class="text-3xl font-bold text-gray-200 mb-6 text-center">Edit Teacher Profile</h1>
        <?php if (isset($_SESSION['message'])): ?>
            <div class="p-4 mb-4 text-sm <?php echo $_SESSION['message']['type'] === 'success' ? 'text-green-700 bg-green-100' : 'text-red-700 bg-red-100'; ?> rounded-lg" role="alert">
                <?= htmlspecialchars($_SESSION['message']['text']) ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" class="card">
            <div class="mb-4">
                <label class="block font-medium text-gray-400">Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($teacher['name']); ?>" class="input-field mt-2">
            </div>
            <div class="mb-4">
                <label class="block font-medium text-gray-400">Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($teacher['email']); ?>" class="input-field mt-2">
            </div>
            <div class="mb-4">
                <label class="block font-medium text-gray-400">Phone</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($teacher['phone']); ?>" class="input-field mt-2">
            </div>
            <div class="mb-4">
                <label class="block font-medium text-gray-400">Address</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($teacher['address']); ?>" class="input-field mt-2">
            </div>
            <div class="mb-4">
                <label class="block font-medium text-gray-400">Date of Birth</label>
                <input type="date" name="dob" value="<?php echo htmlspecialchars($teacher['dob']); ?>" class="input-field mt-2">
            </div>
            <div class="mb-4">
                <label class="block font-medium text-gray-400">SSN</label>
                <input type="text" name="ssn" value="<?php echo htmlspecialchars($teacher['ssn']); ?>" class="input-field mt-2">
            </div>
            <div class="mb-4">
                <label class="block font-medium text-gray-400">Department</label>
                <input type="text" name="department" value="<?php echo htmlspecialchars($teacher['department']); ?>" class="input-field mt-2">
            </div>
            <div class="mb-4">
                <label class="block font-medium text-gray-400">Subjects</label>
                <input type="text" name="subjects" value="<?php echo htmlspecialchars($teacher['subjects']); ?>" class="input-field mt-2">
            </div>
            <div class="mb-4">
                <label class="block font-medium text-gray-400">Classes</label>
                <input type="text" name="classes" value="<?php echo htmlspecialchars($teacher['classes']); ?>" class="input-field mt-2">
            </div>
            <div class="mb-4">
                <label class="block font-medium text-gray-400">Hire Date</label>
                <input type="date" name="hire_date" value="<?php echo htmlspecialchars($teacher['hire_date']); ?>" class="input-field mt-2">
            </div>
            <div class="mb-4">
                <label class="block font-medium text-gray-400">Education Background</label>
                <textarea name="education_background" class="input-field mt-2"><?php echo htmlspecialchars($teacher['education_background']); ?></textarea>
            </div>
            <div class="mb-4">
                <label class="block font-medium text-gray-400">Previous Employment</label>
                <textarea name="previous_employment" class="input-field mt-2"><?php echo htmlspecialchars($teacher['previous_employment']); ?></textarea>
            </div>
            <div class="mb-4">
                <label class="block font-medium text-gray-400">Professional References</label>
                <textarea name="professional_references" class="input-field mt-2"><?php echo htmlspecialchars($teacher['professional_references']); ?></textarea>
            </div>
            <div class="mb-4">
                <label class="block font-medium text-gray-400">Contract Type</label>
                <input type="text" name="contract_type" value="<?php echo htmlspecialchars($teacher['contract_type']); ?>" class="input-field mt-2">
            </div>
            <div class="mb-4">
                <label class="block font-medium text-gray-400">Biography</label>
                <textarea name="biography" class="input-field mt-2"><?php echo htmlspecialchars($teacher['biography']); ?></textarea>
            </div>
            <div class="mb-4">
                <label class="block font-medium text-gray-400">Teaching Philosophy</label>
                <textarea name="teaching_philosophy" class="input-field mt-2"><?php echo htmlspecialchars($teacher['teaching_philosophy']); ?></textarea>
            </div>
            <div class="mb-4">
                <label class="block font-medium text-gray-400">Role</label>
                <input type="text" name="role" value="<?php echo htmlspecialchars($teacher['role']); ?>" class="input-field mt-2">
            </div>
            <div class="mb-4">
                <label class="block font-medium text-gray-400">Photo</label>
                Current: <img src="<?php echo htmlspecialchars($teacher['photo']); ?>" alt="Current Photo" style="height: 100px;"><br>
                <input type="file" name="photo" class="input-field mt-2">
            </div>
            <button type="submit" class="button-blue w-full py-2 px-4 mt-4 rounded-md">Update Teacher</button>
        </form>
    </div>
</body>
</html>
