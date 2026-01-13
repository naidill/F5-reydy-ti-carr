<?php
// register.php - หน้าสมัครสมาชิก (เวอร์ชันตกแต่งใหม่)
session_start();
require 'config.php'; 
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $phone_number = $_POST['phone_number'];
    $email = $_POST['email'];
    $license_number = $_POST['license_number'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE email = ?");
            $stmt_check->execute([$email]);
            
            if ($stmt_check->fetchColumn() > 0) {
                $error = "อีเมลนี้ถูกลงทะเบียนไว้แล้ว";
            } else {
                $stmt = $pdo->prepare("INSERT INTO customers (name, address, phone_number, email, license_number, password) VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$name, $address, $phone_number, $email, $license_number, $hashed_password])) {
                    $message = "ลงทะเบียนสำเร็จ! กำลังพาท่านไปหน้าเข้าสู่ระบบ...";
                    header("refresh:2;url=customer_login.php");
                }
            }
        } catch (PDOException $e) {
            $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก | Rent-A-Car</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body {
            font-family: 'Sarabun', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .register-card {
            border: none;
            border-radius: 25px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .register-header {
            background: #0d6efd;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .form-control {
            border-radius: 10px;
            padding: 10px 15px;
            border: 1px solid #e0e0e0;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.25 margin rgba(13, 110, 253, 0.1);
        }
        .btn-register {
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }
        .input-group-text {
            border-radius: 10px 0 0 10px;
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
        }
        .form-control {
            border-radius: 0 10px 10px 0;
        }
    </style>
</head>
<body>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-md-10">
            <div class="card register-card">
                <div class="register-header">
                    <i class="bi bi-person-plus-fill" style="font-size: 3rem;"></i>
                    <h2 class="fw-bold mt-2">สร้างบัญชีผู้ใช้งานใหม่</h2>
                    <p class="mb-0 opacity-75">เข้าร่วมกับเราเพื่อสัมผัสประสบการณ์การเช่ารถที่เหนือระดับ</p>
                </div>
                <div class="card-body p-4 p-md-5 bg-white">
                    
                    <?php if ($message): ?>
                        <div class="alert alert-success border-0 rounded-3 mb-4"><i class="bi bi-check-circle-fill me-2"></i><?php echo $message; ?></div>
                    <?php endif; ?>

                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 rounded-3 mb-4"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">ชื่อ-นามสกุล</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" name="name" placeholder="นาย สมชาย ใจดี" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">เบอร์โทรศัพท์</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="text" class="form-control" name="phone_number" placeholder="08x-xxx-xxxx" required value="<?php echo htmlspecialchars($_POST['phone_number'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">เลขที่ใบขับขี่</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-card-list"></i></span>
                                    <input type="text" class="form-control" name="license_number" placeholder="ระบุเลขใบขับขี่" required value="<?php echo htmlspecialchars($_POST['license_number'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">อีเมล (ใช้สำหรับเข้าสู่ระบบ)</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" name="email" placeholder="example@mail.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted">ที่อยู่ปัจจุบัน</label>
                                <textarea class="form-control" name="address" rows="2" placeholder="บ้านเลขที่, ถนน, แขวง/ตำบล..." required style="border-radius:10px;"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">รหัสผ่าน</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                    <input type="password" class="form-control" name="password" placeholder="ตั้งรหัสผ่าน" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">ยืนยันรหัสผ่านอีกครั้ง</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                                    <input type="password" class="form-control" name="confirm_password" placeholder="ยืนยันรหัสผ่าน" required>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary btn-register w-100">ลงทะเบียนสมาชิก</button>
                            </div>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <span class="text-muted small">มีบัญชีอยู่แล้ว?</span> 
                        <a href="../car_rental_system/customers/customer_login.php" class="text-decoration-none fw-bold">เข้าสู่ระบบที่นี่</a>
                    </div>
                </div>
            </div>
            <p class="text-center mt-4 text-muted small">&copy; 2024 Premium Car Rental. All rights reserved.</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>