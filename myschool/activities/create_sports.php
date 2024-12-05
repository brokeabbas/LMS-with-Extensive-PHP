<?php
session_start(); // Start or resume the session

// Redirect if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../registration/myschool_login.php");
    exit;
}

require_once '../../connections/db_school_data.php'; // Include the school database connection

// Assuming the school_id is stored in session when the user logs in
$school_id = $_SESSION['school_id'] ?? null; // Ensure this variable is assigned during login

// Handling the form submission for adding new sports teams
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_sport'])) {
    $team_name = $_POST['team_name'] ?? '';
    $team_description = $_POST['team_description'] ?? '';

    // Insert new sports team into the database
    if (!empty($team_name) && $school_id) { // Check if school_id is not null
        $insert_query = "INSERT INTO extracurricular_activities (name, description, type, school_id) VALUES (?, ?, 'sport', ?)";
        if ($stmt = $schoolDataConn->prepare($insert_query)) {
            $stmt->bind_param("ssi", $team_name, $team_description, $school_id);
            $stmt->execute();
            echo "<p>Sports team added successfully!</p>";
            $stmt->close();
        } else {
            echo "<p>Error adding sports team: " . $schoolDataConn->error . "</p>";
        }
    } else {
        echo "<p>Required fields are missing or invalid school ID.</p>"; // Additional error handling for missing school_id
    }
}

$schoolDataConn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Sports Team</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-gray-100">
    <header class="bg-blue-500 text-white p-4">
        <h1 class="text-xl font-bold">Add New Sports Team</h1>
    </header>

    <div class="container mx-auto px-4 py-6">
        <form method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="mb-6">
            <div class="mb-4">
                <label for="team_name" class="block text-gray-700 font-bold mb-2">Sports Team Name:</label>
                <input type="text" id="team_name" name="team_name" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label for="team_description" class="block text-gray-700 font-bold mb-2">Sports Team Description:</label>
                <textarea id="team_description" name="team_description" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
            </div>
            <button type="submit" name="add_sport" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Add Sports Team</button>
        </form>
    </div>
</body>
</html>
