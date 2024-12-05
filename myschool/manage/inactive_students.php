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

// Prepare SQL based on search query, including check for is_active
$sql = "SELECT id, fullname, dob, email, phone, student_number FROM student_info WHERE school_id = ? AND is_active = 0";
$sql .= !empty($search_query) ? " AND (fullname LIKE ? OR student_number LIKE ?)" : "";

if ($stmt = $userInfoConn->prepare($sql)) {
    if (!empty($search_query)) {
        $like_query = '%' . $search_query . '%';
        $stmt->bind_param("iss", $school_id, $like_query, $like_query);
    } else {
        $stmt->bind_param("i", $school_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
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
    <title>Manage Inactive Students</title>
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
            transition: box-shadow 0.3s ease-in-out;
        }
        .input-field:focus {
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.5);
            border-color: #3182ce;
        }
        .button-blue {
            background-color: #63b3ed;
            color: #fff;
            transition: background-color 0.2s ease-in-out, transform 0.2s ease-in-out;
        }
        .button-blue:hover {
            background-color: #2b6cb0;
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
        <h1 class="text-3xl font-bold text-gray-200 mb-6 text-center">Inactive Students at <?php echo htmlspecialchars($school_name); ?></h1>

        <!-- Search Form -->
        <form method="GET" class="flex flex-col md:flex-row md:items-center gap-4 mb-5">
            <div class="flex-grow">
                <input type="text" name="search" placeholder="Search by name or number" value="<?php echo htmlspecialchars($search_query); ?>" class="input-field form-input w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-indigo-500">
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
                            Date of Birth
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                            Email
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                            Phone
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                            Student Number
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-gray-700 divide-y divide-gray-600 text-white">
                    <?php foreach ($students as $student): ?>
                    <tr class="table-row">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <?php echo htmlspecialchars($student['fullname']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?php echo htmlspecialchars($student['dob']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?php echo htmlspecialchars($student['email']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?php echo htmlspecialchars($student['phone']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?php echo htmlspecialchars($student['student_number']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="view_students.php?id=<?php echo $student['id']; ?>" class="text-green-500 hover:text-green-700">View</a>
                            <a href="edit-student.php?id=<?php echo $student['id']; ?>" class="text-indigo-500 hover:text-indigo-700 ml-4">Edit</a>
                            <a href="activate_student.php?student_id=<?php echo $student['id']; ?>" onclick="return confirm('Are you sure you want to enable this student?');" class="text-blue-500 hover:text-blue-700 ml-4">Enable</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
