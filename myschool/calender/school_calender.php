<?php
session_start();
require_once '../../connections/db_school_data.php'; // Database connection

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

// Assuming school_id is stored in session when the user logs in
$school_id = $_SESSION['school_id'] ?? null;

$message = $_SESSION['message'] ?? '';
$message_type = $_SESSION['message_type'] ?? '';
unset($_SESSION['message'], $_SESSION['message_type']);

// Handle POST request to add an event
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_event'])) {
    $event_title = $_POST['event_title'] ?? '';
    $event_date = $_POST['event_date'] ?? '';
    $event_week = $_POST['event_week'] ?? '';
    $event_description = $_POST['event_description'] ?? '';

    if (!empty($event_title) && !empty($event_date) && !empty($event_week) && $school_id) {
        $insert_query = "INSERT INTO school_calendar (event_title, event_date, event_week, event_description, school_id) VALUES (?, ?, ?, ?, ?)";
        if ($stmt = $schoolDataConn->prepare($insert_query)) {
            $stmt->bind_param("ssisi", $event_title, $event_date, $event_week, $event_description, $school_id);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Event added successfully!";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error adding event: " . $schoolDataConn->error;
                $_SESSION['message_type'] = "error";
            }
            $stmt->close();
        } else {
            $_SESSION['message'] = "Error preparing query: " . $schoolDataConn->error;
            $_SESSION['message_type'] = "error";
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $_SESSION['message'] = "Please fill in all required fields.";
        $_SESSION['message_type'] = "error";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Fetch events from the database
$events = [];
$query = "SELECT * FROM school_calendar WHERE school_id = ? ORDER BY event_date ASC";
if ($stmt = $schoolDataConn->prepare($query)) {
    $stmt->bind_param("i", $school_id);
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
    <title>School Calendar Management</title>
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
    </style>
</head>
<body class="bg-gray-900">
    <header class="bg-blue-500 text-white p-4">
        <h1 class="text-3xl font-bold text-center">School Calendar Management</h1>
    </header>

    <div class="container mx-auto px-4 py-6">
        <?php if (!empty($message)): ?>
            <div class="mb-4 <?= $message_type === 'success' ? 'bg-green-500' : 'bg-red-500' ?> text-white font-bold py-2 px-4 rounded">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        <form method="post" class="mb-6 card p-6 rounded-lg shadow">
            <div class="mb-4">
                <label for="event_week" class="block text-gray-300 font-bold mb-2">Event Week:</label>
                <input type="number" id="event_week" name="event_week" required class="input-field shadow appearance-none border rounded w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label for="event_title" class="block text-gray-300 font-bold mb-2">Event Title:</label>
                <input type="text" id="event_title" name="event_title" required class="input-field shadow appearance-none border rounded w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label for="event_date" class="block text-gray-300 font-bold mb-2">Event Date:</label>
                <input type="date" id="event_date" name="event_date" required class="input-field shadow appearance-none border rounded w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label for="event_description" class="block text-gray-300 font-bold mb-2">Event Description:</label>
                <textarea id="event_description" name="event_description" rows="3" class="input-field shadow appearance-none border rounded w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline"></textarea>
            </div>
            <div class="flex justify-between">
                <button type="submit" name="add_event" class="btn-primary text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Add Event</button>
                <button type="button" onclick="window.location.href='calendar_view.php';" class="btn-secondary text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Edit Calendar</button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-gray-800 rounded-lg shadow">
                <thead class="table-header text-white">
                    <tr>
                        <th class="px-4 py-2">Event Week</th>
                        <th class="px-4 py-2">Event Title</th>
                        <th class="px-4 py-2">Event Date</th>
                        <th class="px-4 py-2">Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                    <tr class="text-center table-row">
                        <td class="border px-4 py-2"><?php echo htmlspecialchars($event['event_week']); ?></td>
                        <td class="border px-4 py-2"><?php echo htmlspecialchars($event['event_title']); ?></td>
                        <td class="border px-4 py-2"><?php echo htmlspecialchars($event['event_date']); ?></td>
                        <td class="border px-4 py-2"><?php echo htmlspecialchars($event['event_description']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
