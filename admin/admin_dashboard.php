<?php
session_start();
date_default_timezone_set('Asia/Bangkok'); 

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../login.php');
    exit;
}
require '../config.php';

try {
    // 1. ดึงข้อมูลสรุป (Cards)
    $count_cars = $pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn();
    $count_rentals = $pdo->query("SELECT COUNT(*) FROM rentals WHERE status != 'Canceled'")->fetchColumn();
    $total_revenue = $pdo->query("SELECT SUM(total_cost) FROM rentals WHERE status = 'Completed'")->fetchColumn() ?? 0;
    $unpaid_balance = $pdo->query("SELECT SUM(total_cost) FROM rentals WHERE status = 'Pending'")->fetchColumn() ?? 0;

    // 2. ดึงข้อมูลกราฟ
    $monthly_data = $pdo->query("
        SELECT DATE_FORMAT(rental_date, '%b') as m_name, SUM(total_cost) as total 
        FROM rentals WHERE status = 'Completed'
        GROUP BY MONTH(rental_date) ORDER BY MONTH(rental_date) ASC
    ")->fetchAll();
    $months = []; $revenues = [];
    foreach($monthly_data as $data) {
        $months[] = $data['m_name'];
        $revenues[] = $data['total'];
    }

    // 3. ดึงรายการแจ้งเตือน (ใกล้คืน 60 นาที)
    $stmt_alert = $pdo->prepare("
        SELECT r.*, c.make, c.model, c.license_plate, cust.name as cust_name
        FROM rentals r
        JOIN cars c ON r.car_id = c.car_id
        JOIN customers cust ON r.customer_id = cust.customer_id
        WHERE r.status = 'Active' 
        AND r.return_date <= (NOW() + INTERVAL 60 MINUTE)
        ORDER BY r.return_date ASC
    ");
    $stmt_alert->execute();
    $alerts = $stmt_alert->fetchAll();

} catch (PDOException $e) { die("Error: " . $e->getMessage()); }

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body { font-family: 'Sarabun', sans-serif; background-color: #f4f7f6; }
        .card-custom { border: none; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: 0.3s; }
        .btn-manage { border-radius: 10px; padding: 12px; }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark">แผงควบคุมผู้ดูแลระบบ</h2>
        <span class="badge bg-primary px-3 py-2 rounded-pill">ยินดีต้อนรับ Admin</span>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card card-custom p-3 bg-white border-start border-primary border-4">
                <small class="text-muted fw-bold">รถยนต์ทั้งหมด</small>
                <div class="h3 fw-bold mb-0"><?= $count_cars ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom p-3 bg-white border-start border-success border-4">
                <small class="text-muted fw-bold">รายได้สุทธิ</small>
                <div class="h3 fw-bold mb-0 text-success">฿<?= number_format($total_revenue) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom p-3 bg-white border-start border-danger border-4">
                <small class="text-muted fw-bold">ยอดค้างชำระ</small>
                <div class="h3 fw-bold mb-0 text-danger">฿<?= number_format($unpaid_balance) ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-custom p-3 bg-white border-start border-info border-4">
                <small class="text-muted fw-bold">รายการเช่า</small>
                <div class="h3 fw-bold mb-0 text-info"><?= $count_rentals ?></div>
            </div>
        </div>
    </div>

    <div class="card card-custom mb-4 border-0">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="fw-bold m-0 text-danger"><i class="bi bi-alarm-fill me-2"></i>รายการใกล้ถึงกำหนดคืน (60 นาที)</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ข้อมูลรถ</th>
                        <th>ผู้เช่า</th>
                        <th>เวลาที่เหลือ/สถานะ</th>
                        <th class="text-center">การจัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($alerts)): ?>
                        <tr><td colspan="4" class="text-center py-4 text-muted">ไม่มีการแจ้งเตือนในขณะนี้</td></tr>
                    <?php else: ?>
                        <?php foreach($alerts as $item): 
                            $diff = strtotime($item['return_date']) - time();
                            $is_late = $diff < 0;
                        ?>
                        <tr class="<?= $is_late ? 'table-danger' : '' ?>">
                            <td><div class="fw-bold"><?= $item['make'] ?></div><small class="text-muted"><?= $item['license_plate'] ?></small></td>
                            <td><?= $item['cust_name'] ?></td>
                            <td>
                                <?php if($is_late): 
                                    echo '<span class="badge bg-danger">เกินเวลา '.ceil(abs($diff)/3600).' ชม.</span>';
                                else:
                                    echo '<span class="badge bg-warning text-dark">เหลืออีก '.ceil($diff/60).' นาที</span>';
                                endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="return_car.php?id=<?= $item['rental_id'] ?>" class="btn btn-sm btn-success rounded-pill px-3">รับคืน</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card card-custom p-4 bg-white h-100">
                <h5 class="fw-bold mb-4">สถิติรายได้รายเดือน</h5>
                <canvas id="revenueChart" style="max-height: 300px;"></canvas>
            </div>
        </div>
        <a href="../view_receipt.php?id=<?= $item['rental_id'] ?>" target="_blank" class="btn btn-sm btn-outline-info">
    <i class="bi bi-printer"></i> ใบเสร็จ
</a>

        <div class="col-lg-4">
            <div class="card card-custom p-4 bg-dark text-white h-100">
                <h5 class="fw-bold mb-4">จัดการระบบ</h5>
                <div class="d-grid gap-3">
                    <a href="../cars.php" class="btn btn-outline-light btn-manage text-start"><i class="bi bi-car-front-fill me-2"></i> จัดการรถยนต์</a>
                    <a href="../customers/customers.php" class="btn btn-outline-light btn-manage text-start"><i class="bi bi-people-fill me-2"></i> ข้อมูลลูกค้า</a>
                    <a href="statistics.php" class="btn btn-primary btn-manage text-start"><i class="bi bi-bar-chart-fill me-2"></i> สถิติและบัญชี</a>
                    <hr>
                    <a href="../logout.php" class="btn btn-outline-danger btn-manage"><i class="bi bi-box-arrow-right me-2"></i> ออกจากระบบ</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('revenueChart');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($months) ?>,
        datasets: [{
            label: 'รายได้สุทธิ (฿)',
            data: <?= json_encode($revenues) ?>,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            fill: true, tension: 0.4
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
</script>

</body>
</html>