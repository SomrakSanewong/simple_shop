<?php
// category.php
include 'db.php'; 

// --- โค้ดสำหรับนับจำนวนสินค้าในตะกร้า ---
$cart_item_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) $cart_item_count = array_sum($_SESSION['cart']); 
// ------------------------------------

// ตรวจสอบ id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("location: index.php");
    exit;
}
$category_id = (int)$_GET['id'];

// ดึงข้อมูลหมวดหมู่
$categories_result = mysqli_query($db, "SELECT * FROM categories ORDER BY name");

// ดึงข้อมูลหมวดหมู่ที่เลือก (ใช้ Prepared Statement เดิม)
$cat_stmt = mysqli_prepare($db, "SELECT name FROM categories WHERE id = ?");
mysqli_stmt_bind_param($cat_stmt, "i", $category_id);
mysqli_stmt_execute($cat_stmt);
$category_info = mysqli_fetch_assoc(mysqli_stmt_get_result($cat_stmt));

if (!$category_info) {
    header("location: index.php");
    exit;
}
$category_name = $category_info['name'];

// ดึงข้อมูลสินค้า
$prod_stmt = mysqli_prepare($db, "SELECT * FROM products WHERE category_id = ? ORDER BY name");
mysqli_stmt_bind_param($prod_stmt, "i", $category_id);
mysqli_stmt_execute($prod_stmt);
$products_result = mysqli_stmt_get_result($prod_stmt);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($category_name) ?> - Simple Shop</title>
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
    
    <p class="cart-link-wrapper"><a href="cart_view.php" class="cart-link">🛒 View Cart (<?= $cart_item_count ?>)</a></p>
    <h2>Products in: <?= htmlspecialchars($category_name) ?></h2>
    <div class="product-grid">
    <?php if (mysqli_num_rows($products_result) > 0): ?>
        <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
        <div class="product-card">
            <div class="product-info">
                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <h3><?= htmlspecialchars($product['name']) ?></h3>
                <p class="price"><?= number_format($product['price'], 2) ?> THB</p>
                <p><?= htmlspecialchars($product['description']) ?></p>
            </div>

            <div class="product-action">
                <form action="cart_process.php" method="POST">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <input type="hidden" name="quantity" value="1">
                    <input type="hidden" name="action" value="add_to_cart"> 
                    <button type="submit" class="btn btn-primary">🛒 Add</button>
                </form>
            </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No products found in this category.</p>
    <?php endif; ?>
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