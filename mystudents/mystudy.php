<?php
session_start();
require_once '../connections/db.php';  // Ensure the path to the database connection file is correct
require_once '../connections/db_school_data.php';  // Ensure the path to the school data connection file is correct

// Authentication and session checks
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login_student.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$school_id = $_SESSION['school_id'];

// SQL to fetch grades linked with the student's modules
$sql = "SELECT ss.subject_name, g.grade, g.assessment_type, g.assessment_name, g.overall_score
FROM grades g
JOIN modules_taught mt ON g.module_id = mt.module_id
JOIN class_subject cs ON mt.module_id = cs.module_id
JOIN school_subjects ss ON cs.subject_id = ss.subject_id
WHERE g.student_id = ? AND g.school_id = ?
ORDER BY ss.subject_name";

$grades = [];

if ($stmt = $schoolDataConn->prepare($sql)) {
    $stmt->bind_param("ii", $student_id, $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $grades[] = $row;
    }
    $stmt->close();
} else {
    echo "SQL Error: " . $schoolDataConn->error;
    exit;
}

// SQL to fetch assignments linked with the student's modules and their submission status
$sql = "SELECT mt.module_code, a.id AS assignment_id, a.assignment_name, a.due_date, a.description, a.file_path, 
s.id AS submission_id, COUNT(*) OVER() AS total_assignments
FROM schoolhu_school_data.assignments a
JOIN schoolhu_school_data.student_modules sm ON a.module_id = sm.module_id
JOIN schoolhu_school_data.modules_taught mt ON sm.module_id = mt.module_id
LEFT JOIN schoolhu_school_data.assignment_submissions s ON a.id = s.assignment_id AND s.student_id = ?
WHERE sm.student_id = ? AND sm.school_id = ?
ORDER BY a.due_date DESC LIMIT 3";

$assignments = [];

if ($stmt = $schoolDataConn->prepare($sql)) {
    $stmt->bind_param("iii", $student_id, $student_id, $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }
    $stmt->close();
} else {
    die("SQL Error: " . $schoolDataConn->error);
}

// Initialize variables
$school_name = $student_name = "";

// Fetch school name
if (isset($_SESSION["school_id"])) {
    $schoolQuery = "SELECT school_name FROM schools WHERE school_id = ?";
    if ($stmt = $userInfoConn->prepare($schoolQuery)) {
        $stmt->bind_param("i", $_SESSION["school_id"]);
        $stmt->execute();
        $stmt->bind_result($school_name);
        if (!$stmt->fetch()) {
            $school_name = "School Not Found";
        }
        $stmt->close();
    } else {
        $school_name = "Error preparing school query";
    }
}

// Fetch student name using student_id
if (isset($_SESSION["student_id"])) {
    $studentQuery = "SELECT fullname FROM student_info WHERE id = ?";
    if ($stmt = $userInfoConn->prepare($studentQuery)) {
        $stmt->bind_param("i", $_SESSION["student_id"]);
        $stmt->execute();
        $stmt->bind_result($student_name);
        if (!$stmt->fetch()) {
            $student_name = "Student Not Found";
        }
        $stmt->close();
    } else {
        $student_name = "Error preparing student query";
    }
} else {
    $student_name = "No Student ID in Session";
}

