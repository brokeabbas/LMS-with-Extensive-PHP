<?php
session_start();
require_once '../../connections/db.php'; // Ensure this path is correct

// Redirect if not logged in or if school_id is not set
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["school_id"])) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

$school_id = $_SESSION['school_id'];
$school_name = "Unknown School"; // Default school name if not found

// Fetch school name
$schoolStmt = $userInfoConn->prepare("SELECT school_name FROM schools WHERE school_id = ?");
$schoolStmt->bind_param("i", $school_id);
$schoolStmt->execute();
$schoolResult = $schoolStmt->get_result();
if ($schoolRow = $schoolResult->fetch_assoc()) {
    $school_name = $schoolRow['school_name'];
}
$schoolStmt->close();

$search_query = $_GET['search'] ?? '';

// Prepare SQL based on search query
$sql = "SELECT id, name, email, department FROM teacher_info WHERE school_id = ?";
$sql .= !empty($search_query) ? " AND (name LIKE ? OR email LIKE ?)" : "";

if ($stmt = $userInfoConn->prepare($sql)) {
    if (!empty($search_query)) {
        $like_query = '%' . $search_query . '%';
        $stmt->bind_param("iss", $school_id, $like_query, $like_query);
    } else {
        $stmt->bind_param("i", $school_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $teachers = [];
    while ($row = $result->fetch_assoc()) {
        $teachers[] = $row;
    }
    $stmt->close();
} else {
    echo "Error preparing the statement: " . $userInfoConn->error;
}

$userInfoConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Existing Teachers</title>
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
    </style>
</head>
<body class="bg-gray-900">
    <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-200 mb-6 text-center">Existing Teachers at <?php echo htmlspecialchars($school_name); ?></h1>
        
        <!-- Search Form -->
        <form method="GET" class="flex flex-col md:flex-row md:items-center gap-4 mb-5">
            <div class="flex-grow">
                <input type="text" name="search" placeholder="Search by name or email" value="<?php echo htmlspecialchars($search_query); ?>" class="input-field form-input w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>
            <button type="submit" class="button-blue bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow">
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
                            Email
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                            Department
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                            Actions
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
                            <?php echo htmlspecialchars($teacher['email']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?php echo htmlspecialchars($teacher['department']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="view-teacher.php?id=<?php echo $teacher['id']; ?>" class="text-green-500 hover:text-green-300">View</a>
                            <a href="edit_teacher.php?id=<?php echo $teacher['id']; ?>" class="text-indigo-500 hover:text-indigo-300 ml-4">Edit</a>
                            <a href="delete_teacher.php?action=delete&id=<?php echo $teacher['id']; ?>" onclick="return confirm('Are you sure you want to delete this teacher? This action cannot be undone.');" class="text-red-500 hover:text-red-300 ml-4">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
