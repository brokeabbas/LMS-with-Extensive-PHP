<?php
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

require_once '../../connections/db.php';
require '../../connections/vendor/autoload.php'; // Adjust the path as needed

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Process the form when it is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $schoolName = $_POST['school_name'];
    $email = $_POST['email'];
    $contactNumber = $_POST['contact_number'];

    // Send request and store the result
    $success = sendWebsiteRequest($schoolName, $email, $contactNumber);

    if ($success) {
        $message = "Your request has been submitted successfully!";
    } else {
        $message = "There was an error processing your request.";
    }
}

function sendWebsiteRequest($schoolName, $email, $contactNumber) {
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'Yabbaz4321@gmail.com';
        $mail->Password = 'xbwrrdconariamrn';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        //Recipients
        $mail->setFrom('Yabbaz4321@gmail.com', 'School Management System');
        $mail->addAddress('Yabbaz4321@gmail.com', 'Admin');     // Add a recipient

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'New Website Request';
        $mail->Body    = "A new website request has been made.<br><strong>School Name:</strong> $schoolName<br><strong>Email:</strong> $email<br><strong>Contact Number:</strong> $contactNumber";
        $mail->AltBody = "A new website request has been made.\nSchool Name: $schoolName\nEmail: $email\nContact Number: $contactNumber";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Message could not be sent. Mailer Error: '. $mail->ErrorInfo);
        return false;
    }
}
?>

<!-- Your HTML form remains unchanged -->


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request School Website</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(135deg, #1f2937, #3b82f6);
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
            color: #f8fafc;
            font-family: 'Roboto', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .card {
            background-color: #2d3748;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 20px;
            padding: 20px;
            width: 400px;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        .input-field {
            background-color: #1a202c;
            color: #a0aec0;
            border: 1px solid #4a5568;
            border-radius: 10px;
        }
        .input-field:focus {
            border-color: #63b3ed;
            box-shadow: 0 0 0 1px #63b3ed;
        }
        .btn-primary {
            background-color: #63b3ed;
            color: #fff;
            transition: background-color 0.2s ease-in-out;
            border-radius: 10px;
        }
        .btn-primary:hover {
            background-color: #3182ce;
        }
        .alert {
            background-color: #38a169;
            border-color: #2f855a;
            color: #f0fff4;
            border-radius: 10px;
        }
    </style>
</head>
<body class="bg-gray-900">
    <div class="card">
        <h1 class="text-3xl font-bold mb-4 text-center">Request a Website for Your School</h1>

        <?php if (!empty($message)): ?>
            <div class="alert p-4 mb-4">
                <span><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-4">
                <label for="school_name" class="block text-gray-300 text-sm font-bold mb-2">School Name:</label>
                <input type="text" id="school_name" name="school_name" required class="input-field shadow appearance-none border rounded w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-300 text-sm font-bold mb-2">Contact Email:</label>
                <input type="email" id="email" name="email" required class="input-field shadow appearance-none border rounded w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label for="contact_number" class="block text-gray-300 text-sm font-bold mb-2">Contact Phone Number:</label>
                <input type="text" id="contact_number" name="contact_number" required class="input-field shadow appearance-none border rounded w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="flex items-center justify-between">
                <button type="submit" class="btn-primary font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</body>
</html>
