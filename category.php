<?php
include 'db.php';

// ตรวจสอบว่ามี id ของหมวดหมู่ส่งมาหรือไม่
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("location: index.php");
    exit;
}
$category_id = (int)$_GET['id'];

// ดึงข้อมูลหมวดหมู่ทั้งหมดสำหรับเมนู
$categories_result = mysqli_query($db, "SELECT * FROM categories ORDER BY name");

// ดึงชื่อหมวดหมู่ที่เลือก
$cat_stmt = mysqli_prepare($db, "SELECT name FROM categories WHERE id = ?");
mysqli_stmt_bind_param($cat_stmt, "i", $category_id);
mysqli_stmt_execute($cat_stmt);
$category_info_result = mysqli_stmt_get_result($cat_stmt);
$category_info = mysqli_fetch_assoc($category_info_result);
if (!$category_info) {
    header("location: index.php");
    exit;
}
$category_name = $category_info['name'];

// รับค่าการจัดเรียง
$sort = $_GET['sort'] ?? '';
$order_sql = "ORDER BY p.name ASC";
switch ($sort) {
    case 'price_asc':
        $order_sql = "ORDER BY p.price ASC";
        break;
    case 'price_desc':
        $order_sql = "ORDER BY p.price DESC";
        break;
    case 'name_desc':
        $order_sql = "ORDER BY p.name DESC";
        break;
    case 'name_asc':
    default:
        $order_sql = "ORDER BY p.name ASC";
        break;
}

//ดึงข้อมูลสินค้าในหมวดหมู่ พร้อมคะแนนเฉลี่ย
$sql = "
    SELECT 
        p.*, 
        ROUND(AVG(r.rating), 1) AS avg_rating,
        COUNT(r.id) AS review_count
    FROM products p
    LEFT JOIN reviews r ON p.id = r.product_id
    WHERE p.category_id = ?
    GROUP BY p.id
    $order_sql
";
$stmt = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($stmt, "i", $category_id);
mysqli_stmt_execute($stmt);
$products_result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($category_name); ?> - Simple Shop</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include 'frontend_nav.php'; ?>

    <div class="container">
        <h2>สินค้าในหมวดหมู่: <?= htmlspecialchars($category_name); ?></h2>

        <form method="get" style="margin-bottom:15px;">
            <input type="hidden" name="id" value="<?= $category_id; ?>">
            <label>จัดเรียงตาม:</label>
            <select name="sort" onchange="this.form.submit()">
                <option value="name_asc" <?= $sort == 'name_asc' ? 'selected' : '' ?>>ชื่อ (A-Z)</option>
                <option value="name_desc" <?= $sort == 'name_desc' ? 'selected' : '' ?>>ชื่อ (Z-A)</option>
                <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>ราคาน้อยไปมาก</option>
                <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>ราคามากไปน้อย</option>
            </select>
        </form>

        <div class="product-grid">
            <?php if (mysqli_num_rows($products_result) > 0): ?>
                <?php while ($product = mysqli_fetch_assoc($products_result)): ?>
                    <div class="product-card">
                        <a href="product_view.php?id=<?= $product['id']; ?>">
                            <img src="<?= htmlspecialchars($product['image_url']); ?>"
                                alt="<?= htmlspecialchars($product['name']); ?>">
                        </a>

                        <h3>
                            <a href="product_view.php?id=<?= $product['id']; ?>">
                                <?= htmlspecialchars($product['name']); ?>
                            </a>
                        </h3>
                        <?php if ($product['review_count'] > 0): ?>
                            <p>คะแนนเฉลี่ย: <?= $product['avg_rating']; ?>/5 (<?= $product['review_count']; ?> รีวิว)</p>
                        <?php else: ?>
                            <p>ยังไม่มีรีวิว</p>
                        <?php endif; ?>

                        <p class="price"><?= number_format($product['price'], 2); ?> THB</p>
                        <p><?= htmlspecialchars($product['description']); ?></p>

                        <form action="cart_process.php" method="POST">
                            <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
                            <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock']; ?>" style="width: 60px;">
                            <button type="submit" name="add_to_cart" class="btn">Add to Cart</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>ไม่พบสินค้าในหมวดหมู่นี้</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>