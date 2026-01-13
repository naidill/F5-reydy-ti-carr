<?php
session_start();
if (!isset($_SESSION['customer_loggedin'])) {
    header('Location: customer_login.php');
    exit;
}

// รับค่าจากหน้า car_rent.php (ผ่าน URL หรือ Session ก็ได้)
$rental_id = $_GET['rental_id'] ?? 'REC-'.rand(1000,9999);
$amount = $_GET['amount'] ?? 0;
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ชำระเงิน | Rent-A-Car</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap');
        body { font-family: 'Sarabun', sans-serif; background-color: #f4f7f6; }
        .payment-card { border: none; border-radius: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .method-card { cursor: pointer; border: 2px solid #f0f0f0; transition: all 0.2s; border-radius: 15px; }
        .method-card:hover { border-color: #0d6efd; background-color: #f8fbff; }
        .method-card.active { border-color: #0d6efd; background-color: #f8fbff; }
        .qr-placeholder { background: white; border: 1px solid #eee; width: 200px; height: 200px; margin: 0 auto; display: flex; align-items: center; justify-content: center; }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card payment-card p-4">
                <div class="text-center mb-4">
                    <h2 class="fw-bold">ชำระเงิน</h2>
                    <p class="text-muted small">รหัสการจอง: <span class="fw-bold"><?php echo $rental_id; ?></span></p>
                    <div class="display-5 fw-bold text-primary mt-2">฿<?php echo number_format($amount, 2); ?></div>
                </div>

                <h6 class="fw-bold mb-3">เลือกช่องทางการชำระเงิน</h6>
                
                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="method-card p-3 text-center active" onclick="selectMethod('credit')">
                            <i class="bi bi-credit-card-2-front fs-2 text-primary"></i>
                            <div class="small fw-bold mt-1">บัตรเครดิต</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="method-card p-3 text-center" onclick="selectMethod('qr')">
                            <i class="bi bi-qr-code-scan fs-2 text-primary"></i>
                            <div class="small fw-bold mt-1">PromptPay</div>
                        </div>
                    </div>
                </div>

                <div id="credit-form">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">ชื่อบนบัตร</label>
                        <input type="text" class="form-control" placeholder="ชื่อ-นามสกุล">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">หมายเลขบัตร</label>
                        <input type="text" class="form-control" placeholder="0000 0000 0000 0000">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-muted">วันหมดอายุ</label>
                            <input type="text" class="form-control" placeholder="MM/YY">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label small fw-bold text-muted">CVV</label>
                            <input type="text" class="form-control" placeholder="123">
                        </div>
                    </div>
                </div>

                <div id="qr-form" class="d-none text-center">
                    <div class="qr-placeholder mb-3">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=PromptPayAmount<?php echo $amount; ?>" alt="QR Code">
                    </div>
                    <p class="small text-muted">กรุณาสแกน QR Code เพื่อชำระเงินภายใน 15 นาที</p>
                </div>

                <button class="btn btn-primary w-100 btn-lg rounded-pill mt-4 fw-bold py-3" onclick="processPayment()">
                    ยืนยันการชำระเงิน
                </button>
                
                <a href="customer_dashboard.php" class="btn btn-link w-100 text-muted text-decoration-none mt-2 small">ยกเลิกและกลับไปหน้าหลัก</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="successModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 20px;">
            <div class="modal-body text-center p-5">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                <h3 class="fw-bold mt-4">ชำระเงินสำเร็จ!</h3>
                <p class="text-muted">ระบบได้รับการชำระเงินของคุณเรียบร้อยแล้ว เตรียมตัวออกเดินทางได้เลย</p>
                <button class="btn btn-success rounded-pill px-5 py-2 mt-3" onclick="window.location.href='customer_history.php'">
                    ดูประวัติการจอง
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function selectMethod(method) {
        // เปลี่ยน UI สลับระหว่างบัตรเครดิตกับ QR
        document.querySelectorAll('.method-card').forEach(el => el.classList.remove('active'));
        if (method === 'credit') {
            document.getElementById('credit-form').classList.remove('d-none');
            document.getElementById('qr-form').classList.add('d-none');
            event.currentTarget.classList.add('active');
        } else {
            document.getElementById('credit-form').classList.add('d-none');
            document.getElementById('qr-form').classList.remove('d-none');
            event.currentTarget.classList.add('active');
        }
    }

    function processPayment() {
        // จำลองการโหลดชำระเงิน
        const btn = event.target;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> กำลังตรวจสอบ...';
        
        setTimeout(() => {
            const myModal = new bootstrap.Modal(document.getElementById('successModal'));
            myModal.show();
        }, 2000);
    }
</script>

</body>
</html>