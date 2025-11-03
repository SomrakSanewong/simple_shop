<?php
session_start();
include 'auth.php'; // ตรวจสอบสิทธิ์ก่อน

// ตรวจสอบว่าได้รับ order id
if (!isset($_GET['id'])) {
    die("ไม่พบรหัสคำสั่งซื้อ");
}
$order_id = (int)$_GET['id'];

// ดึงข้อมูลคำสั่งซื้อหลัก
$order_query = $db->prepare("
    SELECT o.*, u.fullname, u.email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
");
$order_query->bind_param("i", $order_id);
$order_query->execute();
$order = $order_query->get_result()->fetch_assoc();

if (!$order) {
    die("ไม่พบคำสั่งซื้อ");
}

// อัปเดตสถานะ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $new_status = $_POST['status'];
    $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    header("Location: order_view.php?id=$order_id&updated=1");
    exit;
}

// ดึงรายการสินค้าในคำสั่งซื้อนี้
$items = mysqli_query($db, "
    SELECT p.name, oi.quantity, oi.price
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = $order_id
");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายละเอียดคำสั่งซื้อ #<?= $order['id'] ?></title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <?php include 'admin_nav.php'; ?>
    <div class="container">
        <h2>รายละเอียดคำสั่งซื้อ</h2>

        <?php if (isset($_GET['updated'])): ?>
            <p style="color:green;">อัปเดตสถานะเรียบร้อยแล้ว</p>
        <?php endif; ?>

        <p><strong>ลูกค้า:</strong> <?= htmlspecialchars($order['fullname']) ?> (<?= htmlspecialchars($order['email']) ?>)</p>
        <p><strong>วันที่สั่งซื้อ:</strong> <?= $order['created_at'] ?></p>
        <p><strong>ยอดรวม:</strong> <?= number_format($order['total_price'], 2) ?> บาท</p>
        <p><strong>สถานะปัจจุบัน:</strong> <?= htmlspecialchars($order['status']) ?></p>

        <h3>รายการสินค้า</h3>
        <table border="1" cellpadding="6" cellspacing="0" width="100%">
            <tr>
                <th>สินค้า</th>
                <th>จำนวน</th>
                <th>ราคาต่อหน่วย</th>
                <th>รวม</th>
            </tr>
            <?php while ($item = mysqli_fetch_assoc($items)): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= number_format($item['price'], 2) ?></td>
                    <td><?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>

        <h3 style="margin-top:20px;">อัปเดตสถานะคำสั่งซื้อ</h3>
        <form method="post">
            <select name="status" required>
                <option value="Pending" <?= $order['status'] === 'Pending' ? 'selected' : '' ?>>Pending (รอดำเนินการ)</option>
                <option value="Processing" <?= $order['status'] === 'Processing' ? 'selected' : '' ?>>Processing (กำลังจัดการ)</option>
                <option value="Shipped" <?= $order['status'] === 'Shipped' ? 'selected' : '' ?>>Shipped (จัดส่งแล้ว)</option>
                <option value="Cancelled" <?= $order['status'] === 'Cancelled' ? 'selected' : '' ?>>Cancelled (ยกเลิก)</option>
            </select>
            <button type="submit" class="btn btn-primary">อัปเดตสถานะ</button>
        </form>
    </div>
</body>
</html>
