<?php
session_start();
require_once '../../connections/db_school_data.php'; // Include the database connection file

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

// Retrieve all subjects and their names once at the beginning if needed
$subjects = [];
$fetchSubjectsQuery = $schoolDataConn->prepare("SELECT subject_id, subject_name FROM school_subjects WHERE school_id = ?");
$fetchSubjectsQuery->bind_param("i", $_SESSION['school_id']);
$fetchSubjectsQuery->execute();
$result = $fetchSubjectsQuery->get_result();
while ($row = $result->fetch_assoc()) {
    $subjects[$row['subject_id']] = $row['subject_name'];
}
$fetchSubjectsQuery->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['class_id'], $_POST['subjects']) && is_array($_POST['subjects'])) {
    $class_id = intval($_POST['class_id']);
    $submittedSubjects = array_map('intval', $_POST['subjects']);
    $school_id = $_SESSION['school_id'];

    $schoolDataConn->begin_transaction();

    try {
        // Fetch current subjects for comparison
        $currentSubjects = [];
        $currentSubjectsQuery = $schoolDataConn->prepare("SELECT subject_id FROM class_subject WHERE class_id = ? AND school_id = ?");
        $currentSubjectsQuery->bind_param("ii", $class_id, $school_id);
        $currentSubjectsQuery->execute();
        $result = $currentSubjectsQuery->get_result();
        while ($row = $result->fetch_assoc()) {
            $currentSubjects[] = $row['subject_id'];
        }
        $currentSubjectsQuery->close();

        if (empty($submittedSubjects) && empty($currentSubjects)) {
            throw new Exception("At least one subject must exist in a class, or delete the class.");
        }

        $subjectsToAdd = array_diff($submittedSubjects, $currentSubjects);
        $subjectsToRemove = array_diff($currentSubjects, $submittedSubjects);

        // Process removals
        foreach ($subjectsToRemove as $subjectId) {
            $moduleIds = [];
            $findModuleIdsQuery = $schoolDataConn->prepare("SELECT module_id FROM class_subject WHERE subject_id = ? AND school_id = ?");
            $findModuleIdsQuery->bind_param("ii", $subjectId, $school_id);
            $findModuleIdsQuery->execute();
            $moduleIdsResult = $findModuleIdsQuery->get_result();
            while ($moduleIdRow = $moduleIdsResult->fetch_assoc()) {
                $moduleIds[] = $moduleIdRow['module_id'];
            }
            $findModuleIdsQuery->close();

            foreach ($moduleIds as $moduleId) {
                // Delete dependent records in the correct order
                $deleteQueries = [
                    "DELETE FROM disciplinary_records WHERE module_id = ?",
                    "DELETE FROM messages WHERE module_id = ?",
                    "DELETE FROM student_messages WHERE module_id = ?",
                    "DELETE FROM assignment_submissions WHERE assignment_id IN (SELECT id FROM assignments WHERE module_id = ?)",
                    "DELETE FROM attendance_records WHERE module_id = ?",
                    "DELETE FROM grades WHERE module_id = ?",
                    "DELETE FROM student_modules WHERE module_id = ?",
                    "DELETE FROM assignments WHERE module_id = ?"
                ];
                foreach ($deleteQueries as $query) {
                    $deleteStmt = $schoolDataConn->prepare($query);
                    $deleteStmt->bind_param("i", $moduleId);
                    $deleteStmt->execute();
                    $deleteStmt->close();
                }
            }

            $removeSubject = $schoolDataConn->prepare("DELETE FROM class_subject WHERE class_id = ? AND subject_id = ? AND school_id = ?");
            $removeSubject->bind_param("iii", $class_id, $subjectId, $school_id);
            $removeSubject->execute();
            $removeSubject->close();
        }

        foreach ($subjectsToAdd as $subjectId) {
            $subject_name = $subjects[$subjectId];
            $module_code = substr($subject_name, 0, 3) . sprintf('%06d', rand(1000, 999999));
            $insertQuery = $schoolDataConn->prepare("INSERT INTO class_subject (class_id, subject_id, school_id, module_code) VALUES (?, ?, ?, ?)");
            $insertQuery->bind_param("iiis", $class_id, $subjectId, $school_id, $module_code);
            $insertQuery->execute();
            $insertQuery->close();
        }

        $schoolDataConn->commit();
        $_SESSION['message'] = "Subject updates successful. Added: " . count($subjectsToAdd) . " Removed: " . count($subjectsToRemove);
    } catch (Exception $e) {
        $schoolDataConn->rollback();
        $_SESSION['message'] = "Error updating subjects: " . $e->getMessage();
    }
} else {
    $_SESSION['message'] = "Invalid request. Make sure class_id and subjects are correctly provided.";
}

$schoolDataConn->close();
header("location: subject_update_status.php");
exit;
?>
