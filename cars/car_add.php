<?php
session_start();
// ตรวจสอบการล็อกอินของ Admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../login.php');
    exit;
}

require '../config.php'; 

$error = '';
$success = '';
$current_year = date('Y');

// --- 1. จัดการเมื่อมีการ Submit ฟอร์ม ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่าจากฟอร์ม
    $license_plate = trim($_POST['license_plate']);
    $make = trim($_POST['make']);
    $model = trim($_POST['model']);
    $car_type = $_POST['car_type']; // <--- รับค่าประเภทรถที่เพิ่มมาใหม่
    $year = $_POST['year'];
    $color = trim($_POST['color']);
    $daily_rate = $_POST['daily_rate'];
    $status = 'Available'; 

    // ตรวจสอบข้อมูลเบื้องต้น
    if (empty($license_plate) || empty($make) || empty($daily_rate)) {
        $error = "กรุณากรอกข้อมูลทะเบียนรถ ยี่ห้อ และอัตราค่าเช่าให้ครบถ้วน";
    } else {
        try {
            // 2. ตรวจสอบทะเบียนรถซ้ำ
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM cars WHERE license_plate = ?");
            $stmt_check->execute([$license_plate]);
            if ($stmt_check->fetchColumn() > 0) {
                $error = "ทะเบียนรถนี้มีอยู่ในระบบแล้ว ไม่สามารถเพิ่มซ้ำได้";
            } else {
                // 3. บันทึกข้อมูล (เพิ่ม car_type เข้าไปใน SQL INSERT)
                $sql = "INSERT INTO cars (license_plate, make, model, car_type, year, color, daily_rate, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $license_plate, 
                    $make, 
                    $model, 
                    $car_type, 
                    $year, 
                    $color, 
                    $daily_rate, 
                    $status
                ]);

                header("Location: ../cars.php?status=added");
                exit;
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
    <title>เพิ่มรถยนต์ใหม่</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0">เพิ่มรถยนต์ใหม่เข้าสู่ระบบ</h4>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="license_plate" class="form-label">ทะเบียนรถ:</label>
                        <input type="text" class="form-control" id="license_plate" name="license_plate" placeholder="เช่น กข-1234" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="make" class="form-label">ยี่ห้อรถ:</label>
                        <input type="text" class="form-control" id="make" name="make" placeholder="เช่น Toyota" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="model" class="form-label">รุ่นรถ:</label>
                        <input type="text" class="form-control" id="model" name="model" placeholder="เช่น Camry">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="car_type" class="form-label">ประเภทรถ:</label>
                        <select class="form-select" id="car_type" name="car_type" required>
                            <option value="">-- เลือกประเภทรถ --</option>
                            <option value="รถเก๋ง">รถเก๋ง</option>
                            <option value="SUV">SUV</option>
                            <option value="รถกระบะ">รถกระบะ</option>
                            <option value="รถตู้">รถตู้</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="year" class="form-label">ปีที่ผลิต:</label>
                        <input type="number" class="form-control" id="year" name="year" value="<?php echo $current_year; ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="color" class="form-label">สีรถ:</label>
                        <input type="text" class="form-control" id="color" name="color">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="daily_rate" class="form-label">ราคาเช่า/วัน:</label>
                        <input type="number" step="0.01" class="form-control" id="daily_rate" name="daily_rate" required>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="../cars.php" class="btn btn-secondary">ยกเลิก</a>
                    <button type="submit" class="btn btn-success">บันทึกข้อมูลรถยนต์</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>