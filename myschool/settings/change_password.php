<?php
session_start();
require_once '../../connections/db.php'; // Include the database connection

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $school_code = $_POST['school_code'];

    // Check if new password and confirm password match
    if ($new_password !== $confirm_password) {
        echo "Passwords do not match.";
        exit;
    }

    // Validate the school code
    $codeQuery = $userInfoConn->prepare("SELECT school_code FROM schools WHERE school_id = ?");
    $codeQuery->bind_param("i", $_SESSION['school_id']);
    $codeQuery->execute();
    $codeResult = $codeQuery->get_result();
    $codeRow = $codeResult->fetch_assoc();
    $codeQuery->close();

    if ($school_code !== $codeRow['school_code']) {
        echo "Invalid school code.";
        exit;
    }

    // If validation passes, update the password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $updateQuery = $userInfoConn->prepare("UPDATE school_users SET password = ? WHERE user_id = ?");
    $updateQuery->bind_param("si", $hashed_password, $_SESSION['user_id']);
    $updateQuery->execute();
    $updateQuery->close();

    echo "Password updated successfully.";
}
?>
