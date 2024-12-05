<?php
session_start();
require_once '../../connections/db_school_data.php'; // Database connection

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_teacher.php");
    exit;
}

$module_code = $_GET['module'] ?? '';
if (empty($module_code)) {
    die("Module code not specified.");
}

$teacher_id = $_SESSION['teacher_id'];
$school_id = $_SESSION['school_id'];

// Fetch the module ID
$module_id = null;
$stmt = $schoolDataConn->prepare("SELECT module_id FROM modules_taught WHERE module_code = ? AND teacher_id = ? AND school_id = ?");
$stmt->bind_param("sii", $module_code, $teacher_id, $school_id);
$stmt->execute();
$stmt->bind_result($module_id);
$stmt->fetch();
$stmt->close();

if (!$module_id) {
    die("No module found or you do not have access to this module.");
}

// Handling deletion of a scheme
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_scheme_id'])) {
    $delete_scheme_id = $_POST['delete_scheme_id'];
    $delete_stmt = $schoolDataConn->prepare("DELETE FROM schemes WHERE scheme_id = ?");
    $delete_stmt->bind_param("i", $delete_scheme_id);
    $delete_stmt->execute();
    $delete_stmt->close();
    header("Location: " . $_SERVER['REQUEST_URI']); // Refresh the page
    exit;
}

// Fetch all schemes for the given module
$schemes = [];
$query = $schoolDataConn->prepare("SELECT scheme_id, week, topic, notes, assignments, materials, notes_files, assignments_files, materials_files FROM schemes WHERE module_id = ?");
$query->bind_param("i", $module_id);
$query->execute();
$result = $query->get_result();
while ($row = $result->fetch_assoc()) {
    $schemes[] = $row;
}
$query->close();
$schoolDataConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Schemes - <?= htmlspecialchars($module_code); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <script>
        function toggleDetails(index) {
            var content = document.getElementById('details-' + index);
            var icon = document.getElementById('toggle-icon-' + index);
            content.classList.toggle('hidden');
            icon.classList.toggle('fa-chevron-up');
            icon.classList.toggle('fa-chevron-down');
        }
    </script>
</head>
<body class="bg-gray-100">
    <div class="w-64 h-full bg-blue-800 fixed">
        <div class="p-5 bg-blue-900 text-white text-xl font-semibold">Menu</div>
        <ul class="list-none p-5">
            <li><a href="../myteach.php" class="block p-3 hover:bg-blue-700 rounded text-white"><i class="fas fa-tachometer-alt mr-2"></i> Dashboard</a></li>
            <li><a href="courses_management.php" class="block p-3 hover:bg-blue-700 rounded text-white"><i class="fas fa-layer-group mr-2"></i> My Modules</a></li>
            <li><a href="delete_scheme.php" class="block p-3 hover:bg-blue-700 rounded text-white"><i class="fas fa-cog mr-2"></i>Delete Schemes</a></li>
            <li><a href="#" class="block p-3 hover:bg-blue-700 rounded text-white"><i class="fas fa-sign-out-alt mr-2"></i> Log Out</a></li>
        </ul>
    </div>
    <div class="pl-64 flex-auto min-h-screen">
        <div class="container mx-auto px-4 py-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-8"><i class="fas fa-chart-line mr-3"></i>Schemes for <?= htmlspecialchars($module_code); ?></h1>
            <?php if (empty($schemes)): ?>
                <p class="text-red-600 font-semibold">No schemes available for this module.</p>
            <?php else: ?>
                <?php foreach ($schemes as $index => $scheme): ?>
                    <div class="mb-5 bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-5 bg-gradient-to-r from-blue-500 to-blue-300 text-white flex justify-between items-center cursor-pointer" onclick="toggleDetails(<?= $index ?>)">
                            <h2 class="font-semibold text-lg"><i class="fas fa-calendar-week mr-2"></i>Week: <?= htmlspecialchars($scheme['week']); ?> - <?= htmlspecialchars($scheme['topic']); ?></h2>
                            <i class="fas fa-chevron-down rotate" id="toggle-icon-<?= $index ?>"></i>
                        </div>
                        <div id="details-<?= $index ?>" class="hidden p-6 bg-white">
                            <div>
                                <h3 class="text-lg font-semibold"><i class="fas fa-book-open text-blue-500 mr-2"></i>Notes</h3>
                                <p><?= nl2br(htmlspecialchars($scheme['notes'])); ?></p>
                                <?php foreach (explode(';', $scheme['notes_files']) as $file): ?>
                                    <a href="<?= htmlspecialchars($file); ?>" download class="inline-block bg-blue-500 hover:bg-blue-600 text-white rounded px-3 py-1"><?= basename($file); ?></a>
                                <?php endforeach; ?>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold"><i class="fas fa-tasks text-green-500 mr-2"></i>Assignments</h3>
                                <p><?= nl2br(htmlspecialchars($scheme['assignments'])); ?></p>
                                <?php foreach (explode(';', $scheme['assignments_files']) as $file): ?>
                                    <a href="<?= htmlspecialchars($file); ?>" download class="inline-block bg-green-500 hover:bg-green-600 text-white rounded px-3 py-1"><?= basename($file); ?></a>
                                <?php endforeach; ?>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold"><i class="fas fa-cubes text-purple-500 mr-2"></i>Materials</h3>
                                <p><?= nl2br(htmlspecialchars($scheme['materials'])); ?></p>
                                <?php foreach (explode(';', $scheme['materials_files']) as $file): ?>
                                    <a href="<?= htmlspecialchars($file); ?>" download class="inline-block bg-purple-500 hover:bg-purple-600 text-white rounded px-3 py-1"><?= basename($file); ?></a>
                                <?php endforeach; ?>
                            </div>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this scheme?');">
                                <input type="hidden" name="delete_scheme_id" value="<?= $scheme['scheme_id']; ?>">
                                <button type="submit" class="mt-4 py-2 px-4 bg-red-600 hover:bg-red-800 text-white font-bold rounded">Delete Scheme</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
