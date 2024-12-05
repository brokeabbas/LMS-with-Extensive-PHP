<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

require_once '../../../connections/db_school_data.php'; // Ensure the database connection is available

// Handling the approval or denial of a book request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve'])) {
        $requestId = $_POST['request_id'];
        $statusUpdateQuery = "UPDATE book_requests SET status = 'Approved' WHERE book_request_id = ?";
    } elseif (isset($_POST['decline'])) {
        $requestId = $_POST['request_id'];
        $statusUpdateQuery = "UPDATE book_requests SET status = 'Declined' WHERE book_request_id = ?";
    }

    if (!empty($statusUpdateQuery)) {
        if ($stmt = $schoolDataConn->prepare($statusUpdateQuery)) {
            $stmt->bind_param('i', $requestId);
            $stmt->execute();
            $stmt->close();
            // Redirect to avoid resubmission on refresh
            header("Location: " . htmlspecialchars($_SERVER['PHP_SELF']));
            exit;
        } else {
            echo "Error updating record: " . $schoolDataConn->error;
        }
    }
}

// Fetch all book requests from the database for the logged-in school
$school_id = $_SESSION['school_id']; // Assuming school_id is stored in session
$requests = [];
$query = "SELECT book_request_id, book_name, author, genre, requester_id, status 
          FROM book_requests 
          WHERE school_id = ? 
          ORDER BY created_at DESC";

if ($stmt = $schoolDataConn->prepare($query)) {
    $stmt->bind_param("i", $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }
    $stmt->close();
} else {
    echo "Error preparing the statement: " . $schoolDataConn->error;
}

$schoolDataConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Book Requests</title>
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
        .btn-approve {
            color: #3b82f6; /* Blue color */
            transition: color 0.2s ease-in-out;
        }
        .btn-approve:hover {
            color: #2563eb; /* Darker blue */
        }
        .btn-decline {
            color: #ef4444; /* Red color */
            transition: color 0.2s ease-in-out;
        }
        .btn-decline:hover {
            color: #b91c1c; /* Darker red */
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
        <h1 class="text-3xl font-bold text-gray-200 mb-6 text-center">Student Book Requests</h1>
        <div class="overflow-x-auto relative bg-gray-800 shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="table-header text-white">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                            Book Name
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                            Author
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                            Genre
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-gray-700 divide-y divide-gray-600 text-white">
                    <?php foreach ($requests as $request): ?>
                    <tr class="table-row">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <?= htmlspecialchars($request['book_name']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?= htmlspecialchars($request['author']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?= htmlspecialchars($request['genre']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <?= htmlspecialchars($request['status']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <form method="post" class="inline">
                                <input type="hidden" name="request_id" value="<?= $request['book_request_id']; ?>">
                                <button type="submit" name="approve" class="btn-approve text-blue-500 hover:text-blue-700">Approve</button>
                                <button type="submit" name="decline" class="btn-decline text-red-500 hover:text-red-700 ml-4">Decline</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (empty($requests)): ?>
                <p class="text-center py-4 text-white">No book requests found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
