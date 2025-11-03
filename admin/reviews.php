<?php
include 'auth.php';

// ✅ ลบรีวิว
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $review_id = (int)$_GET['delete_id'];
    $stmt = mysqli_prepare($db, "DELETE FROM reviews WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $review_id);
    mysqli_stmt_execute($stmt);
    header("Location: reviews.php?deleted=true");
    exit;
}

// ✅ ดึงข้อมูลรีวิวทั้งหมด
$sql = "
    SELECT 
        r.id,
        p.name AS product_name,
        u.email AS user_email,
        u.fullname AS user_name,
        r.rating,
        r.comment,
        r.created_at
    FROM reviews r
    JOIN products p ON r.product_id = p.id
    JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC
";

$result = mysqli_query($db, $sql);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการรีวิวสินค้า</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>
    <?php include 'admin_nav.php'; ?>

    <div class="container">
        <h2>จัดการรีวิวสินค้า</h2>

        <?php if (isset($_GET['deleted'])): ?>
            <p style="color: green;">ลบรีวิวเรียบร้อยแล้ว</p>
        <?php endif; ?>

        <?php if (mysqli_num_rows($result) === 0): ?>
            <p>ยังไม่มีรีวิวในระบบ</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ชื่อสินค้า</th>
                        <th>ผู้รีวิว</th>
                        <th>คะแนน</th>
                        <th>ความคิดเห็น</th>
                        <th>วันที่</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['product_name']); ?></td>
                            <td>
                                <?= htmlspecialchars($row['user_name']); ?>
                                (<?= htmlspecialchars($row['user_email']); ?>)
                            </td>
                            <td><?= $row['rating']; ?>/5</td>
                            <td><?= nl2br(htmlspecialchars($row['comment'])); ?></td>
                            <td><?= $row['created_at']; ?></td>
                            <td>
                                <a href="reviews.php?delete_id=<?= $row['id']; ?>"
                                    class="btn btn-danger"
                                    onclick="return confirm('ต้องการลบรีวิวนี้จริงหรือไม่?');">
                                    ลบ
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>

</html>