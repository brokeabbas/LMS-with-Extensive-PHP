<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

require_once '../../connections/db.php';  // userInfoConn for user data
require_once '../../connections/db_school_data.php';  // schoolDataConn for school data

$teacherInfo = [];
$modulesDetails = [];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['teacher_number'])) {
    $teacher_number = $_POST['teacher_number'];
    $school_id = $_SESSION['school_id']; // Fetch school_id from session

    // Fetch teacher info from userInfo database
    $stmt = $userInfoConn->prepare("SELECT * FROM teacher_info WHERE teacher_number = ? AND school_id = ?");
    $stmt->bind_param("si", $teacher_number, $school_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $teacherInfo = $result->fetch_assoc();
        $teacher_id = $teacherInfo['id'];

        // Fetching modules taught by the teacher from schoolData database
        $stmt = $schoolDataConn->prepare("
            SELECT 
                mt.module_id,
                mt.module_code, 
                ss.subject_name, 
                c.class_name 
            FROM 
                modules_taught mt 
            JOIN 
                class_subject cs ON mt.module_id = cs.module_id 
            JOIN 
                school_subjects ss ON cs.subject_id = ss.subject_id 
            JOIN 
                classes c ON cs.class_id = c.class_id
            WHERE 
                mt.teacher_id = ? AND 
                mt.school_id = ? AND 
                mt.assigned = 1
            ORDER BY 
                ss.subject_name, c.class_name, mt.module_code;
        ");
        $stmt->bind_param("ii", $teacher_id, $school_id);
        $stmt->execute();
        $modulesResult = $stmt->get_result();

        while ($module = $modulesResult->fetch_assoc()) {
            $schemesStmt = $schoolDataConn->prepare("
                SELECT 
                    week, topic, notes, assignments, materials, notes_files, assignments_files, materials_files 
                FROM 
                    schemes 
                WHERE 
                    module_id = ? AND teacher_id = ? AND school_id = ?");
            $schemesStmt->bind_param("iii", $module['module_id'], $teacher_id, $school_id);
            $schemesStmt->execute();
            $schemesResult = $schemesStmt->get_result();
            $schemes = [];
            while ($scheme = $schemesResult->fetch_assoc()) {
                $scheme['notes_files'] = array_map('trim', explode(';', $scheme['notes_files']));
                $scheme['assignments_files'] = array_map('trim', explode(';', $scheme['assignments_files']));
                $scheme['materials_files'] = array_map('trim', explode(';', $scheme['materials_files']));
                $schemes[] = $scheme;
            }
            $module['schemes'] = $schemes;

            // Fetch students enrolled in the module
            $studentsStmt = $userInfoConn->prepare("
                SELECT 
                    si.fullname, 
                    si.email,
                    si.student_number
                FROM 
                    schoolhu_school_data.student_modules sm 
                JOIN 
                    student_info si ON sm.student_id = si.id
                WHERE 
                    sm.module_id = ? AND 
                    sm.school_id = ? AND 
                    si.school_id = ?");
            $studentsStmt->bind_param("iii", $module['module_id'], $school_id, $school_id);
            $studentsStmt->execute();
            $studentsResult = $studentsStmt->get_result();
            $students = [];
            while ($student = $studentsResult->fetch_assoc()) {
                $students[] = $student;
            }
            $module['students'] = $students;
            $studentsStmt->close();

            $modulesDetails[] = $module;
            $schemesStmt->close();
        }
    } else {
        $message = "No teacher found with that number at your school.";
    }
    $stmt->close();
}

$userInfoConn->close();
$schoolDataConn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Monitoring Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(135deg, #1f2937, #3b82f6);
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
            color: #f8fafc;
        }
        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .card {
            background-color: #1e293b;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        .collapsible {
            background-color: #374151;
            color: #f8fafc;
            cursor: pointer;
            padding: 18px;
            width: 100%;
            border: none;
            text-align: left;
            outline: none;
            font-size: 15px;
            border-bottom: 1px solid #4b5563;
        }
        .active, .collapsible:hover {
            background-color: #4b5563;
        }
        .content {
            padding: 0 18px;
            display: none;
            overflow: hidden;
            background-color: #1f2937;
            transition: max-height 0.2s ease-out;
        }
    </style>
</head>
<body class="bg-gray-900 flex items-center justify-center h-screen">
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-3xl font-bold mb-6 text-center">Teacher Monitoring Dashboard</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="mb-6 mx-auto max-w-lg bg-gray-800 p-6 rounded shadow-lg">
            <div class="flex flex-col">
                <label for="teacher_number" class="block text-sm font-medium text-gray-300">Teacher Number:</label>
                <input type="text" name="teacher_number" id="teacher_number" required class="mt-1 px-4 py-2 rounded bg-gray-700 text-white">
                <button type="submit" class="mt-4 px-4 py-2 bg-blue-500 text-white font-semibold rounded hover:bg-blue-600 transition">Search</button>
            </div>
        </form>

        <?php if (!empty($teacherInfo)): ?>
            <div class="bg-gray-800 p-6 rounded shadow-lg card">
                <h2 class="text-2xl font-semibold mb-4"><?php echo htmlspecialchars($teacherInfo['name']); ?></h2>
                <p>Email: <?php echo htmlspecialchars($teacherInfo['email']); ?></p>
                <p>Department: <?php echo htmlspecialchars($teacherInfo['department']); ?></p>

                <button class="collapsible mt-4">Modules and Classes Taught</button>
                <div class="content">
                    <ul class="mt-2">
                        <?php foreach ($modulesDetails as $module): ?>
                            <li class="mb-4">
                                <p class="font-semibold"><?php echo htmlspecialchars($module['class_name']) . " - " . htmlspecialchars($module['subject_name']) . " (" . htmlspecialchars($module['module_code']) . ")"; ?></p>
                                
                                <button class="collapsible mt-2">Schemes</button>
                                <div class="content">
                                    <ul class="mt-2">
                                        <?php foreach ($module['schemes'] as $scheme): ?>
                                            <li class="mb-2">
                                                <button class="collapsible">Week <?php echo htmlspecialchars($scheme['week']); ?>: <?php echo htmlspecialchars($scheme['topic']); ?></button>
                                                <div class="content">
                                                    <p>Notes: <?php echo nl2br(htmlspecialchars($scheme['notes'])); ?></p>
                                                    <p>Assignments: <?php echo nl2br(htmlspecialchars($scheme['assignments'])); ?></p>
                                                    <p>Materials: <?php echo nl2br(htmlspecialchars($scheme['materials'])); ?></p>
                                                    <p>Notes files:
                                                        <?php foreach ($scheme['notes_files'] as $file): ?>
                                                            <a href="<?php echo htmlspecialchars($file); ?>" download>Download Notes</a>
                                                        <?php endforeach; ?>
                                                    </p>
                                                    <p>Assignments files:
                                                        <?php foreach ($scheme['assignments_files'] as $file): ?>
                                                            <a href="<?php echo htmlspecialchars($file); ?>" download>Download Assignments</a>
                                                        <?php endforeach; ?>
                                                    </p>
                                                    <p>Materials files:
                                                        <?php foreach ($scheme['materials_files'] as $file): ?>
                                                            <a href="<?php echo htmlspecialchars($file); ?>" download>Download Materials</a>
                                                        <?php endforeach; ?>
                                                    </p>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                
                                <button class="collapsible mt-2">Students Enrolled in Module (<?php echo htmlspecialchars($module['module_code']); ?>)</button>
                                <div class="content">
                                    <ul class="mt-2">
                                        <?php foreach ($module['students'] as $student): ?>
                                            <li><?php echo htmlspecialchars($student['fullname']) . " - Student Number: " . htmlspecialchars($student['student_number']); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php else: ?>
            <p class="text-center text-gray-300"><?php echo $message; ?></p>
        <?php endif; ?>
    </div>

    <script>
        var coll = document.getElementsByClassName("collapsible");
        for (var i = 0; i < coll.length; i++) {
            coll[i].addEventListener("click", function() {
                this.classList.toggle("active");
                var content = this.nextElementSibling;
                if (content.style.display === "block") {
                    content.style.display = "none";
                } else {
                    content.style.display = "block";
                }
            });
        }
    </script>
</body>
</html>
