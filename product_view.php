<?php
session_start();
include 'db.php';

// ตรวจสอบพารามิเตอร์ id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$product_id = (int)$_GET['id'];

// ดึงข้อมูลสินค้า
$stmt = mysqli_prepare($db, "SELECT * FROM products WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    echo "<p>ไม่พบสินค้านี้</p>";
    exit;
}

// ตรวจสอบสถานะ Wishlist
$isLoggedIn = isset($_SESSION['user']['id']);
$user_id = $_SESSION['user']['id'] ?? null;
$in_wishlist = false;

if ($isLoggedIn) {
    $check = mysqli_query($db, "SELECT 1 FROM wishlist WHERE user_id=$user_id AND product_id=$product_id");
    $in_wishlist = mysqli_num_rows($check) > 0;
}

//พิ่มรีวิว (เฉพาะผู้ใช้ที่ล็อกอิน)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user']['id']) && isset($_POST['rating'])) {
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);

    if ($rating >= 1 && $rating <= 5) {
        // ตรวจสอบว่าผู้ใช้เคยรีวิวสินค้านี้แล้วหรือยัง
        $check_review = mysqli_prepare($db, "SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
        mysqli_stmt_bind_param($check_review, "ii", $product_id, $user_id);
        mysqli_stmt_execute($check_review);
        $review_result = mysqli_stmt_get_result($check_review);

        if (mysqli_num_rows($review_result) > 0) {
            // เคยรีวิวแล้ว
            header("Location: product_view.php?id=$product_id&reviewed=exists");
            exit;
        } else {
            // ยังไม่เคยรีวิว — เพิ่มรีวิวใหม่
            $stmt = mysqli_prepare($db, "INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "iiis", $product_id, $user_id, $rating, $comment);
            mysqli_stmt_execute($stmt);

            header("Location: product_view.php?id=$product_id&reviewed=success");
            exit;
        }
    }
}

// ดึงรีวิวทั้งหมดของสินค้านี้
$reviews_result = mysqli_query($db, "
    SELECT r.*, u.fullname AS user_name
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.product_id = $product_id
    ORDER BY r.created_at DESC
");

// คำนวณคะแนนเฉลี่ย
$avg_result = mysqli_query($db, "
    SELECT AVG(rating) AS avg_rating, COUNT(*) AS total_reviews
    FROM reviews
    WHERE product_id = $product_id
");
$avg_data = mysqli_fetch_assoc($avg_result);
$avg_rating = is_null($avg_data['avg_rating']) ? 0 : round($avg_data['avg_rating'], 1);
$total_reviews = (int)$avg_data['total_reviews'];

$categories_result = mysqli_query($db, "SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title><?= htmlspecialchars($product['name']); ?></title>
</head>

<body>
    <?php include 'frontend_nav.php'; ?>

    <div class="container">
        <div class="product-header">
            <h2><?= htmlspecialchars($product['name']); ?></h2>
            <div class="avg-box">
                <strong>คะแนนเฉลี่ย:</strong> <?= $avg_rating; ?>/5
                (จาก <?= $total_reviews; ?> รีวิว)
            </div>
        </div>

        <img src="<?= htmlspecialchars($product['image_url']); ?>" width="200" alt="<?= htmlspecialchars($product['name']); ?>">
        <p>ราคา: <?= number_format($product['price'], 2); ?> บาท</p>
        <p><?= nl2br(htmlspecialchars($product['description'])); ?></p>
        <hr>

        <!-- ปุ่ม Wishlist -->
        <div>
            <?php if ($isLoggedIn): ?>
                <form action="wishlist_process.php" method="post" style="display:inline-block;">
                    <input type="hidden" name="product_id" value="<?= $product_id; ?>">
                    <?php if ($in_wishlist): ?>
                        <button type="submit" name="remove" class="wishlist-btn remove">
                            <i class="fa-solid fa-heart"></i> 
                        </button> 
                    <?php else: ?>
                        <button type="submit" name="add" class="wishlist-btn">
                            <i class="fa-regular fa-heart"></i> 
                        </button>
                    <?php endif; ?>
                </form>
            <?php else: ?>
                <p style="color:red;">
                    <a href="login.php">เข้าสู่ระบบ</a> เพื่อเพิ่มสินค้านี้ใน Wishlist
                </p>
            <?php endif; ?>
        </div>
        <hr>

        <?php if ($isLoggedIn): ?>
            <h3>เขียนรีวิวของคุณ</h3>
            <?php if (isset($_GET['reviewed']) && $_GET['reviewed'] === 'exists'): ?>
                <p style="color:red;">คุณได้รีวิวสินค้านี้แล้ว</p>
            <?php elseif (isset($_GET['reviewed']) && $_GET['reviewed'] === 'success'): ?>
                <p style="color:green;">ขอบคุณสำหรับรีวิวของคุณ!</p>
            <?php endif; ?>

            <form method="post">
                <label>ให้คะแนน (1-5)</label>
                <select name="rating" required>
                    <option value="">-- เลือกคะแนน --</option>
                    <option value="5">5</option>
                    <option value="4">4</option>
                    <option value="3">3</option>
                    <option value="2">2</option>
                    <option value="1">1</option>
                </select>
                <br><br>
                <textarea name="comment" rows="4" placeholder="พิมพ์รีวิวของคุณ..."></textarea><br>
                <button type="submit" class="btn">ส่งรีวิว</button>
            </form>
        <?php else: ?>
            <p><a href="login.php">เข้าสู่ระบบ</a> เพื่อเขียนรีวิว</p>
        <?php endif; ?>

        <hr>
        <h3>รีวิวจากลูกค้า</h3>
        <?php if ($total_reviews == 0): ?>
            <p>ยังไม่มีรีวิวสำหรับสินค้านี้</p>
        <?php else: ?>
            <?php while ($rev = mysqli_fetch_assoc($reviews_result)): ?>
                <div class="review-box">
                    <div class="rating-number">คะแนน: <?= (int)$rev['rating']; ?>/5</div>
                    <strong><?= htmlspecialchars($rev['user_name']); ?></strong>
                    <p><?= nl2br(htmlspecialchars($rev['comment'])); ?></p>
                    <small>วันที่: <?= htmlspecialchars($rev['created_at']); ?></small>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</body>

</html>
