<?php
session_start(); // Start or resume the session

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../registration/myschool_login.php");
    exit;
}

require_once '../../connections/db_school_data.php'; // Include the school database connection

// Fetch the school ID from the session
$school_id = $_SESSION['school_id'] ?? null; // Ensure that 'school_id' is set during login

// Fetch all clubs from the database that belong to the logged-in school
$clubs = [];
if ($school_id) {
    $query = "SELECT * FROM extracurricular_activities WHERE type = 'club' AND school_id = ? ORDER BY name";
    if ($stmt = $schoolDataConn->prepare($query)) {
        $stmt->bind_param("i", $school_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $clubs[] = $row;
        }
        $stmt->close();
    }
} else {
    echo "<p>Could not identify school. Please ensure you are logged in properly.</p>";
}

$schoolDataConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage School Clubs</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-gray-100">
    <header class="bg-blue-500 text-white p-4">
        <h1 class="text-xl font-bold">School Clubs Management</h1>
    </header>

    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-end mb-4">
            <a href="create_clubs.php" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Add New Club</a>
        </div>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <table class="min-w-full">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Club Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($clubs as $club): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo htmlspecialchars($club['name']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="edit-activity.php?id=<?php echo $club['id']; ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                            <a href="delete-activity.php?id=<?php echo $club['id']; ?>" class="text-red-600 hover:text-red-900 ml-4">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
