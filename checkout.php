<?php
session_start();
include 'db.php';

// ต้องล็อกอินก่อน
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// ต้องมีสินค้าก่อน
if (empty($_SESSION['cart'])) {
    header("Location: cart_view.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// ดึงข้อมูลสินค้าจากฐานข้อมูล
$cart = $_SESSION['cart'];
$ids = implode(',', array_map('intval', array_keys($cart)));
$sql = "SELECT id, name, price, stock FROM products WHERE id IN ($ids)";
$result = mysqli_query($db, $sql);

$products = [];
$total_price = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $id = $row['id'];
    $qty = $cart[$id];
    $subtotal = $row['price'] * $qty;
    $total_price += $subtotal;
    $products[] = [
        'id' => $id,
        'name' => $row['name'],
        'price' => $row['price'],
        'quantity' => $qty,
        'subtotal' => $subtotal
    ];
}

// เมื่อกดยืนยันคำสั่งซื้อ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    mysqli_begin_transaction($db);
    try {
        // บันทึกคำสั่งซื้อหลัก
        $stmt = $db->prepare("INSERT INTO orders (user_id, total_price) VALUES (?, ?)");
        $stmt->bind_param("id", $user_id, $total_price);
        $stmt->execute();
        $order_id = $stmt->insert_id;

        // เพิ่มรายการสินค้าแต่ละชิ้น
        $stmt_item = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        foreach ($products as $p) {
            $stmt_item->bind_param("iiid", $order_id, $p['id'], $p['quantity'], $p['price']);
            $stmt_item->execute();

            // ตัด stock
            $new_stock = $p['quantity'] * -1;
            mysqli_query($db, "UPDATE products SET stock = stock + $new_stock WHERE id = {$p['id']}");
        }

        mysqli_commit($db);
        unset($_SESSION['cart']); // ล้างตะกร้า
        header("Location: my_orders.php?success=1");
        exit;
    } catch (Exception $e) {
        mysqli_rollback($db);
        die("เกิดข้อผิดพลาด: " . $e->getMessage());
    }
}
// ดึงข้อมูลหมวดหมู่ทั้งหมดสำหรับเมนู
$categories_result = mysqli_query($db, "SELECT * FROM categories ORDER BY name");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include 'frontend_nav.php'; ?>
    <div class="container">
        <h2>ยืนยันคำสั่งซื้อ</h2>

        <table>
            <thead>
                <tr>
                    <th>สินค้า</th>
                    <th>ราคา</th>
                    <th>จำนวน</th>
                    <th>รวม</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= number_format($p['price'], 2) ?> บาท</td>
                        <td><?= $p['quantity'] ?></td>
                        <td><?= number_format($p['subtotal'], 2) ?> บาท</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" style="text-align:right;">ยอดรวมทั้งหมด:</th>
                    <th><?= number_format($total_price, 2) ?> บาท</th>
                </tr>
            </tfoot>
        </table>

        <form method="post" style="margin-top:20px;">
            <a href="cart_view.php" class="btn btn-danger">กลับไปแก้ไขตะกร้า</a>
            <button type="submit" style="float: right;" class="btn btn-primary">ยืนยันคำสั่งซื้อ</button>
        </form>
    </div>
</body>
</html>