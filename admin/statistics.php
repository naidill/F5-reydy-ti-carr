<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../login.php');
    exit;
}
require '../config.php';

try {
    // 1. ดึงข้อมูลสรุปตัวเลข
    $count_cars = $pdo->query("SELECT COUNT(*) FROM cars")->fetchColumn();
    $count_rentals = $pdo->query("SELECT COUNT(*) FROM rentals WHERE status != 'Canceled'")->fetchColumn();
    $total_revenue = $pdo->query("SELECT SUM(total_cost) FROM rentals WHERE status = 'Completed'")->fetchColumn() ?? 0;
    $unpaid_balance = $pdo->query("SELECT SUM(total_cost) FROM rentals WHERE status = 'Pending'")->fetchColumn() ?? 0;

    // 2. ดึงข้อมูลรายได้รายเดือน (ปรับ SQL ให้ Sort แม่นยำขึ้น)
    $monthly_data = $pdo->query("
        SELECT 
            DATE_FORMAT(rental_date, '%m') as m_num,
            DATE_FORMAT(rental_date, '%b') as m_name, 
            SUM(total_cost) as total 
        FROM rentals 
        WHERE status = 'Completed'
        GROUP BY m_num, m_name 
        ORDER BY m_num ASC
    ")->fetchAll();

    $months = [];
    $revenues = [];
    
    if (empty($monthly_data)) {
        // กรณีไม่มีข้อมูลเลย ให้ใส่ค่าเริ่มต้นไว้ป้องกันกราฟพัง
        $months = ['No Data'];
        $revenues = [0];
    } else {
        foreach($monthly_data as $data) {
            $months[] = $data['m_name'];
            $revenues[] = (float)$data['total'];
        }
    }
} catch (PDOException $e) { 
    die("Database Error: " . $e->getMessage()); 
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | ระบบจัดการเช่ารถ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body { font-family: 'Sarabun', sans-serif; background-color: #f8f9fc; color: #333; }
        .card-stat { border: none; border-radius: 15px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); transition: 0.3s; background: #fff; }
        .card-stat:hover { transform: translateY(-3px); box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.15); }
        .icon-box { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
        .main-content { padding-top: 30px; }
        .btn-manage { border-radius: 10px; padding: 12px; text-decoration: none; transition: 0.2s; }
        .chart-area { min-height: 300px; }
    </style>
</head>
<body>

<div class="container main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0"><i class="bi bi-speedometer2 me-2"></i>แผงควบคุมระบบ</h3>
        <span class="badge bg-white text-dark shadow-sm p-2 px-3 rounded-pill">
            <i class="bi bi-clock me-1"></i> <?= date('d/m/Y H:i') ?>
        </span>
    </div>
    <div class="mb-3">
    <a href="admin_dashboard.php" class="btn btn-outline-secondary btn-sm rounded-pill">
        <i class="bi bi-arrow-left"></i> กลับไปแผงควบคุม
    </a>
</div>
    
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card card-stat p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-primary text-white me-3"><i class="bi bi-car-front"></i></div>
                    <div>
                        <div class="text-muted small">รถทั้งหมด</div>
                        <div class="h5 fw-bold mb-0"><?= number_format($count_cars) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-stat p-3 text-success">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-success text-white me-3"><i class="bi bi-currency-dollar"></i></div>
                    <div>
                        <div class="text-muted small text-success">รายได้รวม</div>
                        <div class="h5 fw-bold mb-0">฿<?= number_format($total_revenue) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-stat p-3 text-danger">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-danger text-white me-3"><i class="bi bi-exclamation-triangle"></i></div>
                    <div>
                        <div class="text-muted small text-danger">ยอดค้างชำระ</div>
                        <div class="h5 fw-bold mb-0">฿<?= number_format($unpaid_balance) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-stat p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-box bg-info text-white me-3"><i class="bi bi-clipboard-data"></i></div>
                    <div>
                        <div class="text-muted small">ยอดเช่ารถ</div>
                        <div class="h5 fw-bold mb-0"><?= number_format($count_rentals) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card card-stat p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold m-0 text-primary">สถิติรายได้ปีนี้</h5>
                    <i class="bi bi-three-dots-vertical text-muted"></i>
                </div>
                <div class="chart-area">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-stat p-4 h-100 bg-white">
                <h5 class="fw-bold mb-4">เมนูจัดการระบบ</h5>
                <div class="d-grid gap-3">
                    <a href="../cars.php" class="btn btn-light btn-manage text-start border shadow-sm">
                        <i class="bi bi-car-front-fill me-2 text-primary"></i> จัดการรถยนต์
                    </a>
                    <a href="../customers/customers.php" class="btn btn-light btn-manage text-start border shadow-sm">
                        <i class="bi bi-people-fill me-2 text-success"></i> ข้อมูลลูกค้าและการแบน
                    </a>
                    <a href="admin_stats.php" class="btn btn-light btn-manage text-start border shadow-sm">
                        <i class="bi bi-file-earmark-bar-graph-fill me-2 text-warning"></i> รายงานบัญชี/ภาษี
                    </a>
                    <hr>
                    <a href="../logout.php" class="btn btn-outline-danger btn-manage text-center fw-bold">
                        <i class="bi bi-box-arrow-right me-2"></i> ออกจากระบบ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($months) ?>,
        datasets: [{
            label: 'รายได้ (บาท)',
            data: <?= json_encode($revenues) ?>,
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4, // ความโค้งของเส้น
            pointBackgroundColor: '#4e73df',
            pointBorderColor: '#fff',
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { 
            legend: { display: false } 
        },
        scales: {
            y: { 
                beginAtZero: true,
                grid: { color: '#f1f1f1' }
            },
            x: { 
                grid: { display: false } 
            }
        }
    }
});
</script>

</body>
</html>