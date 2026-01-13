<?php
session_start();
if (!isset($_SESSION['loggedin'])) { 
    header('Location: login.php'); 
    exit; 
}
require 'config.php'; 

try {
    // ปรับ Query ให้ดึง car_type ออกมาด้วย
    $stmt = $pdo->query('SELECT car_id, license_plate, make, model, car_type, daily_rate, status FROM cars ORDER BY car_id DESC');
    $cars = $stmt->fetchAll();
} catch (PDOException $e) {
    $cars = [];
    $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการรถยนต์ | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body { font-family: 'Sarabun', sans-serif; background-color: #f4f7f6; }
        .main-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .table thead { background-color: #f8f9fa; }
        .table thead th { border-bottom: none; color: #6c757d; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; }
        .car-info { font-weight: 600; color: #2c3e50; }
        .badge-status { border-radius: 8px; padding: 6px 12px; font-weight: 500; }
        .btn-action { border-radius: 10px; transition: all 0.2s; }
        .btn-action:hover { transform: scale(1.05); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-car-front-fill me-2"></i> RENT-A-CAR</a>
        <div class="ms-auto">
            <a href="index.php" class="btn btn-outline-light btn-sm rounded-pill px-3">กลับหน้าหลัก</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">รายการรถยนต์ทั้งหมด</h2>
            <p class="text-muted">จัดการข้อมูลรถ สถานะการเช่า และอัตราค่าบริการ</p>
        </div>
        <a href="cars/car_add.php" class="btn btn-primary btn-action shadow-sm px-4 py-2">
            <i class="bi bi-plus-lg me-2"></i> เพิ่มรถยนต์ใหม่
        </a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger rounded-4 border-0 shadow-sm"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card main-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">ทะเบียนรถ</th>
                            <th>ยี่ห้อ / รุ่น</th>
                            <th>ประเภท</th>
                            <th>ราคา/วัน</th>
                            <th>สถานะ</th>
                            <th class="text-center pe-4">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cars as $car): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="car-info"><?php echo htmlspecialchars($car['license_plate']); ?></div>
                            </td>
                            <td>
                                <div class="fw-bold"><?php echo htmlspecialchars($car['make']); ?></div>
                                <div class="small text-muted"><?php echo htmlspecialchars($car['model']); ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($car['car_type'] ?: 'ไม่ระบุ'); ?></span>
                            </td>
                            <td class="fw-bold text-primary">
                                ฿<?php echo number_format($car['daily_rate'], 2); ?>
                            </td>
                            <td>
                                <?php 
                                    $status = $car['status'];
                                    $bg = 'bg-secondary';
                                    if ($status == 'Available') $bg = 'bg-success-subtle text-success';
                                    if ($status == 'Rented') $bg = 'bg-warning-subtle text-warning-emphasis';
                                    if ($status == 'Maintenance') $bg = 'bg-danger-subtle text-danger';
                                ?>
                                <span class="badge badge-status <?php echo $bg; ?>">
                                    <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>
                                    <?php echo $status; ?>
                                </span>
                            </td>
                            <td class="text-center pe-4">
                                <div class="btn-group">
                                    <a href="cars/car_edit.php?id=<?php echo $car['car_id']; ?>" class="btn btn-outline-info btn-sm btn-action me-1">
                                        <i class="bi bi-pencil-square"></i> แก้ไข
                                    </a>
                                    <a href="cars/car_delete.php?id=<?php echo $car['car_id']; ?>" 
                                       onclick="return confirm('ยืนยันการลบรถยนต์ทะเบียน <?php echo $car['license_plate']; ?>?')" 
                                       class="btn btn-outline-danger btn-sm btn-action">
                                        <i class="bi bi-trash"></i> ลบ
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($cars)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-3">ไม่มีข้อมูลรถยนต์ในขณะนี้</h5>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<footer class="mt-5 py-4 text-center text-muted small">
    &copy; 2024 Rent-A-Car System - Management Portal
</footer>

</body>
</html>