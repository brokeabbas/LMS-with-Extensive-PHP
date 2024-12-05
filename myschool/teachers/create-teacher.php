<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["school_id"])) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

require_once '../../connections/db.php';
require '../../connections/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function generateTeacherNumber($userInfoConn) {
    for ($i = 0; $i < 10; $i++) {
        $number = mt_rand(100000, 999999);
        $sql = "SELECT COUNT(*) as count FROM teacher_info WHERE teacher_number = ?";
        if ($stmt = $userInfoConn->prepare($sql)) {
            $stmt->bind_param("i", $number);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            if ($row['count'] == 0) {
                return $number;
            }
        }
    }
    return false;
}

function generateTeacherCode($userInfoConn) {
    while (true) {
        $code = "TEA_" . mt_rand(100000, 999999);
        $sql = "SELECT COUNT(*) as count FROM teacher_users WHERE teacher_code = ?";
        if ($stmt = $userInfoConn->prepare($sql)) {
            $stmt->bind_param("s", $code);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            if ($row['count'] == 0) {
                return $code;
            }
        }
    }
}

function generateUniqueFilename($directory, $filename) {
    $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
    $file_base = pathinfo($filename, PATHINFO_FILENAME);
    $unique_filename = $filename;
    $counter = 1;

    while (file_exists($directory . $unique_filename)) {
        $unique_filename = $file_base . '_' . $counter . '.' . $file_ext;
        $counter++;
    }

    return $unique_filename;
}

