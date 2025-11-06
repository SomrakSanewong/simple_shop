<?php
// index.php
include 'db.php'; 

// --- โค้ดสำหรับนับจำนวนสินค้าในตะกร้า (ยุบเหลือ 3 บรรทัด) ---
$cart_item_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) $cart_item_count = array_sum($_SESSION['cart']); 
// ------------------------------------

// ดึงข้อมูลหมวดหมู่และสินค้า
$categories_result = mysqli_query($db, "SELECT * FROM categories ORDER BY name");
$products_result = mysqli_query($db, "SELECT p.*, c.name AS category_name FROM products p JOIN categories c ON p.category_id = c.id ORDER BY p.name");
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>Simple Shop</title>
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
    endif;
    // -------------------------------------------------------- ?>

    <p class="cart-link-wrapper"><a href="cart_view.php" class="cart-link">🛒 View Cart (<?= $cart_item_count ?>)</a></p>

    <h2>All Products</h2>
    <div class="product-grid">
    <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
        <div class="product-card">
            <div class="product-info"> 
                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <h3><?= htmlspecialchars($product['name']) ?></h3>
                <p class="price"><?= number_format($product['price'], 2) ?> THB</p>
                <p><small>Category: <?= htmlspecialchars($product['category_name']) ?></small></p>
                <p><?= htmlspecialchars($product['description']) ?></p>
            </div>
            
            <div class="product-action">
                <?php if ($product['stock'] > 0): ?>
                    <form action="cart_process.php" method="POST">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <input type="hidden" name="quantity" value="1">
                        <input type="hidden" name="action" value="add_to_cart"> 
                        <button type="submit" class="btn btn-primary">🛒 Add</button>
                    </form>
                <?php else: ?>
                    <button type="button" class="btn btn-secondary" disabled>🚫 Out of Stock</button>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
    </div>
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