<?php
session_start();
require_once '../../connections/db.php'; // Connection to the userinfo database
require_once '../../connections/db_school_data.php'; // Connection to the school_data database

// Authentication and session checks
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_student.php");
    exit;
}

if (!isset($_SESSION["student_id"], $_SESSION["school_id"])) {
    echo "Required session variables are not set.";
    exit;
}

$student_id = $_SESSION['student_id'];
$school_id = $_SESSION['school_id'];

$message = '';

// Check for download request
if (isset($_GET['download'])) {
    $format = $_GET['download']; // Could be 'csv' or 'excel'
    downloadGrades($student_id, $school_id, $format);
    exit;
}

// Archive selected grades
if (isset($_POST['archive'])) {
    $selected_grades = $_POST['selected_grades'] ?? [];
    $message = archiveGrades($selected_grades);
}

// Function to archive grades
function archiveGrades($selected_grades) {
    global $schoolDataConn;
    if (empty($selected_grades)) {
        return "No grades selected for archiving.";
    }
    $ids = implode(",", array_map('intval', $selected_grades));
    $sql = "UPDATE schoolhu_school_data.grades SET is_archived = 1 WHERE id IN ($ids)";

    if ($schoolDataConn->query($sql) === TRUE) {
        return "Grades archived successfully.";
    } else {
        return "Error updating record: " . $schoolDataConn->error;
    }
}

