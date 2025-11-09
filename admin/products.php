<?php
include 'auth.php';

if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $id_to_delete = (int)$_GET['delete_id'];

    $stmt_img = mysqli_prepare($db, "SELECT image_url FROM products WHERE id = ?");
    mysqli_stmt_bind_param($stmt_img, "i", $id_to_delete);
    mysqli_stmt_execute($stmt_img);
    $result_img = mysqli_stmt_get_result($stmt_img);
    $product = mysqli_fetch_assoc($result_img);

    if ($product) {
        $image_to_delete = $product['image_url'];
        if (!empty($image_to_delete) && strpos($image_to_delete, 'images/') === 0) {
            if (file_exists('../' . $image_to_delete)) {
                @unlink('../' . $image_to_delete); 
            }
        }
    }
    $stmt = mysqli_prepare($db, "DELETE FROM products WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_to_delete);
    mysqli_stmt_execute($stmt);

    header("location: products.php?deleted=true");
    exit;
}

$result = mysqli_query(
    $db,
    "SELECT p.*, c.name AS category_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        ORDER BY p.id DESC"
);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Manage Products</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>
    <?php include 'admin_nav.php'; ?>

    <div class="container">
        <div class="product-header">
            <h2>จัดการสินค้า</h2>
            <form action="search.php" method="get" style="display:inline-block; margin-left:20px;">
                <input class="box" type="text" name="query" placeholder="ค้นหาสินค้า..." required>
                <button class="search-box" type="submit">ค้นหา</button>
            </form>
        </div>

        <a href="product_form.php" class="btn btn-primary">เพิ่มสินค้าใหม</a>

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
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>

                        <td>
                            <?php
                            $display_url = $row['image_url'];
                            if (strpos($display_url, 'images/') === 0) {
                                $display_url = '../' . $display_url;
                            }
                            ?>
                            <img src="<?php echo htmlspecialchars($display_url); ?>"
                                alt="<?php echo htmlspecialchars($row['name']); ?>" class="product-thumb">
                        </td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                        <td><?php echo number_format($row['price'], 2); ?></td>
                        <td>
                            <a href="product_form.php?id=<?php echo $row['id']; ?>">แก้ไข</a>
                            <a href="products.php?delete_id=<?php echo $row['id']; ?>" class="delete"
                                onclick="return confirm('Are you sure you want to delete this product?');">ลบ</a>
                        </td>
                    </tr>
                <?php
                } 
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>
