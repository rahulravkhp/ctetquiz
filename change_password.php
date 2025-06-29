<?php
session_start();
require_once 'db_connect.php';

// सेशन चेक करें
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // इनपुट्स को मान्य करें
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "सभी फ़ील्ड आवश्यक हैं।";
    } elseif ($new_password !== $confirm_password) {
        $error = "नया पासवर्ड और कन्फर्म पासवर्ड मेल नहीं खाते।";
    } else {
        // वर्तमान पासवर्ड चेक करें
        $stmt = $conn->prepare("SELECT password FROM students WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (password_verify($current_password, $user['password'])) {
            // नया पासवर्ड हैश करें
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // पासवर्ड अपडेट करें
            $stmt = $conn->prepare("UPDATE students SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "पासवर्ड सफलतापूर्वक बदला गया!";
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "पासवर्ड बदलने में विफल। कृपया पुनः प्रयास करें।";
            }
        } else {
            $error = "वर्तमान पासवर्ड गलत है।";
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
    <title>पासवर्ड बदलें - CTET मॉक टेस्ट</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">पासवर्ड बदलें</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group mb-3">
                <label for="current_password">वर्तमान पासवर्ड:</label>
                <input type="password" class="form-control" id="current_password" name="current_password" required>
            </div>
            <div class="form-group mb-3">
                <label for="new_password">नया पासवर्ड:</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <div class="form-group mb-3">
                <label for="confirm_password">पासवर्ड कन्फर्म करें:</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">पासवर्ड बदलें</button>
        </form>
        <p class="text-center mt-3"><a href="dashboard.php">वापस डैशबोर्ड पर जाएं</a></p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>