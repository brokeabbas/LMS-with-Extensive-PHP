<?php
session_start(); // Start or resume the session

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

// Include database connection files
require_once '../../connections/db.php';  // Assuming userInfoConn is defined here for user data
require_once '../../connections/db_school_data.php';  // Assuming schoolDataConn is defined here for school data

$search = $_GET['search'] ?? '';
$students = [];

// Check if school_id is set in the session
if (isset($_SESSION['school_id'])) {
    $school_id = $_SESSION['school_id'];

    // Prepare a statement to fetch student data from the database
    $query = "
        SELECT si.id, si.fullname, si.student_number
        FROM schoolhu_userinfo.student_info si
        WHERE is_active = 1 AND si.school_id = ?";

    if (!empty($search)) {
        $query .= " AND (si.fullname LIKE ? OR si.student_number LIKE ?)";
    }

    $stmt = $schoolDataConn->prepare($query);
    
    if (!empty($search)) {
        $searchTerm = "%$search%";
        $stmt->bind_param("iss", $school_id, $searchTerm, $searchTerm);
    } else {
        $stmt->bind_param("i", $school_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch all students
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
}

$schoolDataConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Achievements Management</title>
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
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 10px;
            padding: 20px;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        .hover\:grow {
            transition: all 0.2s ease-in-out;
        }
        .hover\:grow:hover {
            transform: scale(1.05);
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
            transition: background-color 0.2s ease-in-out;
        }
        .btn-primary:hover {
            background-color: #3182ce;
        }
        .btn-secondary {
            background-color: #48bb78;
            color: #fff;
            transition: background-color 0.2s ease-in-out;
        }
        .btn-secondary:hover {
            background-color: #38a169;
        }
        .btn-search {
            background-color: #4299e1;
            color: #fff;
            transition: background-color 0.2s ease-in-out;
        }
        .btn-search:hover {
            background-color: #3182ce;
        }
        .table-header {
            background-color: #2a4365;
        }
        .table-cell {
            background-color: #2d3748;
        }
        .table-row {
            background-color: #2d3748;
            transition: background-color 0.2s ease-in-out;
        }
        .table-row:hover {
            background-color: #1a202c;
        }
    </style>
</head>
<body class="bg-gray-900">
    <header class="bg-blue-500 text-white p-4">
        <h1 class="text-3xl font-bold text-center">Manage Student Achievements</h1>
    </header>

    <div class="container mx-auto px-4 py-6">
        <!-- Search form -->
        <form method="GET" class="mb-6 flex justify-center gap-4">
            <input type="text" name="search" placeholder="Search by name or number" value="<?= htmlspecialchars($search); ?>" class="input-field shadow appearance-none border rounded py-2 px-3 leading-tight focus:outline-none focus:shadow-outline">
            <button type="submit" class="btn-search font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Search</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-gray-800 rounded-lg shadow">
                <thead class="table-header text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">View Awards</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-700 divide-y divide-gray-600">
                    <?php foreach ($students as $student): ?>
                    <tr class="text-center table-row">
                        <td class="border px-6 py-4 text-sm text-gray-200"><?= htmlspecialchars($student['fullname']); ?></td>
                        <td class="border px-6 py-4 text-sm text-gray-400"><?= htmlspecialchars($student['student_number']); ?></td>
                        <td class="border px-6 py-4 text-sm font-medium">
                            <a href="award_student.php?id=<?= $student['id']; ?>" class="text-indigo-500 hover:text-indigo-300">Award</a>
                        </td>
                        <td class="border px-6 py-4 text-sm font-medium">
                            <a href="view_award.php?id=<?= $student['id']; ?>" class="text-blue-500 hover:text-blue-300">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
