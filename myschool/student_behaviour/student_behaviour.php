<?php
session_start(); // Start or resume the session

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../registration/myschool_login.php");
    exit;
}

// Include database connection files for school data and user info
require_once '../../connections/db_school_data.php'; // Adjust the path as necessary
require_once '../../connections/db.php'; // Connection for user info

// Initialize search query if present
$search = $_GET['search'] ?? '';

// Fetch the school_id from the session
$school_id = $_SESSION['school_id'];

// Fetch all disciplinary records from the database
$records = [];
$query = "SELECT dr.*, si.fullname AS student_name, si.student_number FROM schoolhu_school_data.disciplinary_records dr 
          JOIN schoolhu_userinfo.student_info si ON dr.student_id = si.id 
          WHERE dr.school_id = ? ";

// Append search condition if there is a search term
if (!empty($search)) {
    $query .= "AND (si.fullname LIKE ? OR si.student_number LIKE ?)";
}

$query .= " ORDER BY dr.recorded_on DESC";

if ($stmt = $schoolDataConn->prepare($query)) {
    if (!empty($search)) {
        $param = '%' . $search . '%';
        $stmt->bind_param("iss", $school_id, $param, $param);
    } else {
        $stmt->bind_param("i", $school_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    $stmt->close();
}

// Close both database connections
$schoolDataConn->close();
$userInfoConn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Student Behavior</title>
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
        <h1 class="text-3xl font-bold text-center">Student Behavior Management</h1>
    </header>

    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold text-gray-300">Disciplinary Records</h2>
            <a href="add-disciplinary-record.php" class="btn-secondary font-bold py-2 px-4 rounded">Add New Record</a>
        </div>

        <!-- Search form -->
        <form method="GET" class="mb-6 flex">
            <input type="text" name="search" placeholder="Search by name or number" value="<?php echo htmlspecialchars($search); ?>" class="input-field shadow appearance-none border rounded py-2 px-3 leading-tight focus:outline-none focus:shadow-outline mr-2 flex-grow">
            <button type="submit" class="btn-search font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Search</button>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-gray-800 rounded-lg shadow">
                <thead class="table-header text-white">
                    <tr>
                        <th class="px-4 py-2">Student Name</th>
                        <th class="px-4 py-2">Student Number</th>
                        <th class="px-4 py-2">Incident Date</th>
                        <th class="px-4 py-2">Details</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr class="text-center table-row">
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($record['student_name']); ?></td>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($record['student_number']); ?></td>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($record['recorded_on']); ?></td>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($record['strike_description']); ?></td>
                            <td class="border px-4 py-2">
                                <a href="view-detail.php?id=<?php echo $record['id']; ?>" class="text-blue-500 hover:text-blue-800">View</a> |
                                <a href="delete-record.php?id=<?php echo $record['id']; ?>" onclick="return confirm('Are you sure?');" class="text-red-500 hover:text-red-800">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
