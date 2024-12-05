<?php
session_start(); // Start or resume the session

// Redirect if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

require_once '../../connections/db_school_data.php'; // Include the school database connection

// Assuming the school_id is stored in session when the user logs in
$school_id = $_SESSION['school_id'] ?? null; // Make sure this variable is assigned correctly in your login script

// Handling the form submission for adding new clubs
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_club'])) {
    $club_name = $_POST['club_name'] ?? '';
    $club_description = $_POST['club_description'] ?? '';

    // Insert new club into the database
    if (!empty($club_name) && $school_id) { // Check if school_id is not null
        $insert_query = "INSERT INTO extracurricular_activities (name, description, type, school_id) VALUES (?, ?, 'club', ?)";
        if ($stmt = $schoolDataConn->prepare($insert_query)) {
            $stmt->bind_param("ssi", $club_name, $club_description, $school_id);
            $stmt->execute();
            echo "<p>Club added successfully!</p>";
            $stmt->close();
        } else {
            echo "<p>Error adding club: " . $schoolDataConn->error . "</p>";
        }
    }
}

$schoolDataConn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Club</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="icon" href="IMAGES/3.png" type="image/x-icon">
</head>
<body class="bg-gray-100">
    <header class="bg-blue-500 text-white p-4">
        <h1 class="text-xl font-bold">Add New Club</h1>
    </header>

    <div class="container mx-auto px-4 py-6">
        <form method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="mb-6">
            <div class="mb-4">
                <label for="club_name" class="block text-gray-700 font-bold mb-2">Club Name:</label>
                <input type="text" id="club_name" name="club_name" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label for="club_description" class="block text-gray-700 font-bold mb-2">Club Description:</label>
                <textarea id="club_description" name="club_description" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"></textarea>
            </div>
            <button type="submit" name="add_club" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Add Club</button>
        </form>
    </div>
</body>
</html>
