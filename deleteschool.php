<?php
session_start();

// Include database connection settings
include 'connections/db.php';  // Connection for 'userinfo' database
include 'connections/db_school_data.php';  // Connection for 'school_data' database

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['school_id'])) {
    $school_id = $_POST['school_id'];

    // Start transactions
    $userInfoConn->begin_transaction();
    $schoolDataConn->begin_transaction();

    try {
        // First, delete from `book_requests` where `requester_id` is in `student_info` linked to the school
        $sql = "DELETE FROM school_data.book_requests WHERE requester_id IN (SELECT id FROM userinfo.student_info WHERE school_id = ?)";
        if ($stmt = $schoolDataConn->prepare($sql)) {
            $stmt->bind_param("i", $school_id);
            $stmt->execute();
            $stmt->close();
        }

        // Arrays of table names from both databases
        $userInfoTables = ['students', 'student_info', 'teacher_info', 'teacher_users', 'school_users', 'registered_school_code'];
        $schoolDataTables = ['assignments', 'assignment_submissions', 'awards', 'disciplinary_records', 'extracurricular_activities', 'Unassigned_teachers', 'grades', 'library', 'messages', 'schemes', 'school_calendar', 'student_complaints', 'student_messages', 'student_modules', 'suggestions'];

        // Delete from userinfo related tables
        foreach ($userInfoTables as $table) {
            $sql = "DELETE FROM $table WHERE school_id = ?";
            if ($stmt = $userInfoConn->prepare($sql)) {
                $stmt->bind_param("i", $school_id);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Delete from remaining school_data related tables
        foreach ($schoolDataTables as $table) {
            $sql = "DELETE FROM $table WHERE school_id = ?";
            if ($stmt = $schoolDataConn->prepare($sql)) {
                $stmt->bind_param("i", $school_id);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Finally, delete the school record
        $sql = "DELETE FROM schools WHERE school_id = ?";
        if ($stmt = $userInfoConn->prepare($sql)) {
            $stmt->bind_param("i", $school_id);
            $stmt->execute();
            $stmt->close();
        }

        // Commit transactions after all deletions are successful
        $userInfoConn->commit();
        $schoolDataConn->commit();
        echo "<p>All records related to the school have been successfully deleted.</p>";
    } catch (Exception $e) {
        // Something went wrong, rollback
        $userInfoConn->rollback();
        $schoolDataConn->rollback();
        echo "<p>Failed to delete school records: " . $e->getMessage() . "</p>";
    }

    // Close connections
    $userInfoConn->close();
    $schoolDataConn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete School</title>
</head>
<body>
    <h1>Delete School</h1>
    <form action="deleteschool.php" method="post">
        <label for="school_id">School ID:</label>
        <input type="text" id="school_id" name="school_id" required>
        <button type="submit">Delete School</button>
    </form>
</body>
</html>