$emailSent = false;
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] != UPLOAD_ERR_OK) {
        $message = '<div class="bg-red-500 text-white font-bold py-2 px-4 rounded">Error: No file uploaded or file upload error. Code: ' . $_FILES['photo']['error'] . '</div>';
    } else {
        $uploadDirectory = "../../uploads/";
        $fileName = basename($_FILES['photo']['name']);
        $fileTmpName = $_FILES['photo']['tmp_name'];
        $fileName = generateUniqueFilename($uploadDirectory, $fileName); // Ensure unique filename
        $filePath = $uploadDirectory . $fileName;
        $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (!in_array($fileType, ['jpg', 'png', 'jpeg', 'gif'])) {
            $message = '<div class="bg-red-500 text-white font-bold py-2 px-4 rounded">Error: Only JPG, PNG, JPEG, and GIF files are allowed.</div>';
        } else {
            if (!move_uploaded_file($fileTmpName, $filePath)) {
                $message = '<div class="bg-red-500 text-white font-bold py-2 px-4 rounded">Error uploading the file.</div>';
            } else {
                // Validate username and password
                if (!preg_match('/^[a-zA-Z0-9]{5,}$/', $_POST['username'])) {
                    $message = '<div class="bg-red-500 text-white font-bold py-2 px-4 rounded">Invalid username. Username must be at least 5 characters long and contain only letters and numbers.</div>';
                } elseif (strlen($_POST['password']) < 8 || !preg_match('/[A-Z]/', $_POST['password']) || !preg_match('/[0-9]/', $_POST['password']) || !preg_match('/[!@#$%^&*]/', $_POST['password'])) {
                    $message = '<div class="bg-red-500 text-white font-bold py-2 px-4 rounded">Invalid password. Password must be at least 8 characters long, contain at least one uppercase letter, one number, and one special character.</div>';
                } else {
                    $teacherName = htmlspecialchars($_POST['teacherName']);
                    $email = htmlspecialchars($_POST['email']);
                    $username = htmlspecialchars($_POST['username']);
                    $plaintextPassword = $_POST['password']; // Store plaintext password temporarily
                    $password = password_hash($plaintextPassword, PASSWORD_DEFAULT); // Hash the password
                    $phone = htmlspecialchars($_POST['phone']);
                    $address = htmlspecialchars($_POST['address']);
                    $dob = $_POST['dob'];
                    $ssn = htmlspecialchars($_POST['ssn']);
                    $department = htmlspecialchars($_POST['department']);
                    $subjectsTaught = htmlspecialchars($_POST['subjectsTaught']);
                    $classesTaught = htmlspecialchars($_POST['classesTaught']);
                    $hireDate = $_POST['hireDate'];
                    $educationBackground = htmlspecialchars($_POST['educationBackground']);
                    $previousEmployment = htmlspecialchars($_POST['previousEmployment']);
                    $professionalReferences = htmlspecialchars($_POST['professionalReferences']);
                    $contractType = htmlspecialchars($_POST['contractType']);
                    $biography = htmlspecialchars($_POST['biography']);
                    $teachingPhilosophy = htmlspecialchars($_POST['teachingPhilosophy']);
                    $role = htmlspecialchars($_POST['role']);
                    $schoolId = $_SESSION['school_id'];
                    $teacherNumber = generateTeacherNumber($userInfoConn);

                    if (!$teacherNumber) {
                        $message = "<div class='bg-red-500 text-white font-bold py-2 px-4 rounded'>Failed to generate a unique teacher number. Please try again.</div>";
                    } else {
                        $sql = "INSERT INTO teacher_info (name, email, username, password, phone, address, dob, ssn, department, subjects, classes, hire_date, education_background, previous_employment, professional_references, contract_type, photo, biography, teaching_philosophy, role, school_id, teacher_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        if ($stmt = $userInfoConn->prepare($sql)) {
                            $stmt->bind_param("sssssssssssssssssssssi", $teacherName, $email, $username, $password, $phone, $address, $dob, $ssn, $department, $subjectsTaught, $classesTaught, $hireDate, $educationBackground, $previousEmployment, $professionalReferences, $contractType, $filePath, $biography, $teachingPhilosophy, $role, $schoolId, $teacherNumber);
                            try {
                                if ($stmt->execute()) {
                                    $teacher_id = $stmt->insert_id;
                                    $teacher_code = generateTeacherCode($userInfoConn);

                                    $userSql = "INSERT INTO teacher_users (teacher_account_id, teacher_id, school_id, username, password, teacher_number, teacher_code) VALUES (?, ?, ?, ?, ?, ?, ?)";
                                    if ($userStmt = $userInfoConn->prepare($userSql)) {
                                        $userStmt->bind_param("iiissss", $teacher_id, $teacher_id, $schoolId, $username, $password, $teacherNumber, $teacher_code);
                                        if ($userStmt->execute()) {
                                            $emailSent = true;
                                        }
                                        $userStmt->close();
                                    }
                                    $message = "<div class='bg-green-500 text-white font-bold py-2 px-4 rounded'>Teacher registered successfully. An email has been sent to $email.</div>";
                                } else {
                                    $message = "<div class='bg-red-500 text-white font-bold py-2 px-4 rounded'>Error: " . $stmt->error . "</div>";
                                }
                            } catch (mysqli_sql_exception $e) {
                                if ($e->getCode() == 1062) {
                                    $message = "<div class='bg-red-500 text-white font-bold py-2 px-4 rounded'>A teacher with this email or username already exists. Please use a different email or username.</div>";
                                } else {
                                    $message = "<div class='bg-red-500 text-white font-bold py-2 px-4 rounded'>Error: " . $e->getMessage() . "</div>";
                                }
                            }
                            $stmt->close();
                        }
                    }
                }
            }
        }
    }
    $userInfoConn->close();
}