// Function to download grades
function downloadGrades($student_id, $school_id, $format) {
    global $schoolDataConn;
    $filename = "grades_" . date('Ymd') . ($format === 'excel' ? '.xlsx' : '.csv');
    header('Content-Type: ' . ($format === 'excel' ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' : 'text/csv'));
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $sql = "SELECT ss.subject_name, g.grade, g.assessment_type, g.assessment_name, g.overall_score
            FROM schoolhu_school_data.grades g
            JOIN schoolhu_school_data.modules_taught mt ON g.module_id = mt.module_id
            JOIN schoolhu_school_data.class_subject cs ON mt.module_id = cs.module_id
            JOIN schoolhu_school_data.school_subjects ss ON cs.subject_id = ss.subject_id
            WHERE g.student_id = ? AND g.school_id = ? AND g.is_archived = 0
            ORDER BY ss.subject_name";

    if ($stmt = $schoolDataConn->prepare($sql)) {
        $stmt->bind_param("ii", $student_id, $school_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $fp = fopen('php://output', 'w'); // Open for writing directly to output buffer
        if ($format === 'csv') {
            // Output header for CSV
            fputcsv($fp, ['Subject', 'Grade', 'Assessment Type', 'Assessment Name', 'Overall Score']);
            while ($row = $result->fetch_assoc()) {
                fputcsv($fp, $row);
            }
        } else {
            // Generate Excel file
            echo "Excel generation not implemented.";
        }
        fclose($fp);
        $stmt->close();
    } else {
        echo "SQL Error: " . $schoolDataConn->error;
    }
    $schoolDataConn->close();
}

// SQL to fetch grades linked with the student's modules
$grades = [];
$sql = "SELECT g.id, ss.subject_name, g.grade, g.assessment_type, g.assessment_name, g.overall_score, g.term
        FROM schoolhu_school_data.grades g
        JOIN schoolhu_school_data.modules_taught mt ON g.module_id = mt.module_id
        JOIN schoolhu_school_data.class_subject cs ON mt.module_id = cs.module_id
        JOIN schoolhu_school_data.school_subjects ss ON cs.subject_id = ss.subject_id
        WHERE g.student_id = ? AND g.school_id = ? AND g.is_archived = 0
        ORDER BY g.term, ss.subject_name";

if ($stmt = $schoolDataConn->prepare($sql)) {
    $stmt->bind_param("ii", $student_id, $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $grades[$row['term']][] = $row;
    }
    $stmt->close();
} else {
    $message = "SQL Error: " . $schoolDataConn->error;
}

// SQL to fetch archived grades linked with the student's modules
$archived_grades = [];
$sql_archived = "SELECT g.id, ss.subject_name, g.grade, g.assessment_type, g.assessment_name, g.overall_score, g.term, c.class_name
        FROM schoolhu_school_data.grades g
        JOIN schoolhu_school_data.modules_taught mt ON g.module_id = mt.module_id
        JOIN schoolhu_school_data.class_subject cs ON mt.module_id = cs.module_id
        JOIN schoolhu_school_data.school_subjects ss ON cs.subject_id = ss.subject_id
        JOIN schoolhu_school_data.classes c ON cs.class_id = c.class_id
        WHERE g.student_id = ? AND g.school_id = ? AND g.is_archived = 1
        ORDER BY c.class_name, ss.subject_name, g.term";

if ($stmt_archived = $schoolDataConn->prepare($sql_archived)) {
    $stmt_archived->bind_param("ii", $student_id, $school_id);
    $stmt_archived->execute();
    $result_archived = $stmt_archived->get_result();
    while ($row = $result_archived->fetch_assoc()) {
        $archived_grades[$row['class_name']][$row['subject_name']][$row['term']][] = $row;
    }
    $stmt_archived->close();
} else {
    $message = "SQL Error: " . $schoolDataConn->error;
}

$schoolDataConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Book</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script> <!-- FontAwesome Icons -->
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(to right, #6ee7b7, #3b82f6);
        }
        .menu:hover {
            transform: scale(1.05);
            transition: transform 0.3s ease;
        }
        .fade-in {
            animation: fadeIn 1s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body class="font-sans leading-normal tracking-normal">
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-3xl font-bold text-white mb-4"><i class="fas fa-book-reader"></i> Your Grades</h1>

        <?php if ($message): ?>
            <div id="messageBox" class="bg-<?php echo strpos($message, 'Error') !== false ? 'red' : 'green'; ?>-500 text-white p-4 rounded mb-4">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="mb-4">
            <input type="text" id="searchInput" onkeyup="filterTable('gradesTable')" placeholder="Search for subjects or assessment types..." class="p-2 border rounded w-full">
        </div>
        <div class="mb-4 flex space-x-4">
            <select id="subjectFilter" onchange="filterTable('gradesTable')" class="p-2 border rounded">
                <option value="">All Subjects</option>
                <?php
                $subjects = array_unique(array_column(array_merge(...array_values($grades)), 'subject_name'));
                foreach ($subjects as $subject) {
                    echo '<option value="' . htmlspecialchars($subject) . '">' . htmlspecialchars($subject) . '</option>';
                }
                ?>
            </select>
            <select id="assessmentTypeFilter" onchange="filterTable('gradesTable')" class="p-2 border rounded">
                <option value="">All Assessment Types</option>
                <?php
                $assessmentTypes = array_unique(array_column(array_merge(...array_values($grades)), 'assessment_type'));
                foreach ($assessmentTypes as $type) {
                    echo '<option value="' . htmlspecialchars($type) . '">' . htmlspecialchars($type) . '</option>';
                }
                ?>
            </select>
        </div>

        <button onclick="window.location.href='?download=csv'" class="bg-green-500 text-white p-2 rounded"><i class="fas fa-file-csv mr-2"></i>Download CSV</button>
        <!-- Add Excel button similarly if implemented -->

        <form method="POST" action="">
            <?php if (!empty($grades)): ?>
                <div class="overflow-x-auto bg-white shadow-xl rounded-lg mt-4">
                    <?php foreach ($grades as $term => $termGrades): ?>
                        <h2 class="text-xl font-bold text-blue-700 mb-2"><?= htmlspecialchars($term) ?></h2>
                        <table class="min-w-full border-collapse mb-6" id="gradesTable">
                            <thead class="bg-blue-500 text-white">
                                <tr>
                                    <th class="border px-4 py-2 text-left"><i class="fas fa-check-square"></i> Select</th>
                                    <th class="border px-4 py-2 text-left"><i class="fas fa-book"></i> Subject</th>
                                    <th class="border px-4 py-2 text-left"><i class="fas fa-tasks"></i> Assessment Type</th>
                                    <th class="border px-4 py-2 text-left"><i class="fas fa-pen"></i> Assessment Name</th>
                                    <th class="border px-4 py-2 text-left"><i class="fas fa-graduation-cap"></i> Grade</th>
                                    <th class="border px-4 py-2 text-left"><i class="fas fa-chart-line"></i> Overall Score</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                <?php foreach ($termGrades as $grade): ?>
                                    <tr class="hover:bg-gray-100">
                                        <td class="border px-4 py-2"><input type="checkbox" name="selected_grades[]" value="<?= $grade['id'] ?>"></td>
                                        <td class="border px-4 py-2"><?= htmlspecialchars($grade['subject_name']) ?></td>
                                        <td class="border px-4 py-2"><?= htmlspecialchars($grade['assessment_type']) ?></td>
                                        <td class="border px-4 py-2"><?= htmlspecialchars($grade['assessment_name']) ?></td>
                                        <td class="border px-4 py-2"><?= htmlspecialchars($grade['grade']) ?></td>
                                        <td class="border px-4 py-2"><?= htmlspecialchars($grade['overall_score']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endforeach; ?>
                </div>
                <button type="submit" name="archive" class="bg-red-500 text-white p-2 rounded mt-4"><i class="fas fa-archive mr-2"></i>Archive Selected Grades</button>
            <?php else: ?>
                <p class="text-lg text-white mt-4">No grades found. You may not be enrolled in any modules currently, or grades have not been posted yet.</p>
            <?php endif; ?>
        </form>

        <div class="mt-6">
            <h2 class="text-2xl font-bold text-white mb-4"><i class="fas fa-archive"></i> Previous Grades</h2>
            <div class="mb-4">
                <input type="text" id="searchArchivedInput" onkeyup="filterTable('archivedGradesTable')" placeholder="Search for subjects or assessment types..." class="p-2 border rounded w-full">
            </div>
            <?php if (!empty($archived_grades)): ?>
                <div class="overflow-x-auto bg-white shadow-xl rounded-lg mt-4">
                    <?php foreach ($archived_grades as $class => $subjects): ?>
                        <div>
                            <button onclick="toggleVisibility('class-<?= htmlspecialchars($class) ?>')" class="bg-gray-500 text-white p-2 rounded mb-2"><?= htmlspecialchars($class) ?></button>
                            <div id="class-<?= htmlspecialchars($class) ?>" class="hidden">
                                <?php foreach ($subjects as $subject => $terms): ?>
                                    <button onclick="toggleVisibility('subject-<?= htmlspecialchars($class . '-' . $subject) ?>')" class="bg-gray-400 text-white p-2 rounded mb-2 ml-4"><?= htmlspecialchars($subject) ?></button>
                                    <div id="subject-<?= htmlspecialchars($class . '-' . $subject) ?>" class="hidden ml-6">
                                        <?php foreach ($terms as $term => $termGrades): ?>
                                            <h3 class="text-lg font-bold text-gray-700 ml-4"><?= htmlspecialchars($term) ?></h3>
                                            <div class="ml-6">
                                                <table class="min-w-full border-collapse mb-4" id="archivedGradesTable">
                                                    <thead class="bg-gray-400 text-white">
                                                        <tr>
                                                            <th class="border px-4 py-2 text-left"><i class="fas fa-book"></i> Subject</th>
                                                            <th class="border px-4 py-2 text-left"><i class="fas fa-tasks"></i> Assessment Type</th>
                                                            <th class="border px-4 py-2 text-left"><i class="fas fa-pen"></i> Assessment Name</th>
                                                            <th class="border px-4 py-2 text-left"><i class="fas fa-graduation-cap"></i> Grade</th>
                                                            <th class="border px-4 py-2 text-left"><i class="fas fa-chart-line"></i> Overall Score</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white">
                                                        <?php foreach ($termGrades as $grade): ?>
                                                            <tr class="hover:bg-gray-100">
                                                                <td class="border px-4 py-2"><?= htmlspecialchars($grade['subject_name']) ?></td>
                                                                <td class="border px-4 py-2"><?= htmlspecialchars($grade['assessment_type']) ?></td>
                                                                <td class="border px-4 py-2"><?= htmlspecialchars($grade['assessment_name']) ?></td>
                                                                <td class="border px-4 py-2"><?= htmlspecialchars($grade['grade']) ?></td>
                                                                <td class="border px-4 py-2"><?= htmlspecialchars($grade['overall_score']) ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-lg text-white mt-4">No archived grades found.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function filterTable(tableId) {
            var input, filter, table, tr, td, i, txtValue, selectSubject, selectAssessment, filterSubject, filterAssessment;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById(tableId);
            tr = table.getElementsByTagName("tr");
            selectSubject = document.getElementById("subjectFilter");
            filterSubject = selectSubject ? selectSubject.value.toUpperCase() : "";
            selectAssessment = document.getElementById("assessmentTypeFilter");
            filterAssessment = selectAssessment ? selectAssessment.value.toUpperCase() : "";

            for (i = 1; i < tr.length; i++) { // Start from 1 to skip header row
                td = tr[i].getElementsByTagName("td")[1]; // Subject column
                tdType = tr[i].getElementsByTagName("td")[2]; // Assessment Type column
                txtValue = td ? td.textContent || td.innerText : "";
                txtValueType = tdType ? tdType.textContent || tdType.innerText : "";

                if ((td && (txtValue.toUpperCase().indexOf(filter) > -1 || txtValueType.toUpperCase().indexOf(filter) > -1)) &&
                    (filterSubject === "" || txtValue.toUpperCase() === filterSubject) &&
                    (filterAssessment === "" || txtValueType.toUpperCase() === filterAssessment)) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }

        function toggleVisibility(id) {
            var element = document.getElementById(id);
            if (element.classList.contains('hidden')) {
                element.classList.remove('hidden');
            } else {
                element.classList.add('hidden');
            }
        }
        
        // Automatically hide message box after 5 seconds
        setTimeout(() => {
            var messageBox = document.getElementById('messageBox');
            if (messageBox) {
                messageBox.style.display = 'none';
            }
        }, 5000);
    </script>
</body>
</html>
