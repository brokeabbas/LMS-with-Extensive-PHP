<?php
session_start();
require_once '../../connections/db_school_data.php'; // Ensure this path is correct

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login_student.php");
    exit;
}

$module_code = $_GET['module_code'] ?? '';
if (!$module_code) {
    die("Module code not specified.");
}

// Fetch the module ID using the provided module code
$module_id = null;
$sql = "SELECT module_id FROM modules_taught WHERE module_code = ?";
if ($stmt = $schoolDataConn->prepare($sql)) {
    $stmt->bind_param("s", $module_code);
    $stmt->execute();
    $stmt->bind_result($module_id);
    $stmt->fetch();
    $stmt->close();
}

if (!$module_id) {
    echo "Module not found.";
    exit;
}

// Fetch all schemes for the given module
$schemes = [];
$sql = "SELECT week, topic, notes, assignments, materials, notes_files, assignments_files, materials_files FROM schemes WHERE module_id = ?";
if ($stmt = $schoolDataConn->prepare($sql)) {
    $stmt->bind_param("i", $module_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $schemes[] = $row;
    }
    $stmt->close();
}

$schoolDataConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Module Schemes - <?= htmlspecialchars($module_code); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script> <!-- FontAwesome for icons -->
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(to right, #6ee7b7, #3b82f6); /* Green to blue gradient */
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        .container {
            max-width: 800px;
            margin-left: 260px;
            padding: 20px;
        }
        .accordion-header {
            cursor: pointer;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            margin: 10px 0;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            transition: background-color 300ms, box-shadow 300ms;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .accordion-header:hover {
            background-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 25px rgba(0,0,0,0.5);
        }
        .accordion-header .icon {
            transition: transform 300ms;
        }
        .accordion-content {
            overflow: hidden;
            transition: max-height 500ms ease-in-out;
            max-height: 0;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            padding: 0 15px;
        }
        .accordion-content.open {
            max-height: 1000px; /* Ensure enough space for content */
            padding: 15px;
        }
        .accordion-content p {
            font-size: 16px; /* Slightly larger font size for readability */
            padding: 8px 0; /* More padding around text */
        }
        .sidebar {
            background: linear-gradient(145deg, rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.95));
            backdrop-filter: blur(10px);
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            border-right: 1px solid rgba(255, 255, 255, 0.2);
        }
        .sidebar a {
            color: #aad3ea;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            display: block;
            width: 100%;
            text-align: center;
            transition: background-color 300ms, transform 300ms;
        }
        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(10px);
        }
        .icon {
            margin-right: 5px;
            color: #4facfe; /* Matching the color theme for consistency */
        }
        .page-title {
            background: linear-gradient(90deg, #6ee7b7, #3b82f6); /* Gradient background */
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .page-title:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5);
        }
        .download-link {
            display: inline-block;
            background-color: #4facfe;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            margin: 5px;
            text-decoration: none; /* Remove underline */
            transition: background-color 0.3s, transform 0.3s;
        }
        .download-link:hover, .download-link:focus {
            background-color: #3b8dd9;
            transform: translateY(-2px); /* Slight lift on hover */
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2 class="text-xl font-bold mb-4">Navigation</h2>
        <a href="../mystudy.php"><i class="fas fa-home icon"></i> Home</a>
        <a href="class_schedule.php"><i class="fas fa-user-circle icon"></i> Classes</a>
        <a href="homework.php"><i class="fas fa-cog icon"></i> Homework</a>
        <a href="gradebook.php"><i class="fas fa-cog icon"></i> Grades</a>
    </div>
    <div class="container">
        <h1 class="text-2xl font-bold mb-4 page-title">Schemes for Module: <?= htmlspecialchars($module_code); ?></h1>
        <?php if (empty($schemes)): ?>
            <p>No schemes available for this module.</p>
        <?php else: ?>
            <?php foreach ($schemes as $scheme): ?>
                <div>
                    <div class="accordion-header" onclick="toggleAccordion(this)">
                        Week <?= htmlspecialchars($scheme['week']); ?>: <?= htmlspecialchars($scheme['topic']); ?>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="accordion-content">
                        <p><i class="fas fa-sticky-note icon"></i><strong>Notes:</strong> <?= nl2br(htmlspecialchars($scheme['notes'])); ?></p>
                        <p><i class="fas fa-tasks icon"></i><strong>Assignments:</strong> <?= nl2br(htmlspecialchars($scheme['assignments'])); ?></p>
                        <p><i class="fas fa-book icon"></i><strong>Materials:</strong> <?= nl2br(htmlspecialchars($scheme['materials'])); ?></p>
                        <?php foreach (['notes_files', 'assignments_files', 'materials_files'] as $fileType): ?>
                            <div><i class="fas fa-file-download icon"></i><strong><?= ucfirst(str_replace('_', ' ', $fileType)); ?>:</strong>
                                <?php
                                $files = explode(';', $scheme[$fileType]);
                                if (!empty($files[0])) {
                                    foreach ($files as $file) {
                                        echo '<a href="' . htmlspecialchars($file) . '" class="download-link" download>' . basename($file) . '</a>';
                                    }
                                } else {
                                    echo "No files uploaded.";
                                }
                                ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        function toggleAccordion(element) {
            const content = element.nextElementSibling;
            const icon = element.querySelector('.icon');
            if (content.style.maxHeight) {
                content.style.maxHeight = null;
                icon.style.transform = "rotate(0deg)";
            } else {
                content.style.maxHeight = content.scrollHeight + "px";
                icon.style.transform = "rotate(180deg)";
            }
            content.classList.toggle('open');
        }
    </script>
</body>
</html>
