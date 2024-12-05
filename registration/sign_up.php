<?php
session_start(); // Start session to store error/success messages

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../connections/vendor/autoload.php'; // Autoload files using Composer's autoload
include '../connections/db.php'; // Primary database connection
include '../connections/db_support.php'; // Database connection for subscription support

function generateSchoolCode() {
    return substr(md5(uniqid(mt_rand(), true)), 0, 10);
}

function sendEmail($to, $schoolCode) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'yabbaz4321@gmail.com'; // Your SMTP username
        $mail->Password = 'xbwrrdconariamrn'; // Your SMTP password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('yabbaz4321@gmail.com', 'School Hub');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = 'Your School Registration Code';
        $mail->Body = "Dear Admin,<br>Your school code is <strong>{$schoolCode}</strong>.<br>Use this code to register your account.<br><br>This Code Is very Important do not loose or share with any unauthorized persons";

        $mail->send();
        return 'Email has been sent successfully.';
    } catch (Exception $e) {
        return "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $schoolEmail = mysqli_real_escape_string($userInfoConn, $_POST['schoolEmail']);
    
    // Check if the email already exists in the schools table
    $checkEmailQuery = "SELECT * FROM schools WHERE school_email = '$schoolEmail'";
    $checkEmailResult = mysqli_query($userInfoConn, $checkEmailQuery);
    
    if (mysqli_num_rows($checkEmailResult) > 0) {
        $_SESSION['error'] = "<div class='error-message'>Email already exists. Please use a different email.</div>";
        header('Location: sign_up.php');
        exit;
    }
    
    $query = "SELECT subscription_status FROM subscribers WHERE email = '$schoolEmail'";
    $result = mysqli_query($schSuppConn, $query);
    $data = mysqli_fetch_assoc($result);

    if ($data) {
        if ($data['subscription_status'] == 1) {
            // Generate school code and send email only if the subscription is active
            $schoolCode = generateSchoolCode();
            $emailStatus = sendEmail($schoolEmail, $schoolCode);

            // Sanitize input
            $schoolName = mysqli_real_escape_string($userInfoConn, $_POST['schoolName']);
            $schoolType = mysqli_real_escape_string($userInfoConn, $_POST['schoolType']);
            $schoolDisposition = mysqli_real_escape_string($userInfoConn, $_POST['schoolDisposition']);
            $schoolDistrict = mysqli_real_escape_string($userInfoConn, $_POST['schoolDistrict']);
            $schoolAddress = mysqli_real_escape_string($userInfoConn, $_POST['schoolAddress']);
            $schoolContact = mysqli_real_escape_string($userInfoConn, $_POST['schoolContact']);
            $principalName = mysqli_real_escape_string($userInfoConn, $_POST['principalName']);
            $schoolWebsite = mysqli_real_escape_string($userInfoConn, $_POST['schoolWebsite']);
            $adminContactName = mysqli_real_escape_string($userInfoConn, $_POST['adminContactName']);
            $adminEmail = mysqli_real_escape_string($userInfoConn, $_POST['adminEmail']);
            $adminPhone = mysqli_real_escape_string($userInfoConn, $_POST['adminPhone']);

            $uploadDir = '../uploads/';
            $filePath = '';
            if (isset($_FILES['schoolLogo']) && $_FILES['schoolLogo']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['schoolLogo']['tmp_name'];
                $fileName = $_FILES['schoolLogo']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
                if (in_array($fileExtension, $allowedExts)) {
                    $newFileName = md5(time() . $fileName . uniqid()) . '.' . $fileExtension;
                    $filePath = $uploadDir . $newFileName;
                    move_uploaded_file($fileTmpPath, $filePath);
                }
            }

            // SQL query to insert data into the database
            $sql = "INSERT INTO schools (school_name, school_type, school_disposition, school_district, school_address, school_contact, school_email, school_website, principal_name, admin_contact_name, admin_email, admin_phone, school_code, school_logo)
                    VALUES ('$schoolName', '$schoolType', '$schoolDisposition', '$schoolDistrict', '$schoolAddress', '$schoolContact', '$schoolEmail', '$schoolWebsite', '$principalName', '$adminContactName', '$adminEmail', '$adminPhone', '$schoolCode', '$filePath')";
            if (mysqli_query($userInfoConn, $sql)) {
                $_SESSION['success'] = $emailStatus;
                header('Location: account_registration.php');
                exit;
            } else {
                $_SESSION['error'] = "Database error: " . mysqli_error($userInfoConn);
            }
        } else {
            header('Location: payment_page.php');
            exit;
        }
    } else {
        $_SESSION['error'] = "Email does not exist in our records. Use the email you subscribed with.";
    }
    header('Location: sign_up.php'); // Redirect to the same page to show messages
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Registration - School Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="icon" href="../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background-color: #1e3a8a;
            color: #ffffff;
        }
        .status-icon {
            display: inline-block;
            width: 24px;
            height: 24px;
            margin-left: 10px;
            background-repeat: no-repeat;
            background-position: center;
        }
        .green-tick {
            background-image: url('green_tick_icon.png');
        }
        .red-cross {
            background-image: url('red_cross_icon.png');
        }
        .input-group {
            position: relative;
        }
        .input-icon {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            pointer-events: none;
        }
        .input-field {
            padding-right: 2.5rem;
            background-color: #ffffff;
            color: #1e3a8a;
        }
        label {
            color: #1e3a8a;
        }
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #1e3a8a;
        }
        .checkbox-label input {
            accent-color: #1e3a8a;
        }
        .submit-button:disabled {
            background-color: #a1a1aa;
            cursor: not-allowed;
        }
        .text-link {
            color: #1e3a8a;
            text-decoration: underline;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="container mx-auto p-8">
    <div class="bg-white rounded-xl shadow-lg p-8">
        <h2 class="text-4xl font-bold text-gray-800 text-center mb-8">School Registration</h2>
        <?php
        session_start();
        if (isset($_SESSION['error'])) {
            echo '<div class="bg-red-500 text-white p-4 mb-4 rounded-md">' . $_SESSION['error'] . '</div>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<div class="bg-green-500 text-white p-4 mb-4 rounded-md">' . $_SESSION['success'] . '</div>';
            unset($_SESSION['success']);
        }
        ?>
        <form id="registrationForm" action="sign_up.php" method="post" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- School Email -->
            <div class="input-group col-span-1">
                <label for="schoolEmail" class="block text-lg font-medium text-gray-700">School Email Address:</label>
                <input type="email" id="schoolEmail" name="schoolEmail" required class="input-field mt-1 block w-full px-4 py-3 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 ease-in-out">
                <span id="emailStatus" class="status-icon"></span>
                <span id="emailFeedback" class="text-sm text-red-500"></span>
            </div>

            <!-- School Name -->
            <div class="input-group col-span-1">
                <label for="schoolName" class="block text-lg font-medium text-gray-700">School Name:</label>
                <input type="text" id="schoolName" name="schoolName" required class="input-field mt-1 block w-full px-4 py-3 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 ease-in-out">
            </div>

            <!-- School Type -->
            <div class="input-group col-span-1">
                <label for="schoolType" class="block text-lg font-medium text-gray-700">School Type:</label>
                <select id="schoolType" name="schoolType" required class="input-field mt-1 block w-full px-4 py-3 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 ease-in-out">
                    <option value="secondary">Secondary School</option>
                    <option value="high">Primary/Secondary School</option>
                </select>
            </div>

            <!-- School Disposition -->
            <div class="input-group col-span-1">
                <label for="schoolDisposition" class="block text-lg font-medium text-gray-700">School Disposition:</label>
                <select id="schoolDisposition" name="schoolDisposition" required class="input-field mt-1 block w-full px-4 py-3 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 ease-in-out">
                    <option value="public">Public</option>
                    <option value="private">Private</option>
                    <option value="online">Online</option>
                </select>
            </div>

            <!-- School District -->
            <div class="input-group col-span-1">
                <label for="schoolDistrict" class="block text-lg font-medium text-gray-700">School District/Educational Authority:</label>
                <input type="text" id="schoolDistrict" name="schoolDistrict" required class="input-field mt-1 block w-full px-4 py-3 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 ease-in-out">
            </div>

            <!-- School Address -->
            <div class="input-group col-span-1">
                <label for="schoolAddress" class="block text-lg font-medium text-gray-700">School Address:</label>
                <input type="text" id="schoolAddress" name="schoolAddress" required class="input-field mt-1 block w-full px-4 py-3 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 ease-in-out">
            </div>

            <!-- School Contact -->
            <div class="input-group col-span-1">
                <label for="schoolContact" class="block text-lg font-medium text-gray-700">School Contact Information:</label>
                <input type="tel" id="schoolContact" name="schoolContact" required class="input-field mt-1 block w-full px-4 py-3 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 ease-in-out">
            </div>

            <!-- School Website -->
            <div class="input-group col-span-1">
                <label for="schoolWebsite" class="block text-lg font-medium text-gray-700">School Website URL:</label>
                <input type="url" id="schoolWebsite" name="schoolWebsite" class="input-field mt-1 block w-full px-4 py-3 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 ease-in-out">
            </div>

            <!-- Principal or Headmaster's Name -->
            <div class="input-group col-span-1">
                <label for="principalName" class="block text-lg font-medium text-gray-700">Principal or Headmaster's Name:</label>
                <input type="text" id="principalName" name="principalName" required class="input-field mt-1 block w-full px-4 py-3 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 ease-in-out">
            </div>

            <!-- Administrative Contact Name -->
            <div class="input-group col-span-1">
                <label for="adminContactName" class="block text-lg font-medium text-gray-700">Administrative Contact Name:</label>
                <input type="text" id="adminContactName" name="adminContactName" class="input-field mt-1 block w-full px-4 py-3 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 ease-in-out">
            </div>

            <!-- Administrative Email Address -->
            <div class="input-group col-span-1">
                <label for="adminEmail" class="block text-lg font-medium text-gray-700">Administrative Email Address:</label>
                <input type="email" id="adminEmail" name="adminEmail" class="input-field mt-1 block w-full px-4 py-3 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 ease-in-out">
            </div>

            <!-- Administrative Phone Number -->
            <div class="input-group col-span-1">
                <label for="adminPhone" class="block text-lg font-medium text-gray-700">Administrative Phone Number:</label>
                <input type="tel" id="adminPhone" name="adminPhone" class="input-field mt-1 block w-full px-4 py-3 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 ease-in-out">
            </div>

            <!-- School Logo Upload -->
            <div class="input-group col-span-1">
                <label for="schoolLogo" class="block text-lg font-medium text-gray-700">School Logo:</label>
                <input type="file" id="schoolLogo" name="schoolLogo" required class="input-field mt-1 block w-full file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition duration-200 ease-in-out">
            </div>

            <!-- Submit Button -->
            <div class="flex justify-center col-span-1 md:col-span-2 lg:col-span-3">
                <button type="submit" class="inline-flex justify-center py-3 px-6 border border-transparent shadow-sm text-lg font-medium rounded-md text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200 ease-in-out">
                    Register School
                </button>
            </div>
        </form>
        <div class="text-center mt-4">
            <a href="account_registration.php" class="text-link">Have a school code?</a>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#schoolEmail').on('input', function() {
            var email = $(this).val();
            if (email.length > 5 && email.includes('@')) {
                $.ajax({
                    url: 'check_subscription.php', // PHP script to check the subscription status
                    type: 'POST',
                    data: {email: email},
                    success: function(response) {
                        if (response === '1') {
                            $('#emailStatus').addClass('green-tick').removeClass('red-cross');
                            $('#emailFeedback').text('');
                        } else if (response === '0') {
                            $('#emailStatus').addClass('red-cross').removeClass('green-tick');
                            $('#emailFeedback').text('Subscription inactive. Redirecting to payment...');
                            setTimeout(function() {
                                window.location.href = 'payment_page.php';
                            }, 3000);
                        } else {
                            $('#emailStatus').removeClass('green-tick red-cross');
                            $('#emailFeedback').text('Email does not exist.');
                        }
                    }
                });
            } else {
                $('#emailStatus').removeClass('green-tick red-cross');
                $('#emailFeedback').text('');
            }
        });
    });
</script>
</body>
</html>
