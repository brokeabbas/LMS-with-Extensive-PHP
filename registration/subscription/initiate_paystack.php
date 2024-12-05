<?php
require_once '../../connections/vendor/autoload.php'; // Paystack library via Composer
require_once '../../connections/db_support.php'; // Include the database connection

use Yabacon\Paystack;

$secretKey = "sk_live_67ca50728d3741028be54cf0cbbd14af1511a7b5"; // Actual Paystack Secret Key
$paystack = new Paystack($secretKey);

// Initialize variables
$email = '';
$error_message = '';
$success_message = '';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the email from POST data
    $email = isset($_POST['school_email']) ? $_POST['school_email'] : 'default@example.com'; // Fallback to a default email if none is provided
    $email = $schSuppConn->real_escape_string($email);

    // Check if the email is already in the subscribers table
    $query = "SELECT * FROM subscribers WHERE email = '$email'";
    $result = $schSuppConn->query($query);

    if ($result->num_rows > 0) {
        // Email already subscribed
        $error_message = 'The email address is already subscribed. Please use a different email address.';
    } else {
        try {
            // Initialize Paystack transaction
            $tranx = $paystack->transaction->initialize([
                'amount' => 8500000, // in kobo (85,000 NGN)
                'email' => $email,
                'callback_url' => 'https://schoolhub.ng/registration/subscription/payment_callback.php'
            ]);

            // Check if transaction was successfully initialized
            if ($tranx->status) {
                // Redirect to payment page
                header('Location: ' . $tranx->data->authorization_url);
                exit;
            } else {
                $error_message = 'Transaction initialization failed. Please try again later.';
            }
        } catch (\Yabacon\Paystack\Exception\ApiException $e) {
            $error_message = 'Payment initialization failed: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription</title>
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .error-message {
            color: #ff0000;
            margin-bottom: 20px;
        }
        .success-message {
            color: #28a745;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        input[type="email"], input[type="submit"] {
            padding: 10px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Subscribe</h1>
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <form method="POST" action="initiate_paystacks">
            <label for="school_email">Email:</label>
            <input type="email" id="school_email" name="school_email" required value="<?php echo htmlspecialchars($email); ?>">
            <input type="submit" value="Subscribe">
        </form>
    </div>
</body>
</html>

<?php
$schSuppConn->close();
?>
