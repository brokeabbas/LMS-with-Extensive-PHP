<?php
session_start();

// Include the support database connection
require_once '../connections/db_support.php';  // Adjust the path as needed

$successMessage = "";
$errorMessage = "";

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'] ?? '';  // Optional field
    $suggestions = $_POST['suggestions'];

    // Prepare a statement to insert feedback using the school_support connection
    $sql = "INSERT INTO feedback (name, email, suggestions) VALUES (?, ?, ?)";
    if ($stmt = $schSuppConn->prepare($sql)) {
        $stmt->bind_param("sss", $name, $email, $suggestions);

        // Execute the statement
        if ($stmt->execute()) {
            $successMessage = "Thank you for your feedback!";
        } else {
            $errorMessage = "Error submitting feedback: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $errorMessage = "Error preparing statement: " . $schSuppConn->error;
    }

    $schSuppConn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Form</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script> <!-- FontAwesome for icons -->
    <link rel="icon" href="../IMAGES/3.png" type="image/x-icon">
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
        .form-container h1 {
            color: #fff;
            font-size: 2rem;
            margin-bottom: 1rem;
            text-align: center;
        }
        .form-container p {
            color: #ccc;
            margin-bottom: 2rem;
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
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Feedback Form <i class="fas fa-comments text-blue-500"></i></h1>
        <p>Do you have any improvements you want for the platform?</p>
        <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div>
                <label for="name" class="block text-sm font-bold mb-2"><i class="fas fa-user mr-2"></i>Name:</label>
                <input type="text" id="name" name="name" class="input-field" required>
            </div>
            <div>
                <label for="email" class="block text-sm font-bold mb-2"><i class="fas fa-envelope mr-2"></i>Email (Optional):</label>
                <input type="email" id="email" name="email" class="input-field">
            </div>
            <div>
                <label for="suggestions" class="block text-sm font-bold mb-2"><i class="fas fa-lightbulb mr-2"></i>Suggestions:</label>
                <textarea id="suggestions" name="suggestions" rows="4" class="textarea-field" required></textarea>
            </div>
            <div class="mt-4">
                <button type="submit" class="submit-button">Submit Feedback <i class="fas fa-paper-plane ml-2"></i></button>
            </div>
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
