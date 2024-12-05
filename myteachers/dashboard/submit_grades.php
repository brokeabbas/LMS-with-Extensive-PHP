<?php
session_start();
header('Content-Type: application/json');

require_once '../../connections/db.php';
require_once '../../connections/db_school_data.php';

$data = json_decode(file_get_contents("php://input"), true);

// Authentication and data integrity checks
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($data['teacher_code'])) {
    echo json_encode(['error' => 'Unauthorized access or incomplete data.']);
    exit;
}

$teacher_code = $data['teacher_code'];
$teacher_id = $_SESSION['teacher_id'];
$school_id = $_SESSION['school_id'];

// Validate teacher code
$teacher_code_sql = "SELECT teacher_code FROM userinfo.teacher_users WHERE teacher_id = ? AND school_id = ?";
$code_stmt = $schoolDataConn->prepare($teacher_code_sql);
if ($code_stmt) {
    $code_stmt->bind_param("ii", $teacher_id, $school_id);
    $code_stmt->execute();
    $code_result = $code_stmt->get_result();
    if ($code_row = $code_result->fetch_assoc()) {
        if ($teacher_code === $code_row['teacher_code']) {
            // Process each grade entry
            foreach ($data['grades'] as $entry) {
                if (!empty($entry['mark']) && !empty($entry['value'])) {
                    $student_id = $entry['name'];
                    $grade_value = $entry['value'];
                    $assessment_type = $data['assessment_type'];
                    $assessment_name = $data['assessment_name'];
                    $overall_score = $data['overall_score'];
                    $insert_sql = "INSERT INTO school_data.grades (student_id, module_id, school_id, grade, assessment_type, assessment_name, overall_score, teacher_id)
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $insert_stmt = $schoolDataConn->prepare($insert_sql);
                    if ($insert_stmt) {
                        $insert_stmt->bind_param("iiisssii", $student_id, $_SESSION['module_id'], $school_id, $grade_value, $assessment_type, $assessment_name, $overall_score, $teacher_id);
                        $insert_stmt->execute();
                        $insert_stmt->close();
                    }
                }
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Invalid teacher code.']);
        }
    } else {
        echo json_encode(['error' => 'Teacher code not found.']);
    }
    $code_stmt->close();
} else {
    echo json_encode(['error' => 'SQL Error: ' . $schoolDataConn->error]);
}

$schoolDataConn->close();
?>
