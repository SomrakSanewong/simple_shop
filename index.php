<?php
include 'db.php';
$categories_result = mysqli_query($db, "SELECT * FROM categories ORDER BY name");

$sort = $_GET['sort'] ?? '';
$order_sql = 'ORDER BY p.name ASC';

switch ($sort) {
    case 'price_asc':
        $order_sql = 'ORDER BY p.price ASC';
        break;
    case 'price_desc':
        $order_sql = 'ORDER BY p.price DESC';
        break;
    case 'name_desc':
        $order_sql = 'ORDER BY p.name DESC';
        break;
    case 'name_asc':
    default:
        $order_sql = 'ORDER BY p.name ASC';
        break;
}

$sql = "SELECT 
            p.*, 
            c.name AS category_name,
            ROUND(AVG(r.rating),1) AS avg_rating,
            COUNT(r.id) AS review_count
        FROM products p
        JOIN categories c ON p.category_id = c.id
        LEFT JOIN reviews r ON p.id = r.product_id
        GROUP BY p.id
        $order_sql";

$products_result = mysqli_query($db, $sql);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Simple Shop</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include 'frontend_nav.php'; ?>

    <div class="container">
        <div class="product-header">
            <h2>All Products</h2>

            <form method="get" style="margin:0">
                <label>จัดเรียงตาม:</label>
                <select class="box" name="sort" onchange="this.form.submit()">
                    <option value="name_asc" <?= $sort == 'name_asc' ? 'selected' : '' ?>>ชื่อ (A-Z)</option>
                    <option value="name_desc" <?= $sort == 'name_desc' ? 'selected' : '' ?>>ชื่อ (Z-A)</option>
                    <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>ราคาน้อยไปมาก</option>
                    <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>ราคามากไปน้อย</option>
                </select>
            </form>
        </div>


        <div class="product-grid">
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
                    <p class="price"><?php echo number_format($product['price'], 2); ?> THB</p>
                    <p><small>Category: <?php echo htmlspecialchars($product['category_name']); ?></small></p>
                    <p><?php echo htmlspecialchars($product['description']); ?></p>

                    <form action="cart_process.php" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="number" name="quantity" value="1" min="1"
                            max="<?php echo $product['stock']; ?>" style="width: 60px;">
                        <button type="submit" name="add_to_cart" class="btn">Add to Cart</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>

</html>
