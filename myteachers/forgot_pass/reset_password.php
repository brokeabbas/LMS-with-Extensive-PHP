<?php
session_start();
require_once '../../connections/db.php';  // Ensure this path is correct

if (!isset($_SESSION['username']) || !isset($_SESSION['email_verified'])) {
    header("Location: forgot_password.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_SESSION['username'];
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    $teacher_code = trim($_POST['teacher_code']);
    $school_code = trim($_POST['school_code']);

    // Check if passwords match
    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/", $new_password)) {
        $error = "Password must be at least 8 characters long and contain both letters and numbers.";
    } else {
        // Verify school code
        $schoolSql = "SELECT school_code FROM schools WHERE school_id = (SELECT school_id FROM teacher_users WHERE username = ?)";
        if ($schoolStmt = $userInfoConn->prepare($schoolSql)) {
            $schoolStmt->bind_param("s", $username);
            $schoolStmt->execute();
            $schoolStmt->bind_result($db_school_code);
            $schoolStmt->fetch();
            $schoolStmt->close();

            if ($db_school_code === $school_code) {
                // Update the password in the database
                $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $updateSql = "UPDATE teacher_users SET password = ? WHERE username = ? AND teacher_code = ?";
                if ($updateStmt = $userInfoConn->prepare($updateSql)) {
                    $updateStmt->bind_param("sss", $new_password_hash, $username, $teacher_code);
                    $updateStmt->execute();
                    $updateStmt->close();

                    $success = "Password has been reset successfully!";
                    unset($_SESSION['username']);
                    unset($_SESSION['email_verified']);
                }
            } else {
                $error = "Invalid school code.";
            }
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
    <title>Reset Password</title>
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
    <h2 class="text-2xl font-bold mb-6 text-center">Reset Password</h2>
    <?php if (!empty($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo $error; ?></span>
        </div>
    <?php elseif (!empty($success)): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo $success; ?></span>
        </div>
    <?php endif; ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="mb-4">
            <label for="new_password" class="block text-gray-700 text-sm font-bold mb-2">New Password</label>
            <input type="password" id="new_password" name="new_password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="mb-4">
            <label for="confirm_password" class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="mb-4">
            <label for="teacher_code" class="block text-gray-700 text-sm font-bold mb-2">Teacher Code</label>
            <input type="text" id="teacher_code" name="teacher_code" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="mb-4">
            <label for="school_code" class="block text-gray-700 text-sm font-bold mb-2">School Code</label>
            <input type="text" id="school_code" name="school_code" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="mb-4 flex items-center">
            <input type="checkbox" id="show_password" onclick="togglePasswordVisibility()" class="mr-2">
            <label for="show_password" id="password_toggle" class="text-gray-700 text-sm">Show</label>
        </div>
        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Reset Password</button>
    </form>
</div>

</body>
</html>
