<?php
session_start();
require_once 'db_connect.php';

// सेशन चेक करें
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// यूजर की टेस्ट हिस्ट्री लोड करें
$stmt = $conn->prepare("SELECT id, score, total_questions, created_at, year FROM results WHERE student_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$test_history = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>टेस्ट हिस्ट्री</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center"><strong>आपकी टेस्ट हिस्ट्री</strong></h2>
        <?php if (count($test_history) > 0) { ?>
            <table class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th>क्रम संख्या</th>
                        <th>वर्ष</th>
                        <th>स्कोर</th>
                        <th>कुल प्रश्न</th>
                        <th>प्रतिशत</th>
                        <th>तारीख</th>
                        <th>विवरण</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($test_history as $index => $test) { ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($test['year']); ?></td>
                            <td><?php echo htmlspecialchars($test['score']); ?></td>
                            <td><?php echo htmlspecialchars($test['total_questions']); ?></td>
                            <td><?php echo number_format(($test['score'] / $test['total_questions']) * 100, 2); ?>%</td>
                            <td><?php echo htmlspecialchars(date("d-m-Y H:i", strtotime($test['created_at']))); ?></td>
                            <td>
                                <a href="generate_result_pdf.php?result_id=<?php echo $test['id']; ?>" class="btn btn-primary btn-sm">PDF डाउनलोड</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p class="text-center">आपने अभी तक कोई टेस्ट नहीं दिया है।</p>
        <?php } ?>
        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-primary">वापस डैशबोर्ड पर जाएं</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>