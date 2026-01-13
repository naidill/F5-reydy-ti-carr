<?php
session_start();
// ตรวจสอบการล็อกอินของลูกค้า
if (!isset($_SESSION['customer_loggedin']) || $_SESSION['customer_loggedin'] !== true) {
    header('Location: customer_login.php');
    exit;
}

require '../config.php'; // ตรวจสอบ Path ให้ถูกต้อง

$customer_id = $_SESSION['customer_id'];
$car = null;
$error = '';
$success = '';

// --- 1. ตรวจสอบ car_id และดึงข้อมูลรถ ---
if (isset($_GET['car_id'])) {
    $car_id = filter_var($_GET['car_id'], FILTER_VALIDATE_INT);
    try {
        $stmt = $pdo->prepare("SELECT * FROM cars WHERE car_id = ? AND status = 'Available'");
        $stmt->execute([$car_id]);
        $car = $stmt->fetch();
        if (!$car) { $error = "ไม่พบรถยนต์ที่ต้องการ หรือรถไม่พร้อมใช้งาน"; }
    } catch (PDOException $e) { $error = "Error: " . $e->getMessage(); }
} else {
// ตัวอย่างใน car_rent.php หลังจาก INSERT ลงฐานข้อมูลสำเร็จ
$last_id = $pdo->lastInsertId();
header("Location: payment.php?rental_id=$last_id&amount=$total_cost");
exit;
}

