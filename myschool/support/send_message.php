<?php
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

// Include PHPMailer for sending emails
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../connections/vendor/autoload.php'; // Make sure to adjust the path to the Composer autoload file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $messageContent = htmlspecialchars($_POST['message']);

    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'Yabbaz4321@gmail.com'; // Your Gmail address
        $mail->Password = 'xbwrrdconariamrn'; // Your Google App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Recipients
        $mail->setFrom($email, $name);
        $mail->addAddress('Yabbaz4321@gmail.com'); // Your email address

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Contact Us Message';
        $mail->Body    = "<p><strong>Name:</strong> $name</p><p><strong>Email:</strong> $email</p><p><strong>Message:</strong><br>$messageContent</p>";

        $mail->send();
        $message = 'Message has been sent successfully!';
    } catch (Exception $e) {
        $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}
echo ('Successfully sent.');
exit;
?>
