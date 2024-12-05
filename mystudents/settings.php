<?php
session_start();
require_once '../connections/db.php'; // Database connection setup

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["student_id"])) {
    header("location: login_student.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$school_id = $_SESSION['school_id'];
$passwordUpdated = false;
$error = '';

// Handle password update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_password'], $_POST['confirm_password'], $_POST['student_code'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $student_code = $_POST['student_code'];

    if (empty($new_password) || empty($confirm_password) || empty($student_code)) {
        $error = "Please fill out all fields.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Verify student code
        $code_sql = "SELECT student_code FROM students WHERE student_id = ? AND school_id = ?";
        if ($code_stmt = $userInfoConn->prepare($code_sql)) {
            $code_stmt->bind_param("ii", $student_id, $school_id);
            $code_stmt->execute();
            $code_stmt->bind_result($correct_code);
            $code_stmt->fetch();
            $code_stmt->close();

            if ($student_code == $correct_code) {
                // Student code is correct, proceed with updating the password
                $update_sql = "UPDATE students SET password = ? WHERE student_id = ? AND school_id = ?";
                if ($update_stmt = $userInfoConn->prepare($update_sql)) {
                    $param_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_stmt->bind_param("sii", $param_password, $student_id, $school_id);

                    if ($update_stmt->execute()) {
                        $passwordUpdated = true;
                    } else {
                        $error = "Something went wrong. Please try again later.";
                    }
                    $update_stmt->close();
                }
            } else {
                $error = "Invalid student code.";
            }
        } else {
            $error = "Error preparing the statement: " . $userInfoConn->error;
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
    <title>Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            width: 100%;
            max-width: 500px;
        }
        .header {
            text-align: center;
            margin-bottom: 1rem;
        }
        .header h1 {
            font-size: 2rem;
            color: #fff;
        }
        .notification {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }
        .notification.success {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .notification.error {
            background-color: #f8d7da;
            color: #842029;
        }
        label {
            color: #fff;
        }
        input, button {
            width: 100%;
            padding: 0.75rem;
            margin-top: 0.5rem;
            border-radius: 8px;
            border: none;
        }
        input {
            background: rgba(255, 255, 255, 0.8);
            color: #333;
        }
        input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(130, 170, 255, 0.5);
        }
        button {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: #fff;
            cursor: pointer;
            transition: background 0.3s, transform 0.3s;
        }
        button:hover {
            background: linear-gradient(to right, #5a10ba, #1f64e0);
            transform: translateY(-2px);
        }
        .help-section {
            margin-top: 2rem;
            text-align: center;
        }
        .help-section a {
            color: #a0d2eb;
            text-decoration: underline;
        }
        .help-section a:hover {
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Settings</h1>
        </div>
        <?php if ($passwordUpdated): ?>
            <div class="notification success">
                Password updated successfully.
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="notification error">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-4">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required>
            </div>
            <div class="mb-4">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="mb-4">
                <label for="student_code">Student Code:</label>
                <input type="text" id="student_code" name="student_code" required>
            </div>
            <button type="submit">Update Password</button>
        </form>
        <div class="help-section">
            <h2>Help & Support</h2>
            <p>If you need help understanding the features or navigating the site, click below to learn more.</p>
            <a href="/help">Learn more</a>
        </div>
    </div>
</body>
</html>
