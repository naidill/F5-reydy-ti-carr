<?php
session_start();
if (!isset($_SESSION['loggedin'])) { header('Location:login.php'); exit; }

require '../config.php'; 
$customer_id = $_GET['id'] ?? null;

if (!$customer_id) {
    die("ไม่พบรหัสลูกค้าที่ต้องการลบ");
}

// 1. คำสั่ง SQL: DELETE FROM customers
try {
    $stmt = $pdo->prepare("DELETE FROM customers WHERE customer_id = ?");
    $stmt->execute([$customer_id]);

    // 2. ส่งกลับไปยังหน้าแสดงรายการ
    header('Location:customers.php?status=deleted');
    exit;

} catch (PDOException $e) {
    // ในกรณีที่มี Foreign Key constraint (เช่น ลูกค้ามีประวัติการเช่ารถ)
    die("เกิดข้อผิดพลาดในการลบ: อาจมีข้อมูลอ้างอิงถึงลูกค้าท่านนี้ในตารางอื่น (เช่น Rentals) " . $e->getMessage());
}
?>