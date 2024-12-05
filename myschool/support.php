<?php
session_start(); // Start or resume the session

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../registration/myschool_login.php");
    exit;
}

require_once '../connections/db.php'; // Include the database connection

// Define any necessary functions or variables
$school_id = $_SESSION['school_id'] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    // Implement your email sending logic here (e.g., using PHPMailer)
    // For the purpose of this example, we'll assume the message is sent successfully

    // Set a success message in session and redirect
    $_SESSION['message'] = ['type' => 'success', 'text' => 'Your message has been sent successfully!'];
    header("Location: " . htmlspecialchars($_SERVER["PHP_SELF"]));
    exit;
}

if (isset($_GET['message'])) {
    $message = $_GET['message'];
} elseif (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Clear the message from session
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help - School Management System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(135deg, #1f2937, #3b82f6);
            background-size: 400% 400%;
            animation: gradientAnimation 15s ease infinite;
            color: #f8fafc;
            font-family: 'Roboto', sans-serif;
        }
        @keyframes gradientAnimation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .card {
            background-color: #2d3748;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 1rem;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        .input-field {
            background-color: #1a202c;
            color: #a0aec0;
            border: 1px solid #4a5568;
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
        }
        .input-field:focus {
            border-color: #63b3ed;
            box-shadow: 0 0 0 1px #63b3ed;
        }
        .button-blue {
            background-color: #63b3ed;
            color: #fff;
            transition: background-color 0.2s ease-in-out, transform 0.2s ease-in-out;
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-block;
            margin-top: 10px;
        }
        .button-blue:hover {
            background-color: #3182ce;
            transform: translateY(-2px);
        }
        .header {
            background-color: #1a202c;
            color: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bg-gray-900">

    <header class="bg-blue-800 text-white p-4 header">
        <h1 class="text-xl font-bold">School Management System | Help</h1>
    </header>

    <main class="container mx-auto mt-6 p-4">
        <!-- Feature Explanation Section -->
        <section>
            <h2 class="text-lg font-semibold mb-4">Feature Overview</h2>
            <div class="space-y-4">
                <div class="card">
                    <h3 class="font-bold">Create Student:</h3>
                    <p>This feature allows administrators to add new students to the school database. You can input details such as the student's name, class, date of birth, parent/guardian contact information, and any other relevant data. This ensures that all necessary information is collected and stored securely.</p>
                </div>
                <div class="card">
                    <h3 class="font-bold">Create Teacher:</h3>
                    <p>This function enables the registration of new teachers within the system. Admins can set up teacher profiles by entering details such as name, contact information, subjects taught, and assign them to specific classes. This streamlines the onboarding process and ensures all necessary details are recorded.</p>
                </div>
                <div class="card">
                    <h3 class="font-bold">Manage Users:</h3>
                    <p>Through this feature, admins can view, update, or delete existing profiles for both students and teachers. It provides a centralized place to manage user information, track changes, and maintain up-to-date records for all school members.</p>
                </div>
                <div class="card">
                    <h3 class="font-bold">School Curriculum Management:</h3>
                    <p>Admins can create, update, or delete class timetables, assign teachers and students to classes, and manage the overall curriculum. This feature ensures efficient scheduling and resource allocation, making it easier to organize the academic year.</p>
                </div>
                <div class="card">
                    <h3 class="font-bold">Suggestion Box:</h3>
                    <p>Provides a platform for students and teachers to submit feedback or suggestions for school improvements. Admins can review and act on these suggestions, fostering a more inclusive and responsive school environment.</p>
                </div>
                <div class="card">
                    <h3 class="font-bold">Student Behavior Tracking:</h3>
                    <p>This tool allows for the monitoring and recording of student behavior incidents. Admins and teachers can document disciplinary actions, track patterns, and generate reports for parents and educators, helping to address behavioral issues proactively.</p>
                </div>
                <div class="card">
                    <h3 class="font-bold">Teacher Inspection:</h3>
                    <p>Admins can stay informed about the performance and activities of teachers. This feature helps in monitoring teaching quality, ensuring that educational standards are maintained, and providing support where needed.</p>
                </div>
                <div class="card">
                    <h3 class="font-bold">Student Complaints:</h3>
                    <p>This feature allows students to voice their concerns about school affairs. Admins can view and address these complaints, ensuring that student issues are heard and resolved appropriately.</p>
                </div>
                <div class="card">
                    <h3 class="font-bold">Student Inspection:</h3>
                    <p>Admins can oversee the academic performance and progress of students. This includes tracking grades, attendance, and participation, providing a comprehensive view of each student's development.</p>
                </div>
                <div class="card">
                    <h3 class="font-bold">School Calendar Planning:</h3>
                    <p>This tool helps in managing and publishing the school's calendar. Admins can plan activities, events, and academic schedules, ensuring that the entire school community is informed and organized.</p>
                </div>
                <div class="card">
                    <h3 class="font-bold">Student Achievements:</h3>
                    <p>Recognize and reward students who excel in various activities such as academics, sports, and extracurriculars. This feature helps in celebrating student successes and motivating others.</p>
                </div>
                <div class="card">
                    <h3 class="font-bold">E-Library:</h3>
                    <p>Provides digital access to a range of educational materials including textbooks, papers, and novels. This feature supports learning within and beyond the school premises, making resources readily available to students and teachers.</p>
                </div>
                <div class="card">
                    <h3 class="font-bold">School Website:</h3>
                    <p>If your school does not have a website, this feature provides an option to request one. Having a school website can enhance your school's online presence and provide important information to the community.</p>
                </div>
            </div>
        </section>

        <!-- Contact Us Form -->
        <section class="mt-8">
            <h2 class="text-lg font-semibold mb-4">Contact Us</h2>
            <?php if (isset($message)): ?>
                <div class="bg-<?php echo $message['type'] === 'success' ? 'green' : 'red'; ?>-100 border border-<?php echo $message['type'] === 'success' ? 'green' : 'red'; ?>-400 text-<?php echo $message['type'] === 'success' ? 'green' : 'red'; ?>-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($message['text']); ?></span>
                </div>
            <?php endif; ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="grid grid-cols-1 gap-4 mb-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-300">School Name:</label>
                        <input type="text" id="name" name="name" required class="input-field mt-1 block w-full px-3 py-2 shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-300">Email</label>
                        <input type="email" id="email" name="email" required class="input-field mt-1 block w-full px-3 py-2 shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div>
                        <label for="message" class="block text-sm font-medium text-gray-300">Message</label>
                        <textarea id="message" name="message" rows="4" required class="input-field mt-1 block w-full px-3 py-2 shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                    </div>
                </div>
                <button type="submit" class="button-blue">Send Message</button>
            </form>
        </section>
    </main>

</body>
</html>
