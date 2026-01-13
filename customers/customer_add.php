<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) { 
    header('Location:login.php'); 
    exit; 
}
require '../config.php'; 
$message = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name']; 
    $address = $_POST['address']; 
    $phone_number = $_POST['phone_number'];
    $email = $_POST['email']; 
    $license_number = $_POST['license_number'];
    // เพิ่มรหัสผ่านเริ่มต้นสำหรับลูกค้าที่ Admin เพิ่มให้ (เช่นเบอร์โทรศัพท์ หรือสุ่ม)
    $password = password_hash($phone_number, PASSWORD_DEFAULT); 

    try {
        $sql = "INSERT INTO customers (name, address, phone_number, email, license_number, password) VALUES (:name, :address, :phone, :email, :license, :password)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'name' => $name, 
            'address' => $address, 
            'phone' => $phone_number, 
            'email' => $email, 
            'license' => $license_number,
            'password' => $password
        ]);

        $success = "เพิ่มข้อมูลลูกค้าเรียบร้อยแล้ว! รหัสผ่านเริ่มต้นคือเบอร์โทรศัพท์";
        header("refresh:2;url=customers.php");
    } catch (PDOException $e) {
        $message = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มลูกค้าใหม่ | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body { font-family: 'Sarabun', sans-serif; background-color: #f0f2f5; }
        .add-card { border: none; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .card-header { background: #1cc88a; color: white; border-radius: 15px 15px 0 0 !important; padding: 20px; }
        .form-label { font-weight: 600; color: #2e59d9; font-size: 0.9rem; }
        .form-control { border-radius: 8px; padding: 10px; border: 1px solid #d1d3e2; }
        .btn-submit { background: #1cc88a; border: none; padding: 10px 25px; border-radius: 8px; font-weight: 600; color: white; }
        .btn-submit:hover { background: #17a673; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-3">
                <a href="customers.php" class="text-decoration-none text-muted small">
                    <i class="bi bi-arrow-left"></i> กลับสู่หน้ารายการลูกค้า
                </a>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success border-0 shadow-sm mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="card add-card">
                <div class="card-header d-flex align-items-center">
                    <i class="bi bi-person-plus-fill fs-3 me-3"></i>
                    <h4 class="mb-0">ลงทะเบียนลูกค้าใหม่</h4>
                </div>
                <div class="card-body p-4 p-md-5 bg-white">
                    <form method="POST">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-person me-1"></i> ชื่อ-นามสกุล</label>
                                <input type="text" class="form-control" name="name" placeholder="ระบุชื่อลูกค้า" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-telephone me-1"></i> เบอร์โทรศัพท์</label>
                                <input type="text" class="form-control" name="phone_number" placeholder="08x-xxx-xxxx" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-envelope me-1"></i> อีเมล</label>
                                <input type="email" class="form-control" name="email" placeholder="example@mail.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-card-heading me-1"></i> เลขที่ใบขับขี่</label>
                                <input type="text" class="form-control" name="license_number" placeholder="ระบุเลขใบขับขี่" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label"><i class="bi bi-geo-alt me-1"></i> ที่อยู่ปัจจุบัน</label>
                                <textarea name="address" class="form-control" rows="3" placeholder="ระบุที่อยู่สำหรับการติดต่อและส่งเอกสาร" required></textarea>
                            </div>

                            <div class="col-12 mt-4 bg-light p-3 rounded-3">
                                <p class="small text-muted mb-0">
                                    <i class="bi bi-info-circle me-1"></i> 
                                    <strong>หมายเหตุ:</strong> ระบบจะตั้งรหัสผ่านเริ่มต้นให้ลูกค้าเป็น <strong>"เบอร์โทรศัพท์"</strong> ที่ระบุไว้ข้างต้น ลูกค้าสามารถเปลี่ยนรหัสผ่านได้เองภายหลัง
                                </p>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-5 border-top pt-4">
                            <button type="reset" class="btn btn-light me-2 px-4">ล้างข้อมูล</button>
                            <button type="submit" class="btn btn-submit shadow-sm px-5">บันทึกข้อมูลลูกค้า</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>