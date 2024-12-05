<?php
session_start();
require_once '../../connections/db_school_data.php'; // Adjust this path as needed

// Redirect if not logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["school_id"])) {
    header("location: ../login_student.php");
    exit;
}

$school_id = $_SESSION['school_id'];
$eventsByWeek = [];

// Fetch school calendar events from the database
$sql = "SELECT event_title, event_date, event_description, event_week
        FROM school_calendar
        WHERE school_id = ? AND is_published = 1
        ORDER BY event_week ASC, event_date ASC";

if ($stmt = $schoolDataConn->prepare($sql)) {
    $stmt->bind_param("i", $school_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $eventsByWeek[$row['event_week']][] = $row;
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Calendar</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script> <!-- FontAwesome for icons -->
    <link rel="icon" href="../../IMAGES/3.png" type="image/x-icon">
    <style>
        body {
            background: linear-gradient(to right, #6ee7b7, #3b82f6); /* Green to blue gradient */
            color: #fff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .table-view, .calendar-view {
            display: none;
        }
        .active {
            display: block;
        }
        .fc-event {
            cursor: pointer;
            background-color: #4facfe !important;
            border: none !important;
            color: #ffffff !important;
            padding: 5px !important;
        }
        .fc-title {
            color: #ffffff;
        }
        .fc .fc-toolbar {
            background-color: #4b6cb7;
            color: #ffffff;
        }
        .fc .fc-toolbar h2 {
            font-size: 1.25rem;
            margin: 0;
        }
        .fc button {
            background-color: #4b6cb7;
            color: #ffffff;
            border: none;
            padding: 0.5rem 1rem;
            margin: 0.25rem;
            border-radius: 0.25rem;
            transition: background-color 0.3s ease-in-out;
        }
        .fc button:hover {
            background-color: #182848;
        }
        .fc td, .fc th {
            border-color: #dddddd;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .page-title {
            background: linear-gradient(90deg, #4b6cb7, #182848); /* Gradient background */
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
        .btn {
            background-color: #4b6cb7;
            color: #ffffff;
            border: none;
            padding: 0.5rem 1rem;
            margin: 0.25rem;
            border-radius: 0.25rem;
            transition: background-color 0.3s ease-in-out;
        }
        .btn:hover {
            background-color: #182848;
        }
        .search-input {
            padding: 0.5rem;
            border-radius: 0.25rem;
            border: 1px solid #ddd;
            width: 50%;
            margin: 0 auto;
            display: block;
        }
        .week-container {
            background: #ffffff;
            color: #333333;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .week-container h2 {
            color: #4b6cb7;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
        }
        th {
            background-color: #4b6cb7;
            color: #ffffff;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="container mx-auto px-4 py-6">
        <h1 class="text-3xl font-bold text-center mb-6 page-title">School Calendar</h1>

        <div class="flex justify-center mb-6">
            <button id="tableViewBtn" class="btn">Table View</button>
            <button id="calendarViewBtn" class="btn">Calendar View</button>
        </div>

        <div class="flex justify-center mb-6">
            <input type="text" id="searchInput" placeholder="Search by week" class="search-input">
        </div>

        <div id="tableView" class="table-view active">
            <?php if (!empty($eventsByWeek)): ?>
                <?php foreach ($eventsByWeek as $week => $events): ?>
                    <div class="mb-8 week-container" data-week="<?= htmlspecialchars($week) ?>">
                        <h2 class="text-2xl font-semibold text-center mb-4">Week <?= htmlspecialchars($week) ?></h2>
                        <div class="overflow-x-auto">
                            <table class="min-w-full shadow-md rounded-lg overflow-hidden">
                                <thead>
                                    <tr>
                                        <th class="py-3 px-6 text-left">Date</th>
                                        <th class="py-3 px-6 text-left">Event Title</th>
                                        <th class="py-3 px-6 text-left">Description</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($events as $event): ?>
                                        <tr class="hover:bg-gray-100">
                                            <td class="py-3 px-6 text-left whitespace-nowrap"><?= htmlspecialchars($event['event_date']); ?></td>
                                            <td class="py-3 px-6 text-left"><?= htmlspecialchars($event['event_title']); ?></td>
                                            <td class="py-3 px-6 text-left"><?= nl2br(htmlspecialchars($event['event_description'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">No events to display.</p>
            <?php endif; ?>
        </div>

        <div id="calendarView" class="calendar-view">
            <div id="calendar"></div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
    <script>
        document.getElementById('tableViewBtn').addEventListener('click', function () {
            document.getElementById('tableView').classList.add('active');
            document.getElementById('calendarView').classList.remove('active');
        });

        document.getElementById('calendarViewBtn').addEventListener('click', function () {
            document.getElementById('tableView').classList.remove('active');
            document.getElementById('calendarView').classList.add('active');
            $('#calendar').fullCalendar('render'); // To ensure the calendar renders correctly
        });

        document.getElementById('searchInput').addEventListener('keyup', function () {
            var input = document.getElementById('searchInput').value.toLowerCase();
            var weeks = document.getElementsByClassName('week-container');
            for (var i = 0; i < weeks.length; i++) {
                var week = weeks[i].getAttribute('data-week');
                if (week.toLowerCase().includes(input)) {
                    weeks[i].style.display = '';
                } else {
                    weeks[i].style.display = 'none';
                }
            }
        });

        $(document).ready(function() {
            $('#calendar').fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                events: [
                    <?php foreach ($eventsByWeek as $week => $events): ?>
                        <?php foreach ($events as $event): ?>
                            {
                                title: '<?= htmlspecialchars($event['event_title']) ?>',
                                start: '<?= htmlspecialchars($event['event_date']) ?>',
                                description: '<?= htmlspecialchars($event['event_description']) ?>'
                            },
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                ],
                eventRender: function(event, element) {
                    element.qtip({
                        content: event.description,
                        position: {
                            my: 'bottom center',
                            at: 'top center',
                            target: element
                        },
                        style: {
                            classes: 'qtip-bootstrap'
                        }
                    });
                }
            });
        });
    </script>
</body>
</html>
