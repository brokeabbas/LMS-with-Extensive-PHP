<?php
session_start();
require_once '../../connections/db_school_data.php';

// Initialize message variable
$message = null;

// Check if necessary parameters are present
if (!isset($_GET['student_code'], $_GET['module_code'], $_GET['school_id'])) {
    $message = [
        'type' => 'error',
        'content' => 'Missing parameters.'
    ];
} else {
    // Validate if the school ID matches the session to prevent unauthorized access
    if ($_GET['school_id'] != $_SESSION['school_id']) {
        $message = [
            'type' => 'error',
            'content' => 'Unauthorized access.'
        ];
    } else {
        $student_code = $_GET['student_code'];
        $module_code = $_GET['module_code'];
        $school_id = $_GET['school_id'];

        // First, fetch the module_id from the modules_taught table using the module_code and school_id
        $module_id_sql = "SELECT module_id FROM schoolhu_school_data.modules_taught WHERE module_code = ? AND school_id = ?";

        if ($module_stmt = $schoolDataConn->prepare($module_id_sql)) {
            $module_stmt->bind_param("si", $module_code, $school_id);
            $module_stmt->execute();
            $module_result = $module_stmt->get_result();
            if ($module_row = $module_result->fetch_assoc()) {
                $module_id = $module_row['module_id'];
            } else {
                $message = [
                    'type' => 'error',
                    'content' => 'No module found with the provided module code and school ID.'
                ];
            }
            $module_stmt->close();
        } else {
            $message = [
                'type' => 'error',
                'content' => "SQL Error in fetching module ID: " . $schoolDataConn->error
            ];
        }

        if ($message === null) {
            // Now, proceed with the deletion query using the fetched module_id
            $sql = "DELETE FROM schoolhu_school_data.student_modules WHERE student_id = (SELECT id FROM schoolhu_userinfo.student_info WHERE student_number = ?) AND module_id = ? AND school_id = ?";

            if ($stmt = $schoolDataConn->prepare($sql)) {
                $stmt->bind_param("sii", $student_code, $module_id, $school_id);
                $stmt->execute();
                if ($stmt->affected_rows > 0) {
                    $message = [
                        'type' => 'success',
                        'content' => 'Student successfully removed.'
                    ];
                } else {
                    $message = [
                        'type' => 'error',
                        'content' => 'No record found or student could not be removed.'
                    ];
                }
                $stmt->close();
            } else {
                $message = [
                    'type' => 'error',
                    'content' => "SQL Error in deletion: " . $schoolDataConn->error
                ];
            }
        }
    }
}

$schoolDataConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Action Result</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7fafc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .message-box {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            max-width: 600px;
            text-align: center;
        }
        .success {
            color: #48bb78;
        }
        .error {
            color: #f56565;
        }
    </style>
</head>
<body>
    <div class="message-box">
        <?php if ($message): ?>
            <div class="<?= $message['type'] === 'success' ? 'success' : 'error' ?>">
                <i class="<?= $message['type'] === 'success' ? 'fas fa-check-circle' : 'fas fa-times-circle' ?> fa-2x mb-4"></i>
                <p class="text-lg"><?= htmlspecialchars($message['content']) ?></p>
            </div>
        <?php else: ?>
            <p class="text-lg">No message to display.</p>
        <?php endif; ?>
    </div>
</body>
</html>
