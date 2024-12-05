<?php
session_start();

require_once '../../connections/db_school_data.php'; // Adjust the path as needed

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

$school_id = $_SESSION['school_id'] ?? null; // Retrieve the school ID from session

// Define a comprehensive list of subjects
$subjects = [
    "English Language", "Mathematics", "Biology", "Physics", "Chemistry",
    "Economics", "Geography", "Government", "Literature in English", "Agricultural Science",
    "History", "Civic Education", "Christian Religious Studies", "Islamic Religious Studies",
    "Further Mathematics", "Commerce", "Technical Drawing", "Food and Nutrition", "Home Economics",
    "Physical Education", "Business Studies", "Fine Art", "French", "Music", "Accounting",
    "Computer Science", "Technology and Livelihood Education", "Integrated Science", "Social Studies",
    "Health Education", "Auto Mechanics", "Book Keeping", "Building Construction", "Electrical Installation",
    "Metalwork", "Woodwork", "Visual Art", "Theatre Arts", "Data Processing", "Insurance",
    "Marketing", "Office Practice", "Photography", "Physical and Health Education", "Printing Craft Practice",
    "Leatherwork", "Machine Woodworking", "Mining", "Fisheries", "Forestry"
];

// Process the form when it is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_subjects'])) {
    $schoolDataConn->begin_transaction();
    try {
        $query = "INSERT INTO school_subjects (school_id, subject_name) VALUES (?, ?)";
        $stmt = $schoolDataConn->prepare($query);

        foreach ($_POST['subjects'] as $subject) {
            if (in_array($subject, $subjects)) {
                $stmt->bind_param("is", $school_id, $subject);
                $stmt->execute();
            }
        }
        $stmt->close();
        $schoolDataConn->commit();
        header("Location: set_curriculum.php");
        echo "<p>Subjects have been successfully added.</p>";
    } catch (Exception $e) {
        $schoolDataConn->rollback();
        echo "<p>Error adding subjects: " . $e->getMessage() . "</p>";
    }
    $schoolDataConn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Subjects</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 10px;
            padding: 20px;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        .table-header {
            background-color: #2a4365;
        }
        .table-row {
            background-color: #2d3748;
            transition: background-color 0.2s ease-in-out;
        }
        .table-row:hover {
            background-color: #1a202c;
        }
        .input-field {
            background-color: #1a202c;
            color: #a0aec0;
            border: 1px solid #4a5568;
        }
        .input-field:focus {
            border-color: #63b3ed;
            box-shadow: 0 0 0 1px #63b3ed;
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
    </style>
    <script>
        function toggleSubjects(source) {
            checkboxes = document.querySelectorAll('input[type="checkbox"]');
            for (var i = 0, n = checkboxes.length; i < n; i++) {
                checkboxes[i].checked = source.checked;
            }
        }
    </script>
</head>
<body class="bg-gray-900 flex items-center justify-center min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto bg-gray-800 p-8 border border-gray-700 rounded-lg shadow-lg card">
            <h1 class="text-3xl font-semibold text-gray-200 mb-6 text-center">Select Subjects Offered by Your School</h1>
            <form method="POST" action="">
                <div class="mb-6 flex justify-center">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" onchange="toggleSubjects(this)" class="form-checkbox rounded h-6 w-6 text-blue-500 transition duration-150 ease-in-out"><span class="ml-2 text-lg text-gray-300">Select All</span>
                    </label>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($subjects as $subject): ?>
                    <div class="flex items-center mb-2">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="subjects[]" value="<?= htmlspecialchars($subject); ?>" class="form-checkbox rounded h-6 w-6 text-blue-500 transition duration-150 ease-in-out"><span class="ml-2 text-lg text-gray-300"><?= htmlspecialchars($subject); ?></span>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="flex justify-center">
                    <button type="submit" name="submit_subjects" class="mt-4 px-6 py-3 bg-blue-500 hover:bg-blue-700 text-white font-bold rounded-lg shadow focus:outline-none focus:shadow-outline transition duration-150 ease-in-out">Next</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
