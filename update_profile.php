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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    
    // इनपुट्स को मान्य करें
    if (empty($name) || empty($email)) {
        $error = "सभी फ़ील्ड आवश्यक हैं।";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "अमान्य ईमेल प्रारूप।";
    } else {
        // चेक करें कि नया ईमेल पहले से किसी और के द्वारा उपयोग में तो नहीं है
        $stmt = $conn->prepare("SELECT id FROM students WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $_SESSION['user_id']);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = "यह ईमेल पहले से उपयोग में है।";
        } else {
            // प्रोफाइल अपडेट करें
            $stmt = $conn->prepare("UPDATE students SET name = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $email, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['success'] = "प्रोफाइल सफलतापूर्वक अपडेट की गई!";
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "प्रोफाइल अपडेट विफल। कृपया पुनः प्रयास करें।";
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
    <title>प्रोफाइल अपडेट करें - CTET मॉक टेस्ट</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">प्रोफाइल अपडेट करें</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <div class="form-group mb-3">
                <label for="name">नाम:</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="form-group mb-3">
                <label for="email">ईमेल:</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">अपडेट करें</button>
        </form>
        <p class="text-center mt-3"><a href="dashboard.php">वापस डैशबोर्ड पर जाएं</a></p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>