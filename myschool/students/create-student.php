<?php
session_start(); // Start or resume the session

// Redirect if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

// Exit if no associated school is found
if (!isset($_SESSION["school_id"])) {
    exit('Error: No associated school found for the user.');
}

// Database connection
require_once '../../connections/db.php';

// Initialize variables to store form data
$fullname = $dob = $gender = $phone = $email = $parentName = $parentPhone = $parentEmail = $address = "";
$emergencyContact = $emergencyPhone = $medicalInfo = $previousSchools = $admissionDate = $ethnicity = $language = "";
$sen = $religion = $diet = $extracurricular = $photo = $username = $password = $class = "";

// Function to generate a unique filename
function generateUniqueFilename($target_dir, $filename) {
    $file_ext = pathinfo($filename, PATHINFO_EXTENSION);
    $file_base = pathinfo($filename, PATHINFO_FILENAME);
    $unique_filename = $filename;
    $counter = 1;
    
    while (file_exists($target_dir . $unique_filename)) {
        $unique_filename = $file_base . '_' . $counter . '.' . $file_ext;
        $counter++;
    }
    
    return $unique_filename;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $target_dir = "../../uploads/";
    $target_file = $target_dir . basename($_FILES["photo"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is an actual image
    $check = getimagesize($_FILES["photo"]["tmp_name"]);
    if ($check !== false) {
        $uploadOk = 1;
    } else {
        $message = "File is not an image.";
        $uploadOk = 0;
    }

    // Generate a unique filename if file already exists
    if (file_exists($target_file)) {
        $target_file = $target_dir . generateUniqueFilename($target_dir, basename($_FILES["photo"]["name"]));
    }

    // Check file size
    if ($_FILES["photo"]["size"] > 500000) {
        $message = "Sorry, your file is too large.";
        $uploadOk = 0;
    }

    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        $message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $message = "Sorry, your file was not uploaded.";
    // if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            // File upload success, proceed with database operations
            $userInfoConn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
            try {
                // Improve username and password security
                if (!preg_match('/^[a-zA-Z0-9]{5,}$/', $_POST['username'])) {
                    throw new Exception("Invalid username. Username must be at least 5 characters long and contain only letters and numbers.");
                }
                if (strlen($_POST['password']) < 8 || !preg_match('/[A-Z]/', $_POST['password']) || !preg_match('/[0-9]/', $_POST['password']) || !preg_match('/[!@#$%^&*]/', $_POST['password'])) {
                    throw new Exception("Invalid password. Password must be at least 8 characters long, contain at least one uppercase letter, one number, and one special character.");
                }
                $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $student_number = mt_rand(100000, 999999);
                $class = $_POST['class'];
                $sql = "INSERT INTO student_info (school_id, fullname, dob, gender, phone, email, parentName, parentPhone, parentEmail, address, emergencyContact, emergencyPhone, medicalInfo, previousSchools, admissionDate, ethnicity, language, sen, religion, diet, extracurricular, photo, username, password, student_number, class) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmt = $userInfoConn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $userInfoConn->error);
                }
                $stmt->bind_param("isssssssssssssssssssssssis", $_SESSION["school_id"], $_POST['fullname'], $_POST['dob'], $_POST['gender'], $_POST['phone'], $_POST['email'], $_POST['parentName'], $_POST['parentPhone'], $_POST['parentEmail'], $_POST['address'], $_POST['emergencyContact'], $_POST['emergencyPhone'], $_POST['medicalInfo'], $_POST['previousSchools'], $_POST['admissionDate'], $_POST['ethnicity'], $_POST['language'], $_POST['sen'], $_POST['religion'], $_POST['diet'], $_POST['extracurricular'], $target_file, $_POST['username'], $hashedPassword, $student_number, $class);
                $stmt->execute();
                $last_id = $stmt->insert_id; // Capture the last inserted ID

                // Generate a unique student code
                $student_code = 'STU_' . dechex(time()) . dechex(rand(1000, 9999));
                $studentSql = "INSERT INTO students (student_id, school_id, username, password, student_number, student_code) VALUES (?, ?, ?, ?, ?, ?)";
                $studentStmt = $userInfoConn->prepare($studentSql);
                $studentStmt->bind_param("iissss", $last_id, $_SESSION["school_id"], $_POST['username'], $hashedPassword, $student_number, $student_code);
                $studentStmt->execute();
                $studentStmt->close();

                $userInfoConn->commit(); // Commit the transaction
                $message = "<p class='bg-green-500 text-white font-bold py-2 px-4 rounded'>Student registered successfully with student number: $student_number.</p>";
            } catch (Exception $e) {
                $userInfoConn->rollback(); // Rollback transaction on failure
                $message = "<p class='bg-red-500 text-white font-bold py-2 px-4 rounded'>Transaction failed: " . $e->getMessage() . "</p>";
            }
        } else {
            $message = "<p class='bg-red-500 text-white font-bold py-2 px-4 rounded'>Sorry, there was an error uploading your file.</p>";
        }
    }
    $userInfoConn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration Form</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
    </style>
