<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../login.php');
    exit;
}

require '../config.php';

try {
    // 1. ดึงข้อมูลสรุปรายได้แยกตามเดือน (ย้อนหลัง 12 เดือน)
    $sql = "
        SELECT 
            DATE_FORMAT(rental_date, '%Y-%m') as month_key,
            DATE_FORMAT(rental_date, '%M %Y') as month_name,
            SUM(total_cost) as monthly_total,
            COUNT(*) as booking_count
        FROM rentals 
        WHERE status = 'Completed'
        GROUP BY month_key
        ORDER BY month_key DESC
        LIMIT 12
    ";
    $stmt = $pdo->query($sql);
    $monthly_data = $stmt->fetchAll();

    // เตรียมข้อมูลสำหรับกราฟ (ต้องเรียงจากเก่าไปใหม่)
    $chart_labels = [];
    $chart_values = [];
    foreach (array_reverse($monthly_data) as $row) {
        $chart_labels[] = $row['month_name'];
        $chart_values[] = $row['monthly_total'];
    }

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สรุปรายได้รายเดือน | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body { font-family: 'Sarabun', sans-serif; background-color: #f8f9fc; }
        .card { border: none; border-radius: 15px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); }
        .chart-container { position: relative; height: 300px; width: 100%; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-dark"><i class="bi bi-bar-chart-line-fill text-primary me-2"></i>วิเคราะห์รายได้รายเดือน</h2>
    
    <a href="admin_stats.php" class="btn btn-primary rounded-pill btn-sm px-4">
        <i class="bi bi-list-check me-2"></i> ดูรายการทั้งหมด
    </a>
</div>

    <div class="row g-4">
        <div class="col-12">
            <div class="card p-4">
                <h5 class="fw-bold mb-4">แนวโน้มรายได้ 12 เดือนย้อนหลัง</h5>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card p-4">
                <h5 class="fw-bold mb-3">รายละเอียดรายเดือน</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>เดือน/ปี</th>
                                <th class="text-center">จำนวนการจอง</th>
                                <th class="text-end">รายได้รวม (บาท)</th>
                                <th class="text-end">ภาษี (7%)</th>
                                <th class="text-end">รายได้สุทธิ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($monthly_data as $row): 
                                $vat = $row['monthly_total'] * 0.07;
                                $net = $row['monthly_total'] - $vat;
                            ?>
                            <tr>
                                <td class="fw-bold"><?php echo $row['month_name']; ?></td>
                                <td class="text-center"><?php echo $row['booking_count']; ?> รายการ</td>
                                <td class="text-end"><?php echo number_format($row['monthly_total'], 2); ?></td>
                                <td class="text-end text-muted"><?php echo number_format($vat, 2); ?></td>
                                <td class="text-end text-success fw-bold"><?php echo number_format($net, 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // ตั้งค่ากราฟ Chart.js
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line', // เปลี่ยนเป็น 'bar' ได้ถ้าชอบกราฟแท่ง
        data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
                label: 'รายได้ต่อเดือน (บาท)',
                data: <?php echo json_encode($chart_values); ?>,
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                borderColor: 'rgba(78, 115, 223, 1)',
                borderWidth: 3,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>

</body>
</html>