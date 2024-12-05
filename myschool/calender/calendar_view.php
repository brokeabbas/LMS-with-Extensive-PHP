<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

require_once '../../connections/db_school_data.php'; // Include the school database connection

$school_id = $_SESSION['school_id'] ?? null; // Assuming school_id is stored in session

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['publish_event'])) {
        $event_id = $_POST['event_id'] ?? 0;
        $update_query = "UPDATE school_calendar SET is_published = 1 WHERE id = ? AND school_id = ?";
        if ($stmt = $schoolDataConn->prepare($update_query)) {
            $stmt->bind_param("ii", $event_id, $school_id);
            $stmt->execute();
            $stmt->close();
            header("location: " . $_SERVER["PHP_SELF"]);
        } else {
            echo "<p>Error updating event: " . $schoolDataConn->error . "</p>";
        }
    } elseif (isset($_POST['delete_event'])) {
        $event_id = $_POST['event_id'] ?? 0;
        $delete_query = "DELETE FROM school_calendar WHERE id = ? AND school_id = ?";
        if ($stmt = $schoolDataConn->prepare($delete_query)) {
            $stmt->bind_param("ii", $event_id, $school_id);
            $stmt->execute();
            $stmt->close();
            header("location: " . $_SERVER["PHP_SELF"]);
        } else {
            echo "<p>Error deleting event: " . $schoolDataConn->error . "</p>";
        }
    }
}

$search = $_GET['search'] ?? '';
$events = [];
$query = "SELECT * FROM school_calendar WHERE school_id = ? AND (event_title LIKE ? OR event_week LIKE ?) ORDER BY event_week ASC, event_date ASC";
if ($stmt = $schoolDataConn->prepare($query)) {
    $like_search = '%' . $search . '%';
    $stmt->bind_param("iss", $school_id, $like_search, $like_search);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
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
    <title>View School Calendar</title>
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
            background-color: #4a5568;
            color: #fff;
            transition: background-color 0.2s ease-in-out;
        }
        .btn-secondary:hover {
            background-color: #2d3748;
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
        @media print {
            .no-print { display: none; }
            body { background: white; }
            header, form, .btn { visibility: hidden; }
        }
    </style>
    <script>
        function printPage() {
            window.print();
        }
    </script>
</head>
<body class="bg-gray-900">
    <header class="bg-blue-500 text-white p-4 text-center">
        <h1 class="text-3xl font-bold">School Calendar</h1>
    </header>

    <div class="container mx-auto px-4 py-6">
        <div class="mb-4 flex justify-between">
            <form class="flex" method="get">
                <input type="text" name="search" placeholder="Search by week or title" value="<?= htmlspecialchars($search); ?>" class="input-field shadow appearance-none border rounded py-2 px-3 mr-2 leading-tight focus:outline-none focus:shadow-outline no-print">
                <button type="submit" class="btn-primary font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline no-print">Search</button>
            </form>
            <button onclick="printPage()" class="btn-primary bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline no-print">Print</button>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-gray-800 rounded-lg shadow">
                <thead class="table-header text-white">
                    <tr>
                        <th class="px-4 py-2">Event Week</th>
                        <th class="px-4 py-2">Event Title</th>
                        <th class="px-4 py-2">Event Date</th>
                        <th class="px-4 py-2">Description</th>
                        <th class="px-4 py-2 no-print">Status</th>
                        <th class="px-4 py-2 no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                    <tr class="text-center table-row">
                        <td class="border px-4 py-2"><?= htmlspecialchars($event['event_week']); ?></td>
                        <td class="border px-4 py-2"><?= htmlspecialchars($event['event_title']); ?></td>
                        <td class="border px-4 py-2"><?= htmlspecialchars($event['event_date']); ?></td>
                        <td class="border px-4 py-2"><?= htmlspecialchars($event['event_description']); ?></td>
                        <td class="border px-4 py-2 no-print"><?= $event['is_published'] ? 'Published' : 'Unpublished'; ?></td>
                        <td class="border px-4 py-2 no-print">
                            <form method="post">
                                <input type="hidden" name="event_id" value="<?= $event['id']; ?>">
                                <?php if (!$event['is_published']): ?>
                                    <button type="submit" name="publish_event" class="text-green-600 hover:text-green-800">Publish</button>
                                <?php endif; ?>
                                <button type="submit" name="delete_event" class="text-red-500 hover:text-red-800" onclick="return confirm('Are you sure you want to delete this event?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
