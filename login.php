<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // इनपुट्स को मान्य करें
    if (empty($email) || empty($password)) {
        $error = "ईमेल और पासवर्ड आवश्यक हैं।";
    } else {
        // यूजर को खोजें
        $stmt = $conn->prepare("SELECT id, name, password FROM students WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $email;
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "गलत पासवर्ड।";
            }
        } else {
            $error = "ईमेल नहीं मिला।";
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
    <title>लॉगिन - CTET मॉक टेस्ट</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">लॉगिन</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <?php if(isset($_SESSION['success'])) { echo "<p class='success'>{$_SESSION['success']}</p>"; unset($_SESSION['success']); } ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group mb-3">
                <label for="email">ईमेल:</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
            </div>
            <div class="form-group mb-3">
                <label for="password">पासवर्ड:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">लॉगिन करें</button>
        </form>
        <p class="text-center mt-3">अकाउंट नहीं है? <a href="register.php">रजिस्टर करें</a></p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>