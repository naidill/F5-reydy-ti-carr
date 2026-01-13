<?php
session_start();
// ตรวจสอบการล็อกอินของลูกค้า
if (!isset($_SESSION['customer_loggedin']) || $_SESSION['customer_loggedin'] !== true) {
    header('Location: ../customer_login.php');
    exit;
}

require '../config.php'; 

$customer_id = $_SESSION['customer_id'];
$error = '';
$success = '';

if (isset($_GET['id'])) {
    $rental_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

    if ($rental_id === false) {
        $error = "รหัสสัญญาเช่าไม่ถูกต้อง";
    } else {
        // เริ่ม Transaction เพื่อให้มั่นใจว่าข้อมูลทั้งสองตารางถูกอัปเดตพร้อมกัน
        $pdo->beginTransaction();
        try {
            // 1. ตรวจสอบและดึง car_id และสถานะปัจจุบันของรายการเช่า
            $stmt_check = $pdo->prepare("SELECT car_id, status FROM rentals WHERE rental_id = ? AND customer_id = ?");
            $stmt_check->execute([$rental_id, $customer_id]);
            $rental = $stmt_check->fetch();

            if (!$rental) {
                $error = "ไม่พบรายการเช่า หรือรายการนี้ไม่ใช่ของคุณ";
            } elseif ($rental['status'] == 'Canceled' || $rental['status'] == 'Completed') {
                $error = "รายการเช่านี้ถูกยกเลิกไปแล้ว หรือเสร็จสิ้นแล้ว ไม่สามารถยกเลิกได้";
            } elseif ($rental['status'] == 'Active') {
                $error = "ไม่สามารถยกเลิกรายการเช่าที่อยู่ในสถานะ 'ใช้งานอยู่' ได้ ต้องให้ผู้ดูแลระบบดำเนินการ";
            } else {
                $car_id_to_update = $rental['car_id'];

                // 2. อัปเดตสถานะในตาราง rentals เป็น 'Canceled'
                $stmt_update_rental = $pdo->prepare("UPDATE rentals SET status = 'Canceled' WHERE rental_id = ?");
                $stmt_update_rental->execute([$rental_id]);

                // 3. อัปเดตสถานะรถยนต์ในตาราง cars กลับไปเป็น 'Available'
                $stmt_update_car = $pdo->prepare("UPDATE cars SET status = 'Available' WHERE car_id = ?");
                $stmt_update_car->execute([$car_id_to_update]);

                $pdo->commit();
                $success = "รายการเช่ารหัส **#{$rental_id}** ถูกยกเลิกและคืนรถเข้าสู่ระบบแล้ว";
            }

        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "เกิดข้อผิดพลาดในการยกเลิกรายการ: " . $e->getMessage();
        }
    }
} else {
    $error = "ไม่ระบุรหัสรายการเช่า";
}

// Redirect กลับไปที่หน้าประวัติการเช่าพร้อมข้อความแจ้งเตือน
if ($success) {
    // ใช้ URL encoding เพื่อส่งข้อความแจ้งเตือน
    header('Location: customer_history.php?status=success&message=' . urlencode($success));
} elseif ($error) {
    header('Location: customer_history.php?status=error&message=' . urlencode($error));
} else {
    // หากไม่มีการดำเนินการใดๆ ให้กลับหน้าเดิม
    header('Location: customer_history.php');
}
exit;
?>