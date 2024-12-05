<?php
//session_start(); // Start or resume the session

// Check if the user is logged in, otherwise redirect to login page
// if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
//    header("location: registration/myschool_login.php");
//    exit;
//}

// Include database connection file
require_once 'connections/db.php';

// $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="icon" href="IMAGES/3.png" type="image/x-icon">
    <style>
        /* Custom keyframes for background and button animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .animated-button {
            animation: fadeIn 2s ease-in-out;
        }

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
        .futuristic-icon {
            transition: transform 0.3s ease;
        }
        .futuristic-icon:hover {
            transform: scale(1.1);
        }
        .mobile-nav {
            background: #1a2a6c;
        }
        .mobile-nav a {
            color: white;
            padding: 0.5rem 1rem;
            transition: background 0.3s ease;
        }
        .mobile-nav a:hover {
            background: #b21f1f;
        }

        .hero-text {
            animation: fadeIn 3s ease-in-out;
        }

        .hero-button {
            animation: fadeIn 4s ease-in-out;
        }

        .section-heading {
            font-family: 'Poppins', sans-serif;
            font-size: 2rem; /* Decreased font size for smaller screens */
            font-weight: 600;
            color: #1e40af;
            text-shadow: 2px 2px #0ea5e9;
        }

        .section-subtext {
            font-family: 'Poppins', sans-serif;
            font-size: 1rem; /* Decreased font size for smaller screens */
            color: #374151;
        }

        .feature-link {
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .feature-link:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .feature-link::before {
            content: '➡ ';
            animation: slideIn 1s infinite;
        }

        @keyframes slideIn {
            0% { transform: translateX(-10px); opacity: 0; }
            50% { transform: translateX(0); opacity: 1; }
            100% { transform: translateX(-10px); opacity: 0; }
        }

        .full-screen-section {
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .animate-section {
            animation: fadeIn 2s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .fade-in {
            animation: fadeIn 2s ease-in-out;
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        .feature-icon {
            animation: futuristic-pulse 2s infinite;
        }

        @keyframes futuristic-pulse {
            0% { box-shadow: 0 0 5px rgba(13, 110, 253, 0.5); }
            50% { box-shadow: 0 0 20px rgba(13, 110, 253, 1); }
            100% { box-shadow: 0 0 5px rgba(13, 110, 253, 0.5); }
        }

        .section-heading {
            font-family: 'Poppins', sans-serif;
            font-size: 2rem; /* Decreased font size for smaller screens */
            font-weight: 600;
            color: #1e40af;
            text-shadow: 2px 2px #0ea5e9;
        }

        .section-subtext {
            font-family: 'Poppins', sans-serif;
            font-size: 1rem; /* Decreased font size for smaller screens */
            color: #374151;
        }
    </style>
    <script>
        // JavaScript to toggle mobile navigation
        document.addEventListener('DOMContentLoaded', () => {
            const hamburger = document.querySelector('.hamburger');
            const mobileNav = document.querySelector('.mobile-nav');
            hamburger.addEventListener('click', () => {
                mobileNav.classList.toggle('hidden');
            });
        });
    </script>
</head>
<body class="bg-gray-100 font-sans">
<header class="sticky-header shadow-md text-white w-full">
    <div class="container mx-auto flex justify-between items-center p-4">
        <div class="flex items-center">
            <img src="IMAGES/Logo/YY LOGO WHITE.png" alt="School Logo" class="mr-3 h-10 futuristic-icon">
            <span class="text-2xl font-semibold">SCHOOL HUB</span>
        </div>
        <nav>
            <ul class="hidden md:flex space-x-4">
                <li><a href="registration/myschool_login.php" class="nav-link futuristic-icon"><i class="fas fa-school mr-2"></i>My School</a></li>
                <li><a href="studenthub/home.php" class="nav-link futuristic-icon"><i class="fas fa-user-graduate mr-2"></i>Student's Hub</a></li>
                <li><a href="registration/login.php" class="nav-link futuristic-icon"><i class="fas fa-sign-in-alt mr-2"></i>Login</a></li>
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
            <li><a href="studenthub/home.php" class="nav-link"><i class="fas fa-user-graduate mr-2"></i>Student's Hub</a></li>
            <li><a href="registration/login.php" class="nav-link"><i class="fas fa-sign-in-alt mr-2"></i>Login</a></li>
        </ul>
    </div>
</header>

<div class="relative h-screen flex items-center justify-center bg-cover bg-center" style="background-image: url('IMAGES/1716284651567.png');">
    <!-- Black overlay with opacity -->
    <div class="absolute inset-0 bg-black opacity-50"></div>
    
    <div class="relative text-center hero-text px-4 md:px-0">
        <p class="text-sm uppercase text-gray-200 tracking-widest">Knowledge At your finger tips</p>
        <h1 class="text-4xl md:text-5xl font-bold text-white leading-tight mt-4">Digitalizing Your Learning Experience</h1>
        <p class="text-sm uppercase text-gray-200 tracking-widest mt-4">Click on Get Started To Register Your School Today</p>
        <a href="get-started.php">
            <button class="mt-6 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg hero-button">
                Get Started
            </button>
        </a>
    </div>
</div>

<main class="mt-10">
    <div class="full-screen-section bg-gray-50 animate-section">
        <div class="container mx-auto text-center px-4">
            <h2 class="section-heading mb-2">Who Needs School Hub?</h2>
            <p class="section-subtext mb-4">Committed to excellence in teaching, learning, and research, and developing leaders in many disciplines who make a difference globally.</p>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">
                <div class="bg-white p-6 rounded-lg shadow-lg hover:bg-blue-100 transition duration-300 flex flex-col items-center">
                    <div class="feature-icon mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">My School</h3>
                    <p class="text-gray-700 mb-4">Discover the comprehensive management features tailored for school administrators. From student records to staff management, streamline all your school's operations efficiently.</p>
                    <a href="info/info_myschool.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow transition duration-200 feature-link">Learn More</a>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg hover:bg-blue-100 transition duration-300 flex flex-col items-center">
                    <div class="feature-icon mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Teacher Hub</h3>
                    <p class="text-gray-700 mb-4">Join a vibrant community where teachers collaborate, share ideas, and find opportunities to inspire students. Earn extra income by offering teaching services, such as lessons and projects, and enrich your professional life.</p>
                    <a href="info/info_teacherhub.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow transition duration-200 feature-link">Learn More</a>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-lg hover:bg-blue-100 transition duration-300 flex flex-col items-center">
                    <div class="feature-icon mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">Student Hub</h3>
                    <p class="text-gray-700 mb-4">Engage with a supportive community designed for students. Access personalized assistance from teachers, pay for lessons or projects, and enhance your learning experience through direct interactions with educators.</p>
                    <a href="info/info_studenthub.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow transition duration-200 feature-link">Learn More</a>
                </div>
            </div>
        </div>
    </div>

    <div class="full-screen-section bg-gray-800 animate-section">
        <div class="container mx-auto text-center px-4">
            <h2 class="section-heading mb-2 text-white">My School Features</h2>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-8">
                <div class="bg-gray-700 p-6 rounded-lg shadow-lg hover:bg-gray-600 transition duration-300 flex flex-col items-center">
                    <div class="feature-icon mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-white">Administrators</h3>
                    <p class="text-gray-300 mb-4">Explore the extensive privileges available for school administrators on School Hub. Manage student records, staff details, and streamline school operations effectively.</p>
                </div>
                <div class="bg-gray-700 p-6 rounded-lg shadow-lg hover:bg-gray-600 transition duration-300 flex flex-col items-center">
                    <div class="feature-icon mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-white">Students</h3>
                    <p class="text-gray-300 mb-4">Experience a learning platform designed to help you overcome every educational challenge. Access personalized resources and support to enhance your academic journey.</p>
                </div>
                <div class="bg-gray-700 p-6 rounded-lg shadow-lg hover:bg-gray-600 transition duration-300 flex flex-col items-center">
                    <div class="feature-icon mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2 text-white">Teachers</h3>
                    <p class="text-gray-300 mb-4">Join a community where teachers collaborate, share ideas, and find opportunities to inspire students. Offer teaching services, manage classes, and professionalize your career with ease.</p>
                </div>
            </div>
            <a href="registration/subscription/request_demo.php" class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg shadow transition duration-200 mt-6 inline-block">Request Demo</a>
        </div>
    </div>

    <!-- New Content Section: Generational Benefits -->
    <div class="container mx-auto text-center px-4 mt-10">
        <h2 class="text-3xl font-bold text-blue-400 mb-4 fade-in">Generational Benefits</h2>
        <div class="bg-gray-700 p-10 rounded-lg shadow-lg hover:bg-gray-600 transition duration-300 fade-in">
            <img src="IMAGES/3.png" alt="School Hub Logo" class="mx-auto mb-8 h-16 pulse">
            <p class="text-gray-300 mb-6">Our platform not only benefits students and teachers, but it also offers substantial advantages for parents. By using our system, parents can:</p>
            <ul class="list-disc list-inside text-left text-gray-300 mb-6 space-y-2">
                <li><span class="flex items-center"><svg class="w-6 h-6 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01m-6 8h.01m6-8H7m0 0h.01M3 5h18M9 3h6m0 0v3H9V3z"></path></svg>Stay updated with their child's academic progress.</span></li>
                <li><span class="flex items-center"><svg class="w-6 h-6 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-4 9v-4m0 4v8m-6-4h12m-6 4h.01"></path></svg>Monitor attendance and ensure their child is attending classes regularly.</span></li>
                <li><span class="flex items-center"><svg class="w-6 h-6 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-3.3 0-6 2.7-6 6s2.7 6 6 6 6-2.7 6-6-2.7-6-6-6zm0 0V4m0 0L8 8m4-4l4 4"></path></svg>Access schedules and plan accordingly for their child's activities.</span></li>
                <li><span class="flex items-center"><svg class="w-6 h-6 text-blue-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10a8.38 8.38 0 01-.9 3.8l2.1 2.1-1.4 1.4-2.1-2.1A8.38 8.38 0 0114 18h-4a8.38 8.38 0 01-3.8-.9l-2.1 2.1-1.4-1.4 2.1-2.1A8.38 8.38 0 016 14v-4a8.38 8.38 0 01.9-3.8L4.8 4.1 6.2 2.7l2.1 2.1A8.38 8.38 0 0110 6h4a8.38 8.38 0 013.8.9l2.1-2.1 1.4 1.4-2.1 2.1A8.38 8.38 0 0121 10z"></path></svg>Communicate easily with teachers and school administrators.</span></li>
            </ul>
            <p class="text-gray-300">Our goal is to create a holistic educational environment that supports the needs of all stakeholders in the education system.</p>
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
                    <li><a href="registration/myschool_login.php" class="hover:text-blue-300">My School</a></li>
                    <li><a href="registration/subscription/subscribe.php" class="hover:text-blue-300">Join School Hub Today</a></li>
                    <li><a href="" class="hover:text-blue-300">Are you a teacher?</a></li>
                    <li><a href="registration/login.php" class="hover:text-blue-300">Are you a student?</a></li>
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
