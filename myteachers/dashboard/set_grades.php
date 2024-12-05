<?php
session_start();
require_once '../../connections/db.php';  // Connect to the userinfo database
require_once '../../connections/db_school_data.php'; // Connect to the school_data database

// Check for user authentication
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_teacher.php");
    exit;
}

// Initialize message variable
$message = null;

// Check for required session and query data
if (!isset($_SESSION["teacher_id"], $_SESSION["school_id"], $_GET['module_id'])) {
    $message = [
        'type' => 'error',
        'content' => 'Required information is not available.'
    ];
} else {
    $module_id = $_GET['module_id'];
    $teacher_id = $_SESSION['teacher_id'];
    $school_id = $_SESSION['school_id'];

    // SQL to fetch enrolled students
    $sql = "SELECT si.fullname, si.student_number, si.id as student_id 
            FROM schoolhu_userinfo.student_info si
            JOIN schoolhu_school_data.student_modules sm ON si.id = sm.student_id
            WHERE sm.module_id = ? AND sm.school_id = ?";
    $students = [];

    if ($stmt = $userInfoConn->prepare($sql)) {
        $stmt->bind_param("ii", $module_id, $school_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        $stmt->close();
    } else {
        $message = [
            'type' => 'error',
            'content' => "SQL Error: " . $userInfoConn->error
        ];
    }

    // Handle grade submission for selected students
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
        $assessment_type = $_POST['assessment_type'];
        $assessment_name = $_POST['assessment_name'];
        $teacher_code = $_POST['teacher_code'];
        $term = $_POST['term'];
        $overall_score = $_POST['overall_score'] ?? null;

        // Validate teacher code
        $teacher_code_sql = "SELECT teacher_code FROM schoolhu_userinfo.teacher_users WHERE teacher_id = ? AND school_id = ?";
        if ($code_stmt = $schoolDataConn->prepare($teacher_code_sql)) {
            $code_stmt->bind_param("ii", $teacher_id, $school_id);
            $code_stmt->execute();
            $code_result = $code_stmt->get_result();
            if ($code_row = $code_result->fetch_assoc()) {
                if ($teacher_code === $code_row['teacher_code']) {
                    // Insert grades for selected students
                    $insert_sql = "INSERT INTO schoolhu_school_data.grades (student_id, module_id, school_id, grade, assessment_type, assessment_name, overall_score, term, teacher_id)
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    if ($insert_stmt = $schoolDataConn->prepare($insert_sql)) {
                        foreach ($_POST['grades'] as $student_id => $grade) {
                            // Check if the student was selected and the grade field is not empty
                            if (isset($_POST['select'][$student_id]) && !empty($grade)) {
                                $insert_stmt->bind_param("iiisssssi", $student_id, $module_id, $school_id, $grade, $assessment_type, $assessment_name, $overall_score, $term, $teacher_id);
                                $insert_stmt->execute();
                            }
                        }
                        $message = [
                            'type' => 'success',
                            'content' => 'Grades added successfully!'
                        ];
                        $insert_stmt->close();
                    } else {
                        $message = [
                            'type' => 'error',
                            'content' => "SQL Error: " . $schoolDataConn->error
                        ];
                    }
                } else {
                    $message = [
                        'type' => 'error',
                        'content' => 'Invalid teacher code.'
                    ];
                }
            } else {
                $message = [
                    'type' => 'error',
                    'content' => 'No teacher code found.'
                ];
            }
            $code_stmt->close();
        } else {
            $message = [
                'type' => 'error',
                'content' => "SQL Error: " . $schoolDataConn->error
            ];
        }
    }
}

