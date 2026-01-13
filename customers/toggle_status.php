<?php
session_start();
if (!isset($_SESSION['loggedin'])) { exit; }
require '../config.php';

$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? '';

if ($id) {
    // กำหนดสถานะใหม่: ถ้าจะแบนส่ง 0, ถ้าจะปลดส่ง 1
    $status = ($action === 'activate') ? 1 : 0;
    
    $stmt = $pdo->prepare("UPDATE customers SET is_active = ? WHERE customer_id = ?");
    $stmt->execute([$status, $id]);
}

// เมื่อเสร็จแล้วเด้งกลับหน้าเดิม
header('Location: customers.php');
exit;