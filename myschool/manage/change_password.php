<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

// Database connection
require_once '../../connections/db.php';

$student_id = $_GET['student_id'] ?? null;
if ($student_id === null) {
    exit('Error: No student ID provided.');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $school_code = $_POST['school_code'];

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/", $new_password)) {
        $error = "Password must be at least 8 characters long and contain both letters and numbers.";
    } else {
        // Verify school code
        $schoolSql = "SELECT school_code FROM schools WHERE school_id = (SELECT school_id FROM student_info WHERE id = ?)";
        if ($schoolStmt = $userInfoConn->prepare($schoolSql)) {
            $schoolStmt->bind_param("i", $student_id);
            $schoolStmt->execute();
            $schoolStmt->bind_result($db_school_code);
            $schoolStmt->fetch();
            $schoolStmt->close();

            if ($db_school_code === $school_code) {
                // Update the password in the database
                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                $updateSql1 = "UPDATE student_info SET password = ? WHERE id = ?";
                $updateSql2 = "UPDATE students SET password = ? WHERE student_id = ?";
                
                // Begin transaction
                $userInfoConn->begin_transaction();
                try {
                    // Update student_info table
                    $stmt1 = $userInfoConn->prepare($updateSql1);
                    $stmt1->bind_param("si", $hashedPassword, $student_id);
                    $stmt1->execute();
                    
                    // Update students table
                    $stmt2 = $userInfoConn->prepare($updateSql2);
                    $stmt2->bind_param("si", $hashedPassword, $student_id);
                    $stmt2->execute();
                    
                    // Commit transaction
                    $userInfoConn->commit();
                    $success = "Password updated successfully.";
                    
                    $stmt1->close();
                    $stmt2->close();
                } catch (Exception $e) {
                    // Rollback transaction on failure
                    $userInfoConn->rollback();
                    $error = "Failed to update password: " . $e->getMessage();
                }
            } else {
                $error = "Invalid school code.";
            }
        }
    }
}

$sql = "SELECT fullname FROM student_info WHERE id = ?";
$stmt = $userInfoConn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

$stmt->close();
$userInfoConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <script>
        function togglePasswordVisibility() {
            const passwordField = document.getElementById('new_password');
            const confirmPasswordField = document.getElementById('confirm_password');
            const passwordToggle = document.getElementById('password_toggle');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                confirmPasswordField.type = 'text';
                passwordToggle.textContent = 'Hide';
            } else {
                passwordField.type = 'password';
                confirmPasswordField.type = 'password';
                passwordToggle.textContent = 'Show';
            }
        }
    </script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

<div class="bg-white p-8 rounded-lg shadow-md w-full max-w-sm">
    <h2 class="text-2xl font-bold mb-6 text-center">Change Password for <?php echo htmlspecialchars($student['fullname']); ?></h2>
    <?php if (!empty($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo $error; ?></span>
        </div>
    <?php elseif (!empty($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo $success; ?></span>
        </div>
    <?php endif; ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?student_id=" . htmlspecialchars($student_id); ?>" method="post">
        <div class="mb-4">
            <label for="new_password" class="block text-gray-700 text-sm font-bold mb-2">New Password:</label>
            <input type="password" id="new_password" name="new_password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="mb-4">
            <label for="confirm_password" class="block text-gray-700 text-sm font-bold mb-2">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="mb-4">
            <label for="school_code" class="block text-gray-700 text-sm font-bold mb-2">School Code:</label>
            <input type="text" id="school_code" name="school_code" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="flex items-center justify-between">
            <button type="button" id="password_toggle" onclick="togglePasswordVisibility()" class="text-blue-500 hover:text-blue-700">Show</button>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Change Password
            </button>
        </div>
    </form>
</div>

</body>
</html>
