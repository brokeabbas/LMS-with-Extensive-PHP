<?php
session_start();
require_once '../../connections/db.php';  // Connection for 'userinfo' database
require_once '../../connections/db_school_data.php';  // Connection for 'school_data' database

// Redirect if not logged in or if school_id is not set
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["school_id"])) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

if (isset($_GET['student_id'])) {
    $student_id = intval($_GET['student_id']);

    // Start transaction
    $userInfoConn->begin_transaction();

    try {
        // Update query to set is_active to 0 in 'student_info'
        $query = "UPDATE student_info SET is_active = 0 WHERE id = ?";
        
        // Prepare and execute the query
        $stmt = $userInfoConn->prepare($query);
        if (!$stmt) {
            throw new Exception("Database prepare error: " . $userInfoConn->error);
        }
        $stmt->bind_param("i", $student_id);
        if (!$stmt->execute()) {
            throw new Exception("Execution error: " . $stmt->error);
        }
        $stmt->close();

        // Commit the transaction
        $userInfoConn->commit();
        echo "<script>alert('Student account has been disabled successfully.'); window.location.href='manage-existing-students.php';</script>";
    } catch (Exception $e) {
        // Rollback the transaction on error
        $userInfoConn->rollback();
        echo "<script>alert('Failed to disable the student account: " . $e->getMessage() . "'); window.location.href='manage-existing-students.php';</script>";
    }
    echo ('error');
}

// Close the database connection
$userInfoConn->close();
?>
