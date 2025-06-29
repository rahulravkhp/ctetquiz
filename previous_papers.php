<?php
session_start();
require_once 'db_connect.php';

// सेशन चेक करें
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// उपलब्ध वर्षों की सूची (2018 से 2024)
$years = range(2018, 2024);
?>

<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>पिछले प्रश्न पत्र - CTET मॉक टेस्ट</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">पिछले प्रश्न पत्र</h2>
        <p class="text-center">नीचे दिए गए वर्षों में से किसी एक को चुनें:</p>
        <table class="papers-table table table-bordered">
            <thead>
                <tr>
                    <th>वर्ष</th>
                    <th>क्रिया</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($years as $year) { ?>
                    <tr>
                        <td><?php echo $year; ?></td>
                        <td><a href="caution.php?year=<?php echo $year; ?>" class="btn btn-primary btn-sm">टेस्ट शुरू करें</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <p class="text-center mt-3"><a href="dashboard.php" class="btn btn-primary">वापस डैशबोर्ड पर जाएं</a></p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>