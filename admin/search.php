<?php
include 'auth.php';

if (!isset($_GET['query']) || trim($_GET['query']) === '') {
    header("location: products.php");
    exit;
}

$search = trim($_GET['query']);

$stmt = mysqli_prepare(
    $db,
    "SELECT p.*, c.name AS category_name
     FROM products p
     JOIN categories c ON p.category_id = c.id
     WHERE p.name LIKE CONCAT('%', ?, '%')
        OR c.name LIKE CONCAT('%', ?, '%')
     ORDER BY p.id DESC"
);
mysqli_stmt_bind_param($stmt, "ss", $search, $search);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ผลการค้นหา: <?= htmlspecialchars($search); ?></title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<?php include 'admin_nav.php'; ?>

<div class="container">
    <h2>ผลการค้นหา: "<?= htmlspecialchars($search); ?>"</h2>
    <a href="products.php" class="btn btn-primary">ย้อนกลับ</a>

    <?php if (mysqli_num_rows($result) === 0): ?>
        <p>ไม่พบสินค้าที่ตรงกับคำค้นหา</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ภาพสินค้า</th>
                    <th>ชื่อสินค้า</th>
                    <th>หมวดหมู่</th>
                    <th>ราคา</th>
                    <th>ดำเนินการ</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $row['id']; ?></td>
                        <td>
                            <?php
                            $display_url = $row['image_url'];
                            if (strpos($display_url, 'images/') === 0) {
                                $display_url = '../' . $display_url;
                            }
                            ?>
                            <img src="<?= htmlspecialchars($display_url); ?>"
                                 alt="<?= htmlspecialchars($row['name']); ?>"
                                 class="product-thumb">
                        </td>
                        <td><?= htmlspecialchars($row['name']); ?></td>
                        <td><?= htmlspecialchars($row['category_name']); ?></td>
                        <td><?= number_format($row['price'], 2); ?></td>
                        <td>
                            <a href="product_form.php?id=<?= $row['id']; ?>">แก้ไข</a>
                            <a href="products.php?delete_id=<?= $row['id']; ?>"
                               class="delete"
                               onclick="return confirm('คุณต้องการลบสินค้านี้หรือไม่?');">ลบ</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
