<?php
session_start();
require_once '../../connections/db.php';  // Connection to the user info database
require_once '../../connections/db_school_data.php'; // Connection to the school data database

// Check for a logged-in student and validate the session
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["student_id"])) {
    header("location: ../login_student.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$school_id = $_SESSION['school_id'];

// SQL to fetch assignments linked with the student's modules and their submission status
$sql = "SELECT mt.module_code, a.id AS assignment_id, a.assignment_name, a.due_date, a.description, a.file_path, 
        s.id AS submission_id
        FROM schoolhu_school_data.assignments a
        JOIN schoolhu_school_data.student_modules sm ON a.module_id = sm.module_id
        JOIN schoolhu_school_data.modules_taught mt ON sm.module_id = mt.module_id
        LEFT JOIN schoolhu_school_data.assignment_submissions s ON a.id = s.assignment_id AND s.student_id = ?
        WHERE sm.student_id = ? AND sm.school_id = ?
        ORDER BY a.due_date ASC";

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

$schoolDataConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homework Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.3/js/all.js"></script> <!-- FontAwesome Icons -->
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(to right, #6ee7b7, #3b82f6);
        }
        .menu:hover {
            transform: scale(1.05);
            transition: transform 0.3s ease;
        }
        .card {
            backdrop-filter: blur(10px);
            border-radius: 12px;
            transition: transform 0.3s, box-shadow 0.3s;
            background-color: #f1f5f9; /* Slightly darker than white */
        }
        .card p, .card h2 {
            color: #2d3748; /* Text color */
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .submit-button {
            background: linear-gradient(to right, #4e54c8, #8f94fb);
            border: none;
            color: white;
            cursor: pointer;
            transition: background 0.3s;
        }
        .submit-button:hover {
            background: linear-gradient(to right, #8f94fb, #4e54c8);
        }
    </style>
</head>
<body class="font-sans leading-normal tracking-normal">
    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 min-h-screen flex flex-col bg-blue-800 text-white shadow-lg">
            <div class="p-5 text-center text-2xl font-bold border-b-2 border-blue-700">
                Navigation
            </div>
            <ul class="flex-1 p-5">
                <a href="../mystudy.php">
                    <li class="menu mb-4 text-lg hover:bg-blue-700 p-2 rounded cursor-pointer">
                        <i class="fas fa-home"></i> Dashboard
                    </li>
                </a>
                <a href="gradebook.php">
                    <li class="menu mb-4 text-lg hover:bg-blue-700 p-2 rounded cursor-pointer">
                        <i class="fas fa-chart-line"></i> Grades
                    </li>
                </a>
                <a href="library.php">
                    <li class="menu mb-4 text-lg hover:bg-blue-700 p-2 rounded cursor-pointer">
                        <i class="fas fa-book"></i> Library
                    </li>
                </a>
                <a href="">
                    <li class="menu mb-4 text-lg hover:bg-blue-700 p-2 rounded cursor-pointer">
                        <i class="fas fa-question-circle"></i> Need Help?
                    </li>
                </a>
            </ul>
        </div>

        <!-- Main content -->
        <div class="flex-1 p-10 text-gray-800">
            <div class="bg-white shadow-xl rounded-lg p-6 opacity-95">
                <h1 class="text-4xl font-bold text-gray-800 mb-4">Homework Assignments</h1>
                <?php if (!empty($assignments)): ?>
                    <div class="space-y-6">
                        <?php foreach ($assignments as $assignment): ?>
                            <div class="card p-6">
                                <h2 class="font-semibold text-lg mb-2"><?= htmlspecialchars($assignment['assignment_name']); ?></h2>
                                <p class="mb-2"><i class="fas fa-book mr-2"></i>Module: <?= htmlspecialchars($assignment['module_code']); ?> - <i class="fas fa-calendar-alt mr-2"></i>Due: <?= htmlspecialchars($assignment['due_date']); ?></p>
                                <p class="mb-2"><?= nl2br(htmlspecialchars($assignment['description'])); ?></p>
                                <?php if (!empty($assignment['file_path'])): ?>
                                    <a href="<?= htmlspecialchars($assignment['file_path']); ?>" download class="text-blue-400 hover:underline flex items-center mb-2">
                                        <i class="fas fa-download mr-2"></i> Download Attachment
                                    </a>
                                <?php endif; ?>
                                <?php if (empty($assignment['submission_id'])): ?>
                                    <form action="submit_assignment.php" method="post" enctype="multipart/form-data" class="mt-4">
                                        <input type="hidden" name="assignment_id" value="<?= $assignment['assignment_id']; ?>">
                                        <input type="file" name="submitted_file" required class="block mt-2 text-sm text-gray-900">
                                        <button type="submit" class="submit-button mt-4 px-4 py-2 rounded flex items-center"><i class="fas fa-upload mr-2"></i>Submit Assignment</button>
                                    </form>
                                <?php else: ?>
                                    <p class="text-green-400"><i class="fas fa-check-circle mr-2"></i>✔ Submitted</p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-lg text-gray-700 mt-4">No assignments have been set for your enrolled modules yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
