<?php
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../registration/myschool_login.php");
    exit;
}

require_once '../connections/db.php'; // Include the database connection

$school_id = $_SESSION['school_id'] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $school_code = $_POST['school_code'];

    // Validate password strength
    if (strlen($new_password) < 8 || 
        !preg_match('/[A-Z]/', $new_password) || 
        !preg_match('/[a-z]/', $new_password) || 
        !preg_match('/[0-9]/', $new_password) || 
        !preg_match('/[!@#$%^&*]/', $new_password)) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.'];
        header("Location: " . htmlspecialchars($_SERVER["PHP_SELF"]));
        exit;
    }

    if ($new_password !== $confirm_password) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Passwords do not match.'];
        header("Location: " . htmlspecialchars($_SERVER["PHP_SELF"]));
        exit;
    }

    // Ensure the school code is correct (you may need to adjust this part based on how you store and validate the school code)
    $stmt = $userInfoConn->prepare("SELECT * FROM schools WHERE school_code = ? AND school_id = ?");
    $stmt->bind_param("si", $school_code, $school_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the password in the database
        $update_stmt = $userInfoConn->prepare("UPDATE school_users SET password = ? WHERE school_id = ?");
        $update_stmt->bind_param("si", $hashed_password, $_SESSION['id']);

        if ($update_stmt->execute()) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Password changed successfully.'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Failed to change the password. Please try again later.'];
        }

        $update_stmt->close();
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Invalid school code.'];
    }

    $stmt->close();
    header("Location: " . htmlspecialchars($_SERVER["PHP_SELF"]));
    exit;
}

$userInfoConn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - School Management System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(135deg, #1f2937, #3b82f6);
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
            color: #f8fafc;
            font-family: 'Roboto', sans-serif;
        }
        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .card {
            background-color: #2d3748;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 1rem;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        .input-field {
            background-color: #1a202c;
            color: #a0aec0;
            border: 1px solid #4a5568;
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
        }
        .input-field:focus {
            border-color: #63b3ed;
            box-shadow: 0 0 0 1px #63b3ed;
        }
        .button-blue {
            background-color: #63b3ed;
            color: #fff;
            transition: background-color 0.2s ease-in-out, transform 0.2s ease-in-out;
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-block;
            margin-top: 10px;
        }
        .button-blue:hover {
            background-color: #3182ce;
            transform: translateY(-2px);
        }
        .button-red {
            background-color: #ef4444;
            color: #fff;
            transition: background-color 0.2s ease-in-out, transform 0.2s ease-in-out;
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-block;
            margin-top: 10px;
        }
        .button-red:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gray-900">
    <header class="bg-blue-800 text-white p-4">
        <h1 class="text-xl font-bold">ADMIN | Settings</h1>
    </header>

    <main class="container mx-auto mt-6 p-4">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="p-4 mb-4 text-sm <?php echo $_SESSION['message']['type'] === 'success' ? 'text-green-700 bg-green-100' : 'text-red-700 bg-red-100'; ?> rounded-lg" role="alert">
                <?= htmlspecialchars($_SESSION['message']['text']) ?>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <!-- Account Settings -->
        <section class="card">
            <h2 class="text-lg font-semibold mb-4">Account Settings</h2>
            <form method="post" action="">
                <div class="mb-4">
                    <label for="new_password" class="block text-sm font-medium">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required class="input-field">
                </div>
                <div class="mb-4">
                    <label for="confirm_password" class="block text-sm font-medium">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required class="input-field">
                </div>
                <div class="mb-4">
                    <label for="school_code" class="block text-sm font-medium">School Code:</label>
                    <input type="text" id="school_code" name="school_code" required class="input-field">
                </div>
                <button type="submit" class="button-blue">Change</button>
            </form>
        </section>
    </main>
</body>
</html>
