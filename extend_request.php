<?php
session_start();
date_default_timezone_set('Asia/Bangkok');
require '../config.php';

if (!isset($_SESSION['customer_id'])) { header('Location: customer_login.php'); exit; }

$rental_id = $_GET['id'] ?? null;
$customer_id = $_SESSION['customer_id'];

try {
    $stmt = $pdo->prepare("SELECT r.*, c.daily_rate FROM rentals r JOIN cars c ON r.car_id = c.car_id WHERE r.rental_id = ? AND r.customer_id = ?");
    $stmt->execute([$rental_id, $customer_id]);
    $rental = $stmt->fetch();
    if (!$rental) die("ข้อมูลไม่ถูกต้อง");

    $rate_per_hour = $rental['daily_rate'] / 24;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $hours = (int)$_POST['extend_hours'];
        $extra_cost = $hours * $rate_per_hour;
        $new_return = date('Y-m-d H:i:s', strtotime($rental['return_date'] . " + $hours hours"));
        $new_total = $rental['total_cost'] + $extra_cost;

        $update = $pdo->prepare("UPDATE rentals SET return_date = ?, total_cost = ? WHERE rental_id = ?");
        $update->execute([$new_return, $new_total, $rental_id]);
        header('Location: customer_dashboard.php?status=success'); exit;
    }
} catch (PDOException $e) { die($e->getMessage()); }
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ต่อสัญญาเช่ารถ | Rent-A-Car</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body { font-family: 'Sarabun', sans-serif; background-color: #f4f7fe; }
        .extend-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .btn-select { cursor: pointer; border: 2px solid #eee; transition: 0.3s; border-radius: 15px; }
        .btn-check:checked + .btn-select { border-color: #0d6efd; background-color: #f0f7ff; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card extend-card p-4">
                <div class="text-center mb-4">
                    <div class="icon-box bg-primary-subtle text-primary d-inline-block p-3 rounded-circle mb-3">
                        <i class="bi bi-calendar-plus fs-1"></i>
                    </div>
                    <h3 class="fw-bold">ต่อสัญญาการเช่า</h3>
                    <p class="text-muted">รถ: <?= $rental['make'].' '.$rental['model'] ?></p>
                </div>

                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label fw-bold">เลือกเวลาที่ต้องการต่อสัญญา:</label>
                        <div class="row g-2">
                            <?php 
                            $options = [1 => '1 ชม.', 3 => '3 ชม.', 6 => '6 ชม.', 12 => '12 ชม.', 24 => '1 วัน'];
                            foreach($options as $hours => $label): 
                                $price = $hours * $rate_per_hour;
                            ?>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="extend_hours" id="h<?= $hours ?>" value="<?= $hours ?>" required>
                                <label class="btn btn-select w-100 p-3" for="h<?= $hours ?>">
                                    <div class="fw-bold"><?= $label ?></div>
                                    <div class="small text-primary">฿<?= number_format($price, 0) ?></div>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="bg-light p-3 rounded-3 mb-4">
                        <div class="d-flex justify-content-between small mb-2">
                            <span>กำหนดคืนเดิม:</span>
                            <span class="fw-bold"><?= date('d/m/Y H:i', strtotime($rental['return_date'])) ?></span>
                        </div>
                        <div class="d-flex justify-content-between text-primary fw-bold">
                            <span>อัตราค่าเช่ารายชั่วโมง:</span>
                            <span>฿<?= number_format($rate_per_hour, 2) ?></span>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow">ยืนยันการต่อสัญญา</button>
                        <a href="customer_dashboard.php" class="btn btn-link text-muted">ยกเลิก</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>