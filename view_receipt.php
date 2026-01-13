<?php
session_start();
date_default_timezone_set('Asia/Bangkok');
require 'config.php'; // แก้ไข path ตรงนี้

// ตรวจสอบสิทธิ์
if (!isset($_SESSION['customer_id']) && !isset($_SESSION['loggedin'])) {
    die("ไม่มีสิทธิ์เข้าถึง");
}
// ... โค้ดส่วนที่เหลือถูกต้องแล้ว ...

$rental_id = $_GET['id'] ?? null;

try {
    $stmt = $pdo->prepare("
        SELECT r.*, c.make, c.model, c.license_plate, c.daily_rate, cust.name as cust_name, cust.email
        FROM rentals r
        JOIN cars c ON r.car_id = c.car_id
        JOIN customers cust ON r.customer_id = cust.customer_id
        WHERE r.rental_id = ? AND r.status = 'Completed'
    ");
    $stmt->execute([$rental_id]);
    $data = $stmt->fetch();

    if (!$data) die("ไม่พบข้อมูลใบเสร็จ หรือการเช่ายังไม่เสร็จสมบูรณ์");

} catch (PDOException $e) { die($e->getMessage()); }
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ใบเสร็จรับเงิน #<?= $data['rental_id'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;700&display=swap');
        body { font-family: 'Sarabun', sans-serif; background-color: #f0f0f0; }
        .receipt-box { max-width: 800px; margin: 50px auto; background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .invoice-title { color: #0d6efd; font-weight: bold; }
        @media print {
            .no-print { display: none; }
            body { background: white; }
            .receipt-box { box-shadow: none; margin: 0; width: 100%; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="receipt-box">
        <div class="d-flex justify-content-between mb-4">
            <div>
                <h2 class="invoice-title">RECEIPT</h2>
                <p class="text-muted">ใบเสร็จรับเงิน / ใบกำกับภาษีอย่างย่อ</p>
            </div>
            <div class="text-end">
                <h5 class="fw-bold">Rent-A-Car Co., Ltd.</h5>
                <p class="small text-muted">123 ถนนสุขุมวิท, กรุงเทพฯ 10110<br>โทร: 02-123-4567</p>
            </div>
        </div>

        <hr>

        <div class="row mb-4">
            <div class="col-6">
                <p class="mb-1 text-muted text-uppercase small">ออกให้แก่:</p>
                <h6 class="fw-bold"><?= htmlspecialchars($data['cust_name']) ?></h6>
                <p class="small"><?= htmlspecialchars($data['email']) ?></p>
            </div>
            <div class="col-6 text-end">
                <p class="mb-1 text-muted text-uppercase small">เลขที่ใบเสร็จ:</p>
                <h6 class="fw-bold">#REC-<?= str_pad($data['rental_id'], 5, '0', STR_PAD_LEFT) ?></h6>
                <p class="small">วันที่: <?= date('d/m/Y H:i', strtotime($data['actual_return_date'])) ?></p>
            </div>
        </div>

        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>รายละเอียดรถยนต์</th>
                    <th class="text-center">ทะเบียน</th>
                    <th class="text-end">รวมเป็นเงิน</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong><?= $data['make'] ?> <?= $data['model'] ?></strong><br>
                        <small class="text-muted">ระยะเวลาเช่า: <?= date('d/m/Y', strtotime($data['rental_date'])) ?> ถึง <?= date('d/m/Y', strtotime($data['return_date'])) ?></small>
                    </td>
                    <td class="text-center align-middle"><?= $data['license_plate'] ?></td>
                    <td class="text-end align-middle">฿<?= number_format($data['total_cost'], 2) ?></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="text-end fw-bold">ยอดเงินสุทธิ (Net Total)</td>
                    <td class="text-end fw-bold text-primary fs-5">฿<?= number_format($data['total_cost'], 2) ?></td>
                </tr>
            </tfoot>
        </table>

        <div class="mt-5 text-center">
            <p class="small text-muted">ขอบคุณที่ใช้บริการเดินทางไปกับเรา</p>
            <div class="no-print mt-4">
                <button onclick="window.print()" class="btn btn-primary px-4 rounded-pill me-2">
                    <i class="bi bi-printer me-2"></i>พิมพ์ใบเสร็จ
                </button>
                <button onclick="window.close()" class="btn btn-outline-secondary px-4 rounded-pill">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

</body>
</html>