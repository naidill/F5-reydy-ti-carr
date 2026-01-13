<?php
session_start();
if (!isset($_SESSION['loggedin'])) { header('Location:login.php'); exit; }
require '../config.php';

// ดึงข้อมูลลูกค้า พร้อมยอดรวมที่ยังไม่จ่าย (Pending) และจำนวนรถที่ยังไม่คืน (Active)
$sql = "SELECT c.*, 
        (SELECT SUM(total_cost) FROM rentals WHERE customer_id = c.customer_id AND status = 'Pending') as unpaid_amount,
        (SELECT COUNT(*) FROM rentals WHERE customer_id = c.customer_id AND status = 'Active') as active_rentals
        FROM customers c ORDER BY c.customer_id DESC";
$customers = $pdo->query($sql)->fetchAll();
?>

<?php foreach ($customers as $row): ?>
<tr>
    <td><?php echo htmlspecialchars($row['name']); ?></td>
    <td class="text-center">
        <?php if ($row['unpaid_amount'] > 0): ?>
            <span class="badge bg-danger">ค้างชำระ ฿<?php echo number_format($row['unpaid_amount'], 2); ?></span>
        <?php else: ?>
            <span class="badge bg-success">ไม่มีค้าง</span>
        <?php endif; ?>
    </td>
    <td class="text-center">
        <?php if ($row['is_active'] == 1): ?>
            <span class="text-success">ปกติ</span>
        <?php else: ?>
            <span class="text-danger">ถูกระงับ</span>
        <?php endif; ?>
    </td>
    <td class="text-end">
        <div class="btn-group">
            <a href="toggle_status.php?id=<?php echo $row['customer_id']; ?>&action=<?php echo ($row['is_active'] == 1 ? 'ban' : 'activate'); ?>" 
               class="btn btn-sm <?php echo ($row['is_active'] == 1 ? 'btn-outline-danger' : 'btn-outline-success'); ?>">
               <?php echo ($row['is_active'] == 1 ? 'ระงับบัญชี' : 'ปลดระงับ'); ?>
            </a>
            <a href="customer_delete.php?id=<?php echo $row['customer_id']; ?>" class="btn btn-sm btn-light text-danger" onclick="return confirm('ลบถาวร?')">ลบ</a>
        </div>
    </td>
</tr>
<?php endforeach; ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการข้อมูลลูกค้า | Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body { font-family: 'Sarabun', sans-serif; background-color: #f4f7f6; }
        .navbar { background: linear-gradient(90deg, #1a2a6c, #b21f1f); }
        .main-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; }
        .table thead { background-color: #f8f9fa; }
        .table thead th { border-bottom: none; color: #6c757d; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; padding: 15px; }
        .customer-name { font-weight: 600; color: #2c3e50; }
        .icon-box-small { width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; margin-right: 10px; }
        .btn-action { border-radius: 10px; transition: all 0.2s; }
        .btn-action:hover { transform: scale(1.05); }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-people-fill me-2"></i> CUSTOMER MGMT</a>
        <div class="ms-auto">
            <a href="../index.php" class="btn btn-outline-light btn-sm rounded-pill px-3">กลับหน้าหลัก</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">รายชื่อลูกค้าทั้งหมด</h2>
            <p class="text-muted">ตรวจสอบข้อมูลสมาชิกและเลขที่ใบขับขี่ของลูกค้า</p>
        </div>
        <a href="customer_add.php" class="btn btn-success btn-action shadow-sm px-4">
            <i class="bi bi-person-plus-fill me-2"></i> เพิ่มลูกค้าใหม่
        </a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger border-0 shadow-sm rounded-4"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="card main-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">ชื่อ-นามสกุล</th>
                            <th>ข้อมูลติดต่อ</th>
                            <th>เลขที่ใบขับขี่</th>
                            <th class="text-center pe-4">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="icon-box-small bg-primary-subtle text-primary">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <span class="customer-name"><?php echo htmlspecialchars($customer['name']); ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="small"><i class="bi bi-envelope text-muted me-2"></i><?php echo htmlspecialchars($customer['email']); ?></div>
                                <div class="small"><i class="bi bi-telephone text-muted me-2"></i><?php echo htmlspecialchars($customer['phone_number']); ?></div>
                            </td>
                            <td>
                                <code class="text-dark fw-bold"><?php echo htmlspecialchars($customer['license_number']); ?></code>
                            </td>
                            <td class="text-center pe-4">
                                <div class="btn-group">
                                    <a href="customer_edit.php?id=<?php echo $customer['customer_id']; ?>" class="btn btn-outline-info btn-sm btn-action me-1">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="customer_delete.php?id=<?php echo $customer['customer_id']; ?>" 
                                       onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบลูกค้าคุณ <?php echo $customer['name']; ?>?')" 
                                       class="btn btn-outline-danger btn-sm btn-action">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($customers)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <i class="bi bi-people text-muted" style="font-size: 3rem; opacity: 0.3;"></i>
                                <p class="text-muted mt-3">ยังไม่มีข้อมูลลูกค้าในระบบ</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<footer class="mt-5 py-4 text-center text-muted small">
    &copy; 2024 Rent-A-Car System | Management Console
</footer>

</body>
</html>