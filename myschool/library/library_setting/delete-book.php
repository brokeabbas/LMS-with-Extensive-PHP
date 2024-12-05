<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../../registration/myschool_login.php");
    exit;
}

require_once '../../../connections/db_school_data.php'; // Adjust path as necessary

$school_id = $_SESSION['school_id']; // Retrieve the school ID from session
$message = '';

// Handle deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    
    // Optional: Fetch file paths to delete files if needed
    $fileQuery = "SELECT cover, book_pdf FROM library WHERE id = ? AND school_id = ?";
    if ($stmt = $schoolDataConn->prepare($fileQuery)) {
        $stmt->bind_param("ii", $delete_id, $school_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $fileData = $result->fetch_assoc();
        $stmt->close();

        // Delete files from server
        if ($fileData) {
            @unlink($fileData['cover']);
            @unlink($fileData['book_pdf']);
        }
    }

    // Delete the book from the database
    $sql = "DELETE FROM library WHERE id = ? AND school_id = ?";
    if ($stmt = $schoolDataConn->prepare($sql)) {
        $stmt->bind_param("ii", $delete_id, $school_id);
        if ($stmt->execute()) {
            $message = "Book deleted successfully!";
        } else {
            $message = "Error deleting book.";
        }
        $stmt->close();
    }
}

// Fetch all books to display for the logged-in school
$books = [];
$query = "SELECT id, title, author, cover FROM library WHERE school_id = ?";
$stmt = $schoolDataConn->prepare($query);
$stmt->bind_param("i", $school_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $books[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Book from Library</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background-color: #2d3748; /* Darker background */
            color: #e2e8f0; /* Light text color for better contrast */
        }
        .book-card {
            max-width: 180px;
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            background-color: #1a202c; /* Darker background for book cards */
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
        .btn-red {
            background-color: #e53e3e; /* Red color */
            color: #fff;
            transition: background-color 0.2s ease-in-out, transform 0.2s ease-in-out;
        }
        .btn-red:hover {
            background-color: #c53030; /* Darker red */
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="font-sans antialiased">
    <nav>
        <div class="logo">
            <h1 class="text-2xl font-bold">Library</h1>
        </div>
        <div class="nav-links">
            <a href="add-book.php">Add Book</a>
            <a href="view-books.php">View Library</a>
        </div>
    </nav>
    <div class="search-bar">
        <input type="text" id="searchInput" placeholder="Search by book name, author, or genre..." class="p-2 border rounded">
    </div>
    <div class="container mx-auto px-4 py-6">
        <p class="text-center p-4 text-red-500"><?php echo $message; ?></p>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4" id="booksGrid">
            <?php foreach ($books as $book): ?>
            <div class="book-card">
                <img src="<?= htmlspecialchars($book['cover']); ?>" alt="Cover image for <?= htmlspecialchars($book['title']); ?>">
                <div class="info">
                    <div class="title"><?= htmlspecialchars($book['title']); ?></div>
                    <div class="author">Author: <?= htmlspecialchars($book['author']); ?></div>
                    <form action="delete-book.php" method="post" class="mt-2">
                        <input type="hidden" name="delete_id" value="<?= $book['id']; ?>">
                        <button type="submit" class="btn-red w-full py-2 rounded-md shadow-sm">Delete</button>
                    </form>
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

                if (title.includes(searchTerm) || author.includes(searchTerm)) {
                    book.style.display = 'block';
                } else {
                    book.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
