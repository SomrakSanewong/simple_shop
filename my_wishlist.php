<?php
session_start();
include 'db.php';

//ตรวจสอบว่าผู้ใช้ล็อกอินหรือยัง
if (!isset($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

//ดึงสินค้าที่ผู้ใช้ถูกใจไว้
$sql = "
    SELECT p.id, p.name, p.price, p.image_url
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    WHERE w.user_id = ?
    ORDER BY w.created_at DESC
";
$stmt = mysqli_prepare($db, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$categories_result = mysqli_query($db, "SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายการสินค้าที่ถูกใจของฉัน</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <?php include 'frontend_nav.php'; ?>

    <div class="container">
        <h2>รายการสินค้าที่คุณถูกใจ</h2>

        <?php if (mysqli_num_rows($result) === 0): ?>
            <p>คุณยังไม่มีสินค้าที่ถูกใจ</p>
            <p><a href="index.php" class="btn">กลับไปเลือกสินค้า</a></p>
        <?php else: ?>
            <div class="product-grid">
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="product-card">
                        <a href="product_view.php?id=<?= $row['id']; ?>">
                            <img src="<?= htmlspecialchars($row['image_url']); ?>" alt="<?= htmlspecialchars($row['name']); ?>">
                            <h3><?= htmlspecialchars($row['name']); ?></h3>
                        </a>
                        <p>ราคา: <?= number_format($row['price'], 2); ?> บาท</p>

                        <form action="wishlist_process.php" method="post">
                            <input type="hidden" name="product_id" value="<?= $row['id']; ?>">
                            <button type="submit" name="remove" class="wishlist-btn remove">
                                <i class="fa-solid fa-heart"></i>
                            </button>
                        </form>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>
