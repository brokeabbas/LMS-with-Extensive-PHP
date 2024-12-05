<?php
session_start();
require_once '../connections/db_support.php';  // Adjust the path as needed

// Handle POST request when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'] ?? '';  // Optional field
    $suggestions = $_POST['suggestions'];

    if (!empty($name) && !empty($suggestions)) {
        $sql = "INSERT INTO feedback (name, email, suggestions) VALUES (?, ?, ?)";
        if ($stmt = $schSuppConn->prepare($sql)) {
            $stmt->bind_param("sss", $name, $email, $suggestions);
            if ($stmt->execute()) {
                $_SESSION['message'] = ['type' => 'success', 'content' => 'Thank you for your feedback!'];
            } else {
                $_SESSION['message'] = ['type' => 'error', 'content' => 'Error submitting feedback: ' . $stmt->error];
            }
            $stmt->close();
        } else {
            $_SESSION['message'] = ['type' => 'error', 'content' => 'Error preparing statement: ' . $schSuppConn->error];
        }
    } else {
        $_SESSION['message'] = ['type' => 'error', 'content' => 'Please fill in all required fields.'];
    }
    $schSuppConn->close();
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
    <title>Feedback Form</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(to right, #f3e6ff, #e0f7fa);
            background-attachment: fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .form-container {
            width: 100%;
            max-width: 500px;
            padding: 2rem;
            background: #ffffff;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            border-radius: 8px;
        }
        .form-icon {
            color: #4f46e5; /* Tailwind indigo-600 */
        }
        .form-title {
            color: #2d3748;
            font-size: 1.5rem;
            font-weight: 700;
        }
        .form-description {
            color: #4a5568;
            font-size: 1rem;
            margin-bottom: 1.5rem;
        }
        .input-field {
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .input-field:focus {
            border-color: #63b3ed;
            box-shadow: 0 0 0 3px rgba(99, 179, 237, 0.5);
        }
        .submit-button {
            background-color: #4299e1;
            transition: background-color 0.3s ease;
        }
        .submit-button:hover {
            background-color: #3182ce;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="form-container">
        <h1 class="form-title mb-2">Feedback Form</h1>
        <p class="form-description">Teachers, how would you like us to help you improve your work?</p>

        <!-- Display session messages -->
        <?php if ($session_message): ?>
            <div class="<?= $session_message['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?> p-4 mb-4 rounded">
                <?= htmlspecialchars($session_message['content']) ?>
            </div>
        <?php endif; ?>

        <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-4">
                <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Name:</label>
                <input type="text" id="name" name="name" class="input-field shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email (Optional):</label>
                <input type="email" id="email" name="email" class="input-field shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-6">
                <label for="suggestions" class="block text-gray-700 text-sm font-bold mb-2">Suggestions:</label>
                <textarea id="suggestions" name="suggestions" rows="4" class="input-field shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required></textarea>
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="submit-button text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Submit Feedback</button>
            </div>
        </form>
    </div>
</body>
</html>
