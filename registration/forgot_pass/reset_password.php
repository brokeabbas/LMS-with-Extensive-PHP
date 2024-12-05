<?php
session_start();
require_once '../../connections/db.php';  // Connection for 'userinfo' database

$username = $_GET['username'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $school_code = $_POST['school_code'];

    // Check if new password and confirm password match
    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if the password meets the criteria
        if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^a-zA-Z\d]).{8,}$/', $new_password)) {
            $error = "Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.";
        } else {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Verify school code
            $stmt = $userInfoConn->prepare("SELECT school_code FROM schools WHERE school_id = (SELECT school_id FROM school_users WHERE username = ?)");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                if ($row['school_code'] === $school_code) {
                    // Update the password in the database
                    $updateStmt = $userInfoConn->prepare("UPDATE school_users SET password = ? WHERE username = ?");
                    $updateStmt->bind_param("ss", $hashed_password, $username);
                    $updateStmt->execute();
                    $updateStmt->close();

                    $success = "Password has been reset successfully!";
                } else {
                    $error = "Invalid school code.";
                }
            } else {
                $error = "Username not found.";
            }

            $stmt->close();
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        .password-requirements {
            display: none;
            list-style-type: none;
            padding-left: 0;
        }
        .password-requirements li {
            color: red;
        }
        .password-requirements.valid li {
            color: green;
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md max-w-sm w-full">
        <h2 class="text-2xl font-bold mb-6 text-center">Reset Password</h2>
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php elseif (!empty($success)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                <span class="block sm:inline"><?php echo $success; ?></span>
            </div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="resetPasswordForm">
            <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
            <div class="mb-4">
                <label for="new_password" class="block text-gray-700 text-sm font-bold mb-2">New Password</label>
                <input type="password" id="new_password" name="new_password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <button type="button" onclick="togglePasswordVisibility('new_password')" class="mt-2 text-blue-500">Show Password</button>
                <ul class="password-requirements" id="password-requirements">
                    <li id="length">At least 8 characters long</li>
                    <li id="uppercase">At least one uppercase letter</li>
                    <li id="lowercase">At least one lowercase letter</li>
                    <li id="number">At least one number</li>
                    <li id="special">At least one special character</li>
                </ul>
            </div>
            <div class="mb-4">
                <label for="confirm_password" class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <button type="button" onclick="togglePasswordVisibility('confirm_password')" class="mt-2 text-blue-500">Show Password</button>
            </div>
            <div class="mb-4">
                <label for="school_code" class="block text-gray-700 text-sm font-bold mb-2">School Code</label>
                <input type="text" id="school_code" name="school_code" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Reset Password</button>
        </form>
    </div>
    <script>
        function togglePasswordVisibility(id) {
            const input = document.getElementById(id);
            if (input.type === "password") {
                input.type = "text";
            } else {
                input.type = "password";
            }
        }

        const passwordInput = document.getElementById('new_password');
        const requirements = document.getElementById('password-requirements');
        const length = document.getElementById('length');
        const uppercase = document.getElementById('uppercase');
        const lowercase = document.getElementById('lowercase');
        const number = document.getElementById('number');
        const special = document.getElementById('special');

        passwordInput.addEventListener('focus', () => {
            requirements.style.display = 'block';
        });

        passwordInput.addEventListener('blur', () => {
            requirements.style.display = 'none';
        });

        passwordInput.addEventListener('input', () => {
            const value = passwordInput.value;
            length.classList.toggle('valid', value.length >= 8);
            uppercase.classList.toggle('valid', /[A-Z]/.test(value));
            lowercase.classList.toggle('valid', /[a-z]/.test(value));
            number.classList.toggle('valid', /\d/.test(value));
            special.classList.toggle('valid', /[^a-zA-Z\d]/.test(value));
        });
    </script>
</body>
</html>
