<?php
session_start();
require_once '../connections/db.php';  // Database connection for userinfo
require_once '../connections/db_support.php';  // Database connection for school_support

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION['school_id'])) {
    header("location: ../registration/myschool_login.php");
    exit;
}

$schoolName = "Default School Name";  // Default name if not found
$subscriptionStatus = "Unknown";  // Default subscription status
$subscriptionColor = "gray";  // Default color for unknown status
$nextPaymentDate = "";
$renewLink = "#";
$daysLeftMessage = "";

// Retrieve the school name and email from the database
$school_id = $_SESSION['school_id'];
$stmt = $userInfoConn->prepare("SELECT school_name, school_email FROM schools WHERE school_id = ?");
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $schoolName = $row['school_name'];
    $schoolEmail = $row['school_email'];
}

$stmt->close();

// Check the subscription status using the school email
if (isset($schoolEmail)) {
    $subStmt = $schSuppConn->prepare("SELECT subscribed_at, subscription_status FROM subscribers WHERE email = ?");
    $subStmt->bind_param("s", $schoolEmail);
    $subStmt->execute();
    $subStmt->bind_result($subscribedAt, $subStatus);
    if ($subStmt->fetch()) {
        if ($subStatus == 1) {
            $subscriptionStatus = "Active";
            $subscriptionColor = "green";

            // Calculate next payment date
            $subscribedAtDateTime = new DateTime($subscribedAt);
            $subscribedAtDateTime->add(new DateInterval('P31D'));
            $nextPaymentDate = $subscribedAtDateTime->format('Y-m-d');
            
            // Determine days left for subscription expiration
            $today = new DateTime();
            $daysLeft = $today->diff($subscribedAtDateTime)->format("%a");
            
            if ($daysLeft <= 7) {
                $subscriptionColor = "yellow";
                $daysLeftMessage = "$daysLeft days left to renew.";
            }
        } else {
            $subscriptionStatus = "Expired";
            $subscriptionColor = "red";
        }
        $renewLink = "../registration/subscription/renew_plan.php?email=" . urlencode($schoolEmail);
    } else {
        $subscriptionStatus = "No Subscription Found";
        $subscriptionColor = "red";
        $renewLink = "renew_subscription.php?email=" . urlencode($schoolEmail);
    }
    $subStmt->close();
}

