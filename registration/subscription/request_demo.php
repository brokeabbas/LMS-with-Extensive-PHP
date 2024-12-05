<?php
session_start(); // Start or resume the session

require_once '../../connections/db.php'; // Include the database connection
require_once '../../connections/db_support.php'; // Include the database connection


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if email is already subscribed
    $stmt = $schSuppConn->prepare("SELECT * FROM subscribers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Email already subscribed
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Email is already subscribed. Please try another.'];
    } else {
        // Skip payment process for demo request
        $subscription_end_date = date('Y-m-d H:i:s', strtotime('+31 days'));
        $stmt = $schSuppConn->prepare("INSERT INTO subscribers (email, subscribed_at, subscription_status) VALUES (?, NOW(), 1)");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Subscription successful! You have a demo valid for 1 month.'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'There was an error subscribing. Please try again.'];
        }
    }

    $stmt->close();
    header("Location: " . htmlspecialchars($_SERVER["PHP_SELF"]));
    exit;
}

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Clear the message from session
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Demo - School Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(13, 110, 253, 0.9); /* Use your school's color with some transparency for a modern look */
            backdrop-filter: saturate(180%) blur(20px);
            transition: background-color 0.3s;
        }

        .sticky-header:hover {
            background-color: rgba(13, 110, 253, 1);
        }

        .sticky-header {
            background: linear-gradient(90deg, #1a2a6c, #b21f1f, #fdbb2d);
            background-size: 200% 200%;
            animation: gradientBG 10s ease infinite;
        }

        .nav-link {
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: #fdbb2d; /* Match one of the gradient colors */
        }

        .hamburger svg {
            transition: transform 0.3s ease;
        }

        .hamburger:hover svg {
            transform: rotate(90deg);
        }

        body {
            background-color: #f7f8fa;
        }

        .header-link:hover {
            text-decoration: underline;
        }

        .btn-primary {
            background-color: #2563eb;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
        }

        .footer-link:hover {
            color: #bfdbfe;
        }

        .card-shadow {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 5px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .demo-link {
            display: inline-block;
            background-color: #10b981;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            transition: background-color 0.3s;
        }

        .demo-link:hover {
            background-color: #059669;
        }
    </style>
</head>
<body class="font-sans">
<header class="sticky-header shadow-md text-white w-full">
    <div class="container mx-auto flex justify-between items-center p-4">
        <div class="flex items-center">
            <img src="../../IMAGES/Logo/YY LOGO WHITE.png" alt="School Logo" class="mr-3 h-10 futuristic-icon">
            <span class="text-2xl font-semibold">SCHOOL HUB</span>
        </div>
        <nav>
            <ul class="hidden md:flex space-x-4">
                <li><a href="../../myschool/access.php" class="nav-link futuristic-icon"><i class="fas fa-school mr-2"></i>My School</a></li>
                <li><a href="#home" class="nav-link futuristic-icon"><i class="fas fa-user-graduate mr-2"></i>Student's Hub</a></li>
                <li><a href="#about" class="nav-link futuristic-icon"><i class="fas fa-chalkboard-teacher mr-2"></i>Teacher's Hub</a></li>
                <li><a href="../../registration/login.php" class="nav-link futuristic-icon"><i class="fas fa-sign-in-alt mr-2"></i>Login</a></li>
            </ul>
            <button class="hamburger md:hidden text-white focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                    <path d="M4 6h16M4 12h16m-7 6h7"></path>
                </svg>
            </button>
        </nav>
    </div>
    <div class="mobile-nav md:hidden hidden">
        <ul class="flex flex-col space-y-2 mt-4">
            <li><a href="myschool/access.php" class="nav-link"><i class="fas fa-school mr-2"></i>My School</a></li>
            <li><a href="#home" class="nav-link"><i class="fas fa-user-graduate mr-2"></i>Student's Hub</a></li>
            <li><a href="#about" class="nav-link"><i class="fas fa-chalkboard-teacher mr-2"></i>Teacher's Hub</a></li>
            <li><a href="registration/login.php" class="nav-link"><i class="fas fa-sign-in-alt mr-2"></i>Login</a></li>
        </ul>
    </div>
</header>

<main class="mt-10">
    <div class="container mx-auto px-4">
        <div class="bg-white p-8 rounded-lg shadow-lg card-shadow">
            <h1 class="text-4xl font-bold text-center text-blue-900 mb-6">Request a Demo</h1>
            <?php if (isset($message)): ?>
                <div class="text-center mb-4 <?= $message['type'] === 'success' ? 'text-green-500' : 'text-red-500' ?>">
                    <?= htmlspecialchars($message['text']) ?>
                    <?php if ($message['type'] === 'success'): ?>
                        <div class="mt-4">
                            <a href="../sign_up.php" class="btn-primary w-full py-2 px-4 rounded-lg text-white font-bold">Start Registration</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" class="max-w-lg mx-auto">
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-semibold mb-2">Email:</label>
                    <input type="email" id="email" name="email" required class="w-full p-3 rounded-lg border border-gray-300">
                </div>
                <div class="text-center">
                    <button type="submit" class="btn-primary w-full py-2 px-4 rounded-lg text-white font-bold">Request Demo</button>
                </div>
            </form>
        </div>
    </div>
</main>

<footer class="bg-blue-900 text-white py-12 mt-10">
    <div class="container mx-auto px-4 text-center md:text-left">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Contact Us -->
            <div>
                <h3 class="text-xl font-bold mb-4">Contact Us</h3>
                <p class="mb-2">Phone: (123) 456-7890</p>
                <p class="mb-2">Email: <a href="mailto:email@example.com" class="hover:text-blue-300">email@example.com</a></p>
                <p class="mb-2">Address: 1234 School St, Education City, ED 56789</p>
            </div>
            <!-- Follow Us -->
            <div>
                <h3 class="text-xl font-bold mb-4">Follow Us</h3>
                <div class="flex justify-center md:justify-start space-x-4">
                    <a href="http://twitter.com/yyorganization" class="hover:text-blue-300">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 4.557a9.926 9.926 0 01-2.828.775 4.93 4.93 0 002.168-2.723 9.918 9.918 0 01-3.127 1.195 4.92 4.92 0 00-8.388 4.482A13.974 13.974 0 011.671 3.149a4.919 4.919 0 001.523 6.56 4.902 4.902 0 01-2.228-.616v.061a4.92 4.92 0 003.946 4.829 4.936 4.936 0 01-2.224.084 4.928 4.928 0 004.6 3.417 9.868 9.868 0 01-6.102 2.105c-.395 0-.779-.023-1.158-.068a13.943 13.943 0 007.557 2.212c9.055 0 14.01-7.506 14.01-14.01 0-.213-.005-.425-.014-.637a10.004 10.004 0 002.457-2.548l-.047-.02z"/>
                        </svg>
                    </a>
                    <a href="http://facebook.com" class="hover:text-blue-300">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M22.675 0h-21.35C.6 0 0 .6 0 1.35v21.3C0 23.4.6 24 1.325 24h11.497v-9.294H9.693V11.31h3.13V8.413c0-3.1 1.893-4.79 4.657-4.79 1.325 0 2.463.098 2.794.142v3.24l-1.918.001c-1.503 0-1.793.714-1.793 1.763v2.31h3.587l-.468 3.397h-3.12V24h6.116C23.4 24 24 23.4 24 22.675v-21.3C24 .6 23.4 0 22.675 0z"/>
                        </svg>
                    </a>
                    <a href="http://instagram.com/yyorganization" class="hover:text-blue-300">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0C8.74 0 8.332.015 7.053.072 5.775.13 4.75.372 3.89.645 2.87.97 2.01 1.454 1.32 2.145.63 2.835.146 3.695-.18 4.714-.452 5.575-.693 6.6-.75 7.878-.807 9.157-.822 9.565-.822 12.001c0 2.436.015 2.844.072 4.122.057 1.278.298 2.303.57 3.162.326 1.02.81 1.88 1.5 2.57.69.69 1.55 1.174 2.57 1.5.86.272 1.885.513 3.162.57 1.278.057 1.686.072 4.122.072 2.436 0 2.844-.015 4.122-.072 1.278-.057 2.303-.298 3.162-.57 1.02-.326 1.88-.81 2.57-1.5.69-.69 1.174-1.55 1.5-2.57.272-.86.513-1.885.57-3.162.057-1.278.072-1.686.072-4.122 0-2.436-.015-2.844-.072-4.122-.057-1.278-.298-2.303-.57-3.162-.326-1.02-.81-1.88-1.5-2.57-.69-.69-1.55-1.174-2.57-1.5-.86-.272-1.885-.513-3.162-.57C14.844.015 14.436 0 12 0zM12 5.838c3.402 0 6.162 2.76 6.162 6.162S15.402 18.162 12 18.162 5.838 15.402 5.838 12 8.598 5.838 12 5.838zm0 10.161c2.208 0 3.999-1.791 3.999-3.999S14.208 8.001 12 8.001s-3.999 1.791-3.999 3.999 1.791 3.999 3.999 3.999zm6.406-10.909c.796 0 1.441-.645 1.441-1.441 0-.796-.645-1.441-1.441-1.441-.796 0-1.441.645-1.441 1.441 0 .796.645 1.441 1.441 1.441z"/>
                        </svg>
                    </a>
                </div>
            </div>
            <!-- Quick Links -->
            <div>
                <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li><a href="myschool/access.php" class="hover:text-blue-300">My School</a></li>
                    <li><a href="index.php" class="hover:text-blue-300">Home</a></li>
                    <li><a href="#about" class="hover:text-blue-300">About</a></li>
                    <li><a href="#contact" class="hover:text-blue-300">Contact</a></li>
                    <li><a href="registration/login.php" class="hover:text-blue-300">Login</a></li>
                </ul>
            </div>
            <!-- Newsletter -->
            <div>
                <h3 class="text-xl font-bold mb-4">Newsletter</h3>
                <p class="mb-2">Sign up to receive updates and special offers.</p>
                <form action="newsletter_signup.php" method="post">
                    <input type="email" name="email" placeholder="Your email" class="w-full p-2 rounded mb-2">
                    <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Subscribe</button>
                </form>
            </div>
        </div>
        <div class="mt-8 text-center text-gray-400">
            <p>&copy; 2024 School Hub. All rights reserved.</p>
        </div>
    </div>
</footer>
</body>
</html>
