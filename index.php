<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location:login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าหลัก | ระบบจัดการเช่ารถ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body { font-family: 'Sarabun', sans-serif; background-color: #f8f9fa; }
        .navbar { background: linear-gradient(90deg, #1a2a6c, #b21f1f, #fdbb2d); border-bottom: none; }
        .hero-section { background: white; border-radius: 20px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-bottom: 40px; }
        .menu-card { border: none; border-radius: 15px; transition: all 0.3s ease; height: 100%; }
        .menu-card:hover { transform: translateY(-10px); box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
        .icon-circle { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 20px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark shadow">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-car-front-fill"></i> RENT-A-CAR ADMIN</a>
    <div class="ms-auto d-flex align-items-center">
        <span class="text-white me-3 d-none d-md-block">ยินดีต้อนรับ, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
        <a class="btn btn-light btn-sm rounded-pill px-3" href="logout.php">ออกจากระบบ</a>
    </div>
  </div>
</nav>

<div class="container mt-5">
    <div class="hero-section text-center text-md-start d-md-flex align-items-center justify-content-between">
        <div>
            <h1 class="display-5 fw-bold text-dark">ระบบจัดการหลังบ้าน</h1>
            <p class="text-muted fs-5">เลือกจัดการข้อมูลรถยนต์ ลูกค้า หรือดูรายงานสถิติเพื่อวางแผนโปรโมชั่น</p>
        </div>
        <div class="d-none d-md-block">
            <i class="bi bi-shield-lock-fill text-primary" style="font-size: 5rem; opacity: 0.1;"></i>
        </div>
    </div>
    
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card menu-card shadow-sm">
                <div class="card-body p-4 text-center">
                    <div class="icon-circle bg-primary-subtle text-primary mx-auto">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h5 class="fw-bold">ข้อมูลรถยนต์</h5>
                    <p class="text-muted small">เพิ่มคันใหม่, แก้ไขสเปค หรือเปลี่ยนสถานะการว่างของรถ</p>
                    <a href="cars.php" class="btn btn-primary w-100 rounded-pill">จัดการรถยนต์</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card menu-card shadow-sm">
                <div class="card-body p-4 text-center">
                    <div class="icon-circle bg-success-subtle text-success mx-auto">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h5 class="fw-bold">ข้อมูลลูกค้า</h5>
                    <p class="text-muted small">ดูรายชื่อลูกค้าทั้งหมด ประวัติการสมัคร และเบอร์โทรติดต่อ</p>
                    <a href="../car_rental_system/customers/customers.php" class="btn btn-success w-100 rounded-pill">จัดการลูกค้า</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card menu-card shadow-sm border-warning" style="border-width: 2px;">
                <div class="card-body p-4 text-center">
                    <div class="icon-circle bg-warning-subtle text-warning mx-auto">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <h5 class="fw-bold">สถิติและโปรโมชั่น</h5>
                    <p class="text-muted small">วิเคราะห์รายได้รายเดือน และดูรถรุ่นยอดนิยมเพื่อทำโปรฯ</p>
                    <a href="../car_rental_system/admin/admin_dashboard.php" class="btn btn-warning w-100 rounded-pill fw-bold">ดูรายงานสรุป</a>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5 p-4 bg-white rounded-4 shadow-sm border-start border-primary border-5">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle-fill text-primary fs-3 me-3"></i>
            <div>
                <h6 class="mb-0 fw-bold">ระบบทำงานปกติ</h6>
                <small class="text-muted">ฐานข้อมูลเชื่อมต่อเรียบร้อย พร้อมสำหรับการบันทึกข้อมูลใหม่</small>
            </div>
        </div>
    </div>
</div>

<footer class="mt-5 py-4 text-center text-muted small">
    &copy; 2024 Rent-A-Car System - Admin Management Panel
</footer>

</body>
</html>