$userInfoConn->close();
$schSuppConn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Access Panel - School Management System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@heroicons/vue/solid"></script>
    <link rel="icon" href="../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background-color: #edf2f7; /* Lighter background for a fresher look */
        }
        .card-hover:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease-in-out;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); /* Smoother shadow effect */
        }
        .link-button:hover {
            background-color: #667eea; /* Softer blue for a more modern look */
            color: white;
        }
        .icon {
            height: 24px;
            width: 24px;
            color: #4c51bf; /* Consistent icon color with theme */
        }
        .nav-link:hover {
            color: #2b6cb0; /* Consistent hover effect */
        }
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); /* More vibrant gradient header */
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
        .animated-button {
            animation: fadeIn 2s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
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
    </style>
</head>
<body class="bg-gray-100">
    <!-- Admin Dashboard Header -->
    <header class="sticky-header shadow-md text-white w-full">
        <div class="container mx-auto flex justify-between items-center p-4">
            <div class="flex items-center">
                <img src="../IMAGES/3.png" alt="School Logo" class="mr-3 h-10 futuristic-icon">
                <span class="text-2xl font-semibold"><?php echo htmlspecialchars($schoolName); ?> | Admin Dashboard</span>
            </div>
            <!-- Navigation Links -->
            <nav>
                <ul class="hidden md:flex space-x-4">
                    <li><a href="dashboard.php" class="nav-link futuristic-icon">DASHBOARD</a></li>
                    <li><a href="system-settings.php" class="nav-link futuristic-icon">SETTINGS</a></li>
                    <li><a href="support.php" class="nav-link futuristic-icon">SUPPORT</a></li>
                    <li><a href="../registration/logout.php" class="nav-link futuristic-icon">LOG OUT</a></li>
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
                <li><a href="dashboard.php" class="nav-link">DASHBOARD</a></li>
                <li><a href="system-settings.php" class="nav-link">SETTINGS</a></li>
                <li><a href="support.php" class="nav-link">SUPPORT</a></li>
                <li><a href="../registration/logout.php" class="nav-link">LOG OUT</a></li>
            </ul>
        </div>
    </header>
    
    <div class="container mx-auto mt-4">
        <div class="bg-<?php echo $subscriptionColor; ?>-500 text-white text-center p-3 rounded-md">
            Subscription: <?php echo $subscriptionStatus; ?>
            <?php if (!empty($nextPaymentDate)): ?>
                | Next Payment Due: <?php echo $nextPaymentDate; ?>
            <?php endif; ?>
            <?php if (!empty($daysLeftMessage)): ?>
                - <?php echo $daysLeftMessage; ?>
            <?php endif; ?>
        </div>
        
        <?php if ($subscriptionStatus == "Expired" || $subscriptionStatus == "No Subscription Found"): ?>
            <div class="text-center mt-3">
                <a href="<?php echo $renewLink; ?>" class="px-4 py-2 text-white font-bold bg-red-500 rounded-full hover:bg-red-700 transition duration-300">
                    Renew Now
                </a>
            </div>
        <?php endif; ?>
    </div>

    <main class="mt-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Cards Container -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Create Student Card -->
                <div class="bg-white overflow-hidden shadow-lg rounded-lg card-hover futuristic-border">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <h3 class="text-lg leading-6 font-medium text-gray-900 ml-2">Create Student</h3>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">
                            Add a new student to the system.
                        </p>
                        <a href="students/create-student.php" class="mt-4 futuristic-link">Go to create student</a>
                    </div>
                </div>

                <!-- Create Teacher Card -->
                <div class="bg-white overflow-hidden shadow-lg rounded-lg card-hover futuristic-border">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <h3 class="text-lg leading-6 font-medium text-gray-900 ml-2">Create Teacher</h3>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">
                            Register a new teacher in the system.
                        </p>
                        <a href="teachers/create-teacher.php" class="mt-4 futuristic-link">Go to create teacher</a>
                    </div>
                </div>

                <!-- Manage Users Card -->
                <div class="bg-white overflow-hidden shadow-lg rounded-lg card-hover futuristic-border">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <h3 class="text-lg leading-6 font-medium text-gray-900 ml-2">Manage Users</h3>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">
                            Manage existing student and teacher profiles.
                        </p>
                        <a href="manage/manage-users.php" class="mt-4 futuristic-link">Go to manage users</a>
                    </div>
                </div>

                <!-- Timetable Management Card -->
                <div class="bg-white overflow-hidden shadow-lg rounded-lg card-hover futuristic-border">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon h-6 w-6 mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2m-6 0h6m-6 0V5a2 2 0 012-2h4a2 2 0 012 2v2m-6 0h6m0 0v11a2 2 0 01-2 2H6a2 2 0 01-2-2V7h12z" />
                            </svg>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">School Curriculum Management</h3>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">
                            Create and modify class timetables, efficiently manage classroom and assign teachers and students to them.
                        </p>
                        <a href="timetable/curriculum_manager.php" class="mt-4 futuristic-link">Manage Classes And Subjects</a>
                    </div>
                </div>

                <!-- Suggestion Box Card -->
                <div class="bg-white overflow-hidden shadow-lg rounded-lg card-hover futuristic-border">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="icon h-6 w-6 mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm0 0v7m0 0h-4m4 0h4m-4 0h-4m4 0H9m3-16h-2m4 0h2m-2 0H7m6 0V7m0 1V6m0 1V4" />
                            </svg>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Suggestion Box</h3>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">
                            View the opinions of your teachers and students.
                        </p>
                        <a href="suggestions/suggestion_box.php" class="mt-4 futuristic-link">View Suggestions</a>
                    </div>
                </div>

                <!-- Student Behavior Tracking Card -->
                <div class="bg-white overflow-hidden shadow-lg rounded-lg card-hover futuristic-border">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.5 4.5m-9 0L15 10m0 0l-9-9m0 0L4.5 6M10 3h6m6 6h-6"></path>
                            </svg>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Student Behavior Tracking</h3>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">
                            Monitor and record student behavior incidents, manage disciplinary actions, and generate reports for parents and educators.
                        </p>
                        <a href="student_behaviour/student_behaviour.php" class="mt-4 futuristic-link">Manage Behavior</a>
                    </div>
                </div>

                <!-- Teacher Inspection Card -->
                <div class="bg-white overflow-hidden shadow-lg rounded-lg card-hover futuristic-border">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 0H8m4 0h4M12 4v4m0 4v4m0 0h4m-4 0H8"></path>
                            </svg>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Teacher Inspection</h3>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">
                            Be up to date and alert about the module affairs of your teachers to students.
                        </p>
                        <a href="monitor_teacher/teacher-monitoring.php" class="mt-4 futuristic-link">Inspect Teacher</a>
                    </div>
                </div>

                <!-- Student Complaints Card -->
                <div class="bg-white overflow-hidden shadow-lg rounded-lg card-hover futuristic-border">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.5 4.5m-9 0L15 10m0 0l-9-9m0 0L4.5 6M10 3h6m6 6h-6"></path>
                            </svg>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Student Complaints</h3>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">
                            Know how your students feel about the affairs of the school.
                        </p>
                        <a href="student_complaints/student_complaints.php" class="mt-4 futuristic-link">View Complaints</a>
                    </div>
                </div>

                <!-- Student Inspection Card -->
                <div class="bg-white overflow-hidden shadow-lg rounded-lg card-hover futuristic-border">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 0H8m4 0h4M12 4v4m0 4v4m0 0h4m-4 0H8"></path>
                            </svg>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Student Inspection</h3>
                        </div>
                        <p class="mt-2 text-sm text-gray-600">
                            Oversee the performance of your school students.
                        </p>
                        <a href="inspect_student/inspect-student.php" class="mt-4 futuristic-link">Inspect Student</a>
                    </div>
                </div>

                <!-- School Calendar Planning Card -->
                <div class="bg-white overflow-hidden shadow-lg rounded-lg card-hover futuristic-border">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M19 3v4M6 9h12M6 19h12M6 13h8M6 17h8M6 7h12M9 21V3M15 21V3"></path>
                        </svg>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">School Calendar Planning</h3>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">
                        Manage and publish the Schools Calendar to structure and plan the activities that will take place in your school over the coming weeks.
                    </p>
                    <a href="calender/school_calender.php" class="mt-4 futuristic-link">Manage Calendar</a>
                </div>
            </div>

            <!-- Student Achievements Card -->
            <div class="bg-white overflow-hidden shadow-lg rounded-lg card-hover futuristic-border">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 0H8m4 0h4M12 4v4m0 4v4m0 0h4m-4 0H8"></path>
                        </svg>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Student Achievements</h3>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">
                        Reward and recognize the students of your school, who excel in various activities from sports, learning, behavioral practices, and many more.
                    </p>
                    <a href="student_achievements/achievement_page.php" class="mt-4 futuristic-link">Manage Achievements</a>
                </div>
            </div>

            <!-- E-Library Card -->
            <div class="bg-white overflow-hidden shadow-lg rounded-lg card-hover futuristic-border">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m0 0l8-8m-8 8H4m8-16v16m0 0l8-8m-8 8H4m16-8H8m8-8h-8M8 12H4"></path>
                        </svg>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">E-Library</h3>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">
                        Grant digital access to exclusive papers, textbooks, novels, etc. so your students and teachers can gain knowledge within and outside of school premises.
                    </p>
                    <a href="library/manage_library.php" class="mt-4 futuristic-link">Manage Library</a>
                </div>
            </div>

            <!-- School Website Card -->
            <div class="bg-white overflow-hidden shadow-lg rounded-lg card-hover futuristic-border">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="icon h-6 w-6 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zm0 0v7m0 0h-4m4 0h4m-4 0H9m3-16h-2m4 0h2m-2 0H7m6 0V7m0 1V6m0 1V4"></path>
                        </svg>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">School Website</h3>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">
                        Does your school have its own website? Contact us today to put you on the World Wide Web!.
                    </p>
                    <a href="req_website/request_website.php" class="mt-4 futuristic-link">Get Website</a>
                </div>
            </div>
        </div>
    </div>
    </main>
</body>
</html>
