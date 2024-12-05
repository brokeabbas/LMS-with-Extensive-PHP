<?php
session_start();
require_once '../../connections/db_school_data.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_teacher.php");
    exit;
}

if (!isset($_SESSION["school_id"], $_GET['student_id'], $_GET['module_id'])) {
    exit('Necessary information not available.');
}

$student_id = $_GET['student_id'];
$module_id = $_GET['module_id'];
$school_id = $_SESSION['school_id'];

// Handle grade deletion
if (isset($_GET['delete_grade'])) {
    $grade_id = $_GET['delete_grade'];
    $delete_sql = "DELETE FROM schoolhu_school_data.grades WHERE id = ? AND student_id = ? AND module_id = ? AND school_id = ?";
    if ($delete_stmt = $schoolDataConn->prepare($delete_sql)) {
        $delete_stmt->bind_param("iiii", $grade_id, $student_id, $module_id, $school_id);
        if ($delete_stmt->execute()) {
            $message = "Grade deleted successfully.";
        } else {
            $message = "Error deleting grade: " . $schoolDataConn->error;
        }
        $delete_stmt->close();
    } else {
        $message = "SQL Error: " . $schoolDataConn->error;
    }
}

if (isset($_GET['download']) && isset($_GET['term'])) {
    $term = $_GET['term'];
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="grades_' . $term . '.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Assessment Type', 'Assessment Name', 'Grade', 'Overall Score', 'Term']);
    $sql = "SELECT g.assessment_type, g.assessment_name, g.grade, g.overall_score, g.term
            FROM schoolhu_school_data.grades g
            JOIN schoolhu_school_data.student_modules sm ON g.student_id = sm.student_id
            WHERE g.student_id = ? AND g.module_id = ? AND g.school_id = ? AND g.term = ?";
    $stmt = $schoolDataConn->prepare($sql);
    $stmt->bind_param("iiis", $student_id, $module_id, $school_id, $term);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [$row['assessment_type'], $row['assessment_name'], $row['grade'], $row['overall_score'], $row['term']]);
    }
    fclose($output);
    exit;
}

$sql = "SELECT si.fullname, si.student_number, g.id as grade_id, g.grade, g.assessment_type, g.assessment_name, g.overall_score, g.term
        FROM schoolhu_school_data.student_modules sm
        JOIN schoolhu_userinfo.student_info si ON sm.student_id = si.id
        LEFT JOIN schoolhu_school_data.grades g ON g.student_id = si.id AND g.module_id = sm.module_id
        WHERE sm.student_id = ? AND sm.module_id = ? AND sm.school_id = ?";

$grades = [];
$studentDetails = [];
$terms = [];
if ($stmt = $schoolDataConn->prepare($sql)) {
    $stmt->bind_param("iii", $student_id, $module_id, $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $studentDetails = $row;
        $grades[$row['term']][] = $row;
        $terms[$row['term']] = $row['term'];
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
    <title>Grades Detail for <?= htmlspecialchars($studentDetails['fullname']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
        }
        .custom-header {
            background: linear-gradient(to right, #667eea, #764ba2);
            color: white;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar for navigation -->
        <aside class="w-64 bg-gradient-to-b from-gray-800 to-gray-900 text-white p-5 shadow-md overflow-auto">
            <div class="px-6 py-8">
                <h1 class="text-xl font-semibold">Grading System</h1>
                <nav class="mt-10 space-y-4">
                    <a href="../myteach.php" class="flex items-center p-2 rounded hover:bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-tachometer-alt mr-3"></i>Dashboard
                    </a>
                    <a href="view_grades.php" class="flex items-center p-2 rounded hover:bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-eye mr-3"></i>View Grades
                    </a>
                    <a href="/logout" class="flex items-center p-2 rounded hover:bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-sign-out-alt mr-3"></i>Logout
                    </a>
                </nav>
            </div>
        </aside>
        <!-- Main content area -->
        <div class="flex-1 flex flex-col">
            <header class="custom-header p-6 shadow-lg">
                <div class="max-w-7xl mx-auto flex justify-between items-center">
                    <h2 class="text-3xl font-bold">Grades for <?= htmlspecialchars($studentDetails['fullname']) ?> (<?= htmlspecialchars($studentDetails['student_number']) ?>)</h2>
                    <i class="fas fa-chalkboard-teacher text-lg"></i>
                </div>
            </header>
            <main class="p-6 bg-white text-gray-800 flex-1">
                <div class="container mx-auto px-4">
                    <?php if (isset($message)): ?>
                        <div class="bg-<?= strpos($message, 'Error') === false ? 'green' : 'red' ?>-100 text-<?= strpos($message, 'Error') === false ? 'green' : 'red' ?>-800 p-4 mb-4 rounded">
                            <?= htmlspecialchars($message) ?>
                        </div>
                    <?php endif; ?>
                    <div class="mb-4">
                        <form method="get">
                            <input type="hidden" name="student_id" value="<?= $student_id ?>">
                            <input type="hidden" name="module_id" value="<?= $module_id ?>">
                            <input type="hidden" name="school_id" value="<?= $school_id ?>">
                            <select name="term" class="form-select mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select Term</option>
                                <?php foreach ($terms as $term): ?>
                                    <option value="<?= htmlspecialchars($term) ?>"><?= htmlspecialchars($term) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="download" value="1" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-2">Download Grades</button>
                        </form>
                    </div>
                    <div class="bg-white p-6 rounded-lg shadow-lg">
                        <?php foreach ($grades as $term => $termGrades): ?>
                            <h3 class="text-xl font-semibold mb-2"><?= htmlspecialchars($term) ?></h3>
                            <table class="min-w-full leading-normal mb-4">
                                <thead>
                                    <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left">Assessment Type</th>
                                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left">Assessment Name</th>
                                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left">Grade</th>
                                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left">Overall Score</th>
                                        <th class="px-5 py-3 border-b-2 border-gray-200 text-left">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-600 text-sm font-light">
                                    <?php foreach ($termGrades as $grade): ?>
                                        <tr class="border-b border-gray-200 hover:bg-gray-100">
                                            <td class="px-5 py-5 bg-white"><?= htmlspecialchars($grade['assessment_type']); ?></td>
                                            <td class="px-5 py-5 bg-white"><?= htmlspecialchars($grade['assessment_name']); ?></td>
                                            <td class="px-5 py-5 bg-white"><?= htmlspecialchars($grade['grade']); ?></td>
                                            <td class="px-5 py-5 bg-white"><?= htmlspecialchars($grade['overall_score']); ?></td>
                                            <td class="px-5 py-5 bg-white">
                                                <a href="?student_id=<?= $student_id ?>&module_id=<?= $module_id ?>&school_id=<?= $school_id ?>&delete_grade=<?= $grade['grade_id'] ?>" class="text-red-500 hover:text-red-700">Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endforeach; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
