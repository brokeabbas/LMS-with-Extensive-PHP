<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Update Status</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-6 rounded-lg shadow-lg text-center">
        <?php
        session_start();
        if (isset($_SESSION['message'])) {
            echo "<p class='text-lg font-semibold'>" . htmlspecialchars($_SESSION['message']) . "</p>";
            unset($_SESSION['message']);
        } else {
            echo "<p class='text-lg font-semibold'>No message to display.</p>";
        }
        ?>
    </div>
</body>
</html>
