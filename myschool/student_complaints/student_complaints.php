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

$complaints = [];
$search = $_GET['search'] ?? '';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

// Check if school_id is set in the session
if (isset($_SESSION['school_id'])) {
    $school_id = $_SESSION['school_id'];

    // Prepare a statement to fetch complaints and student names from the database
    $query = "
        SELECT sc.title, sc.body, sc.created_at, sc.updated_at, si.fullname, si.student_number
        FROM student_complaints sc
        JOIN schoolhu_userinfo.student_info si ON sc.student_id = si.id
        WHERE sc.school_id = ? AND si.school_id = ?";

    if (!empty($search)) {
        $query .= " AND (sc.title LIKE ? OR si.fullname LIKE ?)";
    }
    if (!empty($startDate) && !empty($endDate)) {
        $query .= " AND sc.created_at BETWEEN ? AND ?";
    }

    $stmt = $schoolDataConn->prepare($query);
    
    if (!empty($search) && !empty($startDate) && !empty($endDate)) {
        $searchTerm = "%$search%";
        $stmt->bind_param("iissss", $school_id, $school_id, $searchTerm, $searchTerm, $startDate, $endDate);
    } elseif (!empty($search)) {
        $searchTerm = "%$search%";
        $stmt->bind_param("iiss", $school_id, $school_id, $searchTerm, $searchTerm);
    } elseif (!empty($startDate) && !empty($endDate)) {
        $stmt->bind_param("iiss", $school_id, $school_id, $startDate, $endDate);
    } else {
        $stmt->bind_param("ii", $school_id, $school_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch all complaints
    while ($row = $result->fetch_assoc()) {
        $complaints[] = $row;
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
    <title>Student Complaints</title>
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
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-3xl font-bold mb-6 text-center text-gray-200">Student Complaints</h1>
        
        <!-- Search form -->
        <form method="GET" class="mb-6 flex flex-wrap justify-center gap-4">
            <input type="text" name="search" placeholder="Search by title or student" value="<?php echo htmlspecialchars($search); ?>" class="input-field shadow appearance-none border rounded py-2 px-3 leading-tight focus:outline-none focus:shadow-outline">
            <input type="date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>" class="input-field shadow appearance-none border rounded py-2 px-3 leading-tight focus:outline-none focus:shadow-outline">
            <input type="date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>" class="input-field shadow appearance-none border rounded py-2 px-3 leading-tight focus:outline-none focus:shadow-outline">
            <button type="submit" class="btn-search font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Search</button>
        </form>

        <?php if (!empty($complaints)): ?>
            <div class="bg-gray-800 p-4 rounded shadow card">
                <h2 class="text-lg font-semibold text-gray-300">Complaints List</h2>
                <?php foreach ($complaints as $complaint): ?>
                    <div class="mt-4 p-4 border border-gray-200 rounded">
                        <h3 class="font-semibold text-lg text-gray-200">
                            <?php echo htmlspecialchars($complaint['title']); ?> - <?php echo htmlspecialchars($complaint['fullname']); ?>
                            <span class="text-sm text-gray-500">(<?php echo htmlspecialchars($complaint['student_number']); ?>)</span>
                        </h3>
                        <p class="text-gray-400"><?php echo nl2br(htmlspecialchars($complaint['body'])); ?></p>
                        <p class="text-sm text-gray-500">Posted on: <?php echo date('F j, Y, g:i a', strtotime($complaint['created_at'])); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-300">No complaints found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