$userInfoConn->close(); // Close the database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyStudy Space</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="icon" href="../IMAGES/3.png" type="image/x-icon">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            overflow-x: hidden;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Consistent font styling */
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <div class="flex min-h-screen">
        <aside class="bg-blue-900 w-64 h-screen p-6 fixed top-0 left-0 overflow-y-auto shadow-lg">
            <h2 class="text-xl font-semibold text-white mb-6">Student Dashboard</h2>
            <nav>
                <a href="../studenthub/home.php" class="sidebar-link flex items-center text-white mb-4">
                    <i class="fas fa-home mr-3"></i>Student's Hub
                </a>
                <a href="dashboard/homework.php" class="sidebar-link flex items-center text-white mb-4">
                    <i class="fas fa-book-open mr-3"></i>Homework Portal
                </a>
                <a href="dashboard/class_schedule.php" class="sidebar-link flex items-center text-white mb-4">
                    <i class="fas fa-calendar-alt mr-3"></i>Class Schedule
                </a>
                <a href="dashboard/gradebook.php" class="sidebar-link flex items-center text-white mb-4">
                    <i class="fas fa-chart-line mr-3"></i>Gradebook
                </a>
                <a href="dashboard/module_scheme.php" class="sidebar-link flex items-center text-white mb-4">
                    <i class="fas fa-graduation-cap mr-3"></i>Module Work Scheme
                </a>
                <a href="dashboard/attendance_tracking.php" class="sidebar-link flex items-center text-white mb-4">
                    <i class="fas fa-user-check mr-3"></i>Attendance Tracking
                </a>
                <a href="dashboard/school_calendar.php" class="sidebar-link flex items-center text-white mb-4">
                    <i class="fas fa-calendar-day mr-3"></i>School Calendar
                </a>
                <a href="dashboard/achievements.php" class="sidebar-link flex items-center text-white mb-4">
                    <i class="fas fa-trophy mr-3"></i>View Achievements
                </a>
                <a href="dashboard/library.php" class="sidebar-link flex items-center text-white mb-4">
                    <i class="fas fa-book mr-3"></i>E-Library
                </a>
                <a href="dashboard/disciplinary_records.php" class="sidebar-link flex items-center text-white mb-4">
                    <i class="fas fa-gavel mr-3"></i>Disciplinary Records
                </a>
                <a href="dashboard/students_complaints.php" class="sidebar-link flex items-center text-white mb-4">
                    <i class="fas fa-exclamation-triangle mr-3"></i>Students Complaints
                </a>
                <a href="dashboard/suggestion_box.php" class="sidebar-link flex items-center text-white mb-4">
                    <i class="fas fa-lightbulb mr-3"></i>Suggestion Box
                </a>
                <a href="feedback.php" class="sidebar-link flex items-center text-white mb-4">
                    <i class="fas fa-user-secret mr-3"></i>More Features...
                </a>
            </nav>
        </aside>

        <div class="flex-1 ml-64 p-6">
            <!-- Enhanced Header -->
            <header class="bg-gradient-to-r from-blue-600 to-blue-900 text-white p-6 shadow-lg rounded-lg mb-6">
                <div class="flex justify-between items-center">
                    <h1 class="text-4xl font-extrabold">Hello, <?= htmlspecialchars($student_name) ?>!</h1>
                    <nav class="flex space-x-4">
                        <a href="profile.php" class="flex items-center text-white hover:text-yellow-400">
                            <i class="fas fa-user-circle mr-2"></i> Profile
                        </a>
                        <a href="dashboard/student_comunication.php" class="flex items-center text-white hover:text-yellow-400">
                            <i class="fas fa-envelope mr-2"></i> Messages
                        </a>
                        <a href="settings.php" class="flex items-center text-white hover:text-yellow-400">
                            <i class="fas fa-cog mr-2"></i> Settings
                        </a>
                        <a href="logout_student.php" class="flex items-center text-white hover:text-yellow-400">
                            <i class="fas fa-sign-out-alt mr-2"></i> Log Out
                        </a>
                    </nav>
                </div>
            </header>

            <!-- Main Content with Animation and Icons -->
            <main>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 fade-in">
                    <?php foreach ($assignments as $assignment): ?>
                    <a href="dashboard/homework.php">
                        <div class="bg-white shadow-md rounded-lg p-6 transform hover:scale-105 transition-transform">
                            <h2 class="text-xl font-semibold"><i class="fas fa-tasks text-blue-500 mr-2"></i><?= htmlspecialchars($assignment['assignment_name']); ?></h2>
                            <p class="text-gray-700">Module: <?= htmlspecialchars($assignment['module_code']); ?></p>
                            <p class="text-gray-700">Due: <?= htmlspecialchars($assignment['due_date']); ?></p>
                            <p class="text-gray-700">Status: <?= empty($assignment['submission_id']) ? "Pending" : "Submitted"; ?></p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center mt-6 fade-in">
                    <!-- Interactive and engaging content panels -->
                    <a href="dashboard/library.php">
                        <div class="bg-pink-200 p-6 shadow-lg rounded-lg transform hover:scale-105 transition-transform">
                            <i class="fas fa-book-reader fa-3x text-pink-600 mb-2"></i>
                            <h2 class="text-2xl font-bold">Read New Books</h2>
                            <p class="text-gray-800">Explore new adventures and stories in our digital library!</p>
                        </div>
                    </a>
                    <a href="dashboard/class_schedule.php">
                        <div class="bg-green-200 p-6 shadow-lg rounded-lg transform hover:scale-105 transition-transform">
                            <i class="fas fa-chalkboard-teacher fa-3x text-green-600 mb-2"></i>
                            <h2 class="text-2xl font-bold">My Classes</h2>
                            <p class="text-gray-800">Check out your schedule to see what exciting things you'll learn!</p>
                        </div>
                    </a>
                    <a href="dashboard/achievements.php">
                        <div class="bg-blue-200 p-6 shadow-lg rounded-lg transform hover:scale-105 transition-transform">
                            <i class="fas fa-award fa-3x text-blue-600 mb-2"></i>
                            <h2 class="text-2xl font-bold">Achievements</h2>
                            <p class="text-gray-800">View your awards and track your progress in various subjects!</p>
                        </div>
                    </a>
                </div>
                <div class="bg-white shadow-lg rounded-lg p-6 mt-8 fade-in">
                    <h1 class="text-3xl font-bold text-gray-900 mb-6">Performance Tracking</h1>
                    
                    <!-- Filters -->
                    <div class="mb-6">
                        <label for="moduleFilter" class="block mb-2 text-sm font-medium text-gray-700">Filter by Module:</label>
                        <select id="moduleFilter" class="block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Modules</option>
                            <!-- Dynamically populate module options -->
                            <?php
                                $modules = array_unique(array_column($grades, 'subject_name'));
                                foreach ($modules as $module) {
                                    echo '<option value="' . htmlspecialchars($module) . '">' . htmlspecialchars($module) . '</option>';
                                }
                            ?>
                        </select>
                    </div>

                    <div class="mb-6">
                        <label for="assessmentTypeFilter" class="block mb-2 text-sm font-medium text-gray-700">Filter by Assessment Type:</label>
                        <select id="assessmentTypeFilter" class="block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Assessment Types</option>
                            <!-- Dynamically populate assessment type options -->
                            <?php
                                $assessmentTypes = array_unique(array_column($grades, 'assessment_type'));
                                foreach ($assessmentTypes as $type) {
                                    echo '<option value="' . htmlspecialchars($type) . '">' . htmlspecialchars($type) . '</option>';
                                }
                            ?>
                        </select>
                    </div>

                    <div class="relative">
                        <canvas id="gradesChart" class="rounded-lg shadow-lg"></canvas>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('gradesChart').getContext('2d');
        let grades = <?= json_encode($grades); ?>;
        const moduleFilter = document.getElementById('moduleFilter');
        const assessmentTypeFilter = document.getElementById('assessmentTypeFilter');

        function updateChart() {
            const selectedModule = moduleFilter.value;
            const selectedAssessmentType = assessmentTypeFilter.value;

            const filteredGrades = grades.filter(grade => {
                return (selectedModule === "" || grade.subject_name === selectedModule) &&
                       (selectedAssessmentType === "" || grade.assessment_type === selectedAssessmentType);
            });

            const labels = filteredGrades.map(grade => grade.assessment_name + ' / ' + grade.subject_name);
            const scores = filteredGrades.map(grade => grade.overall_score);

            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(0, 255, 255, 1)');
            gradient.addColorStop(1, 'rgba(0, 128, 128, 1)');

            const colors = scores.map(score => score >= 90 ? 'rgba(0, 255, 0, 0.6)' : 
                                              score >= 75 ? 'rgba(255, 255, 0, 0.6)' : 
                                              score >= 50 ? 'rgba(255, 165, 0, 0.6)' : 
                                                            'rgba(255, 0, 0, 0.6)');

            gradesChart.data.labels = labels;
            gradesChart.data.datasets[0].data = scores;
            gradesChart.data.datasets[0].backgroundColor = colors;
            gradesChart.update();
        }

        const gradesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: grades.map(grade => grade.assessment_name + ' / ' + grade.subject_name),
                datasets: [{
                    label: 'Scores',
                    data: grades.map(grade => grade.overall_score),
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    hoverBackgroundColor: 'rgba(75, 192, 192, 0.8)',
                    hoverBorderColor: 'rgba(255, 206, 86, 1)'
                }]
            },
            options: {
                responsive: true,
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuad'
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: 100
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const grade = grades.find(g => g.assessment_name + ' / ' + g.subject_name === context.label);
                                return `${grade.grade} out of ${grade.overall_score}`;
                            },
                            afterLabel: function(context) {
                                return context.label.split(' / ')[1];
                            }
                        }
                    }
                },
                hover: {
                    animationDuration: 400
                }
            }
        });

        moduleFilter.addEventListener('change', updateChart);
        assessmentTypeFilter.addEventListener('change', updateChart);
    </script>
</body>
</html>
