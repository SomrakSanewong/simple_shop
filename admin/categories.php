<?php
include 'auth.php';

// --- (Delete) ---
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $id_to_delete = (int)$_GET['delete_id'];
    
    // หมายเหตุ: ควรมีการตรวจสอบก่อนว่ามีสินค้าในหมวดหมู่นี้หรือไม่
    // แต่ส าหรับโจทย์ CRUD แบบง่าย เราจะลบเลย (ฐานข้อมูลตั้งค่า ON DELETE CASCADE ไว้)
    $stmt = mysqli_prepare($db, "DELETE FROM categories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_to_delete);
    mysqli_stmt_execute($stmt);
    header("location: categories.php?deleted=true");
    exit;
}

// --- (Read) ---
$result = mysqli_query($db, "SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>
<?php include 'admin_nav.php'; ?>

<div class="container">
    <h2>จัดการหมวดสินค้า</h2>
    <a href="category_form.php" class="btn btn-primary">เพิ่มหมวดสินค้าใหม่</a>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>ชื่อสินค้า</th>
                <th>ด าเนินการ</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td>
                        <a href="category_form.php?id=<?php echo $row['id']; ?>">แก้ไข</a>
                        <a href="categories.php?delete_id=<?php echo $row['id']; ?>" class="delete"
                        onclick="return confirm('Are you sure you want to delete this category?');">ลบ</a>
                    </td>
                </tr>
            <?php
            } // end while
            ?>
        </tbody>
     </table>
   </div>
</body>
</html>