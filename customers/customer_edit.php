<?php
session_start();
if (!isset($_SESSION['loggedin'])) { header('Location:login.php'); exit; }
require '../config.php';
$customer_id = $_GET['id'] ?? null; $customer = null; $message = '';

if (!$customer_id) { die("ไม่พบรหัสลูกค้า"); }

// (โค้ด PHP ส่วน SELECT และ UPDATE เหมือนเดิม)
try {
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE customer_id = ?"); $stmt->execute([$customer_id]);
    $customer = $stmt->fetch();
    if (!$customer) { die("ไม่พบข้อมูลลูกค้าท่านนี้ในระบบ"); }
} catch (PDOException $e) { die("Error: " . $e->getMessage()); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name']; $address = $_POST['address']; $phone_number = $_POST['phone_number'];
    $email = $_POST['email']; $license_number = $_POST['license_number'];
    try {
        $sql = "UPDATE customers SET name=?, address=?, phone_number=?, email=?, license_number=? WHERE customer_id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $address, $phone_number, $email, $license_number, $customer_id]);
        $message = "แก้ไขข้อมูลลูกค้าสำเร็จ!";
        $customer = ['name' => $name, 'address' => $address, 'phone_number' => $phone_number, 'email' => $email, 'license_number' => $license_number, 'customer_id' => $customer_id];
    } catch (PDOException $e) {
        $message = "เกิดข้อผิดพลาดในการแก้ไขข้อมูล: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลลูกค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark"><div class="container"><a class="navbar-brand" href="index.php">🚗 Rent-A-Car System</a></div></nav>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">แก้ไขข้อมูลลูกค้า (ID: <?php echo $customer['customer_id']; ?>)</h5>
        </div>
        <div class="card-body">
            <p><a href="customers.php" class="btn btn-sm btn-secondary">กลับสู่หน้ารายการลูกค้า</a></p>
            <?php if ($message): ?><div class="alert alert-<?php echo strpos($message, 'ข้อผิดพลาด') !== false ? 'danger' : 'success'; ?>"><?php echo $message; ?></div><?php endif; ?>
            
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">ชื่อ-นามสกุล:</label><input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($customer['name']); ?>" required></div>
                    <div class="col-md-6"><label class="form-label">เบอร์โทรศัพท์:</label><input type="text" class="form-control" name="phone_number" value="<?php echo htmlspecialchars($customer['phone_number']); ?>" required></div>
                    <div class="col-12"><label class="form-label">ที่อยู่:</label><textarea name="address" class="form-control" rows="2" required><?php echo htmlspecialchars($customer['address']); ?></textarea></div>
                    <div class="col-md-6"><label class="form-label">อีเมล:</label><input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($customer['email']); ?>" required></div>
                    <div class="col-md-6"><label class="form-label">เลขที่ใบขับขี่:</label><input type="text" class="form-control" name="license_number" value="<?php echo htmlspecialchars($customer['license_number']); ?>" required></div>

                    <div class="col-12 mt-4"><button type="submit" class="btn btn-info text-white">บันทึกการแก้ไข</button></div>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>