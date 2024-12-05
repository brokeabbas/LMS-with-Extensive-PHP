<?php
session_start();
require_once '../../connections/db.php'; // Connection to the user session and authentication
require_once '../../connections/db_school_data.php'; // Connection to the school-specific data operations

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_teacher.php");
    exit;
}

$school_id = $_SESSION['school_id'] ?? null;

// Handle POST request when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $suggestion_title = $_POST['suggestion_title'] ?? '';
    $suggestion_body = $_POST['suggestion_body'] ?? '';

    if (!empty($suggestion_title) && !empty($suggestion_body) && $school_id) {
        $sql = "INSERT INTO suggestions (school_id, suggestion_title, suggestion_body) VALUES (?, ?, ?)";
        if ($stmt = $schoolDataConn->prepare($sql)) {
            $stmt->bind_param("iss", $school_id, $suggestion_title, $suggestion_body);
            if ($stmt->execute()) {
                $_SESSION['message'] = ['type' => 'success', 'content' => 'Suggestion submitted successfully!'];
            } else {
                $_SESSION['message'] = ['type' => 'error', 'content' => 'Error submitting suggestion: ' . $schoolDataConn->error];
            }
            $stmt->close();
        } else {
            $_SESSION['message'] = ['type' => 'error', 'content' => 'SQL Error: ' . $schoolDataConn->error];
        }
    } else {
        $_SESSION['message'] = ['type' => 'error', 'content' => 'Please fill in all fields and ensure you are logged in with a valid school ID.'];
    }
    $schoolDataConn->close();
    header("Location: " . htmlspecialchars($_SERVER["PHP_SELF"]));
    exit;
}

// Retrieve any session messages and then clear them
$session_message = null;
if (isset($_SESSION['message'])) {
    $session_message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suggestion Box</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            font-family: 'Inter', sans-serif; /* Modern and clean font */
            background-color: rgba(0, 0, 0, 0.4); /* Dim background */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .form-container {
            width: 100%;
            max-width: 500px; /* Smaller box width */
            padding: 2rem;
            background: #ffffff;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            border-radius: 8px;
        }
        .form-icon {
            color: #4f46e5; /* Tailwind indigo-600 */
        }
        .form-heading {
            color: #333; /* Darker text for better contrast on white background */
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1 class="text-2xl font-bold form-heading mb-4 flex items-center">
            <i class="fas fa-lightbulb form-icon mr-2"></i> Suggestion Box
        </h1>

        <!-- Display session messages -->
        <?php if ($session_message): ?>
            <div class="<?= $session_message['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?> p-4 mb-4 rounded">
                <?= htmlspecialchars($session_message['content']) ?>
            </div>
        <?php endif; ?>

        <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="rounded-lg p-4">
            <div class="mb-4">
                <label for="suggestion_title" class="block text-gray-700 text-sm font-bold mb-2">
                    <i class="fas fa-heading form-icon mr-1"></i> Suggestion Title:
                </label>
                <input type="text" id="suggestion_title" name="suggestion_title" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label for="suggestion_body" class="block text-gray-700 text-sm font-bold mb-2">
                    <i class="fas fa-align-left form-icon mr-1"></i> Suggestion Body:
                </label>
                <textarea id="suggestion_body" name="suggestion_body" rows="4" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"></textarea>
            </div>
            <button type="submit" name="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                <i class="fas fa-paper-plane mr-2"></i> Submit Suggestion
            </button>
        </form>
    </div>
</body>
</html>
