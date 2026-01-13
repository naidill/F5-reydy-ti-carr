<?php
session_start();
date_default_timezone_set('Asia/Bangkok'); 
require '../config.php';

// 1. ตรวจสอบการ Login และชื่อผู้ใช้
if (!isset($_SESSION['customer_id'])) { 
    header('Location: customer_login.php'); 
    exit; 
}
$customer_id = $_SESSION['customer_id'];
// ดึงชื่อลูกค้าจาก Session (ถ้าไม่มีให้แสดงว่า 'คุณลูกค้า')
$customer_name = $_SESSION['customer_name'] ?? 'คุณลูกค้า'; 

// 2. จัดการระบบค้นหา
$search = $_GET['search'] ?? '';
if (!empty($search)) {
    $stmt_cars = $pdo->prepare("SELECT * FROM cars WHERE status = 'Available' AND (make LIKE :s OR model LIKE :s)");
    $stmt_cars->execute(['s' => "%$search%"]);
} else {
    $stmt_cars = $pdo->query("SELECT * FROM cars WHERE status = 'Available'");
}
$cars = $stmt_cars->fetchAll();

// 3. ดึงข้อมูลแจ้งเตือนรถที่เช่าอยู่
$stmt_check = $pdo->prepare("
    SELECT r.*, c.make, c.model, c.license_plate 
    FROM rentals r
    JOIN cars c ON r.car_id = c.car_id
    WHERE r.customer_id = ? AND r.status = 'Active' 
    AND r.return_date <= (NOW() + INTERVAL 60 MINUTE)
");
$stmt_check->execute([$customer_id]);
$rental_alert = $stmt_check->fetch();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าแรก | Rent-A-Car Premium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&family=Sarabun:wght@300;400;600&display=swap');
        body { font-family: 'Plus Jakarta Sans', 'Sarabun', sans-serif; background-color: #f4f7f6; color: #2d3436; }
        .navbar { background: white; border-bottom: 1px solid #eee; }
        .hero-welcome { background: white; padding: 40px 0; border-bottom: 1px solid #eee; margin-bottom: 40px; }
        .car-card { border: none; border-radius: 24px; transition: 0.3s; background: white; box-shadow: 0 10px 20px rgba(0,0,0,0.02); }
        .car-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.08); }
        .search-box { border-radius: 15px; border: 1px solid #eee; padding: 12px 20px; background: #f8f9fa; }
        .btn-custom { border-radius: 12px; padding: 10px 24px; font-weight: 600; }
        .price-tag { font-size: 1.4rem; font-weight: 700; color: #0d6efd; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top py-3">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="customer_dashboard.php">
            <i class="bi bi-car-front-fill me-2"></i>RENT-A-CAR
        </a>
        <div class="d-flex align-items-center">
            <span class="me-3 d-none d-md-inline text-muted">สวัสดี, <strong><?= htmlspecialchars($customer_name) ?></strong></span>
            <a href="customer_history.php" class="btn btn-light btn-sm rounded-pill px-3 me-2 border">
                <i class="bi bi-clock-history me-1 text-primary"></i> ประวัติการเช่า
            </a>
            <a href="../logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">ออกจากระบบ</a>
        </div>
    </div>
</nav>

<div class="hero-welcome">
    <div class="container text-center">
        <h2 class="fw-bold mb-3">ยินดีต้อนรับคุณ <?= htmlspecialchars($customer_name) ?></h2>
        <p class="text-muted mb-4">ค้นหารถยนต์ที่คุณต้องการเดินทางวันนี้</p>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <form action="" method="GET" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control search-box" placeholder="ค้นหายี่ห้อหรือรุ่นรถ เช่น Toyota..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="btn btn-primary btn-custom shadow-sm">
                        <i class="bi bi-search"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container mb-5">
    <?php if ($rental_alert): 
        $diff = strtotime($rental_alert['return_date']) - time();
        $is_late = $diff < 0;
    ?>
    <div class="alert shadow-sm p-4 rounded-4 mb-5 <?= $is_late ? 'alert-danger border-start border-4 border-danger' : 'alert-warning border-start border-4 border-warning' ?> bg-white">
        <div class="d-flex align-items-center flex-wrap">
            <div class="p-3 rounded-circle bg-light me-3">
                <i class="bi <?= $is_late ? 'bi-exclamation-triangle-fill text-danger' : 'bi-clock-fill text-warning' ?> fs-2"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="fw-bold mb-1"><?= $is_late ? 'เกินเวลาคืนรถ!' : 'รถของคุณใกล้ถึงกำหนดคืน' ?></h5>
                <p class="mb-0 text-muted">รถ: <?= $rental_alert['make'] ?> (<?= $rental_alert['license_plate'] ?>) | คืนภายใน: <?= date('H:i', strtotime($rental_alert['return_date'])) ?></p>
            </div>
            <div class="mt-3 mt-lg-0">
                <a href="extend_request.php?id=<?= $rental_alert['rental_id'] ?>" class="btn btn-dark btn-custom">ต่อเวลาเช่า</a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">เลือกรถที่พร้อมให้บริการ</h4>
        <?php if($search): ?>
            <a href="customer_dashboard.php" class="small text-decoration-none">ล้างการค้นหา</a>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <?php if ($cars): ?>
            <?php foreach ($cars as $car): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card car-card h-100 p-2">
                        <div class="card-body p-4">
                            <h4 class="fw-bold mb-1"><?= htmlspecialchars($car['make']); ?></h4>
                            <p class="text-muted small mb-4"><?= htmlspecialchars($car['model']); ?></p>
                            
                            <div class="d-flex justify-content-between align-items-end mt-auto pt-3 border-top">
                                <div>
                                    <span class="text-muted small">ราคาเช่า</span><br>
                                    <span class="price-tag">฿<?= number_format($car['daily_rate'], 0); ?></span>
                                    <small class="text-muted">/วัน</small>
                                </div>
                                <a href="car_rent.php?car_id=<?= $car['car_id']; ?>" class="btn btn-primary btn-custom shadow-sm">เช่ารถ</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-search text-muted display-1"></i>
                <h5 class="mt-3 text-muted">ไม่พบรถที่คุณต้องการในขณะนี้</h5>
            </div>
        <?php endif; ?>
    </div>
</div>

<footer class="text-center py-5 text-muted border-top bg-white mt-auto">
    <small>© 2024 Rent-A-Car System. ความสุขทุกการเดินทาง</small>
</footer>

</body>
</html>