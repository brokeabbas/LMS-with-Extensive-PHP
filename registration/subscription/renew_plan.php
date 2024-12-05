<?php
session_start();
require_once '../../connections/db.php';  // Database connection for userinfo
require_once '../../connections/db_support.php';  // Database connection for school_support
require_once '../../connections/vendor/autoload.php'; // Paystack library via Composer

use Yabacon\Paystack;

$secretKey = "sk_live_67ca50728d3741028be54cf0cbbd14af1511a7b5"; // Your actual Paystack Secret Key
$paystack = new Paystack($secretKey);
$email = isset($_GET['email']) ? $_GET['email'] : null; // Get the school email from URL parameter

if (!$email) {
    echo "Email address is required for renewal.";
    exit;
}

// Define the amount to charge (e.g., 150,000 NGN in kobo)
$amount = 8500000; // 85,000 NGN

try {
    $tranx = $paystack->transaction->initialize([
        'amount' => $amount, // Amount in kobo
        'email' => $email,
        'callback_url' => 'http://schoolhub.ng/registration/subscription/payment_callback.php' // Redirect to a callback page
    ]);

    // Redirect to payment page if transaction was successfully initialized
    if ($tranx->status) {
        header('Location: ' . $tranx->data->authorization_url);
        exit;
    } else {
        echo "Failed to initialize payment.";
    }
} catch (\Yabacon\Paystack\Exception\ApiException $e) {
    echo 'Payment initialization failed: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renew Subscription</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md max-w-md w-full">
        <h2 class="text-2xl font-bold mb-6 text-center">Renew Your Subscription</h2>
        <p class="text-center text-gray-700 mb-4">Your subscription is about to expire or has expired. Please renew to continue using the platform.</p>
        <?php if (isset($email)): ?>
            <p class="text-center text-sm text-gray-600">Renewing for: <?php echo htmlspecialchars($email); ?></p>
        <?php endif; ?>
        <div class="mt-4 text-center">
            <button onclick="window.location.href='<?php echo $tranx->data->authorization_url ?? '#'; ?>'" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                Proceed to Payment
            </button>
        </div>
    </div>
</body>
</html>
