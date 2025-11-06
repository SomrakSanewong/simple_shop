<?php
include 'db.php'; 

// --- โค้ดสำหรับนับจำนวนสินค้าในตะกร้า ---
$cart_item_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) $cart_item_count = array_sum($_SESSION['cart']); 
// ------------------------------------

$cart_products = [];
$total_price = 0;

if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $id_list_string = implode(',', array_map('intval', $product_ids)); 
    $result = mysqli_query($db, "SELECT * FROM products WHERE id IN ($id_list_string)");
    
    $products_db = [];
    while ($row = mysqli_fetch_assoc($result)) $products_db[$row['id']] = $row;

    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        if (isset($products_db[$product_id])) {
            $product = $products_db[$product_id];
            $subtotal = $product['price'] * $quantity;
            $total_price += $subtotal;
            $cart_products[] = ['id' => $product_id, 'name' => $product['name'], 'image_url' => $product['image_url'], 'price' => $product['price'], 'stock' => $product['stock'], 'quantity' => $quantity, 'subtotal' => $subtotal];
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Shopping Cart</title>
<link rel="stylesheet" href="style.css?v=1.9"> 
</head>
<body>
<?php include 'frontend_nav.php'; ?>

<div class="container">

    <?php // --- โค้ดแสดง Alert (ยุบเหลือ 5 บรรทัด) ---
    if (isset($_SESSION['notification'])):
        list($type, $message) = explode('|', $_SESSION['notification'], 2);
        $alert_class = ($type == 'success') ? 'alert-success' : 'alert-danger'; ?>
        <div class="alert <?= $alert_class ?>" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <?php unset($_SESSION['notification']);
    endif; ?>

    <h2>Your Shopping Cart</h2>

    <?php if (empty($cart_products)): ?>
        <p>Your cart is empty.</p>
        <a href="index.php" class="btn btn-primary">Continue Shopping</a>
    <?php else: ?>
        
        <form action="cart_process.php" method="POST">
            <table>
                <thead>
                    <tr><th>Product</th><th>Price</th><th>Quantity (Stock)</th><th>Subtotal</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_products as $item): ?>
                    <tr>
                        <td>
                            <img src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-thumb">
                            <br> <?= htmlspecialchars($item['name']) ?>
                        </td>
                        <td><?= number_format($item['price'], 2) ?></td>
                        <td>
                            <input type="number" name="quantities[<?= $item['id'] ?>]" value="<?= $item['quantity'] ?>" min="0" max="<?= $item['stock'] ?>" class="quantity-input" style="width: 80px;">
                            <small>(Max: <?= $item['stock'] ?>)</small>
                        </td>
                        <td><?= number_format($item['subtotal'], 2) ?></td>
                        <td><a href="cart_process.php?action=remove_from_cart&id=<?= $item['id'] ?>" class="delete" onclick="return confirm('Are you sure you want to remove this item?');">Delete</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-summary">
                <h3>Total Price: <?= number_format($total_price, 2) ?> THB</h3>
                
                <button type="submit" name="action" value="update_cart" class="btn btn-secondary">Update Cart</button>
                <button type="submit" name="action" value="proceed_to_checkout" class="btn btn-primary btn-checkout">Proceed to Checkout</button>
            </div>
        </form>

    <?php endif; ?>

</div>

<script>
// Script นี้ใช้สำหรับปิด Alert 
var closeButtons = document.querySelectorAll('[data-dismiss="alert"]');
closeButtons.forEach(function(button) {
    button.addEventListener('click', function() {
        var alertElement = this.closest('.alert');
        if (alertElement) alertElement.style.display = 'none';
    });
});
</script>
</body>
</html>