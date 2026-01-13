<?php
session_start();
require '../config.php'; 

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        $customer = $stmt->fetch(); // เก็บค่าลงใน $customer

        // ตรวจสอบว่ามีข้อมูลลูกค้า และรหัสผ่านถูกต้อง
        if ($customer && password_verify($password, $customer['password'])) {
            
            // ตรวจสอบสถานะการแบน (is_active)
            if (isset($customer['is_active']) && $customer['is_active'] == 0) {
                $error = "บัญชีของคุณถูกระงับการใช้งานชั่วคราว กรุณาติดต่อเจ้าหน้าที่";
            } else {
                // เข้าสู่ระบบสำเร็จ: ตั้งค่า Session ให้ตรงกับที่หน้า Dashboard เรียกใช้
                $_SESSION['customer_loggedin'] = true;
                $_SESSION['customer_id'] = $customer['customer_id'];
                $_SESSION['customer_name'] = $customer['name'];
                
                header('Location: customer_dashboard.php');
                exit;
            }
        } else {
            $error = "อีเมลหรือรหัสผ่านไม่ถูกต้อง";
        }
    } catch (PDOException $e) {
        $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
} // ปิดปีกกาของ IF POST
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบสมาชิก | Rent-A-Car</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body {
            font-family: 'Sarabun', sans-serif;
            background: #f4f7f6;
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .btn-primary {
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            background-color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card login-card p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-person-circle text-primary" style="font-size: 3rem;"></i>
                        <h4 class="fw-bold mt-2">เข้าสู่ระบบลูกค้า</h4>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 small"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small">อีเมล</label>
                            <input type="email" class="form-control" name="email" placeholder="example@mail.com" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small">รหัสผ่าน</label>
                            <input type="password" class="form-control" name="password" placeholder="••••••••" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">เข้าสู่ระบบ</button>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="small text-muted">ยังไม่มีบัญชี? <a href="../register.php" class="text-decoration-none">สมัครสมาชิก</a></p>
                        <hr>
                        <a href="../login.php" class="text-muted small text-decoration-none">เข้าสู่ระบบผู้ดูแล (Admin)</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>