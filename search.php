<?php
session_start();
include 'db.php';

$query = trim($_GET['query'] ?? '');
$products = [];

if ($query !== '') {
    $like = '%' . $query . '%';
    $stmt = $db->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ? ORDER BY name ASC");
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

$categories_result = mysqli_query($db, "SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ผลการค้นหา</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'frontend_nav.php'; ?>

<div class="container">
    <h2>ผลการค้นหาสำหรับ: "<?= htmlspecialchars($query) ?>"</h2>

    <?php if (empty($products)): ?>
        <p>ไม่พบสินค้าที่ตรงกับคำค้นหา</p>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $p): ?>
                <div class="product-card">
                    <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="max-width:100%;">
                    <h4><?= htmlspecialchars($p['name']) ?></h4>
                    <p><?= number_format($p['price'], 2) ?> บาท</p>
                    <form action="cart_process.php" method="post">
                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                        <button type="submit" name="add_to_cart" class="btn">เพิ่มลงตะกร้า</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
