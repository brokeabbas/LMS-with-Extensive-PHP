<?php
session_start();
require_once '../connections/db.php';
require_once '../connections/db_school_data.php';

if (!isset($_GET['module_id'])) {
    echo json_encode(['error' => 'Module ID is required']);
    exit;
}

$module_id = $_GET['module_id'];
$teacher_id = $_SESSION['teacher_id'];
$school_id = $_SESSION['school_id'];

// Fetch Attendance Data
function fetch_attendance_trends($teacher_id, $school_id, $module_id) {
    global $schoolDataConn;
    $dates = [];
    $presentCounts = [];
    $totalCounts = [];

    $sql = "SELECT mt.module_code, ar.attendance_date, COUNT(ar.attendance_id) AS total,
                   SUM(CASE WHEN ar.attendance_status = 'present' THEN 1 ELSE 0 END) AS present
            FROM attendance_records ar
            JOIN modules_taught mt ON ar.module_id = mt.module_id
            WHERE teacher_id = ? AND ar.school_id = ? AND mt.module_id = ?
            GROUP BY ar.attendance_date, mt.module_code
            ORDER BY ar.attendance_date DESC
            LIMIT 5";

    if ($stmt = $schoolDataConn->prepare($sql)) {
        $stmt->bind_param("iii", $teacher_id, $school_id, $module_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $dates[] = $row['attendance_date'];
            $presentCounts[] = $row['present'];
            $totalCounts[] = $row['total'];
        }
        $stmt->close();
    }
    return [$dates, $presentCounts, $totalCounts];
}

// Fetch Grade Analysis Data
function fetch_grade_analysis($teacher_id, $school_id, $module_id) {
    global $schoolDataConn;
    $grades = [];

    $sql = "SELECT si.fullname, AVG(g.grade) AS average_grade
            FROM userinfo.student_info si
            JOIN school_data.grades g ON si.id = g.student_id
            WHERE g.module_id = ? AND g.school_id = ?
            GROUP BY si.id
            ORDER BY average_grade DESC
            LIMIT 5";

    if ($stmt = $schoolDataConn->prepare($sql)) {
        $stmt->bind_param("ii", $module_id, $school_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $grades[] = $row;
        }

        $stmt->close();
    }
    return $grades;
}

// Fetch Attendance Summary
function fetch_attendance_analytics($module_id) {
    global $schoolDataConn;
    $teacher_id = $_SESSION["teacher_id"];
    $school_id = $_SESSION["school_id"];

    $recent_sql = "SELECT mt.module_code, COUNT(ar.attendance_id) AS total, SUM(CASE WHEN ar.attendance_status = 'present' THEN 1 ELSE 0 END) AS present
    FROM attendance_records ar
    JOIN modules_taught mt ON ar.module_id = mt.module_id
    WHERE teacher_id = ? AND ar.school_id = ? AND mt.module_id = ?
    GROUP BY mt.module_code
    ORDER BY ar.attendance_date DESC
    LIMIT 1";
    
    $summary = "No recent sessions recorded.";
    if ($stmt = $schoolDataConn->prepare($recent_sql)) {
        $stmt->bind_param("iii", $teacher_id, $school_id, $module_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $recent_attendance = $result->fetch_assoc();
        $stmt->close();

        if ($recent_attendance) {
            $summary = "Latest session in " . $recent_attendance['module_code'] . ": " . $recent_attendance['present'] . " present out of " . $recent_attendance['total'];
        } else {
            $summary = "No recent sessions recorded.";
        }
    } else {
        $summary = "Error preparing statement to fetch attendance data.";
    }

    return $summary;
}

list($attendance_dates, $presentCounts, $totalCounts) = fetch_attendance_trends($teacher_id, $school_id, $module_id);
$grades = fetch_grade_analysis($teacher_id, $school_id, $module_id);
$recent_attendance = fetch_attendance_analytics($module_id);

echo json_encode([
    'dates' => $attendance_dates,
    'presentCounts' => $presentCounts,
    'totalCounts' => $totalCounts,
    'recentAttendance' => $recent_attendance,
    'students' => array_column($grades, 'fullname'),
    'averageGrades' => array_column($grades, 'average_grade')
]);
?>
<script>
    function updateAttendanceData() {
    const moduleId = document.getElementById('attendanceModuleSelect').value;

    fetch(`/path/to/your/api/get_chart_data.php?module_id=${moduleId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error:', data.error);
                return;
            }
            attendanceChart.data.labels = data.dates;
            attendanceChart.data.datasets[0].data = data.presentCounts;
            attendanceChart.data.datasets[1].data = data.totalCounts;
            attendanceChart.update();

            document.getElementById('attendanceSummary').innerText = data.recentAttendance;
        })
        .catch(error => console.error('Error fetching attendance data:', error));
}

function updateGradeChart() {
    const moduleId = document.getElementById('moduleSelect').value;

    fetch(`/path/to/your/api/get_chart_data.php?module_id=${moduleId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Error:', data.error);
                return;
            }
            gradeChart.data.labels = data.students;
            gradeChart.data.datasets[0].data = data.averageGrades;
            gradeChart.update();
        })
        .catch(error => console.error('Error fetching grade data:', error));
}

</script>