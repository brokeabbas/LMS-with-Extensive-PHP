<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

// Database connection
require_once '../../connections/db.php';

// Initialize search query
$search_query = '';
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
    $sql = "SELECT name, teacher_number, username FROM teacher_info WHERE school_id = ? AND (name LIKE ? OR teacher_number LIKE ?)";
    $search_param = "%" . $search_query . "%";
    $stmt = $userInfoConn->prepare($sql);
    $stmt->bind_param("iss", $_SESSION["school_id"], $search_param, $search_param);
} else {
    // Fetch teacher data
    $sql = "SELECT name, teacher_number, username FROM teacher_info WHERE school_id = ?";
    $stmt = $userInfoConn->prepare($sql);
    $stmt->bind_param("i", $_SESSION["school_id"]);
}

$stmt->execute();
$result = $stmt->get_result();

$teachers = [];
while ($row = $result->fetch_assoc()) {
    $teachers[] = $row;
}

$stmt->close();
$userInfoConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Accounts</title>
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
        .button-blue {
            background-color: #63b3ed;
            color: #fff;
            transition: background-color 0.2s ease-in-out, transform 0.2s ease-in-out;
        }
        .button-blue:hover {
            background-color: #3182ce;
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="bg-gray-900">
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-200 mb-6 text-center">Teacher Accounts</h1>

        <!-- Search Form -->
        <form method="GET" class="flex flex-col md:flex-row md:items-center gap-4 mb-5">
            <div class="flex-grow">
                <input type="text" name="search" placeholder="Search by name or teacher number" value="<?php echo htmlspecialchars($search_query); ?>" class="input-field form-input w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            <button type="submit" class="button-blue font-bold py-2 px-6 rounded shadow">
                Search
            </button>
        </form>

        <div class="bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="table-header text-white">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                            Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                            Teacher Number
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                            Username
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-gray-700 divide-y divide-gray-600 text-white">
                    <?php foreach ($teachers as $teacher): ?>
                    <tr class="table-row">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <?php echo htmlspecialchars($teacher['name']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?php echo htmlspecialchars($teacher['teacher_number']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?php echo htmlspecialchars($teacher['username']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