if ($emailSent) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'yabbaz4321@gmail.com';
        $mail->Password = 'xbwrrdconariamrn';
        $mail->SMTPSecure = "tls";
        $mail->Port = 587;

        $mail->setFrom('yabbaz4321@gmail.com', 'School Admin');
        $mail->addAddress($email, $teacherName);
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to Our School';
        $mail->Body = "Hello <strong>$teacherName</strong>,<br>Welcome to School Hub! Here are your account details:<br><strong>Username:</strong> $username<br><strong>Password:</strong> $plaintextPassword<br><strong>Teacher Code:</strong> $teacher_code<br><strong>Teacher Number:</strong> $teacherNumber<br>Please change your password upon first login.";
        $mail->AltBody = "Hello $teacherName,\nWelcome to our school! Here are your account details:\nUsername: $username\nPassword: $plaintextPassword\nTeacher Code: $teacher_code\nTeacher Number: $teacherNumber\nPlease change your password upon first login.\n\n www.schoolhub.com";

        $mail->send();
    } catch (Exception $e) {
        $message = "<div class='bg-red-500 text-white font-bold py-2 px-4 rounded'>The welcome email could not be sent. Error: " . $mail->ErrorInfo . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Teacher - School Management System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(135deg, #1f2937, #3b82f6);
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
            color: #f8fafc;
        }
        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .card {
            background-color: #1e293b;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        .hover\:grow { transition: all .2s ease-in-out; }
        .hover\:grow:hover { transform: scale(1.05); }
        .form-section {
            border-top: 1px solid #374151;
            border-bottom: 1px solid #374151;
            padding-top: 1rem;
            padding-bottom: 1rem;
        }
        .form-header {
            text-align: center;
            color: #f8fafc;
            animation: fadeInDown 1s both;
        }
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translate3d(0, -100%, 0);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }
        .form-control {
            background-color: grey;
            border: 1px solid #cbd5e0;
            border-radius: 0.375rem;
            padding: 0.5rem 1rem;
        }
        .form-control:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 1px #4f46e5;
        }
        .button-primary {
            background-color: #4f46e5;
            color: white;
            padding: 0.5rem 1.5rem;
            border-radius: 0.375rem;
            font-size: 1rem;
            transition: background-color 0.2s;
        }
        .button-primary:hover {
            background-color: #5a67d8;
        }
        .text-white-override {
            color: white;
        }
    </style>
