<?php
session_start();
include 'db.php';

// ต้องล็อกอินก่อน
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// ดึงคำสั่งซื้อของผู้ใช้
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $db->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();

$categories_result = mysqli_query($db, "SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ประวัติการสั่งซื้อ</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }

        th {
            background: #f5f5f5;
        }

        .btn-review {
            background-color: #007bff;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            text-decoration: none;
        }

        .btn-review:hover {
            background-color: #0056b3;
        }

        .reviewed {
            color: green;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php include 'frontend_nav.php'; ?>

    <div class="container">
        <h2>ประวัติการสั่งซื้อของคุณ</h2>

        <?php if (isset($_GET['success'])): ?>
            <p style="color:green;">สั่งซื้อสำเร็จแล้ว!</p>
        <?php endif; ?>

        <?php if ($orders->num_rows === 0): ?>
            <p>ยังไม่มีคำสั่งซื้อ</p>
        <?php else: ?>
            <?php while ($order = $orders->fetch_assoc()): ?>
                <div style="border:1px solid #ccc; padding:10px; margin-bottom:10px;">
                    <strong>คำสั่งซื้อ #<?= $order['id'] ?></strong><br>
                    วันที่: <?= $order['created_at'] ?><br>
                    ยอดรวม: <?= number_format($order['total_price'], 2) ?> บาท<br>
                    สถานะปัจจุบัน:<?= htmlspecialchars(string: $order['status']) ?><br><br>

                    <?php
                    // ดึงสินค้าในคำสั่งซื้อ
                    $items = mysqli_query($db, "
                    SELECT p.id AS product_id, p.name, oi.quantity, oi.price 
                    FROM order_items oi 
                    JOIN products p ON oi.product_id = p.id 
                    WHERE oi.order_id = {$order['id']}
                ");
                    ?>
                    <table>
                        <tr>
                            <th>สินค้า</th>
                            <th>จำนวน</th>
                            <th>ราคา</th>
                            <th>รวม</th>
                            <th>รีวิว</th>
                        </tr>
                        <?php while ($item = mysqli_fetch_assoc($items)): ?>
                            <?php
                            // ตรวจสอบว่าเคยรีวิวแล้วหรือยัง
                            $check_review = mysqli_prepare($db, "SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
                            mysqli_stmt_bind_param($check_review, "ii", $item['product_id'], $user_id);
                            mysqli_stmt_execute($check_review);
                            $review_result = mysqli_stmt_get_result($check_review);
                            $already_reviewed = mysqli_num_rows($review_result) > 0;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td><?= number_format($item['price'], 2) ?></td>
                                <td><?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                <td>
                                    <?php if ($already_reviewed): ?>
                                        <span class="reviewed">รีวิวแล้ว</span>
                                    <?php else: ?>
                                        <a href="product_view.php?id=<?= $item['product_id']; ?>" class="btn-review">รีวิวสินค้า</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</body>

</html>