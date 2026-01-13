<?php
session_start();
if (!isset($_SESSION['customer_loggedin']) || $_SESSION['customer_loggedin'] !== true) {
    header('Location: customer_login.php');
    exit;
}
require '../config.php'; 

$customer_id = $_SESSION['customer_id'];
$rental_history = [];

try {
    $sql = "SELECT r.*, c.make, c.model, c.license_plate 
            FROM rentals r 
            JOIN cars c ON r.car_id = c.car_id 
            WHERE r.customer_id = ? 
            ORDER BY r.rental_date DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$customer_id]);
    $rental_history = $stmt->fetchAll();
} catch (PDOException $e) { $error = $e->getMessage(); }

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการเช่ารถ | Rent-A-Car</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body { font-family: 'Sarabun', sans-serif; background-color: #f8f9fa; }
        .history-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .table thead { background-color: #f8f9fa; }
        .status-badge { font-weight: 600; padding: 5px 12px; border-radius: 50px; font-size: 0.85rem; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="customer_dashboard.php">RENT-A-CAR</a>
        <a href="customer_dashboard.php" class="btn btn-light btn-sm rounded-pill px-3"><i class="bi bi-arrow-left"></i> กลับไปเลือกเช่ารถ</a>
    </div>
</nav>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            <h3 class="fw-bold mb-4"><i class="bi bi-clock-history me-2"></i>ประวัติการเช่ารถของคุณ</h3>
            
            <div class="card history-card overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">รหัสการเช่า</th>
                                <th>ข้อมูลรถ</th>
                                <th>วันที่เริ่มเช่า</th>
                                <th>กำหนดคืน</th>
                                <th>ยอดรวม</th>
                                <th>สถานะ</th>
                                <th class="text-center">การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rental_history as $row): ?>
                            <tr>
                                <td class="ps-4 fw-bold">#<?php echo $row['rental_id']; ?></td>
                                <td>
                                    <div class="fw-bold"><?php echo $row['make'] . ' ' . $row['model']; ?></div>
                                    <small class="text-muted"><?php echo $row['license_plate']; ?></small>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($row['rental_date'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row['return_date'])); ?></td>
                                <td class="fw-bold text-primary">฿<?php echo number_format($row['total_cost']); ?></td>
                                <td>
                                    <?php 
                                        $status_class = 'bg-secondary';
                                        $status_text = $row['status'];
                                        if($row['status'] == 'Active') { $status_class = 'bg-primary'; $status_text = 'กำลังเช่า'; }
                                        if($row['status'] == 'Completed') { $status_class = 'bg-success'; $status_text = 'คืนรถแล้ว'; }
                                        if($row['status'] == 'Canceled') { $status_class = 'bg-danger'; $status_text = 'ยกเลิก'; }
                                    ?>
                                    <span class="badge status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </td>
                                <td class="text-center">
    <?php if($row['status'] == 'Pending'): ?>
        <a href="cancel_rental.php?id=<?= $row['rental_id']; ?>" 
           class="btn btn-outline-danger btn-sm rounded-pill px-3" 
           onclick="return confirm('ยืนยันการยกเลิก?')">ยกเลิก</a>
    
    <?php elseif($row['status'] == 'Completed'): ?>
        <a href="view_receipt.php?id=<?= $row['rental_id']; ?>" 
           target="_blank" 
           class="btn btn-success btn-sm rounded-pill px-3">
            <i class="bi bi-file-earmark-text"></i> ใบเสร็จ
        </a>

    <?php else: ?>
        <span class="text-muted small">-</span>
    <?php endif; ?>
</td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($rental_history)): ?>
                            <tr>
                                <td class="text-center">
    <?php if($row['status'] == 'Pending'): ?>
        <a href="cancel_rental.php?id=<?php echo $row['rental_id']; ?>" 
           class="btn btn-outline-danger btn-sm rounded-pill" 
           onclick="return confirm('ยืนยันการยกเลิกการจองนี้ใช่หรือไม่?')">
           <i class="bi bi-x-circle"></i> ยกเลิกการจอง
        </a>
    <?php else: ?>
        <span class="text-muted small">ยกเลิกไม่ได้</span>
    <?php endif; ?>
</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>