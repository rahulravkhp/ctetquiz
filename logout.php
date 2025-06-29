<?php
session_start();

// सेशन को नष्ट करें
session_unset();
session_destroy();

// लॉगिन पेज पर रीडायरेक्ट करें
header("Location: login.php");
exit();
?>