</head>
<body class="bg-gray-900">
    <div class="max-w-screen-lg mx-auto p-5">
        <h1 class="text-center text-3xl font-bold text-gray-200 my-6">Student Registration Form</h1>
        <?php if (isset($message)) echo $message; ?>
        <form action="create-student.php" method="post" enctype="multipart/form-data" class="bg-gray-800 shadow-md rounded px-8 pt-6 pb-8 mb-4 card">
            <fieldset class="form-section">
                <legend class="text-xl font-semibold text-gray-200 px-2"><i class="fas fa-user-graduate mr-2"></i>Student Information</legend>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="mb-4">
                        <label for="fullname" class="block text-gray-400 text-sm font-bold mb-2">Full Name:</label>
                        <input type="text" id="fullname" name="fullname" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="mb-4">
                        <label for="dob" class="block text-gray-400 text-sm font-bold mb-2">Date of Birth:</label>
                        <input type="date" id="dob" name="dob" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="mb-4">
                        <label for="gender" class="block text-gray-400 text-sm font-bold mb-2">Gender:</label>
                        <select id="gender" name="gender" required class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="phone" class="block text-gray-400 text-sm font-bold mb-2">Phone Number:</label>
                        <input type="tel" id="phone" name="phone" placeholder="Enter phone number" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="mb-4">
                        <label for="email" class="block text-gray-400 text-sm font-bold mb-2">Email Address:</label>
                        <input type="email" id="email" name="email" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="mb-4">
                        <label for="parentName" class="block text-gray-400 text-sm font-bold mb-2">Parent/Guardian Name:</label>
                        <input type="text" id="parentName" name="parentName" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="mb-4">
                        <label for="parentPhone" class="block text-gray-400 text-sm font-bold mb-2">Parent/Guardian Phone Number:</label>
                        <input type="tel" id="parentPhone" name="parentPhone" placeholder="Enter phone number" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="mb-4">
                        <label for="parentEmail" class="block text-gray-400 text-sm font-bold mb-2">Parent/Guardian Email:</label>
                        <input type="email" id="parentEmail" name="parentEmail" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="mb-4">
                        <label for="address" class="block text-gray-400 text-sm font-bold mb-2">Home Address:</label>
                        <input type="text" id="address" name="address" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="mb-4">
                        <label for="emergencyContact" class="block text-gray-400 text-sm font-bold mb-2">Emergency Contact Name:</label>
                        <input type="text" id="emergencyContact" name="emergencyContact" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="mb-4">
                        <label for="emergencyPhone" class="block text-gray-400 text-sm font-bold mb-2">Emergency Contact Phone:</label>
                        <input type="tel" id="emergencyPhone" name="emergencyPhone" placeholder="Enter phone number" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="mb-4 md:col-span-2">
                        <label for="medicalInfo" class="block text-gray-400 text-sm font-bold mb-2">Medical Conditions (Allergies, medications, etc.):</label>
                        <textarea id="medicalInfo" name="medicalInfo" rows="4" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                    </div>

                    <div class="mb-4 md:col-span-2">
                        <label for="previousSchools" class="block text-gray-400 text-sm font-bold mb-2">Previous Schools Attended:</label>
                        <textarea id="previousSchools" name="previousSchools" rows="4" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="admissionDate" class="block text-gray-400 text-sm font-bold mb-2">Admission Date:</label>
                        <input type="date" id="admissionDate" name="admissionDate" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="mb-4">
                        <label for="class" class="block text-gray-400 text-sm font-bold mb-2">Class:</label>
                        <select id="class" name="class" required class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <option value="">Select Class</option>
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
                        <label for="ethnicity" class="block text-gray-400 text-sm font-bold mb-2">Ethnicity and Race:</label>
                        <input type="text" id="ethnicity" name="ethnicity" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="mb-4">
                        <label for="language" class="block text-gray-400 text-sm font-bold mb-2">First Language:</label>
                        <input type="text" id="language" name="language" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="mb-4">
                        <label for="sen" class="block text-gray-400 text-sm font-bold mb-2">Special Educational Needs (SEN):</label>
                        <textarea id="sen" name="sen" rows="2" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="religion" class="block text-gray-400 text-sm font-bold mb-2">Religious Considerations:</label>
                        <input type="text" id="religion" name="religion" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="mb-4">
                        <label for="diet" class="block text-gray-400 text-sm font-bold mb-2">Dietary Restrictions:</label>
                        <input type="text" id="diet" name="diet" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="mb-4">
                        <label for="extracurricular" class="block text-gray-400 text-sm font-bold mb-2">Extracurricular Interests:</label>
                        <input type="text" id="extracurricular" name="extracurricular" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="mb-4">
                        <label for="photo" class="block text-gray-400 text-sm font-bold mb-2">Upload Photo:</label>
                        <input type="file" id="photo" name="photo" accept="image/*" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="mb-4">
                        <label for="username" class="block text-gray-400 text-sm font-bold mb-2">Username for School Portal:</label>
                        <input type="text" id="username" name="username" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-gray-400 text-sm font-bold mb-2">Password:</label>
                        <input type="password" id="password" name="password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <input type="submit" value="Register Student" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                </div>
            </fieldset>
        </form>
    </div>
</body>
</html>
