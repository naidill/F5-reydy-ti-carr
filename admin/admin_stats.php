<?php
session_start();
// ตรวจสอบการล็อกอิน Admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: ../login.php');
    exit;
}

require '../config.php';

try {
    // 1. ดึงรายได้รวมและจำนวนครั้งที่เช่า
    $stmt = $pdo->query("SELECT SUM(total_cost) as total, COUNT(*) as count FROM rentals WHERE status = 'Completed'");
    $summary = $stmt->fetch();
    
    $total_revenue = $summary['total'] ?? 0;
    $total_count = $summary['count'] ?? 0;
    
    // คำนวณภาษี (ตัวอย่าง VAT 7%)
    $vat_rate = 0.07;
    $net_amount = $total_revenue / (1 + $vat_rate);
    $vat_amount = $total_revenue - $net_amount;

    // 2. ดึงรายละเอียดการเช่าทั้งหมดเพื่อประกอบการลงบัญชี
    $stmt_details = $pdo->query("
        SELECT r.rental_id, r.rental_date, r.return_date, r.total_cost, c.make, c.model, cust.name as customer_name
        FROM rentals r
        JOIN cars c ON r.car_id = c.car_id
        JOIN customers cust ON r.customer_id = cust.customer_id
        WHERE r.status = 'Completed'
        ORDER BY r.rental_date DESC
    ");
    $all_rentals = $stmt_details->fetchAll();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานสรุปรายได้และภาษี | Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body { font-family: 'Sarabun', sans-serif; background-color: #f8f9fc; }
        .report-card { border: none; border-radius: 15px; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); }
        .stat-card { border-left: 4px solid #4e73df; border-radius: 10px; }
        .table thead { background-color: #f1f3f9; }
        @media print {
            .btn-print, .nav-back { display: none !important; }
            body { background-color: white; }
            .report-card { box-shadow: none; border: 1px solid #eee; }
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
    <a href="admin_dashboard.php" class="text-decoration-none nav-back text-muted small">
        <i class="bi bi-arrow-left"></i> กลับไปหน้าหลัก
    </a>
    <div class="d-flex gap-2">
        <a href="admin_revenue_monthly.php" class="btn btn-outline-primary rounded-pill btn-print">
            <i class="bi bi-graph-up-arrow me-2"></i> วิเคราะห์รายเดือน
        </a>
        
        <button onclick="window.print()" class="btn btn-primary btn-print rounded-pill shadow-sm">
            <i class="bi bi-printer me-2"></i> พิมพ์รายงาน (PDF)
        </button>
    </div>
</div>

    <div class="card report-card p-4 p-md-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold">รายงานสรุปรายได้ (ยอดขาย)</h2>
            <p class="text-muted">ข้อมูล ณ วันที่: <?php echo date('d/m/Y H:i'); ?></p>
            <hr class="w-25 mx-auto">
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card stat-card p-3 h-100 bg-white shadow-sm">
                    <div class="small text-muted fw-bold text-uppercase">รายได้รวมทั้งหมด (Inc. VAT)</div>
                    <div class="h3 fw-bold text-primary mt-1">฿<?php echo number_format($total_revenue, 2); ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card p-3 h-100 bg-white shadow-sm" style="border-left-color: #1cc88a;">
                    <div class="small text-muted fw-bold text-uppercase">มูลค่าสุทธิ (Before VAT)</div>
                    <div class="h3 fw-bold text-success mt-1">฿<?php echo number_format($net_amount, 2); ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card p-3 h-100 bg-white shadow-sm" style="border-left-color: #f6c23e;">
                    <div class="small text-muted fw-bold text-uppercase">ภาษีมูลค่าเพิ่ม (VAT 7%)</div>
                    <div class="h3 fw-bold text-warning mt-1">฿<?php echo number_format($vat_amount, 2); ?></div>
                </div>
            </div>
        </div>

        <h5 class="fw-bold mb-3"><i class="bi bi-list-ul me-2"></i>รายละเอียดการทำรายการ</h5>
        <div class="table-responsive">
            <table class="table table-hover border">
                <thead>
                    <tr class="small text-muted">
                        <th>ID</th>
                        <th>วันที่เช่า</th>
                        <th>ชื่อลูกค้า</th>
                        <th>รถยนต์</th>
                        <th class="text-end">ยอดรวม (บาท)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($all_rentals as $row): ?>
                    <tr>
                        <td class="small fw-bold">#<?php echo $row['rental_id']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($row['rental_date'])); ?></td>
                        <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['make'] . ' ' . $row['model']); ?></td>
                        <td class="text-end fw-bold text-dark"><?php echo number_format($row['total_cost'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td colspan="4" class="text-end">รวมยอดขายสุทธิ</td>
                        <td class="text-end text-primary">฿<?php echo number_format($total_revenue, 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-5 pt-4 border-top text-center text-muted small">
            <p>เอกสารนี้เป็นรายงานสรุปข้อมูลจากระบบจัดการเช่ารถยนต์ (Rent-A-Car System)</p>
            <p>ผู้พิมพ์รายงาน: <?php echo $_SESSION['username']; ?></p>
        </div>
    </div>
</div>

</body>
</html>