<?php
// डेटाबेस कनेक्शन कॉन्फ़िगरेशन
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ctet_mock_test');

// कनेक्शन बनाएं
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// कनेक्शन चेक करें
if ($conn->connect_error) {
    die("कनेक्शन विफल: " . $conn->connect_error);
}

// एन्कोडिंग समस्याओं से बचने के लिए charset सेट करें
$conn->set_charset("utf8mb4");
?>