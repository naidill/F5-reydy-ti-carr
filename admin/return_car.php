<?php
session_start();
date_default_timezone_set('Asia/Bangkok');
require '../config.php';

// ตรวจสอบสิทธิ์ Admin
if (!isset($_SESSION['loggedin'])) { header('Location: ../login.php'); exit; }

$rental_id = $_GET['id'] ?? null;
$message = '';

if (!$rental_id) { die("ไม่ระบุรหัสการเช่า"); }

try {
    // 1. ดึงข้อมูลการเช่าปัจจุบัน
    $stmt = $pdo->prepare("
        SELECT r.*, c.make, c.model, c.license_plate, c.daily_rate 
        FROM rentals r
        JOIN cars c ON r.car_id = c.car_id
        WHERE r.rental_id = ?
    ");
    $stmt->execute([$rental_id]);
    $rental = $stmt->fetch();

    if (!$rental) { die("ไม่พบข้อมูล"); }

    // 2. คำนวณค่าปรับ (กรณีคืนสาย)
    $scheduled_return = strtotime($rental['return_date']);
    $actual_return = time(); // เวลาปัจจุบันที่กดรับคืน
    $diff_seconds = $actual_return - $scheduled_return;
    
    $late_hours = 0;
    $penalty_fee = 0;
    $rate_per_hour = $rental['daily_rate'] / 24;

    if ($diff_seconds > 0) {
        $late_hours = ceil($diff_seconds / 3600); // ปัดเศษนาทีเป็น 1 ชม.
        $penalty_fee = $late_hours * $rate_per_hour * 1.5; // ปรับ 1.5 เท่าของราคาปกติ (หรือตั้งเป็นค่าคงที่ได้)
    }

    $final_total = $rental['total_cost'] + $penalty_fee;

    // 3. บันทึกข้อมูลเมื่อกดยืนยัน
    // ... โค้ดเดิมด้านบน ...
    if (isset($_POST['confirm_return'])) {
        $update = $pdo->prepare("
            UPDATE rentals 
            SET actual_return_date = NOW(), 
                total_cost = ?, 
                status = 'Completed' 
            WHERE rental_id = ?
        ");
        
        if ($update->execute([$final_total, $rental_id])) {
            
            // --- เพิ่มโค้ดบรรทัดนี้ลงไป ---
            $pdo->prepare("UPDATE cars SET status = 'Available' WHERE car_id = ?")
                ->execute([$rental['car_id']]);
            // --------------------------

            header('Location: admin_dashboard.php?status=returned');
            exit;
        }
    }
// ... โค้ดเดิมด้านล่าง ...

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รับคืนรถและสรุปค่าใช้จ่าย</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body { font-family: 'Sarabun', sans-serif; background-color: #f8f9fa; }
        .receipt-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card receipt-card">
                <div class="card-body p-4">
                    <h4 class="fw-bold text-center mb-4 text-success"><i class="bi bi-check-circle-fill me-2"></i>สรุปการคืนรถ</h4>
                    
                    <div class="mb-3 border-bottom pb-3">
                        <label class="small text-muted">ข้อมูลรถ:</label>
                        <div class="fw-bold"><?= $rental['make'].' '.$rental['model'] ?></div>
                        <div class="small"><?= $rental['license_plate'] ?></div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <label class="small text-muted">กำหนดคืน:</label>
                            <div class="small"><?= date('d/m/Y H:i', strtotime($rental['return_date'])) ?></div>
                        </div>
                        <div class="col-6 text-end">
                            <label class="small text-muted">คืนจริงเมื่อ:</label>
                            <div class="small fw-bold text-primary"><?= date('d/m/Y H:i', $actual_return) ?></div>
                        </div>
                    </div>

                    <div class="bg-light p-3 rounded-3 mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span>ค่าเช่าเดิม (รวมที่ต่อสัญญา):</span>
                            <span>฿<?= number_format($rental['total_cost'], 2) ?></span>
                        </div>
                        
                        <?php if ($penalty_fee > 0): ?>
                        <div class="d-flex justify-content-between text-danger mb-2">
                            <span>ค่าปรับคืนสาย (<?= $late_hours ?> ชม.):</span>
                            <span>+ ฿<?= number_format($penalty_fee, 2) ?></span>
                        </div>
                        <?php endif; ?>

                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5 text-dark">
                            <span>ยอดเงินที่ต้องเรียกเก็บ:</span>
                            <span>฿<?= number_format($final_total, 2) ?></span>
                        </div>
                    </div>

                    <form method="POST">
                        <div class="d-grid gap-2">
                            <button type="submit" name="confirm_return" class="btn btn-success btn-lg rounded-pill shadow">บันทึกการรับคืนและปิดยอด</button>
                            <a href="admin_dashboard.php" class="btn btn-link text-muted">ยกเลิก</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>