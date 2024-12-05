<?php
session_start(); // Start session

// Include database connection settings
require_once '../connections/db.php';  // Connection for 'userinfo' database
require_once '../connections/db_support.php'; // Connection for 'school_support' database

// Initialize an error message variable
$login_err = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assign and sanitize form values
    $username = mysqli_real_escape_string($userInfoConn, $_POST['username']);
    $password = mysqli_real_escape_string($userInfoConn, $_POST['password']);

    // Prepare SQL statement to fetch user info
    $stmt = $userInfoConn->prepare("SELECT user_id, school_id, username, password FROM school_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Fetch school email using school_id
            $schoolStmt = $userInfoConn->prepare("SELECT school_email FROM schools WHERE school_id = ?");
            $schoolStmt->bind_param("i", $user['school_id']);
            $schoolStmt->execute();
            $schoolResult = $schoolStmt->get_result();
            if ($schoolResult->num_rows === 1) {
                $schoolInfo = $schoolResult->fetch_assoc();
                $schoolEmail = $schoolInfo['school_email'];

                // Check subscription status using school_email
                $subStmt = $schSuppConn->prepare("SELECT subscription_status FROM subscribers WHERE email = ?");
                $subStmt->bind_param("s", $schoolEmail);
                $subStmt->execute();
                $subResult = $subStmt->get_result();
                if ($subResult->num_rows === 1) {
                    $subscription = $subResult->fetch_assoc();
                    if ($subscription['subscription_status'] == 1) {
                        // Subscription is active, proceed with login
                        $_SESSION["loggedin"] = true;
                        $_SESSION["user_id"] = $user['user_id'];
                        $_SESSION["school_id"] = $user['school_id'];
                        $_SESSION["username"] = $user['username'];
                        
                        // Redirect user to welcome page
                        header("location: ../myschool/dashboard.php");
                        exit;
                    } else {
                        // Subscription is inactive, redirect to renewal page with email
                        header("location: ../registration/subscription/renew_plan.php?email=" . urlencode($schoolEmail));
                        exit;
                    }
                } else {
                    // No valid subscription found
                    $login_err = "No valid subscription found for this school.";
                }
            } else {
                $login_err = "Failed to retrieve school information.";
            }
        } else {
            // Password is not valid
            $login_err = "Invalid username or password.";
        }
    } else {
        // Username doesn't exist
        $login_err = "Invalid username or password.";
    }

    // Close statements and connections
    $stmt->close();
    $userInfoConn->close();
    $schSuppConn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Access Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="icon" href="../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background-color: #edf2f7; /* light blue background */
        }
        .login-box {
            box-shadow: 0 10px 25px 0 rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="flex items-center justify-center h-screen bg-blue-50">

    <div class="login-box p-8 bg-white rounded-lg max-w-sm w-full">
        <div class="flex justify-center mb-6">
            <img src="../IMAGES/3.png" alt="School Logo" class="h-16 w-16">
        </div>
        <h2 class="text-2xl text-center text-gray-700 font-bold mb-8">School Login</h2>
        
        <!-- Display error message if any -->
        <?php if(!empty($login_err)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"><?php echo $login_err; ?></span>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-6">
            <div>
                <label for="username" class="text-sm font-medium text-gray-700">Username</label>
                <input type="text" id="username" name="username" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" placeholder="Enter username">
            </div>
            <div>
                <label for="password" class="text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" name="password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm" placeholder="Enter password">
            </div>
            <div class="flex items-center justify-between">
                <div class="text-sm">
                    <a href="forgot_pass/forgot_password.php" class="font-medium text-blue-600 hover:text-blue-500">
                        Forgot your password?
                    </a>
                </div>
            </div>
            <div>
                <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Sign in
                </button>
            </div>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm">Need an account? <a href="subscription/subscribe.php" class="text-blue-600 hover:text-blue-500">Sign up</a></p>
        </div>
    </div>

</body>
</html>
