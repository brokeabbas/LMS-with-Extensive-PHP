<?php
session_start();
require_once '../connections/db.php';  // Ensure this path is correct
require_once '../connections/db_school_data.php';  // Database connection for school_data
require_once '../connections/db_support.php';  // Database connection for school_support

$login_err = $username = $password = $student_code = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $student_code = trim($_POST["student_code"]);

    if (empty($username) || empty($password) || empty($student_code)) {
        $login_err = "Please enter all fields.";
    } else {
        $sql = "SELECT s.student_id, s.school_id, s.username, s.password, s.student_code, si.is_active 
                FROM students s 
                JOIN student_info si ON s.student_id = si.id 
                WHERE s.username = ? AND s.student_code = ?";
        if ($stmt = $userInfoConn->prepare($sql)) {
            $stmt->bind_param("ss", $username, $student_code);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($student_id, $school_id, $fetched_username, $hashed_password, $fetched_student_code, $is_active);
                    if ($stmt->fetch()) {
                        if ($is_active == 0) {
                            $login_err = "Your account is restricted. Please contact your school admin.";
                        } elseif (password_verify($password, $hashed_password)) {
                            // Verify school subscription before logging in
                            $schoolSql = "SELECT school_email FROM schools WHERE school_id = ?";
                            if ($schoolStmt = $userInfoConn->prepare($schoolSql)) {
                                $schoolStmt->bind_param("i", $school_id);
                                $schoolStmt->execute();
                                $schoolStmt->bind_result($school_email);
                                if ($schoolStmt->fetch() && $school_email) {
                                    // Check subscription status
                                    $subSql = "SELECT subscription_status FROM subscribers WHERE email = ?";
                                    if ($subStmt = $schSuppConn->prepare($subSql)) {
                                        $subStmt->bind_param("s", $school_email);
                                        $subStmt->execute();
                                        $subStmt->bind_result($subscription_status);
                                        if ($subStmt->fetch()) {
                                            if ($subscription_status == 1) {
                                                $_SESSION["loggedin"] = true;
                                                $_SESSION["student_id"] = $student_id;
                                                $_SESSION["school_id"] = $school_id;
                                                header("location: mystudy.php");
                                                exit;
                                            } else {
                                                $login_err = "Login restricted. Contact School Admin.";
                                            }
                                        } else {
                                            $login_err = "No valid subscription found.";
                                        }
                                        $subStmt->close();
                                    }
                                }
                                $schoolStmt->close();
                            }
                        } else {
                            $login_err = "Invalid username, student code, or password.";
                        }
                    }
                } else {
                    $login_err = "Invalid username, student code, or password.";
                }
                $stmt->close();
            } else {
                $login_err = "Oops! Something went wrong. Please try again later.";
            }
        }
        $userInfoConn->close();
        $schoolDataConn->close();
        $schSuppConn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Educational Platform</title>
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
    <h2 class="text-3xl font-bold mb-6 text-center">Student Login</h2>
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
            <label for="student_code" class="block mb-2 text-sm font-bold text-gray-700">Student Code:</label>
            <input type="text" name="student_code" id="student_code" class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500" required>
        </div>
        <div class="mb-4 text-right">
            <a href="" onclick="forgotPassword()" class="text-sm font-semibold text-pink-500 hover:text-pink-600">Forgot Password?</a>
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