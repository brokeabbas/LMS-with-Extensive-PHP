<?php
session_start(); // Start or resume the session

require_once '../../connections/db.php'; // Include the database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if email is already subscribed
    $stmt = $dbConn->prepare("SELECT * FROM subscribers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Email already subscribed
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Email is already subscribed. Please try another.'];
    } else {
        // Subscribe the email
        $stmt = $dbConn->prepare("INSERT INTO subscribers (email, subscribed_at, subscription_status) VALUES (?, NOW(), 1)");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Subscription successful!'];
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'There was an error subscribing. Please try again.'];
        }
    }

    $stmt->close();
    header("Location: " . htmlspecialchars($_SERVER["HTTP_REFERER"]));
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
    <title>Subscription Plans - School Management System</title>
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

        /* Add keyframes for futuristic animations */
        @keyframes futuristic-pulse {
            0% {
                box-shadow: 0 0 5px rgba(13, 110, 253, 0.5);
            }
            50% {
                box-shadow: 0 0 20px rgba(13, 110, 253, 1);
            }
            100% {
                box-shadow: 0 0 5px rgba(13, 110, 253, 0.5);
            }
        }

        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
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
                <li><a href="../myschool_login.php" class="nav-link futuristic-icon"><i class="fas fa-school mr-2"></i>My School</a></li>
                <li><a href="../sign_up.php" class="nav-link futuristic-icon"><i class="fas fa-sign-in-alt mr-2"></i>Already Registered?</a></li>
                <li><a href="../login.php" class="nav-link futuristic-icon"><i class="fas fa-sign-in-alt mr-2"></i>Login</a></li>
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
            <li><a href="../myschool_login.php" class="nav-link"><i class="fas fa-school mr-2"></i>My School</a></li>
            <li><a href="../sign_up.php" class="nav-link futuristic-icon"><i class="fas fa-sign-in-alt mr-2"></i>Already Registered?</a></li>
            <li><a href="../login.php" class="nav-link"><i class="fas fa-sign-in-alt mr-2"></i>Login</a></li>
        </ul>
    </div>
</header>

<main class="mt-10">
    <div class="container mx-auto px-4">
        <div class="bg-white p-8 rounded-lg shadow-lg card-shadow">
            <h1 class="text-4xl font-bold text-center text-blue-900 mb-6">Subscribe to School Hub Today!</h1>
            <p class="text-lg text-gray-700 text-center mb-8">Join our educational platform with exclusive subscription plans designed to provide comprehensive management tools and resources for your school.</p>
            <div class="grid md:grid-cols-3 gap-6">
                <!-- Premium Plan -->
                <div class="bg-blue-100 rounded-xl p-6 shadow-xl border border-blue-300 transition duration-300 transform hover:scale-105">
                    <h2 class="text-2xl font-bold text-blue-900 mb-4">My School</h2>
                    <p class="text-gray-700 mb-4">My school plan, Made exclusively for Secondary/high schools looking to up their game, includes access to all platform features, providing a holistic approach to school management. Features include:</p>
                    <ul class="list-disc list-inside text-gray-700 mb-6">
                        <li>Gradebook Management</li>
                        <li>Advanced Attendance Tracking</li>
                        <li>Student Performance Analytics and Reporting</li>
                        <li>Student-Teacher Communication Tools</li>
                        <li>Digitalized School Curriculum</li>
                        <li>24/7 Customer Support</li>
                        <li>And Much More. Subscribe or try out a demo for your school today.</li>
                    </ul>
                    <p class="text-lg text-gray-700 font-semibold mb-6">Only ₦85,000 / month</p>
                    <form action="initiate_paystack.php" method="POST" class="mb-4">
                        <input type="email" name="school_email" placeholder="Enter School Email" required class="mb-4 p-3 w-full rounded-lg border border-gray-300">
                        <input type="hidden" name="email" value="user@example.com">
                        <button type="submit" class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Subscribe Now</button>
                    </form>
                    <div class="text-center">
                        <a href="request_demo.php" class="inline-block mt-2 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">Request a Demo</a>
                    </div>
                </div>
                <!-- Student Hub Plan -->
                <div class="bg-green-100 rounded-xl p-6 shadow-xl border border-green-300 transition duration-300 transform hover:scale-105">
                    <h2 class="text-2xl font-bold text-green-900 mb-4">Student Hub</h2>
                    <p class="text-gray-700 mb-4">Empower students with tools to enhance their learning experience. Features include:</p>
                    <ul class="list-disc list-inside text-gray-700 mb-6">
                        <li>Personalized Learning Paths</li>
                        <li>Homework and Assignment Tracking</li>
                        <li>Access to Educational Resources</li>
                        <li>Interactive Forums and Discussion Boards</li>
                        <li>Progress Reports and Analytics</li>
                    </ul>
                    <p class="text-lg text-gray-700 font-semibold mb-6">Only ₦5,000 / month</p>
                    <form action="../../info/info_studenthub.php" method="POST" class="mb-4">
                        <button type="submit" class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">Learn More</button>
                    </form>
                </div>
                <!-- Teacher Hub Plan -->
                <div class="bg-purple-100 rounded-xl p-6 shadow-xl border border-purple-300 transition duration-300 transform hover:scale-105">
                    <h2 class="text-2xl font-bold text-purple-900 mb-4">Teacher Hub</h2>
                    <p class="text-gray-700 mb-4">Equip teachers with the necessary tools to enhance their teaching experience. Features include:</p>
                    <ul class="list-disc list-inside text-gray-700 mb-6">
                        <li>Lesson Planning and Management</li>
                        <li>Attendance and Grade Tracking</li>
                        <li>Communication Tools with Parents and Students</li>
                        <li>Professional Development Resources</li>
                        <li>Classroom Management Tools</li>
                    </ul>
                    <p class="text-lg text-gray-700 font-semibold mb-6">Only ₦2,000 / month</p>
                    <form action="../../info/info_teacherhub.php" method="POST" class="mb-4">
                        <button type="submit" class="w-full bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">Learn More</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="bg-white p-8 rounded-lg shadow-lg card-shadow mt-10">
            <h2 class="text-3xl font-bold text-center text-blue-900 mb-4">Already Paid? Complete Your Registration</h2>
            <p class="text-lg text-gray-700 text-center mb-6">If you have already made a payment but have not completed your registration, please click the link below to verify your payment and complete the registration process.</p>
            <div class="max-w-md mx-auto bg-yellow-100 rounded-xl p-6 shadow-xl border border-yellow-300">
                <a href="../sign_up.php" class="mt-4 bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 transform hover:scale-105 text-center block">Complete Registration</a>
            </div>
        </div>
    </div>
