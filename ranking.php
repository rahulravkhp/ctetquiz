<?php
session_start();
require_once 'db_connect.php';

// सेशन चेक करें
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// टॉप 10 यूजर्स की रैंकिंग प्राप्त करें
$stmt = $conn->prepare("
    SELECT s.name, AVG(r.score / r.total_questions) * 100 AS avg_score
    FROM results r
    JOIN students s ON r.student_id = s.id
    GROUP BY s.id
    ORDER BY avg_score DESC
    LIMIT 10");
$stmt->execute();
$result = $stmt->get_result();
$top_users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// यूजर का औसत स्कोर प्राप्त करें
$stmt = $conn->prepare("SELECT AVG(score / total_questions) * 100 AS user_avg_score FROM results WHERE student_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user_avg = $result->fetch_assoc()['user_avg_score'] ?? 0;
$stmt->close();

// यूजर के परफॉर्मेंस डेटा को ग्राफ के लिए प्राप्त करें
$stmt = $conn->prepare("SELECT score, total_questions, created_at FROM results WHERE student_id = ? ORDER BY created_at ASC LIMIT 10");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$performance = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Chart.js के लिए डेटा तैयार करें
$chart_labels = [];
$chart_data = [];
foreach ($performance as $record) {
    $chart_labels[] = date('d-m-Y', strtotime($record['created_at']));
    $chart_data[] = ($record['total_questions'] > 0) ? ($record['score'] / $record['total_questions']) * 100 : 0;
}
?>

<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>रैंकिंग और एनालिटिक्स - CTET मॉक टेस्ट</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">रैंकिंग और एनालिटिक्स</h2>
        <h3 class="mt-4">टॉप 10 यूजर्स </h3>
        <?php if (empty($top_users)) { ?>
            <p class="text-center">कोई रैंकिंग डेटा उपलब्ध नहीं है।</p>
        <?php } else { ?>
            <table class="ranking-table table table-bordered">
                <thead>
                    <tr>
                        <th>रैंक</th>
                        <th>नाम</th>
                        <th>औसत स्कोर (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($top_users as $index => $user) { ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo number_format($user['avg_score'], 2); ?>%</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
        <h3 class="mt-4">Your Performance</h3>
        <p><strong>आपका औसत स्कोर:</strong> <?php echo number_format($user_avg, 2); ?>%</p>
        <h3 class="mt-4">Performance ग्राफ</h3>
        <div class="chart-container">
            <canvas id="performanceChart"></canvas>
        </div>
        <p class="text-center mt-3"><a href="dashboard.php" class="btn btn-primary">वापस डैशबोर्ड पर जाएं</a></p>
    </div>
    <script>
        // Chart.js कॉन्फ़िगरेशन
        const ctx = document.getElementById('performanceChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chart_labels); ?>,
                datasets: [{
                    label: 'स्कोर (%)',
                    data: <?php echo json_encode($chart_data); ?>,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        title: {
                            display: true,
                            text: 'स्कोर (%)'
                        },
                        beginAtZero: true
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'तारीख'
                        }
                    }
                }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>