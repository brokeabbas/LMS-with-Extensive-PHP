<?php
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

require_once '../../connections/db_school_data.php'; // Adjust the path as needed for the school-specific database

$school_id = $_SESSION['school_id'] ?? null;
$suggestions = [];

// Fetch suggestions from the database if school ID is set, ordered by creation date descending
if ($school_id) {
    $query = "SELECT id, suggestion_title, suggestion_body, created_at FROM suggestions WHERE school_id = ? ORDER BY created_at DESC";
    if ($stmt = $schoolDataConn->prepare($query)) {
        $stmt->bind_param("i", $school_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $suggestions[] = $row;
        }
        $stmt->close();
    }
    $schoolDataConn->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Suggestions</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(135deg, #1f2937, #3b82f6);
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
            color: #f8fafc;
        }
        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .card {
            background-color: #1e293b;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
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
        .read {
            background-color: #4b5563;
            color: #9ca3af;
        }
    </style>
</head>
<body class="bg-gray-900">
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-3xl font-bold text-center text-gray-200 mb-6">Suggestions for <?= htmlspecialchars($_SESSION['school_name'] ?? 'Your School'); ?></h1>
        <div class="max-w-lg mx-auto mb-4">
            <input type="text" id="searchBar" placeholder="Search suggestions..." class="w-full p-2 rounded bg-gray-700 text-gray-200 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div id="suggestionsContainer" class="mt-5">
            <?php if (!empty($suggestions)): ?>
                <?php foreach ($suggestions as $suggestion): ?>
                    <div class="bg-gray-800 rounded-lg p-5 shadow mb-4 card">
                        <h3 class="text-lg font-semibold text-gray-200"><?= htmlspecialchars($suggestion['suggestion_title']); ?></h3>
                        <p class="text-gray-400"><?= nl2br(htmlspecialchars($suggestion['suggestion_body'])); ?></p>
                        <small class="text-sm text-gray-500">Posted on: <?= date('F j, Y, g:i a', strtotime($suggestion['created_at'])); ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center text-gray-500">No suggestions have been made yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchBar = document.getElementById('searchBar');
            const suggestionsContainer = document.getElementById('suggestionsContainer');
            const markAsReadButtons = document.querySelectorAll('.mark-as-read');

            searchBar.addEventListener('input', function() {
                const filter = searchBar.value.toLowerCase();
                const suggestions = suggestionsContainer.querySelectorAll('.card');
                suggestions.forEach(function(suggestion) {
                    const title = suggestion.querySelector('h3').textContent.toLowerCase();
                    const body = suggestion.querySelector('p').textContent.toLowerCase();
                    if (title.includes(filter) || body.includes(filter)) {
                        suggestion.style.display = '';
                    } else {
                        suggestion.style.display = 'none';
                    }
                });
            });

            markAsReadButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    const suggestion = button.parentElement;
                    suggestion.classList.add('read');
                    suggestionsContainer.appendChild(suggestion);
                    button.remove();
                });
            });
        });
    </script>
</body>
</html>