// --- 2. จัดการการส่งฟอร์มจองรถ ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $car) {
    $rental_date = $_POST['rental_date'];
    $return_date = $_POST['return_date'];
    $total_cost = $_POST['total_cost'];

    try {
        $pdo->beginTransaction();
        
        // 1. บันทึกข้อมูลการจอง
// ไฟล์ car_rent.php ส่วนบันทึกข้อมูล
$stmt = $pdo->prepare("INSERT INTO rentals (customer_id, car_id, rental_date, return_date, total_cost, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
//                                                                                                        ^^^^^^^^^ ต้องเป็นคำนี้        $stmt->execute([$customer_id, $car['car_id'], $rental_date, $return_date, $total_cost]);
        
        // ดึง ID ที่เพิ่งบันทึกสำเร็จ
        $last_id = $pdo->lastInsertId();

        // 2. อัปเดตสถานะรถเป็น 'Rented' (หรือจะรอให้จ่ายเงินก่อนค่อยเปลี่ยนก็ได้ แต่เบื้องต้นให้เปลี่ยนเลยเพื่อกันคนอื่นจองซ้ำ)
        $stmt_update = $pdo->prepare("UPDATE cars SET status = 'Rented' WHERE car_id = ?");
        $stmt_update->execute([$car['car_id']]);
        
        $pdo->commit();

        // --- จุดสำคัญ: เมื่อบันทึกสำเร็จ ให้กระโดดไปหน้าชำระเงินทันที ---
        header("Location: payment.php?rental_id=$last_id&amount=$total_cost");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จองรถยนต์ | Rent-A-Car</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body { font-family: 'Sarabun', sans-serif; background-color: #f0f2f5; }
        .car-info-card { border: none; border-radius: 20px; overflow: hidden; }
        .booking-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .price-display { background: #f8f9fa; border-radius: 15px; padding: 20px; border-left: 5px solid #0d6efd; }
        .car-image-big { background: #fff; height: 300px; display: flex; align-items: center; justify-content: center; font-size: 8rem; color: #e9ecef; }
        .total-amount { font-size: 2rem; font-weight: 700; color: #0d6efd; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <a href="customer_dashboard.php" class="btn btn-link text-decoration-none text-muted mb-4 p-0">
                <i class="bi bi-arrow-left"></i> กลับไปหน้าเลือกรถ
            </a>

            <?php if ($success): ?>
                <div class="alert alert-success border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h4 class="alert-heading fw-bold"><i class="bi bi-check-circle-fill me-2"></i>จองสำเร็จ!</h4>
                    <p class="mb-0"><?php echo $success; ?></p>
                    <hr>
                    <a href="customer_dashboard.php" class="btn btn-success rounded-pill px-4">กลับหน้าหลัก</a>
                </div>
            <?php elseif ($error): ?>
                <div class="alert alert-danger border-0 shadow-sm rounded-4 p-4 mb-4"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($car && !$success): ?>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card car-info-card shadow-sm h-100">
                        <div class="car-image-big">
                            <i class="bi bi-car-front-fill"></i>
                        </div>
                        <div class="card-body p-4">
                            <span class="badge bg-primary mb-2 px-3 rounded-pill"><?php echo $car['car_type']; ?></span>
                            <h2 class="fw-bold mb-1"><?php echo $car['make'] . ' ' . $car['model']; ?></h2>
                            <p class="text-muted">ทะเบียน: <?php echo $car['license_plate']; ?></p>
                            
                            <div class="row mt-4">
                                <div class="col-6 mb-3">
                                    <small class="text-muted d-block">ปีรถ</small>
                                    <span class="fw-bold"><?php echo $car['year']; ?></span>
                                </div>
                                <div class="col-6 mb-3">
                                    <small class="text-muted d-block">สี</small>
                                    <span class="fw-bold"><?php echo $car['color']; ?></span>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted d-block">ราคาเช่าต่อวัน</small>
                                    <span class="fw-bold text-primary fs-4">฿<?php echo number_format($car['daily_rate']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card booking-card h-100">
                        <div class="card-body p-4">
                            <h4 class="fw-bold mb-4">รายละเอียดการเช่า</h4>
                            <form id="rentalForm" method="POST">
                                <div class="mb-3">
                                    <label class="form-label small fw-bold">วันที่เริ่มเช่า</label>
                                    <input type="date" class="form-control form-control-lg" name="rental_date" id="rental_date" 
                                           min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="mb-4">
                                    <label class="form-label small fw-bold">วันที่กำหนดคืน</label>
                                    <input type="date" class="form-control form-control-lg" name="return_date" id="return_date" required>
                                </div>

                                <div class="price-display mb-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>จำนวนวัน:</span>
                                        <span id="rental_days" class="fw-bold">0</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>ยอดรวมสุทธิ:</span>
                                        <div class="text-end">
                                            <span class="total-amount" id="display_total_cost">0.00</span>
                                            <small class="d-block text-muted">บาท</small>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="total_cost" id="total_cost" value="0">
                                <button type="submit" id="submitBtn" class="btn btn-primary w-100 btn-lg rounded-pill fw-bold py-3 shadow" disabled>
                                    ยืนยันการจองรถ
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    const rentalDateInput = document.getElementById('rental_date');
    const returnDateInput = document.getElementById('return_date');
    const rentalDaysText = document.getElementById('rental_days');
    const totalCostText = document.getElementById('display_total_cost');
    const totalCostInput = document.getElementById('total_cost');
    const submitBtn = document.getElementById('submitBtn');
    const dailyRate = <?php echo $car['daily_rate'] ?? 0; ?>;

    function calculateTotal() {
        const start = new Date(rentalDateInput.value);
        const end = new Date(returnDateInput.value);

        if (rentalDateInput.value && returnDateInput.value && end > start) {
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            const total = diffDays * dailyRate;

            rentalDaysText.innerText = diffDays;
            totalCostText.innerText = total.toLocaleString(undefined, {minimumFractionDigits: 2});
            totalCostInput.value = total;
            submitBtn.disabled = false;
        } else {
            rentalDaysText.innerText = '0';
            totalCostText.innerText = '0.00';
            submitBtn.disabled = true;
        }
    }

    rentalDateInput.addEventListener('change', () => {
        returnDateInput.min = rentalDateInput.value;
        calculateTotal();
    });
    returnDateInput.addEventListener('change', calculateTotal);
</script>

</body>
</html>