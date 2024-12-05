<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Started - School Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="icon" href="IMAGES/3.png" type="image/x-icon">
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
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .fade-in {
            animation: fadeIn 2s ease-in-out;
        }

        .fullscreen-section {
            position: relative;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            overflow: hidden;
        }

        .gradient-background {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -2;
            background: linear-gradient(45deg, #000428, #004e92);
            background-size: 200% 200%;
            animation: gradientAnimation 5s ease infinite;
        }

        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .particles-container {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        .nav-link:hover {
            color: #3b82f6; /* Tailwind's blue-500 */
        }

        .nav-link {
            transition: color 0.3s;
        }

        .feature-icon {
            animation: futuristic-pulse 2s infinite;
        }

        @keyframes futuristic-pulse {
            0% { box-shadow: 0 0 5px rgba(13, 110, 253, 0.5); }
            50% { box-shadow: 0 0 20px rgba(13, 110, 253, 1); }
            100% { box-shadow: 0 0 5px rgba(13, 110, 253, 0.5); }
        }

        .hero-text, .section-heading, .section-subtext {
            font-family: 'Poppins', sans-serif;
        }

        .section-heading {
            font-size: 3rem;
            font-weight: 600;
            color: #1e40af;
            text-shadow: 2px 2px #0ea5e9;
        }

        .section-subtext {
            font-size: 1.25rem;
            color: #374151;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/particles.js"></script>
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
                <li><a href="registration/sign_up.php" class="nav-link futuristic-icon"><i class="fas fa-sign-in-alt mr-2"></i>Already Registered?</a></li>
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
            <li><a href="registration/sign_up.php" class="nav-link futuristic-icon"><i class="fas fa-sign-in-alt mr-2"></i>Already Registered?</a></li>
            <li><a href="registration/login.php" class="nav-link"><i class="fas fa-sign-in-alt mr-2"></i>Login</a></li>
        </ul>
    </div>
</header>

    <section class="fullscreen-section text-white">
            <div class="gradient-background"></div>
            <div id="particles-js" class="particles-container"></div>
            <h1 class="text-5xl font-bold mb-4 fade-in">Empowering the Educational Journey</h1>
            <p class="text-lg max-w-2xl mx-auto fade-in">Join us on a transformative journey where technology meets education, creating unparalleled opportunities for students, teachers, and parents alike.</p>
            <a href="info/info_myschool.php" class="mt-6 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 fade-in">Learn More</a>
        </section>

    <main class="mt-20 animated-main">

    <section class="py-16">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl font-bold text-blue-900 mb-6 fade-in">What We Do</h2>
        <p class="text-gray-600 mb-12 fade-in text-lg">Our platform offers a comprehensive suite of tools designed to maximize learning potential through the use of innovative digital platforms. With a focus on student performance analytics and cutting-edge educational technology, we strive to unlock every student's full potential.</p>
        <div class="grid md:grid-cols-3 gap-10">
            <!-- Feature 1 -->
            <div class="bg-white p-8 rounded-lg shadow-lg hover:shadow-xl transition duration-300 fade-in">
                <div class="flex justify-center mb-6">
                    <svg class="w-16 h-16 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12l9-5-9-5-9 5 9 5z"></path></svg>
                </div>
                <h3 class="text-2xl font-bold mb-4 text-center">Digital Learning Platforms</h3>
                <p class="text-gray-600 text-center mb-4">Interactive and engaging learning experiences tailored for each student, accessible anywhere, at any time.</p>
                <ul class="text-gray-600 text-sm list-disc list-inside">
                    <li>Customizable learning paths</li>
                    <li>Interactive lessons and quizzes</li>
                    <li>24/7 access to learning materials</li>
                    <li>Seamless integration with classroom activities</li>
                </ul>
            </div>
            <!-- Feature 2 -->
            <div class="bg-white p-8 rounded-lg shadow-lg hover:shadow-xl transition duration-300 fade-in">
                <div class="flex justify-center mb-6">
                    <svg class="w-16 h-16 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 20l-9-5 9-5 9 5-9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20v-8"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12V4"></path></svg>
                </div>
                <h3 class="text-2xl font-bold mb-4 text-center">Performance Analytics</h3>
                <p class="text-gray-600 text-center mb-4">Data-driven insights that track progress, identify areas for improvement, and celebrate achievements.</p>
                <ul class="text-gray-600 text-sm list-disc list-inside">
                    <li>Real-time performance dashboards</li>
                    <li>Individual and group analytics</li>
                    <li>Customizable reporting tools</li>
                    <li>Early intervention alerts</li>
                </ul>
            </div>
            <!-- Feature 3 -->
            <div class="bg-white p-8 rounded-lg shadow-lg hover:shadow-xl transition duration-300 fade-in">
                <div class="flex justify-center mb-6">
                    <svg class="w-16 h-16 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8V4m0 4l2 2-2 2m0-4H8m4 0H4m16 0h-4M4 12h16M4 16h16M4 20h16"></path></svg>
                </div>
                <h3 class="text-2xl font-bold mb-4 text-center">Technological Empowerment</h3>
                <p class="text-gray-600 text-center mb-4">Leveraging the latest in tech to provide resources that support educational excellence and innovation.</p>
                <ul class="text-gray-600 text-sm list-disc list-inside">
                    <li>State-of-the-art digital tools</li>
                    <li>Access to a vast library of resources</li>
                    <li>Virtual and augmented reality experiences</li>
                    <li>Continuous professional development for educators</li>
                </ul>
            </div>
        </div>
    </div>
</section>


<section class="bg-blue-900 text-white py-16">
    <div class="container mx-auto px-4 text-center">
        <h2 class="text-4xl font-bold mb-6 fade-in">The Path to Greatness</h2>
        <p class="max-w-2xl mx-auto mb-12 fade-in text-lg">Our approach integrates technological advancements with a personalized touch, ensuring that each member of our educational community is equipped to achieve greatness. Discover why we are the most affordable and the best choice for your educational journey.</p>
        
        <div class="grid md:grid-cols-2 gap-8 fade-in">
            <!-- Feature 1 -->
            <div class="bg-white text-blue-900 p-8 rounded-lg shadow-lg hover:shadow-xl transition duration-300">
                <h3 class="text-2xl font-bold mb-4">Innovative Technology</h3>
                <p class="text-gray-600 mb-4">We leverage cutting-edge technology to enhance the learning experience, ensuring that our students have access to the latest tools and resources.</p>
                <ul class="list-disc list-inside text-gray-600">
                    <li>State-of-the-art digital classrooms</li>
                    <li>Virtual and augmented reality learning tools</li>
                    <li>Advanced performance analytics</li>
                    <li>24/7 access to learning materials</li>
                </ul>
            </div>
            <!-- Feature 2 -->
            <div class="bg-white text-blue-900 p-8 rounded-lg shadow-lg hover:shadow-xl transition duration-300">
                <h3 class="text-2xl font-bold mb-4">Personalized Learning</h3>
                <p class="text-gray-600 mb-4">Our platform adapts to the unique needs of each student, providing personalized learning paths that cater to individual strengths and areas for improvement.</p>
                <ul class="list-disc list-inside text-gray-600">
                    <li>Customizable learning experiences</li>
                    <li>Individualized feedback and support</li>
                    <li>Progress tracking and goal setting</li>
                    <li>Interactive and engaging content</li>
                </ul>
            </div>
            <!-- Feature 3 -->
            <div class="bg-white text-blue-900 p-8 rounded-lg shadow-lg hover:shadow-xl transition duration-300">
                <h3 class="text-2xl font-bold mb-4">Affordable Excellence</h3>
                <p class="text-gray-600 mb-4">We believe that high-quality education should be accessible to everyone. Our platform offers top-notch features and services at the most competitive prices.</p>
                <ul class="list-disc list-inside text-gray-600">
                    <li>Best value for your investment</li>
                    <li>Flexible pricing plans</li>
                    <li>No hidden fees or charges</li>
                    <li>Scholarships and financial aid available</li>
                </ul>
            </div>
            <!-- Feature 4 -->
            <div class="bg-white text-blue-900 p-8 rounded-lg shadow-lg hover:shadow-xl transition duration-300">
                <h3 class="text-2xl font-bold mb-4">Community Support</h3>
                <p class="text-gray-600 mb-4">Join a vibrant community of learners, educators, and parents who are all committed to supporting each other and achieving educational excellence together.</p>
                <ul class="list-disc list-inside text-gray-600">
                    <li>Collaborative learning environments</li>
                    <li>Active forums and discussion groups</li>
                    <li>Regular webinars and workshops</li>
                    <li>Peer support and mentorship programs</li>
                </ul>
            </div>
        </div>
        
        <div class="mt-12">
            <a href="registration/subscription/subscribe.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition duration-200 fade-in">Sign Up Today</a>
        </div>
    </div>
</section>


<section class="py-16 bg-white text-gray-900">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl font-bold text-blue-900 mb-6 fade-in">Your Success is Our Goal</h2>
        <p class="text-lg text-gray-600 mb-12 fade-in max-w-3xl mx-auto">We're dedicated to not just meeting, but exceeding the educational needs of the digital age. Whether you're a student craving knowledge, a teacher aspiring to inspire, or a parent supporting a learner's dream, our platform is your stepping stone to success. Here's how we make it happen:</p>
        <div class="grid md:grid-cols-2 gap-8 fade-in">
            <!-- Feature 1 -->
            <div class="bg-blue-50 p-6 rounded-lg shadow-lg hover:shadow-xl transition duration-300">
                <h3 class="text-2xl font-bold mb-4">Student-Focused Learning</h3>
                <p class="text-gray-700 mb-4">We put students at the heart of everything we do, offering personalized learning experiences that adapt to individual needs and learning styles.</p>
                <ul class="list-disc list-inside text-gray-700">
                    <li>Adaptive learning technology</li>
                    <li>Interactive course materials</li>
                    <li>24/7 access to resources</li>
                    <li>Supportive learning community</li>
                </ul>
            </div>
            <!-- Feature 2 -->
            <div class="bg-blue-50 p-6 rounded-lg shadow-lg hover:shadow-xl transition duration-300">
                <h3 class="text-2xl font-bold mb-4">Teacher Empowerment</h3>
                <p class="text-gray-700 mb-4">We provide teachers with the tools and resources they need to inspire and engage their students, fostering an environment of creativity and growth.</p>
                <ul class="list-disc list-inside text-gray-700">
                    <li>Comprehensive training programs</li>
                    <li>Advanced teaching aids</li>
                    <li>Collaborative teaching community</li>
                    <li>Continuous professional development</li>
                </ul>
            </div>
            <!-- Feature 3 -->
            <div class="bg-blue-50 p-6 rounded-lg shadow-lg hover:shadow-xl transition duration-300">
                <h3 class="text-2xl font-bold mb-4">Parental Involvement</h3>
                <p class="text-gray-700 mb-4">We believe in the power of partnership between parents and educators. Our platform provides parents with the tools they need to support their children's education effectively.</p>
                <ul class="list-disc list-inside text-gray-700">
                    <li>Real-time progress tracking</li>
                    <li>Parental engagement resources</li>
                    <li>Regular updates and feedback</li>
                    <li>Support forums and webinars</li>
                </ul>
            </div>
            <!-- Feature 4 -->
            <div class="bg-blue-50 p-6 rounded-lg shadow-lg hover:shadow-xl transition duration-300">
                <h3 class="text-2xl font-bold mb-4">Innovative Technology</h3>
                <p class="text-gray-700 mb-4">Our cutting-edge technology enhances the educational experience, making learning more interactive, engaging, and effective for everyone involved.</p>
                <ul class="list-disc list-inside text-gray-700">
                    <li>AI-driven analytics</li>
                    <li>Secure, cloud-based access</li>
                    <li>Integrated communication systems</li>
                </ul>
            </div>
        </div>
    </div>
</section>

    </main>

    <footer class="bg-blue-900 text-white py-12 mt-10">
    <div class="container mx-auto px-4 text-center md:text-left">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Contact Us -->
            <div>
                <h3 class="text-xl font-bold mb-4">Contact Us</h3>
                <p class="mb-2">Phone: (+234) 814-088-8654</p>
                <p class="mb-2">Email: <a href="mailto:support@schoolhub.ng" class="hover:text-blue-300">support@schoolhub.ng</a></p>
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


    <script>
        particlesJS.load('particles-js', 'particles.json', function() {
            console.log('callback - particles.js config loaded');
        });
    </script>
</body>
</html>
