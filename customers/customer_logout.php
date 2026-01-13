<?php
session_start();
session_unset();
session_destroy();

// กลับไปหน้า Login (เนื่องจากไฟล์นี้อยู่ระดับเดียวกับ Login)
header('Location: customer_login.php'); 
exit;
?>