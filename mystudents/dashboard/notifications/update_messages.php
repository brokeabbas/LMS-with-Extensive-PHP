<?php
session_start();
require_once '../../connections/db_school_data.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION['student_id'])) {
    echo "Not logged in";
    exit;
}

$student_id = $_SESSION['student_id'];
$sql = "UPDATE messages SET is_read = 1 WHERE student_id = ?";

$stmt = $schoolDataConn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->close();
$schoolDataConn->close();

echo "Messages marked as read";
?>
