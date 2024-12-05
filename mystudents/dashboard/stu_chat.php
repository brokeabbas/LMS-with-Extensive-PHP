<?php
session_start();
require_once '../../connections/db_school_data.php'; // Ensure this path is correct

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_student.php");
    exit;
}

$module_code = $_GET['module'] ?? ''; // Secure fetching of module code

// Fetch module details for communication
$module_details = [];
if ($module_code) {
    $sql = "SELECT mt.module_id, mt.module_code, ss.subject_name, t.name as teacher_name, t.id as teacher_id
            FROM modules_taught mt
            JOIN schoolhu_userinfo.teacher_info t ON mt.teacher_id = t.id
            JOIN class_subject cs ON mt.module_id = cs.module_id
            JOIN school_subjects ss ON cs.subject_id = ss.subject_id
            WHERE mt.module_code = ?";
    if ($stmt = $schoolDataConn->prepare($sql)) {
        $stmt->bind_param("s", $module_code);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $module_details = $row;
        }
        $stmt->close();
    } else {
        echo "SQL Error: " . $schoolDataConn->error;
    }
}

// Handle message submission
$message_sent = false;
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($module_details)) {
    $message_title = $_POST['title'] ?? '';
    $message_body = $_POST['message'] ?? '';

    // Insert message into the database
    $sql = "INSERT INTO student_messages (student_id, teacher_id, school_id, module_id, message_title, message_body)
            VALUES (?, ?, ?, ?, ?, ?)";
    if ($stmt = $schoolDataConn->prepare($sql)) {
        $stmt->bind_param("iiiiss", $_SESSION['student_id'], $module_details['teacher_id'], $_SESSION['school_id'], $module_details['module_id'], $message_title, $message_body);
        if ($stmt->execute()) {
            $message_sent = true;
        } else {
            echo "<p>Error sending message: " . $stmt->error . "</p>";
        }
        $stmt->close();
    }
    $schoolDataConn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Message to Teacher</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script> <!-- FontAwesome for icons -->
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to left, #4b6cb7, #182848); /* Darker blue gradient */
            color: #ffffff;
            overflow-x: hidden;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .message-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            width: 100%;
            max-width: 500px;
        }
        .message-box h1 {
            color: #ffffff;
            font-size: 2rem;
            margin-bottom: 1rem;
            text-align: center;
        }
        .input-field, .textarea-field {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: #FFFFFF;
            border-radius: 8px;
            padding: 12px 20px;
            width: 100%;
            margin-bottom: 1rem;
        }
        .input-field:focus, .textarea-field:focus {
            background: rgba(255, 255, 255, 0.3);
            outline: none;
            box-shadow: 0 0 0 2px rgba(130, 170, 255, 0.5);
        }
        .submit-button {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            border: none;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: bold;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .submit-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.2);
        }
        label {
            font-size: 14px;
            color: #ccc;
        }
        .message-info {
            color: #ccc;
            margin-top: 1rem;
        }
        .success-message {
            background: rgba(72, 187, 120, 0.2);
            color: #48bb78;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 1rem;
        }
        .error-message {
            color: #ffb3b3;
            margin-top: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container mx-auto px-4 py-6">
        <div class="message-box">
            <h1>Send Message to Your Teacher</h1>
            <?php if ($message_sent): ?>
                <p class="success-message">Message sent successfully!</p>
            <?php endif; ?>
            <form action="stu_chat.php?module=<?= htmlspecialchars($module_code) ?>" method="post">
                <div>
                    <label for="title" class="block text-sm font-medium"><i class="fas fa-heading mr-2"></i>Title:</label>
                    <input type="text" id="title" name="title" required class="input-field">
                </div>
                <div>
                    <label for="message" class="block text-sm font-medium"><i class="fas fa-envelope mr-2"></i>Message:</label>
                    <textarea id="message" name="message" rows="4" required class="textarea-field"></textarea>
                </div>
                <button type="submit" class="submit-button"><i class="fas fa-paper-plane mr-2"></i>Send Message</button>
            </form>
            <?php if (!empty($module_details)): ?>
                <div class="message-info">
                    <p><strong>Module:</strong> <?= htmlspecialchars($module_details['subject_name']); ?></p>
                    <p><strong>Teacher:</strong> <?= htmlspecialchars($module_details['teacher_name']); ?></p>
                </div>
            <?php else: ?>
                <p class="error-message">Module details not found. Please check your link or contact administration.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