// Close both database connections
$userInfoConn->close();
$schoolDataConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Grades</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/js/all.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            font-family: 'Nunito', sans-serif; /* Consistent modern font */
        }
        .input-inactive {
            background-color: #f3f4f6; /* light gray */
            color: #9ca3af; /* gray */
        }
        input:focus {
            outline: none;
            box-shadow: 0 0 0 2px #4f46e5; /* focus ring color matches with Tailwind's indigo */
        }
        .hover-rise:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .button-style {
            display: inline-block;
            background-color: #4f46e5; /* Indigo shade */
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.2s ease;
        }
        .button-style:hover {
            background-color: #4338ca; /* Darker indigo on hover */
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }
        .custom-header {
            background: linear-gradient(to right, #667eea, #764ba2);
            color: white;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"][name^="select"]');
            checkboxes.forEach(checkbox => {
                toggleInput(checkbox);
                checkbox.addEventListener('change', function() {
                    toggleInput(checkbox);
                });
            });

            function toggleInput(checkbox) {
                const input = document.querySelector('input[name="grades[' + checkbox.value + ']"]');
                if (checkbox.checked) {
                    input.disabled = false;
                    input.classList.remove('input-inactive');
                } else {
                    input.disabled = true;
                    input.classList.add('input-inactive');
                    input.value = ''; // Clear input when disabled
                }
            }

            document.querySelector('form').addEventListener('submit', function(e) {
                const overallScore = parseFloat(document.querySelector('input[name="overall_score"]').value);
                let valid = true;
                document.querySelectorAll('input[name^="grades"]').forEach(function(input) {
                    const grade = parseFloat(input.value);
                    if (!isNaN(grade) && grade > overallScore) {
                        const studentNumber = input.closest('tr').querySelector('td[data-label="Number"]').textContent;
                        alert(`Grade score for student number ${studentNumber} exceeds the overall score.`);
                        valid = false;
                    }
                });
                if (!valid) {
                    e.preventDefault();
                }
            });
        });
    </script>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar for navigation -->
        <aside class="w-64 bg-gradient-to-b from-gray-800 to-gray-900 text-white p-5 shadow-md overflow-auto">
            <div class="px-6 py-8">
                <h1 class="text-xl font-semibold">Grading System</h1>
                <nav class="mt-10 space-y-4">
                    <a href="../myteach.php" class="flex items-center p-2 rounded hover:bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-tachometer-alt mr-3 icon"></i>Dashboard
                    </a>
                    <a href="view_grades.php" class="flex items-center p-2 rounded hover:bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-eye mr-3 icon"></i>View Grades
                    </a>
                    <a href="/logout" class="flex items-center p-2 rounded hover:bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-sign-out-alt mr-3 icon"></i>Logout
                    </a>
                </nav>
            </div>
        </aside>
        <!-- Main content area -->
        <div class="flex-1 flex flex-col">
            <header class="custom-header p-6 shadow-lg">
                <div class="max-w-7xl mx-auto flex justify-between items-center">
                    <h2 class="text-3xl font-bold">Set Grades for Students</h2>
                    <i class="fas fa-pencil-alt text-lg"></i>
                </div>
            </header>
            <main class="p-6 bg-white text-gray-800 flex-1">
                <div class="container mx-auto px-4">
                    <?php if ($message): ?>
                        <div class="<?= $message['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?> p-4 mb-4 rounded">
                            <?= htmlspecialchars($message['content']) ?>
                        </div>
                    <?php endif; ?>
                    <div class="mb-6">
                        <input id="searchInput" type="text" placeholder="Search by name or number..." class="form-input rounded-md shadow-sm block w-full border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 transition duration-150 ease-in-out">
                    </div>
                    <form method="POST" class="bg-white shadow-md rounded-lg p-8 mb-4">
                        <?php if (!empty($students)): ?>
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white">
                                    <thead class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                                        <tr>
                                            <th class="py-3 px-6 text-left">Select</th>
                                            <th class="py-3 px-6 text-left">Student Name</th>
                                            <th class="py-3 px-6 text-left">Student Number</th>
                                            <th class="py-3 px-6 text-left">Grade</th>
                                        </tr>
                                    </thead>
                                    <tbody id="studentsTableBody" class="text-gray-600 text-sm font-light">
                                        <?php foreach ($students as $student): ?>
                                            <tr class="student-row border-b border-gray-200 hover:bg-gray-100">
                                                <td class="py-3 px-6 text-left whitespace-nowrap"><input type="checkbox" name="select[<?= $student['student_id'] ?>]" value="<?= $student['student_id'] ?>" class="form-checkbox h-5 w-5 text-blue-600"></td>
                                                <td class="py-3 px-6 text-left" data-label="Name"><?= htmlspecialchars($student['fullname']) ?></td>
                                                <td class="py-3 px-6 text-left" data-label="Number"><?= htmlspecialchars($student['student_number']) ?></td>
                                                <td class="py-3 px-6 text-left">
                                                    <input type="text" name="grades[<?= $student['student_id'] ?>]" class="input-inactive form-input rounded-md shadow-sm" disabled placeholder="Enter grade" required>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>No students found for this module.</p>
                        <?php endif; ?>
                        <div class="mt-6 flex flex-wrap gap-4">
                            <select name="assessment_type" class="form-select block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                <option value="Test">Test</option>
                                <option value="Assignment">Assignment</option>
                                <option value="Exam">Examination</option>
                            </select>
                            <select name="term" class="form-select block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                <option value="First Term">First Term</option>
                                <option value="Second Term">Second Term</option>
                                <option value="Third Term">Third Term</option>
                            </select>
                            <input type="text" name="assessment_name" placeholder="Assessment Name" class="form-input rounded-md shadow-sm mt-1 block w-full" required>
                            <input type="number" name="overall_score" placeholder="Total Score Achievable" class="form-input rounded-md shadow-sm mt-1 block w-full">
                            <input type="text" name="teacher_code" placeholder="Enter your teacher code" class="form-input rounded-md shadow-sm mt-1 block w-full" required>
                            <button type="submit" name="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Set Grades</button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const tableBody = document.getElementById('studentsTableBody');
            searchInput.addEventListener('keyup', function(e) {
                const searchValue = e.target.value.toLowerCase();
                document.querySelectorAll('.student-row').forEach(function(row) {
                    const name = row.querySelector('td[data-label="Name"]').textContent.toLowerCase();
                    const number = row.querySelector('td[data-label="Number"]').textContent.toLowerCase();
                    if (name.includes(searchValue) || number.includes(searchValue)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>
