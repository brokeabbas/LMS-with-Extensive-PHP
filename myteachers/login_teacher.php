<?php
session_start();
require_once '../connections/db.php'; // Database connection for userinfo
require_once '../connections/db_school_data.php'; // Database connection for school_data (if necessary)
require_once '../connections/db_support.php'; // Database connection for school_support

$login_err = $username = $password = $teacher_code = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($userInfoConn, $_POST["username"]);
    $password = mysqli_real_escape_string($userInfoConn, $_POST["password"]);
    $teacher_code = mysqli_real_escape_string($userInfoConn, $_POST["teacher_code"]);

    if (empty($username) || empty($password) || empty($teacher_code)) {
        $login_err = "Please enter all fields.";
    } else {
        $sql = "SELECT teacher_id, school_id, username, password FROM teacher_users WHERE username = ? AND teacher_code = ?";
        if ($stmt = $userInfoConn->prepare($sql)) {
            $stmt->bind_param("ss", $username, $teacher_code);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($teacher_id, $school_id, $fetched_username, $hashed_password);
                $stmt->fetch();
                if (password_verify($password, $hashed_password)) {
                    // Fetch school_email from schools table
                    $schoolSql = "SELECT school_email FROM schools WHERE school_id = ?";
                    if ($schoolStmt = $userInfoConn->prepare($schoolSql)) {
                        $schoolStmt->bind_param("i", $school_id);
                        $schoolStmt->execute();
                        $schoolStmt->bind_result($school_email);
                        $schoolStmt->fetch();
                        $schoolStmt->close();

                        // Check the subscription status
                        $subSql = "SELECT subscription_status FROM subscribers WHERE email = ?";
                        if ($subStmt = $schSuppConn->prepare($subSql)) {
                            $subStmt->bind_param("s", $school_email);
                            $subStmt->execute();
                            $subStmt->bind_result($subscription_status);
                            $subStmt->fetch();

                            if ($subscription_status == 1) {
                                // Set session variables and redirect
                                $_SESSION["loggedin"] = true;
                                $_SESSION["teacher_id"] = $teacher_id;
                                $_SESSION["school_id"] = $school_id;
                                header("location: myteach.php");
                                exit;
                            } else {
                                $login_err = "Login restricted, please contact your school administrator to renew the subscription.";
                            }
                            $subStmt->close();
                        }
                    }
                } else {
                    $login_err = "Invalid username, teacher code, or password.";
                }
            } else {
                $login_err = "Invalid username, teacher code, or password.";
            }
            $stmt->close();
        }
        $userInfoConn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - School Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="../IMAGES/3.png" type="image/x-icon">
    <script>
        function forgotPassword() {
            alert("Please contact your School Admin for password recovery.");
        }
    </script>
    <style>
        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        body {
            background: linear-gradient(90deg, #1a2a6c, #b21f1f, #fdbb2d);
            background-size: 200% 200%;
            animation: gradientBG 10s ease infinite;
            font-family: 'Nunito', sans-serif;
        }
    </style>
</head>
<body class="flex items-center justify-center h-screen">

<div class="bg-white p-10 rounded-lg shadow-md w-full max-w-md">
    <div class="flex justify-center mb-6">
        <img src="../IMAGES/3.png" alt="School Logo" class="h-20 w-20">
    </div>
    <h2 class="text-3xl font-bold mb-6 text-center">Teacher Login</h2>
    <?php
    if (!empty($login_err)) {
        echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">' . $login_err . '</div>';
    }
    ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="mb-4">
            <label for="username" class="block mb-2 text-sm font-bold text-gray-700">Username:</label>
            <input type="text" name="username" id="username" class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500" required>
        </div>
        <div class="mb-4">
            <label for="password" class="block mb-2 text-sm font-bold text-gray-700">Password:</label>
            <input type="password" name="password" id="password" class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500" required>
        </div>
        <div class="mb-4">
            <label for="teacher_code" class="block mb-2 text-sm font-bold text-gray-700">Teacher Code:</label>
            <input type="text" name="teacher_code" id="teacher_code" class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500" required>
        </div>
        <div class="mb-4 text-right">
            <a href="forgot_pass/forgot_password.php" class="text-sm font-semibold text-pink-500 hover:text-pink-600">Forgot Password?</a>
        </div>
        <div class="flex items-center justify-between">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Login
            </button>
        </div>
    </form>
</div>

</body>
</html>
