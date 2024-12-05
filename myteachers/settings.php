<?php
session_start();
require_once '../connections/db.php'; // Database connection setup

// Check if the teacher is logged in, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["teacher_id"], $_SESSION["school_id"])) {
    header("location: login_teacher.php");
    exit;
}

$teacher_id = $_SESSION['teacher_id'];
$school_id = $_SESSION['school_id'];
$passwordUpdated = false;
$error = '';

// Handle password update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_password'], $_POST['confirm_password'], $_POST['teacher_code'])) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $teacher_code = $_POST['teacher_code'];

    if (empty($new_password) || empty($confirm_password) || empty($teacher_code)) {
        $error = "Please fill out all fields.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // First, validate the teacher code and current password
        $sql = "SELECT password, teacher_code FROM teacher_users WHERE teacher_id = ? AND school_id = ?";
        if ($stmt = $userInfoConn->prepare($sql)) {
            $stmt->bind_param("ii", $teacher_id, $school_id);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($hashed_password, $stored_teacher_code);
                $stmt->fetch();

                // Verify stored teacher code and provided teacher code
                if ($teacher_code === $stored_teacher_code) {
                    // Prepare to update the new password
                    $update_sql = "UPDATE teacher_users SET password = ? WHERE teacher_id = ? AND school_id = ?";
                    if ($update_stmt = $userInfoConn->prepare($update_sql)) {
                        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                        $update_stmt->bind_param("sii", $new_hashed_password, $teacher_id, $school_id);

                        // Attempt to execute the update statement
                        if ($update_stmt->execute()) {
                            $passwordUpdated = true;
                        } else {
                            $error = "Something went wrong. Please try again later.";
                        }
                        $update_stmt->close();
                    }
                } else {
                    $error = "Invalid teacher code.";
                }
            } else {
                $error = "No account found with that ID.";
            }
            $stmt->close();
        }
    }
    $userInfoConn->close();
}

// Use session variables for messages and display them on the page
$_SESSION['message'] = ['type' => $passwordUpdated ? 'success' : 'error', 'content' => $passwordUpdated ? 'Password updated successfully.' : $error];

// Retrieve any session messages and then clear them
$session_message = null;
if (isset($_SESSION['message'])) {
    $session_message = $_SESSION['message'];
    unset($_SESSION['message']);
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
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(to right, #f3e6ff, #e0f7fa);
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .form-container {
            width: 100%;
            max-width: 500px;
            padding: 2rem;
            background: #ffffff;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            border-radius: 8px;
        }
        .form-title {
            color: #2d3748;
            font-size: 1.5rem;
            font-weight: 700;
        }
        .input-field {
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .input-field:focus {
            border-color: #63b3ed;
            box-shadow: 0 0 0 3px rgba(99, 179, 237, 0.5);
        }
        .submit-button {
            background-color: #4299e1;
            transition: background-color 0.3s ease;
        }
        .submit-button:hover {
            background-color: #3182ce;
        }
        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        .message-success {
            background-color: #d4edda;
            color: #155724;
        }
        .message-error {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="form-container">
        <h1 class="form-title mb-4">Settings</h1>
        <?php if(isset($session_message)): ?>
            <div class="message <?= $session_message['type'] === 'success' ? 'message-success' : 'message-error' ?>">
                <?= htmlspecialchars($session_message['content']); ?>
            </div>
        <?php endif; ?>
        <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-4">
                <label for="new_password" class="block text-gray-700 text-sm font-bold mb-2">New Password:</label>
                <input type="password" id="new_password" name="new_password" class="input-field shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-6">
                <label for="confirm_password" class="block text-gray-700 text-sm font-bold mb-2">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" class="input-field shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-6">
                <label for="teacher_code" class="block text-gray-700 text-sm font-bold mb-2">Enter Teacher Code:</label>
                <input type="text" id="teacher_code" name="teacher_code" class="input-field shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="submit-button text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update Password</button>
            </div>
        </form>
        <div class="mt-6">
            <h2 class="text-lg font-semibold">Help & Support</h2>
            <p class="text-sm text-gray-600">If you need help understanding the features or navigating the site, click below to learn more.</p>
            <a href="/help" class="text-blue-500 hover:underline">Learn more</a>
        </div>
    </div>
</body>
</html>
