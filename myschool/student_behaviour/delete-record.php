<?php
session_start();

require_once '../../connections/db_school_data.php'; // Adjust the path as necessary

// Check if the user is logged in, otherwise redirect to the login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

// Validate the record ID
$recordId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($recordId <= 0) {
    echo "Invalid Record ID.";
    exit;
}

// Prepare SQL statement to delete the record
$query = "DELETE FROM disciplinary_records WHERE id = ? AND school_id = ?";
$stmt = $schoolDataConn->prepare($query);
if (!$stmt) {
    echo "Preparation failed: (" . $schoolDataConn->errno . ") " . $schoolDataConn->error;
    exit;
}

// Bind the integer parameter for the record ID
$stmt->bind_param('ii', $recordId, $_SESSION['school_id']);

// Execute the query
if ($stmt->execute()) {
    $stmt->close();
    $schoolDataConn->close();
    // Redirect back to the records page with a success message
    header("Location: student_behaviour.php");
    exit;
} else {
    echo "Error deleting record: " . $stmt->error;
}

$stmt->close();
$schoolDataConn->close();
?>
