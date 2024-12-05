<?php
session_start();
require_once '../../connections/db_school_data.php'; // Adjust this path as needed

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["school_id"])) {
    header("location: login_teacher.php");
    exit;
}

$school_id = $_SESSION['school_id'];
$eventsByWeek = [];

// Fetch school calendar events from the database
$sql = "SELECT event_title, event_date, event_description, event_week
        FROM school_calendar
        WHERE school_id = ? AND is_published = 1
        ORDER BY event_week ASC, event_date ASC";

if ($stmt = $schoolDataConn->prepare($sql)) {
    $stmt->bind_param("i", $school_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $eventsByWeek[$row['event_week']][] = $row;
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
    <title>School Calendar</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            font-family: 'Nunito', sans-serif; /* Consistent modern font */
            background-color: #f3f4f6;
        }
        .hover-rise:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .custom-header {
            background: linear-gradient(to right, #667eea, #764ba2);
            color: white;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar for navigation -->
        <aside class="w-64 bg-gradient-to-b from-gray-800 to-gray-900 text-white p-5 shadow-md overflow-auto">
            <div class="px-6 py-8">
                <h1 class="text-xl font-semibold">School Calendar</h1>
                <nav class="mt-10 space-y-4">
                    <a href="../myteach.php" class="flex items-center p-2 rounded hover:bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-tachometer-alt mr-3 icon"></i>Dashboard
                    </a>
                    <a href="" class="flex items-center p-2 rounded bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-calendar-alt mr-3 icon"></i>School Calendar
                    </a>
                    <a href="logout.php" class="flex items-center p-2 rounded hover:bg-gray-700 transition-colors duration-200">
                        <i class="fas fa-sign-out-alt mr-3 icon"></i>Logout
                    </a>
                </nav>
            </div>
        </aside>
        <!-- Main content area -->
        <div class="flex-1 flex flex-col">
            <header class="custom-header p-6 shadow-lg">
                <div class="max-w-7xl mx-auto flex justify-between items-center">
                    <h2 class="text-3xl font-bold">School Calendar</h2>
                    <i class="fas fa-calendar-alt text-lg"></i>
                </div>
            </header>
            <main class="p-6 bg-white text-gray-800 flex-1">
                <div class="container mx-auto px-4">
                    <?php if (!empty($eventsByWeek)): ?>
                        <?php foreach ($eventsByWeek as $week => $events): ?>
                            <div class="mb-8">
                                <h2 class="text-2xl font-semibold text-center mb-4">Week <?= $week ?></h2>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full bg-white shadow-lg rounded-lg">
                                        <thead class="bg-gray-200 text-gray-600">
                                            <tr>
                                                <th class="py-3 px-6 text-left">Date</th>
                                                <th class="py-3 px-6 text-left">Event Title</th>
                                                <th class="py-3 px-6 text-left">Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($events as $event): ?>
                                                <tr class="border-b border-gray-200 hover:bg-gray-100 hover-rise">
                                                    <td class="py-3 px-6 text-left whitespace-nowrap"><?= htmlspecialchars($event['event_date']); ?></td>
                                                    <td class="py-3 px-6 text-left"><?= htmlspecialchars($event['event_title']); ?></td>
                                                    <td class="py-3 px-6 text-left"><?= nl2br(htmlspecialchars($event['event_description'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center">No events to display.</p>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