</head>
<body>
    <div class="max-w-4xl mx-auto p-5">
        <h2 class="text-center text-3xl font-bold text-gray-200 my-6 form-header">Add New Teacher</h2>
        <?php if ($message) echo $message; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" onsubmit="return validateForm();" class="bg-gray-800 shadow-md rounded px-8 pt-6 pb-8 mb-4 card">
            <fieldset class="form-section">
                <legend class="text-xl font-semibold text-gray-200 px-2"><i class="fas fa-chalkboard-teacher mr-2"></i>Teacher Information</legend>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-bold mb-2">Name:</label>
                        <input type="text" name="teacherName" required class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-bold mb-2">Email:</label>
                        <input type="email" name="email" required class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-bold mb-2">Phone Number:</label>
                        <input type="text" name="phone" required class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-bold mb-2">Home Address:</label>
                        <input type="text" name="address" required class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-bold mb-2">Date of Birth:</label>
                        <input type="date" name="dob" required class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-bold mb-2">Social Security Number/National ID:</label>
                        <input type="text" name="ssn" required class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-bold mb-2">Department:</label>
                        <select name="department" required class="form-control">
                            <option value="">Select a Department</option>
                            <option value="Science">Science</option>
                            <option value="Mathematics">Mathematics</option>
                            <option value="Languages">Languages</option>
                            <option value="Arts">Arts</option>
                            <option value="Social Sciences">Social Sciences</option>
                            <option value="Physical Education">Physical Education</option>
                            <option value="Technology">Technology</option>
                            <option value="Business Studies">Business Studies</option>
                            <option value="Vocational Studies">Vocational Studies</option>
                            <option value="Music and Drama">Music and Drama</option>
                            <option value="Health Education">Health Education</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-bold mb-2">Subjects Taught:</label>
                        <select name="subjectsTaught" class="form-control">
                            <option value="Basic Science">Basic Science</option>
                            <option value="Basic Technology">Basic Technology</option>
                            <option value="Business Studies">Business Studies</option>
                            <option value="Home Economics">Home Economics</option>
                            <option value="Social Studies">Social Studies</option>
                            <option value="Civic Education">Civic Education</option>
                            <option value="Cultural and Creative Arts">Cultural and Creative Arts</option>
                            <option value="PHE (Physical Health Education)">PHE (Physical Health Education)</option>
                            <option value="Mathematics">Mathematics</option>
                            <option value="English Language">English Language</option>
                            <option value="Physics">Physics</option>
                            <option value="Chemistry">Chemistry</option>
                            <option value="Biology">Biology</option>
                            <option value="Economics">Economics</option>
                            <option value="Geography">Geography</option>
                            <option value="History">History</option>
                            <option value="Literature in English">Literature in English</option>
                            <option value="Government">Government</option>
                            <option value="Religious Studies">Religious Studies</option>
                            <option value="Agricultural Science">Agricultural Science</option>
                            <option value="Computer Science">Computer Science</option>
                            <option value="Physical Education">Physical Education</option>
                            <option value="Art">Art</option>
                            <option value="Music">Music</option>
                            <option value="French">French</option>
                            <option value="Yoruba">Yoruba</option>
                            <option value="Hausa">Hausa</option>
                            <option value="Igbo">Igbo</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-bold mb-2">Classes Taught:</label>
                        <select name="classesTaught" class="form-control">
                            <option value="JS1 - Year 7">JS1 - Year 7</option>
                            <option value="JS2 - Year 8">JS2 - Year 8</option>
                            <option value="JS3 - Year 9">JS3 - Year 9</option>
                            <option value="SS1 - Year 10">SS1 - Year 10</option>
                            <option value="SS2 - Year 11">SS2 - Year 11</option>
                            <option value="SS3 - Year 12">SS3 - Year 12</option>
                            <option value="All Junior Classes (JS1-JS3)">All Junior Classes (JS1-JS3)</option>
                            <option value="All Senior Classes (SS1-SS3)">All Senior Classes (SS1-SS3)</option>
                            <option value="All Classes (JS1-SS3)">All Classes (JS1-SS3)</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-bold mb-2">Hire Date:</label>
                        <input type="date" name="hireDate" required class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-bold mb-2">Education Background:</label>
                        <textarea name="educationBackground" required class="form-control"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-bold mb-2">Previous Employment:</label>
                        <textarea name="previousEmployment" required class="form-control"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-bold mb-2">Professional References:</label>
                        <textarea name="professionalReferences" required class="form-control"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-bold mb-2">Contract Type:</label>
                        <select name="contractType" class="form-control">
                            <option value="Permanent">Permanent</option>
                            <option value="Temporary">Temporary</option>
                            <option value="Part-time">Part-time</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-bold mb-2">Photo:</label>
                        <input type="file" name="photo" accept="image/*" required class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-bold mb-2">Biography:</label>
                        <textarea name="biography" required class="form-control"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-bold mb-2">Teaching Philosophy:</label>
                        <textarea name="teachingPhilosophy" required class="form-control"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-bold mb-2">Role:</label>
                        <select name="role" class="form-control">
                            <option value="regular">Regular Teacher</option>
                            <option value="admin">Administrative Teacher</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-bold mb-2">Username:</label>
                        <input type="text" name="username" required class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-bold mb-2">Password:</label>
                        <input type="password" name="password" id="password" required class="form-control">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-400 text-sm font-bold mb-2">Re-enter Password:</label>
                        <input type="password" name="confirm_password" id="confirm_password" required class="form-control" onkeyup="checkPasswordMatch();">
                        <div id="password-match" class="text-sm"></div>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <input type="submit" value="Register Teacher" class="button-primary">
                </div>
            </fieldset>
        </form>
    </div>
    <script>
        function checkSubjectCount(obj) {
            var selectedCount = 0;
            for (var i = 0; i < obj.options.length; i++) {
                if (obj.options[i].selected) selectedCount++;
                if (selectedCount > 4) {
                    alert('You can only select up to 4 subjects.');
                    obj.options[i].selected = false;
                    return false;
                }
            }
        }
    </script>
    <script>
function checkPasswordMatch() {
    var password = document.getElementById("password").value;
    var confirmPassword = document.getElementById("confirm_password").value;

    if (password != confirmPassword) {
        document.getElementById("password-match").style.color = 'red';
        document.getElementById("password-match").innerHTML = "Passwords do not match!";
    } else {
        document.getElementById("password-match").style.color = 'green';
        document.getElementById("password-match").innerHTML = "Passwords match.";
    }
}

function validateForm() {
    var password = document.getElementById("password").value;
    var confirmPassword = document.getElementById("confirm_password").value;

    if (password != confirmPassword) {
        alert("Passwords do not match.");
        return false;
    }
    return true;
}

</script>


</body>
</html>