</main>



<footer class="bg-blue-900 text-white py-8 mt-10">
    <div class="container mx-auto px-4 text-center md:text-left">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <h3 class="text-xl font-bold mb-4">Contact Us</h3>
                <p class="mb-2">Phone: (+234) 814-088-8654</p>
                <p class="mb-2">Email: support@schoolhub.ng</p>
            </div>
            <div>
                <h3 class="text-xl font-bold mb-4">Follow Us</h3>
                <div class="flex justify-center md:justify-start space-x-4">
                    <a href="http://twitter.com/yyorganization" class="hover:text-blue-300">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 4.557a9.926 9.926 0 01-2.828.775 4.93 4.93 0 002.168-2.723 9.918 9.918 0 01-3.127 1.195 4.92 4.92 0 00-8.388 4.482A13.974 13.974 0 011.671 3.149a4.919 4.919 0 001.523 6.56 4.902 4.902 0 01-2.228-.616v.061a4.92 4.92 0 003.946 4.829 4.936 4.936 0 01-2.224.084 4.928 4.928 0 004.6 3.417 9.868 9.868 0 01-6.102 2.105c-.395 0-.779-.023-1.158-.068a13.943 13.943 0 007.557 2.212c9.055 0 14.01-7.506 14.01-14.01 0-.213-.005-.425-.014-.637a10.004 10.004 0 002.457-2.548l-.047-.02z" />
                        </svg>
                    </a>
                    <a href="http://facebook.com" class="hover:text-blue-300">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M22.675 0h-21.35C.6 0 0 .6 0 1.35v21.3C0 23.4.6 24 1.325 24h11.497v-9.294H9.693V11.31h3.13V8.413c0-3.1 1.893-4.79 4.657-4.79 1.325 0 2.463.098 2.794.142v3.24l-1.918.001c-1.503 0-1.793.714-1.793 1.763v2.31h3.587l-.468 3.397h-3.12V24h6.116C23.4 24 24 23.4 24 22.675v-21.3C24 .6 23.4 0 22.675 0z" />
                        </svg>
                    </a>
                    <a href="http://instagram.com/yyorganization" class="hover:text-blue-300">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0C8.74 0 8.332.015 7.053.072 5.775.13 4.75.372 3.89.645 2.87.97 2.01 1.454 1.32 2.145.63 2.835.146 3.695-.18 4.714-.452 5.575-.693 6.6-.75 7.878-.807 9.157-.822 9.565-.822 12.001c0 2.436.015 2.844.072 4.122.057 1.278.298 2.303.57 3.162.326 1.02.81 1.88 1.5 2.57.69.69 1.55 1.174 2.57 1.5.86.272 1.885.513 3.162.57 1.278.057 1.686.072 4.122.072 2.436 0 2.844-.015 4.122-.072 1.278-.057 2.303-.298 3.162-.57 1.02-.326 1.88-.81 2.57-1.5.69-.69 1.174-1.55 1.5-2.57.272-.86.513-1.885.57-3.162.057-1.278.072-1.686.072-4.122 0-2.436-.015-2.844-.072-4.122-.057-1.278-.298-2.303-.57-3.162-.326-1.02-.81-1.88-1.5-2.57-.69-.69-1.55-1.174-2.57-1.5-.86-.272-1.885-.513-3.162-.57C14.844.015 14.436 0 12 0zM12 5.838c3.402 0 6.162 2.76 6.162 6.162S15.402 18.162 12 18.162 5.838 15.402 5.838 12 8.598 5.838 12 5.838zm0 10.161c2.208 0 3.999-1.791 3.999-3.999S14.208 8.001 12 8.001s-3.999 1.791-3.999 3.999 1.791 3.999 3.999 3.999zm6.406-10.909c.796 0 1.441-.645 1.441-1.441 0-.796-.645-1.441-1.441-1.441-.796 0-1.441.645-1.441 1.441 0 .796.645 1.441 1.441 1.441z" />
                        </svg>
                    </a>
                </div>
            </div>
            <div>
                <h3 class="text-xl font-bold mb-4">Quick Links</h3>
                <ul class="space-y-2">
                    <li><a href="../myschool_login.php" class="hover:text-blue-300">My School</a></li>
                    <li><a href="../login.php" class="hover:text-blue-300">Are you a teacher?</a></li>
                    <li><a href="../login.php" class="hover:text-blue-300">Are you a student?</a></li>
                </ul>
            </div>
        </div>
        <div class="mt-8 text-center text-gray-400">
            <p>&copy; 2024 YY Organization. All rights reserved.</p>
        </div>
    </div>
</footer>
</body>
</html>
