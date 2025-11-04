<?php
include 'auth.php';

// สรุปยอด
$total_sales = mysqli_fetch_assoc(mysqli_query($db, "SELECT SUM(total_price) AS total_sales FROM orders WHERE status = 'Shipped'"))['total_sales'] ?? 0;
$new_orders = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(id) AS count_new FROM orders WHERE status = 'Pending'"))['count_new'] ?? 0;
$total_users = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(id) AS total_users FROM users"))['total_users'] ?? 0;
$total_products = mysqli_fetch_assoc(mysqli_query($db, "SELECT COUNT(id) AS total_products FROM products"))['total_products'] ?? 0;

// สินค้าใกล้หมด
$low_stock_result = mysqli_query($db, "
    SELECT id, name, stock, price 
    FROM products 
    WHERE stock < 10 
    ORDER BY stock ASC 
    LIMIT 10
");

// ออเดอร์ล่าสุด
$recent_orders_result = mysqli_query($db, "
    SELECT o.id, u.email AS user_email, o.total_price, o.status, o.created_at
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.created_at DESC
    LIMIT 5
");

// สินคเ้าขายดี
$top_selling_result = mysqli_query($db, "
    SELECT 
        p.id,
        p.name,
        SUM(oi.quantity) AS total_sold,
        p.price,
        (SUM(oi.quantity) * p.price) AS total_revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>
    <?php include 'admin_nav.php'; ?>

    <div class="container">
        <h2>ภาพรวมระบบร้านค้า</h2>

        <!-- การ์ดสรุป -->
        <div class="stats">
            <div class="card">
                <h3>ยอดขายรวม</h3>
                <p><?= number_format($total_sales, 2); ?> บาท</p>
            </div>
            <div class="card">
                <h3>คำสั่งซื้อใหม่</h3>
                <p><?= $new_orders; ?> รายการ</p>
            </div>
            <div class="card">
                <h3>สมาชิกทั้งหมด</h3>
                <p><?= $total_users; ?> คน</p>
            </div>
            <div class="card">
                <h3>สินค้าทั้งหมด</h3>
                <p><?= $total_products; ?> รายการ</p>
            </div>
        </div>
        
        <h3 style="margin-top:25px;">สินค้าขายดี</h3>
        <table>
            <thead>
                <tr>
                    <th>อันดับ</th>
                    <th>ชื่อสินค้า</th>
                    <th>จำนวนที่ขาย</th>
                    <th>ราคาต่อหน่วย</th>
                    <th>ยอดขายรวม</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $rank = 1;
                while ($row = mysqli_fetch_assoc($top_selling_result)): ?>
                    <tr>
                        <td><?= $rank++; ?></td>
                        <td><?= htmlspecialchars($row['name']); ?></td>
                        <td><?= $row['total_sold']; ?></td>
                        <td><?= number_format($row['price'], 2); ?></td>
                        <td><?= number_format($row['total_revenue'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!--สินค้าใกล้หมด -->
        <h3>สินค้าใกล้หมด</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ชื่อสินค้า</th>
                    <th>สต็อก</th>
                    <th>ราคา</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($p = mysqli_fetch_assoc($low_stock_result)): ?>
                    <tr>
                        <td><?= $p['id']; ?></td>
                        <td><?= htmlspecialchars($p['name']); ?></td>
                        <td><?= $p['stock']; ?></td>
                        <td><?= number_format($p['price'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!--ออเดอร์ล่าสุด -->
        <h3 style="margin-top:25px;">ออเดอร์ล่าสุด</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ผู้สั่งซื้อ</th>
                    <th>ยอดรวม</th>
                    <th>สถานะ</th>
                    <th>วันที่</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($o = mysqli_fetch_assoc($recent_orders_result)): ?>
                    <tr>
                        <td><?= $o['id']; ?></td>
                        <td><?= htmlspecialchars($o['user_email']); ?></td>
                        <td><?= number_format($o['total_price'], 2); ?></td>
                        <td><?= htmlspecialchars($o['status']); ?></td>
                        <td><?= $o['created_at']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>