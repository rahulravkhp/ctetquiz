<?php
session_start();
require_once 'db_connect.php';

// सेशन चेक करें
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// यूजर डेटा प्राप्त करें
$stmt = $conn->prepare("SELECT name, email FROM students WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>डैशबोर्ड - CTET मॉक टेस्ट</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center"><strong>  (-_-)  स्वागत है  -  <?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></h2>
        <div class="profile-info card p-3 mb-4">
            <h3><strong>प्रोफाइल जानकारी</strong></h3>
            <H4><strong>नाम:</strong> <?php echo htmlspecialchars($user['name']); ?></H4>
            <H4><strong>ईमेल:</strong> <?php echo htmlspecialchars($user['email']); ?></H4>
        </div>
        <div class="profile-actions d-flex flex-wrap gap-2 justify-content-center">
            <a href="update_profile.php" class="btn btn-primary">प्रोफाइल अपडेट करें</a>
            <a href="change_password.php" class="btn btn-primary">पासवर्ड बदलें</a>
            <a href="previous_papers.php" class="btn btn-primary">पिछले प्रश्न पत्र</a>
            <a href="history.php" class="btn btn-primary">टेस्ट हिस्ट्री देखें</a>
            <a href="feedback.php" class="btn btn-primary">फीडबैक दें</a>
            <a href="ranking.php" class="btn btn-primary">रैंकिंग और एनालिटिक्स</a>
            <a href="generate_result_pdf.php" class="btn btn-primary">रिजल्ट PDF डाउनलोड</a>
            <a href="logout.php" class="btn btn-danger">लॉगआउट</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>