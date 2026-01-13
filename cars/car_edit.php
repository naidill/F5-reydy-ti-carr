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
$car = null;
$car_id = null;

// --- 1. การดึงข้อมูลรถยนต์เดิมมาแสดงในฟอร์ม ---
if (isset($_GET['id'])) {
    $car_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($car_id) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM cars WHERE car_id = ?");
            $stmt->execute([$car_id]);
            $car = $stmt->fetch();
        } catch (PDOException $e) { $error = "เกิดข้อผิดพลาด: " . $e->getMessage(); }
    }
}

// --- 2. การอัปเดตข้อมูลเมื่อมีการ Submit ฟอร์ม ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['car_id'])) {
    $car_id = $_POST['car_id'];
    $make = $_POST['make'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $license_plate = $_POST['license_plate'];
    $color = $_POST['color'];
    $car_type = $_POST['car_type'];
    $daily_rate = $_POST['daily_rate'];
    $status = $_POST['status'];

    try {
        $sql = "UPDATE cars SET make=?, model=?, year=?, license_plate=?, color=?, car_type=?, daily_rate=?, status=? WHERE car_id=?";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([$make, $model, $year, $license_plate, $color, $car_type, $daily_rate, $status, $car_id])) {
            $success = "อัปเดตข้อมูลรถยนต์เรียบร้อยแล้ว!";
            header("refresh:2;url=../cars.php"); // ส่งกลับหน้าจัดการรถ
        }
    } catch (PDOException $e) { $error = "ไม่สามารถบันทึกข้อมูลได้: " . $e->getMessage(); }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลรถยนต์ | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body { font-family: 'Sarabun', sans-serif; background-color: #f0f2f5; }
        .edit-card { border: none; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .card-header { background: #4e73df; color: white; border-radius: 15px 15px 0 0 !important; padding: 20px; }
        .form-label { font-weight: 600; color: #4e73df; font-size: 0.9rem; }
        .form-control, .form-select { border-radius: 8px; padding: 10px; border: 1px solid #d1d3e2; }
        .form-control:focus { border-color: #4e73df; box-shadow: 0 0 0 0.2rem rgba(78,115,223,0.1); }
        .btn-save { background: #4e73df; border: none; padding: 10px 25px; border-radius: 8px; font-weight: 600; }
        .btn-cancel { background: #858796; border: none; padding: 10px 25px; border-radius: 8px; color: white; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <?php if ($success): ?>
                <div class="alert alert-success border-0 shadow-sm mb-4"><i class="bi bi-check-circle-fill me-2"></i> <?php echo $success; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger border-0 shadow-sm mb-4"><i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($car): ?>
            <div class="card edit-card">
                <div class="card-header d-flex align-items-center">
                    <i class="bi bi-pencil-square fs-3 me-3"></i>
                    <h4 class="mb-0">แก้ไขรายละเอียดรถยนต์ (ID: #<?php echo $car['car_id']; ?>)</h4>
                </div>
                <div class="card-body p-4 p-md-5 bg-white">
                    <form method="POST">
                        <input type="hidden" name="car_id" value="<?php echo $car['car_id']; ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-tag-fill me-1"></i> ยี่ห้อรถ</label>
                                <input type="text" class="form-control" name="make" value="<?php echo htmlspecialchars($car['make']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-car-front me-1"></i> รุ่น</label>
                                <input type="text" class="form-control" name="model" value="<?php echo htmlspecialchars($car['model']); ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-hash me-1"></i> เลขทะเบียน</label>
                                <input type="text" class="form-control" name="license_plate" value="<?php echo htmlspecialchars($car['license_plate']); ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><i class="bi bi-calendar-event me-1"></i> ปี</label>
                                <input type="number" class="form-control" name="year" value="<?php echo htmlspecialchars($car['year']); ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><i class="bi bi-palette me-1"></i> สี</label>
                                <input type="text" class="form-control" name="color" value="<?php echo htmlspecialchars($car['color']); ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-grid-fill me-1"></i> ประเภทรถ</label>
                                <select class="form-select" name="car_type" required>
                                    <option value="Sedan" <?= $car['car_type'] == 'Sedan' ? 'selected' : '' ?>>Sedan (เก๋ง)</option>
                                    <option value="SUV" <?= $car['car_type'] == 'SUV' ? 'selected' : '' ?>>SUV</option>
                                    <option value="Pickup" <?= $car['car_type'] == 'Pickup' ? 'selected' : '' ?>>Pickup</option>
                                    <option value="Van" <?= $car['car_type'] == 'Van' ? 'selected' : '' ?>>Van</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><i class="bi bi-currency-dollar me-1"></i> ค่าเช่าต่อวัน (บาท)</label>
                                <input type="number" step="0.01" class="form-control" name="daily_rate" value="<?php echo $car['daily_rate']; ?>" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label"><i class="bi bi-info-circle-fill me-1"></i> สถานะปัจจุบัน</label>
                                <select class="form-select" name="status" required>
                                    <option value="Available" class="text-success" <?= $car['status'] == 'Available' ? 'selected' : '' ?>>Available (ว่างพร้อมเช่า)</option>
                                    <option value="Rented" class="text-warning" <?= $car['status'] == 'Rented' ? 'selected' : '' ?>>Rented (ถูกเช่าอยู่)</option>
                                    <option value="Maintenance" class="text-danger" <?= $car['status'] == 'Maintenance' ? 'selected' : '' ?>>Maintenance (ซ่อมบำรุง)</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-5 border-top pt-4">
                            <a href="../cars.php" class="btn btn-cancel">ยกเลิก</a>
                            <button type="submit" class="btn btn-primary btn-save shadow-sm">บันทึกข้อมูลใหม่</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>