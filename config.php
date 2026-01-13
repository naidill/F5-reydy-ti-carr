<?php
// config.php - ไฟล์เชื่อมต่อฐานข้อมูล (ปรับปรุง)

// --- 1. ตั้งค่าการเชื่อมต่อ ---
$host = 'localhost';
$db   = 'rent_a_car'; // **สำคัญ:** ชื่อฐานข้อมูลต้องตรงกับที่คุณ Import ใน phpMyAdmin
$user = 'root';      // **สำคัญ:** ชื่อผู้ใช้ MySQL ของคุณ (XAMPP/WAMPP มักเป็น 'root')
$pass = '';          // **สำคัญ:** รหัสผ่าน MySQL ของคุณ (XAMPP มักเป็น '')
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    // กำหนดให้ PDO โยน Exception เมื่อเกิดข้อผิดพลาดทาง SQL
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // กำหนดให้ดึงข้อมูลเป็นแบบ Associative array
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// --- 2. สร้างการเชื่อมต่อและจัดการข้อผิดพลาด ---
try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // ถ้าเชื่อมต่อไม่ได้ ให้แสดงข้อความแจ้งเตือนที่ชัดเจน
     // หากเป็น Production ควรเปลี่ยนข้อความนี้
     die("ERROR: ไม่สามารถเชื่อมต่อฐานข้อมูลได้! กรุณาตรวจสอบ config.php และ MySQL Server. <br> Error Message: " . $e->getMessage());
}
?>