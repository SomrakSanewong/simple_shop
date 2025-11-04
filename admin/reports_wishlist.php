<?php
session_start();
include 'auth.php';


//ดึงข้อมูลสินค้าที่ถูกเพิ่มใน Wishlist มากที่สุด 10 อันดับ
$sql = "
    SELECT 
        p.id,
        p.name,
        p.price,
        p.image_url,
        COUNT(w.product_id) AS total_added
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    GROUP BY w.product_id
    ORDER BY total_added DESC
    LIMIT 10
";

$result = mysqli_query($db, $sql);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายงานสินค้าใน Wishlist ยอดนิยม</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <?php include 'admin_nav.php'; ?> 

    <div class="container">
        <h2>รายงาน: สินค้าที่ถูกเใจมากที่สุด 10 อันดับ</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>อันดับ</th>
                    <th>ภาพสินค้า</th>
                    <th>ชื่อสินค้า</th>
                    <th>ราคา (บาท)</th>
                    <th>จำนวนครั้งที่ถูก</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $rank = 1;
                while ($row = mysqli_fetch_assoc($result)):
                ?>
                    <tr>
                        <td><?= $rank++; ?></td>
                        <td>
                            <img src="../<?= htmlspecialchars($row['image_url']); ?>" alt="<?= htmlspecialchars($row['name']); ?>" width="80">
                        </td>
                        <td><?= htmlspecialchars($row['name']); ?></td>
                        <td><?= number_format($row['price'], 2); ?></td>
                        <td><?= $row['total_added']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
