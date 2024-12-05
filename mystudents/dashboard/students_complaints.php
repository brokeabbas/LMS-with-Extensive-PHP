<?php
session_start();
require_once '../../connections/db.php';  // Adjust the path as necessary

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["student_id"], $_SESSION["school_id"])) {
    header("location: ../login_student.php");
    exit;
}

$student_id = $_SESSION["student_id"];
$school_id = $_SESSION["school_id"];

require_once '../../connections/db_school_data.php';

$title = $body = "";
$errors = [];
$successMessage = "";

// Process the form when it is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $body = trim($_POST['body']);

    // Validate input
    if (empty($title)) {
        $errors['title'] = "Title is required.";
    }
    if (empty($body)) {
        $errors['body'] = "Complaint body is required.";
    }

    // If no errors, insert the complaint into the database
    if (count($errors) === 0) {
        $sql = "INSERT INTO student_complaints (student_id, school_id, title, body) VALUES (?, ?, ?, ?)";
        if ($stmt = $schoolDataConn->prepare($sql)) {
            $stmt->bind_param("iiss", $student_id, $school_id, $title, $body);
            if ($stmt->execute()) {
                $successMessage = "Complaint submitted successfully.";
            } else {
                $successMessage = "Error submitting complaint: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $successMessage = "Error preparing SQL statement.";
        }
    }
    $schoolDataConn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Voice Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(135deg, #6dd5ed, #2193b0);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #ffffff;
        }
        .form-container {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        .input-field {
            border: none;
            background: rgba(255, 255, 255, 0.7);
            border-radius: 8px;
            padding: 10px 15px;
            color: #2c3e50;
            box-shadow: inset 0 2px 3px rgba(0,0,0,0.1);
        }
        .input-field:focus {
            background: rgba(255, 255, 255, 0.9);
            outline: none;
        }
        .button {
            background: linear-gradient(to right, #56ab2f, #a8e063);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .button:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .icon {
            margin-right: 10px;
        }
        .title {
            color: #e74c3c; /* Red color for the title */
        }
        .important {
            color: #e74c3c; /* Red color for important text */
        }
    </style>
</head>
<body>
    <div class="container mx-auto px-4 py-6">
        <div class="form-container">
            <h1 class="text-3xl font-bold title mb-4"><i class="fas fa-bullhorn icon"></i>Student Voice Platform</h1>
            <p class="mb-4 important">Your feedback is crucial. Let us know what's on your mind!</p>
            <form method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="rounded">
                <div class="mb-4">
                    <label for="title" class="block text-gray-700 text-sm font-bold mb-2"><i class="fas fa-tag icon"></i>Subject:</label>
                    <input type="text" id="title" name="title" required class="input-field w-full py-2 px-3">
                    <?php if (!empty($errors['title'])): ?>
                        <p class="text-red-500 text-xs italic"><?= $errors['title'] ?></p>
                    <?php endif; ?>
                </div>
                <div class="mb-6">
                    <label for="body" class="block text-gray-700 text-sm font-bold mb-2"><i class="fas fa-edit icon"></i>Details:</label>
                    <textarea id="body" name="body" rows="6" required class="input-field w-full"></textarea>
                    <?php if (!empty($errors['body'])): ?>
                        <p class="text-red-500 text-xs italic"><?= $errors['body'] ?></p>
                    <?php endif; ?>
                </div>
                <button type="submit" class="button"><i class="fas fa-paper-plane icon"></i>Submit Feedback</button>
            </form>
            <?php if ($successMessage): ?>
                <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    <?= $successMessage; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
