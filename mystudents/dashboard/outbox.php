<?php
session_start();
require_once '../../connections/db_school_data.php'; // Ensure this path is correct

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login_student.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$school_id = $_SESSION['school_id']; // Assuming school_id is stored in the session

// Fetch all messages sent by the logged-in student
$messages = [];
$sql = "SELECT sm.message_title, sm.message_body, t.name as teacher_name, ss.subject_name, sm.created_at
        FROM student_messages sm
        JOIN schoolhu_userinfo.teacher_info t ON sm.teacher_id = t.id
        JOIN modules_taught mt ON sm.module_id = mt.module_id
        JOIN class_subject cs ON mt.module_id = cs.module_id
        JOIN school_subjects ss ON cs.subject_id = ss.subject_id
        WHERE sm.student_id = ? AND sm.school_id = ?
        ORDER BY sm.created_at DESC";

if ($stmt = $schoolDataConn->prepare($sql)) {
    $stmt->bind_param("ii", $student_id, $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    $stmt->close();
} else {
    echo "SQL Error: " . $schoolDataConn->error;
}

$schoolDataConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Outbox</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(to right, #4e54c8, #8f94fb);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #fff;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }
        .container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            width: 100%;
            max-width: 800px;
        }
        .outbox-header {
            text-align: center;
            margin-bottom: 1rem;
        }
        .outbox-header h1 {
            font-size: 2rem;
            color: #fff;
        }
        .filter-section {
            margin-bottom: 1rem;
            text-align: center;
        }
        .filter-section label {
            color: #fff;
        }
        .filter-section input[type="date"] {
            color: #000; /* Ensures the date text is black */
            background: #fff;
        }
        .message-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .message-item {
            background: #fff;
            color: #333;
            border-radius: 10px;
            margin-bottom: 1rem;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }
        .message-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        .message-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .message-body {
            margin-bottom: 1rem;
        }
        .message-details {
            font-size: 0.875rem;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="outbox-header">
            <h1>Outbox</h1>
        </div>
        <div class="filter-section">
            <label for="filter-date" class="block text-sm font-medium">Filter by date:</label>
            <input type="date" id="filter-date" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md">
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <ul id="message-list" class="message-list">
                <!-- Messages will be populated here by JavaScript -->
            </ul>
            <p id="no-messages" class="text-center text-white" style="display: none;">No messages have been sent.</p>
        </div>
    </div>
    <script>
        const messages = <?= json_encode($messages); ?>;
        const messageList = document.getElementById('message-list');
        const noMessages = document.getElementById('no-messages');
        const filterDate = document.getElementById('filter-date');

        function displayMessages(filteredMessages) {
            messageList.innerHTML = '';
            if (filteredMessages.length > 0) {
                filteredMessages.forEach(message => {
                    const messageItem = document.createElement('li');
                    messageItem.classList.add('message-item');
                    messageItem.innerHTML = `
                        <h3 class="message-title">${message.message_title}</h3>
                        <p class="message-body">${message.message_body.replace(/\n/g, '<br>')}</p>
                        <p class="message-details">
                            <strong>To:</strong> ${message.teacher_name} (Subject: ${message.subject_name})
                        </p>
                        <p class="message-details">
                            <strong>Sent on:</strong> ${new Date(message.created_at).toLocaleString()}
                        </p>
                    `;
                    messageList.appendChild(messageItem);
                });
                noMessages.style.display = 'none';
            } else {
                noMessages.style.display = 'block';
            }
        }

        function filterMessages() {
            const selectedDate = new Date(filterDate.value);
            const filteredMessages = messages.filter(message => {
                const messageDate = new Date(message.created_at);
                return messageDate.toDateString() === selectedDate.toDateString();
            });
            displayMessages(filteredMessages);
        }

        filterDate.addEventListener('input', filterMessages);
        displayMessages(messages);
    </script>
</body>
</html>

