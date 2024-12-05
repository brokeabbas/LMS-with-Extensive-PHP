<?php
session_start(); // Start or resume the session

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../registration/myschool_login.php");
    exit;
}

// Include the specific database connection for school data
require_once '../../connections/db_school_data.php'; // Adjust the path as necessary

// Check for a valid school_id in the session
if (!isset($_SESSION['school_id'])) {
    echo "School ID not set in session. Please log in again.";
    exit;
}

$school_id = $_SESSION['school_id']; // Get the school_id from the session

// Fetch all extracurricular activities from the database that belong to the logged in school
$activities = [];
$query = "SELECT * FROM extracurricular_activities WHERE school_id = ? ORDER BY type";
if ($stmt = $schoolDataConn->prepare($query)) {
    $stmt->bind_param("i", $school_id); // Bind the school_id to the query
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    $stmt->close();
}

// Close database connection
$schoolDataConn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Activities Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-gray-100">
    <header class="bg-blue-500 text-white p-4">
        <h1 class="text-xl font-bold">Manage School Activities</h1>
    </header>

    <div class="container mx-auto px-4 py-6">
        <!-- Buttons for creating new activity types -->
        <div class="flex justify-end space-x-2 mb-4">
            <a href="create_clubs.php?type=club" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Add Club</a>
            <a href="create_sports.php?type=sport" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Add Sport</a>
            <a href="create_events.php?type=event" class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">Add Event</a>
        </div>

        <!-- Activity Sections -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Section for Clubs -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">School Clubs</h3>
                    <ul class="mt-3 list-disc list-inside text-sm text-gray-600">
                        <?php foreach ($activities as $activity) if ($activity['type'] === 'club'): ?>
                            <li>
                                <?php echo htmlspecialchars($activity['name']); ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <a href="manage_clubs.php?type=club" class="mt-2 inline-block text-indigo-600 hover:text-indigo-900">Manage Clubs</a>
                </div>
            </div>

            <!-- Section for Sports Teams -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Sports Teams</h3>
                    <ul class="mt-3 list-disc list-inside text-sm text-gray-600">
                        <?php foreach ($activities as $activity) if ($activity['type'] === 'sport'): ?>
                            <li>
                                <?php echo htmlspecialchars($activity['name']); ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <a href="manage_sports.php?type=sport" class="mt-2 inline-block text-indigo-600 hover:text-indigo-900">Manage Sports</a>
                </div>
            </div>

            <!-- Section for Events -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">School Events</h3>
                    <ul class="mt-3 list-disc list-inside text-sm text-gray-600">
                        <?php foreach ($activities as $activity) if ($activity['type'] === 'event'): ?>
                            <li>
                                <?php echo htmlspecialchars($activity['name']); ?>
                            </li>
                        <?php endif; ?>
                    </ul>
                    <a href="manage_events.php?type=event" class="mt-2 inline-block text-indigo-600 hover:text-indigo-900">Manage Events</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
