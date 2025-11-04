<?php
session_start();
include 'auth.php'; // ตรวจสอบสิทธิ์ก่อน

// ตรวจสอบสิทธิ์ (คุณสามารถเพิ่มระบบตรวจว่าเป็น admin ได้ภายหลัง)
$orders = mysqli_query($db, "
    SELECT o.*, u.fullname 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายการคำสั่งซื้อทั้งหมด</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include 'admin_nav.php'; ?>
    <div class="container">
        <h2>รายการคำสั่งซื้อทั้งหมด</h2>
        <table border="1" cellpadding="6" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ชื่อลูกค้า</th>
                    <th>ยอดรวม (บาท)</th>
                    <th>สถานะ</th>
                    <th>วันที่สั่ง</th>
                    <th>ดูรายละเอียด</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = mysqli_fetch_assoc($orders)): ?>
                    <tr>
                        <td><?= $order['id'] ?></td>
                        <td><?= htmlspecialchars($order['fullname']) ?></td>
                        <td><?= number_format($order['total_price'], 2) ?></td>
                        <td><?= htmlspecialchars($order['status']) ?></td>
                        <td><?= $order['created_at'] ?></td>
                        <td>
                            <a href="order_view.php?id=<?= $order['id'] ?>" class="btn btn-primary">ดูรายละเอียด</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
