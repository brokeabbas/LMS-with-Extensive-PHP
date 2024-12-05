<?php
session_start();

require_once '../../connections/db_school_data.php'; // Include the database connection file

// Redirect if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

// Ensure that the class_id is present
if (!isset($_GET['delete']) || empty($_GET['delete'])) {
    header("location: manage_classes.php");
    exit;
}

$school_id = $_SESSION['school_id'] ?? null;
$class_id = intval($_GET['delete']);

// Start transaction
$schoolDataConn->begin_transaction();

try {
    // First, gather all module_ids related to the class from class_subject
    $moduleIds = [];
    $getModuleIdsQuery = $schoolDataConn->prepare(
        "SELECT module_id FROM class_subject 
        WHERE class_id = ? AND school_id = ?"
    );
    $getModuleIdsQuery->bind_param("ii", $class_id, $school_id);
    $getModuleIdsQuery->execute();
    $result = $getModuleIdsQuery->get_result();
    while ($row = $result->fetch_assoc()) {
        $moduleIds[] = $row['module_id'];
    }
    $getModuleIdsQuery->close();

    if (!empty($moduleIds)) {
        // Convert module_ids array into a string for the IN clause
        $moduleIdsString = implode(',', array_map('intval', $moduleIds)); // Ensure each ID is an integer

        // Prepare statements to handle deletions with the list of module IDs
        $queries = [
            "DELETE FROM assignments WHERE module_id IN ($moduleIdsString)",
            "DELETE FROM disciplinary_records WHERE module_id IN ($moduleIdsString)",
            "DELETE FROM student_modules WHERE module_id IN ($moduleIdsString)",
            "DELETE FROM modules_taught WHERE module_id IN ($moduleIdsString)"
        ];

        foreach ($queries as $query) {
            $deleteQuery = $schoolDataConn->prepare($query);
            $deleteQuery->execute();
            $deleteQuery->close();
        }
    }

    // Delete all subjects associated with the class
    $deleteSubjectsQuery = $schoolDataConn->prepare(
        "DELETE FROM class_subject 
        WHERE class_id = ? AND school_id = ?"
    );
    $deleteSubjectsQuery->bind_param("ii", $class_id, $school_id);
    $deleteSubjectsQuery->execute();
    $deleteSubjectsQuery->close();

    // Finally, delete the class itself
    $deleteClassQuery = $schoolDataConn->prepare(
        "DELETE FROM classes 
        WHERE class_id = ? AND school_id = ?"
    );
    $deleteClassQuery->bind_param("ii", $class_id, $school_id);
    $deleteClassQuery->execute();
    $deleteClassQuery->close();

    // Commit the transaction
    $schoolDataConn->commit();

    // Redirect back to the manage classes page with a success message
    header("location: manage_curriculum.php?message=Class and associated records successfully deleted");

} catch (Exception $e) {
    // An error occurred, rollback any changes
    $schoolDataConn->rollback();
    // Redirect back to the manage classes page with an error message
    header("location: manage_curriculum.php?error=" . urlencode($e->getMessage()));
}
?>
