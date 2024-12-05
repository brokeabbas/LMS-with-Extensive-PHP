<?php
session_start();
require_once '../../../connections/db_school_data.php'; // Connection to the school_data database

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["student_id"], $_SESSION["school_id"])) {
    header("location: login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$school_id = $_SESSION['school_id'];

function fetchGrades($student_id, $school_id, $conn) {
    $sql = "SELECT mt.module_code, g.grade, g.overall_score, g.assessment_type
            FROM grades g
            JOIN modules_taught mt ON g.module_id = mt.module_id
            WHERE g.student_id = ? AND g.school_id = ? AND mt.school_id = ?
            ORDER BY mt.module_code, g.assessment_type";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $student_id, $school_id, $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $grades = [];
    while ($row = $result->fetch_assoc()) {
        $grades[] = $row;
    }
    $stmt->close();
    return $grades;
}

$grades = fetchGrades($student_id, $school_id, $schoolDataConn);
$schoolDataConn->close();

// Assign weights
$weights = [
    'Examination' => 0.7,
    'Assignment' => 0.2,
    'Test' => 0.1
];

// Prepare to calculate weighted scores
$moduleDetails = [];
foreach ($grades as $grade) {
    $moduleCode = $grade['module_code'];
    if (!isset($moduleDetails[$moduleCode])) {
        $moduleDetails[$moduleCode] = [
            'scores' => [],
            'weightedScores' => 0,
            'count' => 0,
            'average' => 0
        ];
    }
    $moduleDetails[$moduleCode]['scores'][] = $grade['overall_score'];
}

// Calculate average scores for each module
foreach ($moduleDetails as $moduleCode => $details) {
    $averageScore = array_sum($details['scores']) / count($details['scores']);
    $moduleDetails[$moduleCode]['average'] = $averageScore;
}

// Calculate weighted scores considering average scores
foreach ($grades as $grade) {
    $moduleCode = $grade['module_code'];
    $score = $grade['overall_score'];
    $average = $moduleDetails[$moduleCode]['average'];
    $weight = $weights[$grade['assessment_type']] ?? 0;

    // Only add weight if the score is above or equal to average
    if ($score >= $average) {
        $moduleDetails[$moduleCode]['weightedScores'] += $score * $weight;
        $moduleDetails[$moduleCode]['count'] += $weight;
    }
}

// Normalize the scores to a total of 100
foreach ($moduleDetails as $moduleCode => $details) {
    if ($details['count'] > 0) { // Avoid division by zero
        $moduleDetails[$moduleCode]['finalScore'] = ($details['weightedScores'] / $details['count']) * (100 / 70); // Normalize to 100
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Performance Analysis</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.19.0/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold text-center text-gray-800 py-4">Module Performance Analysis</h1>
        <div style="max-width: 600px; margin: auto;">
            <canvas id="performanceChart"></canvas>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modules = <?= json_encode(array_keys($moduleDetails)); ?>;
            const finalScores = <?= json_encode(array_column($moduleDetails, 'finalScore')); ?>;

            const ctx = document.getElementById('performanceChart').getContext('2d');
            const performanceChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: modules,
                    datasets: [{
                        label: 'Final Weighted Scores',
                        data: finalScores,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            suggestedMax: 100
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>
