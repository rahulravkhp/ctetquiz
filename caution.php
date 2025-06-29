<?php
session_start();
require_once 'db_connect.php';

// सेशन चेक करें
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// वर्ष की जांच करें
$year = isset($_GET['year']) ? (int)$_GET['year'] : 0;
if (!in_array($year, range(2018, 2024))) {
    header("Location: previous_papers.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>सावधानी - CTET मॉक टेस्ट <?php echo $year; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">सावधानी</h2>
        <div class="caution-container">
            <p><strong>निर्देश:</strong></p>
            <ul>
                <li>यह CTET <?php echo $year; ?> का मॉक टेस्ट है।</li>
                <li>टेस्ट की अवधि 2 घंटे 30 मिनट है।</li>
                <li>समय समाप्त होने पर टेस्ट स्वचालित रूप से सबमिट हो जाएगा।</li>
                <li>कृपया टेस्ट शुरू करने से पहले सुनिश्चित करें कि आपके पास स्थिर इंटरनेट कनेक्शन है।</li>
                <li>टेस्ट के दौरान पेज को रिफ्रेश न करें, अन्यथा आपका प्रोग्रेस खो सकता है।</li>
            </ul>
        </div>
        <div class="text-center">
            <a href="test_year.php?year=<?php echo $year; ?>" class="btn btn-success">टेस्ट शुरू करें</a>
            <a href="previous_papers.php" class="btn btn-secondary">वापस</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>