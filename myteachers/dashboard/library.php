<?php
// Start session and include database connection
session_start();
require_once '../../connections/db_school_data.php';

// Redirect to login page if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["school_id"])) {
    header("location: login_teacher.php");
    exit;
}

// Fetch books data
$school_id = $_SESSION['school_id'];
$books = [];
// Fetch approved books from the database
$sql = "SELECT id, title, author, genre, cover, book_pdf
        FROM library
        WHERE school_id = ? AND is_approved = 1
        ORDER BY created_at DESC";

if ($stmt = $schoolDataConn->prepare($sql)) {
    $stmt->bind_param("i", $school_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        // Remove the first occurrence of '../' from cover and book_pdf paths
        $row['cover'] = preg_replace('/^\.\.\//', '', $row['cover'], 1); // Limits the replacement to the first occurrence
        $row['book_pdf'] = preg_replace('/^\.\.\//', '', $row['book_pdf'], 1); // Same here

        $books[] = $row;
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
    <!-- Meta tags, title, and CSS links -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #2d3748;
            color: #e2e8f0;
        }
        .book-card {
            max-width: 180px;
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            background-color: #1a202c;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .book-card img {
            height: 250px;
            object-fit: cover;
            width: 100%;
        }
        .book-card .info {
            padding: 15px;
        }
        .book-card .info .title {
            color: #f7fafc;
            font-size: 1.1rem;
            font-weight: 700;
        }
        .book-card .info .author {
            color: #a0aec0;
            font-size: 0.875rem;
            margin-top: 5px;
        }
        .book-card .info .genre {
            display: inline-block;
            background-color: #4a5568;
            border-radius: 12px;
            padding: 5px 10px;
            color: #f7fafc;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 10px;
        }
        nav {
            background-color: #1a202c;
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        nav .nav-links {
            display: flex;
            gap: 20px;
        }
        nav .nav-links a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease-in-out;
        }
        nav .nav-links a:hover {
            background-color: #2d3748;
        }
        .search-bar {
            margin-top: 20px;
            text-align: center;
        }
        .search-bar input {
            width: 60%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #4a5568;
            background-color: #1a202c;
            color: #e2e8f0;
        }
    </style>
</head>
<body class="font-sans antialiased">
    <nav>
        <div class="logo">
            <h1 class="text-2xl font-bold">Library</h1>
        </div>
    </nav>
    <div class="search-bar">
        <input type="text" id="searchInput" placeholder="Search by book name, author, or genre..." class="p-2 border rounded">
    </div>
    <div class="container mx-auto px-4 py-6">
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4" id="booksGrid">
            <?php foreach ($books as $book): ?>
            <div class="book-card">
                <a href="<?= htmlspecialchars($book['book_pdf']); ?>" target="_blank">
                    <img src="<?= htmlspecialchars($book['cover']); ?>" alt="Cover image for <?= htmlspecialchars($book['title']); ?>">
                </a>
                <div class="info">
                    <div class="title"><?= htmlspecialchars($book['title']); ?></div>
                    <div class="author">Author: <?= htmlspecialchars($book['author']); ?></div>
                    <div class="genre"><?= htmlspecialchars($book['genre']); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        // JavaScript to handle book search
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const books = document.querySelectorAll('.book-card');

            books.forEach(book => {
                const title = book.querySelector('.title').textContent.toLowerCase();
                const author = book.querySelector('.author').textContent.toLowerCase();
                const genre = book.querySelector('.genre').textContent.toLowerCase();

                if (title.includes(searchTerm) || author.includes(searchTerm) || genre.includes(searchTerm)) {
                    book.style.display = 'block';
                } else {
                    book.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
