<?php
require_once '../../connections/vendor/autoload.php'; // Paystack library via Composer
require_once '../../connections/db_support.php'; // Include the database connection

use Yabacon\Paystack;

$secretKey = "sk_test_7b6214fc7a7d9ae8266a47bdab5f15544a289309"; // Actual Paystack Secret Key
$paystack = new Paystack($secretKey);

// Retrieve the transaction reference from the callback
$reference = isset($_GET['reference']) ? $_GET['reference'] : '';
if (!$reference) {
    die('No reference supplied');
}

try {
    // Verify the transaction
    $tranx = $paystack->transaction->verify(['reference' => $reference]);

    // Check if the transaction was successful
    if ($tranx->status && $tranx->data->status === 'success') {
        // Extract email from the transaction data
        $email = $tranx->data->customer->email;

        // Prepare a statement to insert the email into the database
        $stmt = $schSuppConn->prepare("INSERT INTO subscribers (email) VALUES (?)");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();

        // Redirect to a success page
        header('Location: http://schoolhub.ng/registration/sign_up.php');
        exit;
    } else {
        die('Transaction verification failed: ' . $tranx->data->gateway_response);
    }
} catch (\Yabacon\Paystack\Exception\ApiException $e) {
    die('Payment verification failed: ' . $e->getMessage());
} finally {
    $schSuppConn->close();
}
?>
