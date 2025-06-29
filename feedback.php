<?php
session_start();
require_once 'db_connect.php';

// सेशन चेक करें
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $feedback_text = trim($_POST['feedback']);
    
    // इनपुट को मान्य करें
    if (empty($feedback_text)) {
        $error = "फीडबैक फ़ील्ड आवश्यक है।";
    } else {
        // फीडबैक को डेटाबेस में स्टोर करें
        $stmt = $conn->prepare("INSERT INTO feedback (student_id, feedback_text, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $_SESSION['user_id'], $feedback_text);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "फीडबैक सफलतापूर्वक सबमिट किया गया!";
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "फीडबैक सबमिट करने में विफल। कृपया पुनः प्रयास करें।";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="hi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>फीडबैक - CTET मॉक टेस्ट</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">फीडबैक दें</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group mb-3">
                <label for="feedback">आपका फीडबैक:</label>
                <textarea class="form-control" id="feedback" name="feedback" rows="5" required><?php echo isset($feedback_text) ? htmlspecialchars($feedback_text) : ''; ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">फीडबैक सबमिट करें</button>
        </form>
        <p class="text-center mt-3"><a href="dashboard.php">वापस डैशबोर्ड पर जाएं</a></p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>