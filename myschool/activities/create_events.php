<?php
session_start(); // Start or resume the session

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../registration/myschool_login.php");
    exit;
}

require_once '../../connections/db_school_data.php'; // Include the school database connection

// Fetch school_id from session
$school_id = $_SESSION['school_id'] ?? null; // Ensure this variable is assigned during login

// Handling the form submission for adding new events
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_event'])) {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $time = $_POST['time'] ?? '';
    $location = $_POST['location'] ?? '';
    $organizer = $_POST['organizer'] ?? '';

    // Insert new event into the database
    if (!empty($name) && !empty($start_date) && !empty($end_date) && $school_id) {
        $insert_query = "INSERT INTO extracurricular_activities (name, description, type, start_date, end_date, time, location, organizer, school_id) VALUES (?, ?, 'event', ?, ?, ?, ?, ?, ?)";
        if ($stmt = $schoolDataConn->prepare($insert_query)) {
            $stmt->bind_param("sssssssi", $name, $description, $start_date, $end_date, $time, $location, $organizer, $school_id);
            $stmt->execute();
            echo "<p>Event added successfully!</p>";
            $stmt->close();
        } else {
            echo "<p>Error adding event: " . $schoolDataConn->error . "</p>";
        }
    } else {
        echo "<p>Missing required information or school ID.</p>"; // Handle cases where essential data or school_id is missing
    }
}

$schoolDataConn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Event</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-gray-100">
    <header class="bg-blue-500 text-white p-4">
        <h1 class="text-xl font-bold">Add New Event</h1>
    </header>

    <div class="container mx-auto px-4 py-6">
        <form method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="mb-6">
            <div class="mb-4">
                <label for="name" class="block text-gray-700 font-bold mb-2">Event Name:</label>
                <input type="text" id="name" name="name" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label for="description" class="block text-gray-700 font-bold mb-2">Event Description:</label>
                <textarea id="description" name="description" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
            </div>
            <div class="mb-4">
                <label for="start_date" class="block text-gray-700 font-bold mb-2">Start Date:</label>
                <input type="date" id="start_date" name="start_date" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label for="end_date" class="block text-gray-700 font-bold mb-2">End Date:</label>
                <input type="date" id="end_date" name="end_date" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label for="time" class="block text-gray-700 font-bold mb-2">Time:</label>
                <input type="time" id="time" name="time" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label for="location" class="block text-gray-700 font-bold mb-2">Location:</label>
                <input type="text" id="location" name="location" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label for="organizer" class="block text-gray-700 font-bold mb-2">Organizer:</label>
                <input type="text" id="organizer" name="organizer" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <button type="submit" name="add_event" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Add Event</button>
        </form>
    </div>
</body>
</html>
