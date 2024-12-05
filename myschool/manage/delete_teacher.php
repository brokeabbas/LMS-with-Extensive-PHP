<?php
session_start();
require_once '../../connections/db.php'; // Ensure this path is correct

// Redirect if not logged in or if school_id is not set
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["school_id"])) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

// Delete teacher action
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $teacher_id = intval($_GET['id']);

    // Start transaction
    $userInfoConn->begin_transaction();

    try {
        // First delete from teacher_users table to maintain referential integrity
        $stmt1 = $userInfoConn->prepare("DELETE FROM teacher_users WHERE teacher_id = ?");
        $stmt1->bind_param("i", $teacher_id);
        $stmt1->execute();
        $stmt1->close();

        // Then delete from teacher_info table
        $stmt2 = $userInfoConn->prepare("DELETE FROM teacher_info WHERE id = ?");
        $stmt2->bind_param("i", $teacher_id);
        $stmt2->execute();
        $stmt2->close();

        // Commit transaction
        $userInfoConn->commit();
        echo "<script>alert('Teacher has been deleted successfully.'); window.location.href='manage-existing-teachers.php';</script>";
    } catch (Exception $e) {
        // Rollback transaction in case of any error
        $userInfoConn->rollback();
        echo "<script>alert('Failed to delete the teacher. Please try again.');</script>";
    }
}

// Close connection
$userInfoConn->close();
?>
