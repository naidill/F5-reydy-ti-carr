<?php
session_start();
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = 'admin'; 
    $password = '1234';  

    if ($_POST['username'] === $username && $_POST['password'] === $password) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        header('Location:index.php');
        exit;
    } else {
        $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Rent-A-Car</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body {
            font-family: 'Sarabun', sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
        }
        .btn-login {
            background: #1e3c72;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: #122a52;
            transform: translateY(-2px);
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
            background: #f8f9fa;
        }
        .admin-icon {
            font-size: 3rem;
            color: #1e3c72;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card login-card p-4">
                    <div class="text-center mb-4">
                        <div class="admin-icon"><i class="bi bi-person-badge-fill"></i></div>
                        <h4 class="fw-bold text-dark mt-2">ADMIN PANEL</h4>
                        <p class="text-muted small">ระบบจัดการหลังบ้าน Rent-A-Car</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger border-0 small py-2 mb-3" role="alert">
                            <i class="bi bi-exclamation-circle me-2"></i><?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Username</label>
                            <div class="input-group">
                                <span class="input-group-text border-0 bg-light"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control border-0" name="username" placeholder="ระบุชื่อผู้ใช้" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text border-0 bg-light"><i class="bi bi-key"></i></span>
                                <input type="password" class="form-control border-0" name="password" placeholder="ระบุรหัสผ่าน" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary btn-login w-100 mb-3">เข้าสู่ระบบ</button>
                    </form>

                    <div class="text-center mt-3">
    <a href="../car_rental_system/customers/customer_login.php" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left"></i> สลับไปหน้าล็อกอินลูกค้า
    </a>
</div>
                </div>
            </div>
        </div>
    </div>
    <script>
    // ฟังก์ชันเปิด-ปิดตาดูรหัสผ่าน
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('input[name="password"]');

    if(togglePassword) {
        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
    }
</script>
</body>
</html>