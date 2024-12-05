<?php
session_start();
require_once '../../connections/db_school_data.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

$class_id = $_GET['class_id'] ?? null;
if (!$class_id) {
    echo "Class ID is required.";
    exit;
}

// Fetch the class name and its current subjects
$classQuery = $schoolDataConn->prepare("SELECT class_name FROM classes WHERE class_id = ? AND school_id = ?");
$classQuery->bind_param("ii", $class_id, $_SESSION['school_id']);
$classQuery->execute();
$classResult = $classQuery->get_result();
$className = $classResult->fetch_assoc()['class_name'];
$classQuery->close();

// Fetch all subjects from the school
$subjectsQuery = $schoolDataConn->prepare("SELECT subject_id, subject_name FROM school_subjects WHERE school_id = ?");
$subjectsQuery->bind_param("i", $_SESSION['school_id']);
$subjectsQuery->execute();
$subjectsResult = $subjectsQuery->get_result();
$subjects = $subjectsResult->fetch_all(MYSQLI_ASSOC);
$subjectsQuery->close();

// Fetch currently assigned subjects for the class
$assignedSubjectsQuery = $schoolDataConn->prepare("SELECT subject_id FROM class_subject WHERE class_id = ?");
$assignedSubjectsQuery->bind_param("i", $class_id);
$assignedSubjectsQuery->execute();
$assignedSubjectsResult = $assignedSubjectsQuery->get_result();
$assignedSubjects = [];
while ($subject = $assignedSubjectsResult->fetch_assoc()) {
    $assignedSubjects[] = $subject['subject_id'];
}
$assignedSubjectsQuery->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Class: <?= htmlspecialchars($className) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(135deg, #1f2937, #3b82f6);
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
            color: #f8fafc;
            font-family: 'Roboto', sans-serif;
        }
        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .card {
            background-color: #2d3748;
            border-radius: 10px;
            padding: 20px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        .btn-primary {
            background-color: #63b3ed;
            color: #fff;
            transition: background-color 0.2s ease-in-out, transform 0.2s ease-in-out;
        }
        .btn-primary:hover {
            background-color: #3182ce;
            transform: translateY(-2px);
        }
        .form-checkbox:checked {
            background-color: #3182ce;
            border-color: #3182ce;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('searchInput');
            const checkboxes = document.querySelectorAll('input[type="checkbox"][name="subjects[]"]');
            const filterButtons = document.querySelectorAll('.filter-button');
            const updateButton = document.getElementById('updateButton');
            const infoMessage = document.getElementById('infoMessage');

            searchInput.addEventListener('input', handleSearch);
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', handleCheckboxChange);
            });
            filterButtons.forEach(button => {
                button.addEventListener('click', handleFilter);
            });
            handleCheckboxChange(); // Initial call to set correct states
        });

        function handleSearch() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const subjects = document.querySelectorAll('.subject-card');

            subjects.forEach(subject => {
                const subjectName = subject.querySelector('label').textContent.toLowerCase();
                subject.style.display = subjectName.includes(searchTerm) ? 'flex' : 'none';
            });
        }

        function handleFilter(event) {
            const filter = event.target.dataset.filter;
            const subjects = document.querySelectorAll('.subject-card');

            subjects.forEach(subject => {
                const checkbox = subject.querySelector('input[type="checkbox"]');
                if (filter === 'all') {
                    subject.style.display = 'flex';
                } else if (filter === 'selected') {
                    subject.style.display = checkbox.checked ? 'flex' : 'none';
                } else if (filter === 'unselected') {
                    subject.style.display = !checkbox.checked ? 'flex' : 'none';
                }
            });
        }

        function handleCheckboxChange() {
            const checkboxes = document.querySelectorAll('input[type="checkbox"][name="subjects[]"]');
            const checkedCheckboxes = document.querySelectorAll('input[type="checkbox"][name="subjects[]"]:checked');

            // Disable last checked checkbox if it's the only one checked
            checkboxes.forEach(checkbox => {
                checkbox.disabled = checkedCheckboxes.length === 1 && checkbox.checked;
            });

            // Disable or enable the update button based on the count of checked checkboxes
            updateButton.disabled = checkedCheckboxes.length < 2;

            // Show or hide the info message based on the count of checked checkboxes
            infoMessage.style.display = checkedCheckboxes.length < 2 ? 'block' : 'none';
        }
    </script>
</head>
<body class="bg-gray-900">
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-center text-gray-200 mb-6">Select/Remove Subjects for <?= htmlspecialchars($className) ?></h1>
        <div class="mb-6 flex justify-center">
            <input type="text" id="searchInput" placeholder="Search subjects..." class="px-4 py-2 rounded-lg text-gray-900">
        </div>
        <div class="mb-6 flex justify-center space-x-4">
            <button type="button" class="filter-button btn-primary px-4 py-2 rounded" data-filter="all">All</button>
            <button type="button" class="filter-button btn-primary px-4 py-2 rounded" data-filter="selected">Selected</button>
            <button type="button" class="filter-button btn-primary px-4 py-2 rounded" data-filter="unselected">Unselected</button>
        </div>
        <form method="post" action="update_class_subjects.php">
            <input type="hidden" name="class_id" value="<?= htmlspecialchars($class_id) ?>">
            <div id="infoMessage" class="mb-4 text-center text-red-500" style="display: none;">
                You must select at least two subjects.
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <?php foreach ($subjects as $subject): ?>
                    <div class="flex items-center bg-gray-800 p-4 rounded-lg shadow card subject-card">
                        <input type="checkbox" name="subjects[]" id="subj_<?= $subject['subject_id'] ?>" value="<?= $subject['subject_id'] ?>" <?= in_array($subject['subject_id'], $assignedSubjects) ? 'checked' : '' ?> class="form-checkbox h-5 w-5 text-blue-600">
                        <label for="subj_<?= $subject['subject_id'] ?>" class="ml-2 text-gray-300"><?= htmlspecialchars($subject['subject_name']) ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="mt-6 text-center">
                <button type="submit" id="updateButton" class="btn-primary inline-block mb-2 py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update Subjects</button>
            </div>
        </form>
    </div>
</body>
</html>
