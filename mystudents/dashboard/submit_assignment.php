<?php
session_start();
require_once '../../connections/db.php';
require_once '../../connections/db_school_data.php';

// Authentication check
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["student_id"], $_SESSION["school_id"])) {
    header("location: ../login_student.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$school_id = $_SESSION['school_id'];
$assignment_id = $_POST['assignment_id']; // Ensure this is being sent from the form
$submission_date = date('Y-m-d H:i:s'); // Current date and time

// File upload handling
if (isset($_FILES['submitted_file']) && $_FILES['submitted_file']['error'] == 0) {
    $file_tmp = $_FILES['submitted_file']['tmp_name'];
    $file_name = $_FILES['submitted_file']['name'];
    $file_path = '../../submissions_uploads/' . $file_name; // Ensure this directory is writable
    move_uploaded_file($file_tmp, $file_path);

    // Insert into database
    $sql = "INSERT INTO assignment_submissions (student_id, assignment_id, school_id, file_path, submission_date) 
            VALUES (?, ?, ?, ?, ?)";

    if ($stmt = $schoolDataConn->prepare($sql)) {
        $stmt->bind_param("iiiss", $student_id, $assignment_id, $school_id, $file_path, $submission_date);
        $stmt->execute();
        $stmt->close();
        // Redirect to success page
        header("Location: submission_success.php");
        exit;
    } else {
        echo "SQL Error: " . $schoolDataConn->error;
    }
} else {
    echo "Error uploading file.";
}

$schoolDataConn->close();
?>
