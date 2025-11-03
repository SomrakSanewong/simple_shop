<?php
session_start();
include 'db.php';

$cart_products = [];
$total_price = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_map('intval', array_keys($_SESSION['cart']));
    $id_list = implode(',', $ids);

    $sql = "SELECT * FROM products WHERE id IN ($id_list)";
    $result = mysqli_query($db, $sql);

    while ($row = mysqli_fetch_assoc($result)) {
        $cart_products[] = $row;
    }
}

$categories_result = mysqli_query($db, "SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include 'frontend_nav.php'; ?>

    <div class="container">
        <h2>ตะกร้าสินค้า</h2>

        <?php if (!empty($_SESSION['cart_error'])): ?>
            <div style="color:red; margin-bottom:10px;">
                <?php 
                foreach ($_SESSION['cart_error'] as $msg) {
                    echo htmlspecialchars($msg) . "<br>";
                }
                unset($_SESSION['cart_error']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cart_products)): ?>
            <p>ตะกร้าว่างเปล่า</p>
            <p><a href="index.php" class="btn">กลับไปเลือกซื้อสินค้า</a></p>
        <?php else: ?>
            <form action="cart_process.php" method="post">
                <table>
                    <thead>
                        <tr>
                            <th>สินค้า</th>
                            <th>ราคา</th>
                            <th>จำนวน</th>
                            <th>รวม</th>
                            <th>ลบ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_products as $product): ?>
                            <?php
                            $id = $product['id'];
                            $quantity = (int)($_SESSION['cart'][$id] ?? 1);
                            $price = (float)$product['price'];
                            $stock = (int)$product['stock'];

                            if (!empty($_SESSION['cart_outofstock'][$id])) {
                                $subtotal = 0;
                            } else {
                                $subtotal = $price * $quantity;
                                $total_price += $subtotal;
                            }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo number_format($price, 2); ?></td>
                                <td>
                                    <?php if (!empty($_SESSION['cart_outofstock'][$id])): ?>
                                        <span style="color:red;">สินค้าหมด</span>
                                    <?php else: ?>
                                        <input type="number" name="quantities[<?php echo $id; ?>]"
                                               value="<?php echo $quantity; ?>" min="1"
                                               style="width:60px; text-align:center;">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo number_format($subtotal, 2); ?></td>
                                <td>
                                    <a href="cart_process.php?remove=<?php echo $id; ?>"
                                       class="btn btn-danger"
                                       onclick="return confirm('ลบสินค้านี้ออกจากตะกร้า?');">ลบ</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" style="text-align:center;">ยอดรวมทั้งหมด:</th>
                            <th><?php echo number_format($total_price, 2); ?></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>

                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                    <a href="cart_process.php?clear=1"
                       class="btn btn-danger"
                       onclick="return confirm('ล้างตะกร้าทั้งหมดหรือไม่?');">ล้างตะกร้า</a>

                    <div>
                        <button type="submit" name="update_cart" class="btn btn-secondary">อัปเดตจำนวนสินค้า</button>
                        <a href="checkout.php" class="btn">ดำเนินการสั่งซื้อ</a>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
