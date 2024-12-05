<?php
session_start();
require_once '../../connections/db.php';  // Connection for 'userinfo' database

$username = $_GET['username'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];

    // Fetch the email from the database using the username
    $stmt = $userInfoConn->prepare("SELECT school_email FROM schools WHERE school_id = (SELECT school_id FROM school_users WHERE username = ?)");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if ($row['school_email'] === $email) {
            header("Location: reset_password.php?username=" . urlencode($username));
            exit;
        } else {
            $error = "Email does not match our records.";
        }
    } else {
        $error = "Username not found.";
    }

    $stmt->close();
    $userInfoConn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md max-w-sm w-full">
        <h2 class="text-2xl font-bold mb-6 text-center">Verify Email</h2>
        <?php if (!empty($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                <input type="email" id="email" name="email" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Submit</button>
        </form>
    </div>
</body>
</html>
