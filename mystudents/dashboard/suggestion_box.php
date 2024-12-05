<?php
session_start();
require_once '../../connections/db.php'; // Adjust the path as necessary for database connection
require_once '../../connections/db_school_data.php'; // Adjust the path as necessary for school-specific database

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["student_id"])) {
    header("location: ../login_student.php"); // Redirect to the login page if not logged in
    exit;
}

// Fetch school ID from session
$school_id = $_SESSION['school_id'] ?? null;

$successMessage = "";
$errorMessage = "";

// Handle POST request when the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $suggestion_title = $_POST['suggestion_title'] ?? '';
    $suggestion_body = $_POST['suggestion_body'] ?? '';

    // Ensure title and body are not empty and school ID is set
    if (!empty($suggestion_title) && !empty($suggestion_body) && $school_id) {
        $sql = "INSERT INTO suggestions (school_id, suggestion_title, suggestion_body) VALUES (?, ?, ?)";
        if ($stmt = $schoolDataConn->prepare($sql)) {
            $stmt->bind_param("iss", $school_id, $suggestion_title, $suggestion_body);
            if ($stmt->execute()) {
                $successMessage = "Suggestion submitted successfully!";
            } else {
                $errorMessage = "Error submitting suggestion: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $errorMessage = "Error preparing SQL statement.";
        }
    } else {
        $errorMessage = "Please fill in all fields.";
    }
}

$schoolDataConn->close(); // Close the database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suggestion Box</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(to right, #4e54c8, #8f94fb);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .form-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            width: 100%;
            max-width: 500px;
        }
        .input-field, .textarea-field {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: #FFFFFF;
            border-radius: 8px;
            padding: 12px 20px;
            width: 100%;
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
        .anonymous-message {
            text-align: center;
            margin-bottom: 20px;
            font-size: 14px;
            color: #ccc;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1 class="text-2xl font-bold text-center mb-6">Suggestion Box</h1>
        <p class="anonymous-message">Your message will be delivered anonymously.</p>
        <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-4">
                <label for="suggestion_title">Suggestion Title:</label>
                <input type="text" id="suggestion_title" name="suggestion_title" required class="input-field">
            </div>
            <div class="mb-6">
                <label for="suggestion_body">Details of the Suggestion:</label>
                <textarea id="suggestion_body" name="suggestion_body" rows="4" required class="textarea-field"></textarea>
            </div>
            <button type="submit" name="submit" class="submit-button">Submit Suggestion</button>
        </form>
        <?php if ($successMessage): ?>
            <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                <?= $successMessage; ?>
            </div>
        <?php endif; ?>
        <?php if ($errorMessage): ?>
            <div class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                <?= $errorMessage; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
