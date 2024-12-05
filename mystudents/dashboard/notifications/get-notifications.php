<?php
session_start();
require_once '../../connections/db_school_data.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION['student_id'])) {
    echo json_encode(['unread_count' => 0]);
    exit;
}

$student_id = $_SESSION['student_id'];
$sql = "SELECT COUNT(*) AS unread_count FROM messages WHERE student_id = ? AND is_read = 0";

$stmt = $schoolDataConn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode(['unread_count' => $row['unread_count']]);

$stmt->close();
$schoolDataConn->close();
?>
