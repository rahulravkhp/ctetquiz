<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // इनपुट्स को मान्य करें
    if (empty($name) || empty($email) || empty($password)) {
        $error = "सभी फ़ील्ड आवश्यक हैं।";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "अमान्य ईमेल प्रारूप।";
    } else {
        // चेक करें कि ईमेल पहले से मौजूद है या नहीं
        $stmt = $conn->prepare("SELECT id FROM students WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = "यह ईमेल पहले से रजिस्टर्ड है।";
        } else {
            // पासवर्ड हैश करें
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // यूजर को डेटाबेस में डालें
            $stmt = $conn->prepare("INSERT INTO students (name, email, password, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param("sss", $name, $email, $hashed_password);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "रजिस्ट्रेशन सफल! कृपया लॉगिन करें।";
                header("Location: login.php");
                exit();
            } else {
                $error = "रजिस्ट्रेशन विफल। कृपया पुनः प्रयास करें।";
            }
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
    <title>रजिस्ट्रेशन - CTET मॉक टेस्ट</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">रजिस्ट्रेशन</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group mb-3">
                <label for="name">नाम:</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" required>
            </div>
            <div class="form-group mb-3">
                <label for="email">ईमेल:</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
            </div>
            <div class="form-group mb-3">
                <label for="password">पासवर्ड:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">रजिस्टर करें</button>
        </form>
        <p class="text-center mt-3">पहले से अकाउंट है? <a href="login.php">लॉगिन करें</a></p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>