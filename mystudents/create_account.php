<?php
session_start(); // Start or resume the session

require_once '../connections/db.php'; // Include database connection file

$schoolName = '';
$schoolCode = '';
$error = '';
$successMessage = '';

// Function to verify the school code and fetch school name
function verifySchoolCode($conn, $schoolCode) {
    $sql = "SELECT school_id, school_name FROM schools WHERE school_code = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $schoolCode);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $schoolId, $schoolName);
        if (mysqli_stmt_fetch($stmt)) {
            mysqli_stmt_close($stmt);
            return ['schoolId' => $schoolId, 'schoolName' => $schoolName];
        } else {
            mysqli_stmt_close($stmt);
            return false; // Return false if no school is found
        }
    } else {
        return false; // Return false if the SQL statement could not be prepared
    }
}

// Check for POST request to verify school code
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['checkSchoolCode'])) {
    $schoolCode = mysqli_real_escape_string($conn, $_POST['schoolCode']);
    $schoolDetails = verifySchoolCode($conn, $schoolCode);
    if (!$schoolDetails) {
        $error = "No school found with that code.";
    } else {
        $schoolName = $schoolDetails['schoolName']; // Store the school name if found
    }
}

// Check for POST request to register student
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['registerStudent'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirmPassword = mysqli_real_escape_string($conn, $_POST['confirmPassword']);
    $schoolCode = mysqli_real_escape_string($conn, $_POST['schoolCode']);

    if ($password !== $confirmPassword) {
        $error = "Password did not match."; // Check if passwords match
    } else {
        $schoolDetails = verifySchoolCode($conn, $schoolCode);
        if ($schoolDetails) {
            $schoolId = $schoolDetails['schoolId'];
            $studentCode = uniqid('STU_'); // Generate a unique student code
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash the password for security

            // Insert into students table
            $insertStudentSql = "INSERT INTO students (school_id, username, password, student_code) VALUES (?, ?, ?, ?)";
            if ($insertStmt = mysqli_prepare($conn, $insertStudentSql)) {
                mysqli_stmt_bind_param($insertStmt, "isss", $schoolId, $username, $hashedPassword, $studentCode);
                if (mysqli_stmt_execute($insertStmt)) {
                    $successMessage .= "<p>Student account created successfully.</p>";
                } else {
                    $error .= "<p>Error: Could not execute the student account insert. " . mysqli_error($conn) . "</p>";
                }
                mysqli_stmt_close($insertStmt);
            } else {
                $error .= "<p>Error preparing the student account insert statement.</p>";
            }

            // Insert into student_info table
            $insertStudentInfoSql = "INSERT INTO student_info (school_id, student_code, username, password, fullname, dob, gender, phone, email, parent_name, parent_phone, parent_email, address, emergency_contact, emergency_phone, medical_info, previous_schools, admission_date, ethnicity, language, sen, religion, diet, extracurricular, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            if ($insertInfoStmt = mysqli_prepare($conn, $insertStudentInfoSql)) {
                mysqli_stmt_bind_param($insertInfoStmt, "issssssssssssssssssssssss", 
                    $schoolId, $studentCode, $username, $hashedPassword, 
                    $_POST['fullname'], $_POST['dob'], $_POST['gender'], $_POST['phone'], $_POST['email'],
                    $_POST['parentName'], $_POST['parentPhone'], $_POST['parentEmail'], $_POST['address'],
                    $_POST['emergencyContact'], $_POST['emergencyPhone'], $_POST['medicalInfo'], $_POST['previousSchools'],
                    $_POST['admissionDate'], $_POST['ethnicity'], $_POST['language'], $_POST['sen'], $_POST['religion'],
                    $_POST['diet'], $_POST['extracurricular'], $_FILES['photo']['name']
                );
                if (mysqli_stmt_execute($insertInfoStmt)) {
                    $successMessage .= "<p>Student information registered successfully.</p>";
                } else {
                    $error .= "<p>Error: Could not execute the student info insert. " . mysqli_error($conn) . "</p>";
                }
                mysqli_stmt_close($insertInfoStmt);
            } else {
                $error .= "<p>Error preparing the student info insert statement.</p>";
            }
        } else {
            $error .= "<p>No school found with the provided code.</p>";
        }
    }
}

mysqli_close($conn); // Close connection

// Output any success or error messages
echo !empty($successMessage) ? $successMessage : $error;
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Student Account</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="../IMAGES/3.png" type="image/x-icon">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-8">
        <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-xl">
            <h2 class="text-2xl font-bold mb-6 text-gray-900 text-center">Student Registration</h2>

            <!-- Display the school name if school code is valid -->
            <?php if (!empty($schoolName)): ?>
                <div class="mb-4 text-green-500">School: <?= htmlspecialchars($schoolName) ?></div>
            <?php endif; ?>

            <!-- Error message display -->
            <?php if (!empty($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <!-- School code field -->
                <div class="mb-4">
                    <label for="schoolCode" class="block text-gray-700 text-sm font-bold mb-2">School Code:</label>
                    <input type="text" id="schoolCode" name="schoolCode" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="<?= $schoolCode ?>">
                </div>

                <!-- Check school code button -->
                <div class="mb-4">
                    <button type="submit" name="checkSchoolCode" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Check School Code</button>
                </div>

                <!-- Only show the registration form if the school code has been verified -->
                <?php if (!empty($schoolName)): ?>
                    <div class="mb-4">
                        <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username:</label>
                        <input type="text" id="username" name="username" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
                        <input type="password" id="password" name="password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-6">
                        <label for="confirmPassword" class="block text-gray-700 text-sm font-bold mb-2">Confirm Password:</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="flex items-center justify-between">
                        <button type="submit" name="registerStudent" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Register</button>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</body>
</html>
