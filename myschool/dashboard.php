<?php
session_start();
require_once '../connections/db_school_data.php';
require_once '../connections/db.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../registration/myschool_login.php");
    exit;
}

$school_id = $_SESSION['school_id'];
$totalStudents = $totalTeachers = $totalClasses = 0;
$upcomingEvents = [];
$today = date('Y-m-d');
$currentWeekNumber = date('W', strtotime($today));
$weeklyEvents = [];
$schoolCalendar = [];
$schoolLogo = '../IMAGES/3.png';
$attendanceData = [];

if (isset($school_id)) {
    $result = $userInfoConn->query("SELECT school_logo FROM schools WHERE school_id = $school_id");
    if ($result && $row = $result->fetch_assoc()) {
        $schoolLogo = $row['school_logo'] ? $row['school_logo'] : $schoolLogo;
    }

    $result = $schoolDataConn->query("SELECT COUNT(*) AS total FROM userinfo.student_info WHERE school_id = $school_id AND is_active = 1");
    if ($result && $row = $result->fetch_assoc()) {
        $totalStudents = $row['total'];
    }

    $result = $schoolDataConn->query("SELECT COUNT(*) AS total FROM userinfo.teacher_info WHERE school_id = $school_id");
    if ($result && $row = $result->fetch_assoc()) {
        $totalTeachers = $row['total'];
    }

    $result = $schoolDataConn->query("SELECT COUNT(*) AS total FROM school_data.classes WHERE school_id = $school_id");
    if ($result && $row = $result->fetch_assoc()) {
        $totalClasses = $row['total'];
    }

    if (isset($school_id)) {
        $stmt = $schoolDataConn->prepare("
            SELECT name, description, start_date, end_date, time, location 
            FROM extracurricular_activities 
            WHERE school_id = ? AND type = 'event' AND start_date >= CURDATE() AND end_date >= CURDATE()
            ORDER BY start_date ASC LIMIT 5");
        $stmt->bind_param("i", $school_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        while ($row = $result->fetch_assoc()) {
            $upcomingEvents[] = $row;
        }
        $stmt->close();
    }

    if ($school_id > 0) {
        $rangeResult = $schoolDataConn->query("SELECT MIN(event_week) as min_week, MAX(event_week) as max_week FROM school_calendar WHERE school_id = $school_id");
        $range = $rangeResult->fetch_assoc();
        $minWeek = $range['min_week'];
        $maxWeek = $range['max_week'];

        $displayWeek = max($minWeek, min($currentWeekNumber, $maxWeek));

        $stmt = $schoolDataConn->prepare("
            SELECT event_title, event_date, event_description 
            FROM school_calendar 
            WHERE school_id = ? AND event_week = ? AND is_published = 1
            ORDER BY event_date ASC");
        $stmt->bind_param("ii", $school_id, $displayWeek);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $schoolCalendar[] = $row;
        }
        $stmt->close();
    }

    $result = $schoolDataConn->query("
        SELECT attendance_date, COUNT(DISTINCT student_id) as attendance_count
        FROM attendance_records
        WHERE school_id = $school_id
        GROUP BY attendance_date
        ORDER BY attendance_date ASC");
    while ($row = $result->fetch_assoc()) {
        $attendanceData[] = $row;
    }

    $schoolDataConn->close();
} else {
    echo "School ID not set in session.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="../IMAGES/3.png" type="image/x-icon">
    <style>
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(13, 110, 253, 0.9);
            backdrop-filter: saturate(180%) blur(20px);
            transition: background-color 0.3s;
            background: linear-gradient(90deg, #1a2a6c, #b21f1f, #fdbb2d);
            background-size: 200% 200%;
            animation: gradientBG 10s ease infinite;
        }
        .sticky-header:hover {
            background-color: rgba(13, 110, 253, 1);
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
        .sidebar {
            height: 100vh;
            width: 64px;
            position: fixed;
            top: 0;
            left: 0;
            background: #2d3748;
            color: #fff;
            transition: width 0.3s;
        }
        .sidebar:hover {
            width: 200px;
        }
        .sidebar-links {
            padding: 20px;
        }
        .sidebar-links a {
            display: block;
            padding: 10px;
            color: #cbd5e0;
            text-decoration: none;
            transition: background 0.3s, color 0.3s;
        }
        .sidebar-links a:hover {
            background: #4a5568;
            color: #fff;
        }
        .content {
            margin-left: 64px;
            padding: 20px;
            transition: margin-left 0.3s;
        }
        .sidebar:hover ~ .content {
            margin-left: 200px;
        }
        .card {
            background: white;
            color: #2d3748;
            border-radius: 10px;
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .header {
            background: linear-gradient(90deg, #1a2a6c, #b21f1f, #fdbb2d);
            background-size: 200% 200%;
            animation: gradientBG 10s ease infinite;
            color: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .quick-link-card {
            padding: 20px;
            border-radius: 10px;
            color: #fff;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .quick-link-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body class="bg-gray-100">
<header class="sticky-header shadow-md text-white w-full">
    <div class="container mx-auto flex justify-between items-center p-4">
        <div class="flex items-center">
            <img src="../IMAGES/3.png" alt="School Logo" class="mr-3 h-10">
            <span class="text-2xl font-semibold">Admin Dashboard</span>
        </div>
        <nav>
            <ul class="hidden md:flex space-x-4">
                <li><a href="system-settings.php" class="nav-link">Settings</a></li>
                <li><a href="support.php" class="nav-link">Support</a></li>
                <li><a href="../registration/logout.php" class="nav-link">Log Out</a></li>
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
            <li><a href="system-settings.php" class="nav-link">Settings</a></li>
            <li><a href="support.php" class="nav-link">Support</a></li>
            <li><a href="../registration/logout.php" class="nav-link">Log Out</a></li>
        </ul>
    </div>
</header>

<div class="sidebar">
    <div class="sidebar-links">
        <a href="../registration/logout.php">Log Out</a>
        <a href="students/create-student.php">Create Students</a>
        <a href="teachers/create-teacher.php">Create Teachers</a>
        <a href="support.php">Help</a>
        <a href="access.php">Control Panel</a>
    </div>
</div>

<div class="content">
    <div class="header">
        <h1>Welcome to the School Dashboard</h1>
        <p>Overview of the school statistics and quick access to management tools</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8 text-center">
        <div class="card">
            <h2>Total Students</h2>
            <p><?php echo $totalStudents; ?></p>
        </div>
        <div class="card">
            <h2>Total Teachers</h2>
            <p><?php echo $totalTeachers; ?></p>
        </div>
        <div class="card">
            <h2>Total Classes</h2>
            <p><?php echo $totalClasses; ?></p>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6 mb-8">
        <div class="quick-link-card bg-blue-500">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 text-white mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M3 21h18a2 2 0 002-2V7a2 2 0 00-2-2h-1.5a1.5 1.5 0 010-3H16a1.5 1.5 0 010 3H8a1.5 1.5 0 010-3H6.5a1.5 1.5 0 010-3H5a1.5 1.5 0 010-3H3a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <h2>School Calendar</h2>
            </div>
            <p>Manage school events and schedules.</p>
            <a href="calendar/calendar_view.php">View Calendar</a>
        </div>
        
        <div class="quick-link-card bg-green-500">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 text-white mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18" />
                </svg>
                <h2>Suggestion Box</h2>
            </div>
            <p>Read feedback from students and staff.</p>
            <a href="suggestions/suggestion_box.php">View Suggestions</a>
        </div>
        
        <div class="quick-link-card bg-red-500">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 text-white mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M12 12v4m0-4v-4m0 0H6m6 0h6" />
                </svg>
                <h2>Manage Student/Teachers</h2>
            </div>
            <p>Access your school's population.</p>
            <a href="manage/manage-users.php">Manage Individuals</a>
        </div>
        
        <div class="quick-link-card bg-purple-500">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 text-white mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h12M9 4v12m0 0H5m4 0h4m0 0V4M15 10h6m-6 4h6m-6-4V4m6 10v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2" />
                </svg>
                <h2>Control Panel</h2>
            </div>
            <p>Access administrative settings and control features.</p>
            <a href="access.php">Open Control Panel</a>
        </div>
    </div>
</div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var ctx = document.getElementById('attendanceChart').getContext('2d');
            var attendanceData = <?php echo json_encode($attendanceData); ?>;
            var labels = attendanceData.map(function(item) { return item.attendance_date; });
            var data = attendanceData.map(function(item) { return item.attendance_count; });

            if (data.length === 0) {
                document.getElementById('noDataMessage').style.display = 'block';
            } else {
                var chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Student Attendance',
                            data: data,
                            borderColor: 'rgba(75, 192, 192, 1)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: {
                                type: 'time',
                                time: {
                                    unit: 'day',
                                    tooltipFormat: 'MMM DD, YYYY'
                                },
                                title: {
                                    display: true,
                                    text: 'Date'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Number of Students'
                                }
                            }
                        }
                    }
                });
            }

            document.getElementById('filterButton').addEventListener('click', function() {
                var startDate = document.getElementById('startDate').value;
                var endDate = document.getElementById('endDate').value;

                if (startDate && endDate) {
                    var filteredData = attendanceData.filter(function(item) {
                        return item.attendance_date >= startDate && item.attendance_date <= endDate;
                    });

                    if (filteredData.length === 0) {
                        document.getElementById('noDataMessage').style.display = 'block';
                        chart.data.labels = [];
                        chart.data.datasets[0].data = [];
                    } else {
                        document.getElementById('noDataMessage').style.display = 'none';
                        chart.data.labels = filteredData.map(function(item) { return item.attendance_date; });
                        chart.data.datasets[0].data = filteredData.map(function(item) { return item.attendance_count; });
                    }

                    chart.update();
                }
            });
        });
    </script>
</body>
</html>
