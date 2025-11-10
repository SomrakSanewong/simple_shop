<?php
include 'auth.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $code = trim($_POST['code']);
    $type = $_POST['type'];
    $value = (float)$_POST['value'];
    $expiry_date = $_POST['expiry_date'];
    $status = $_POST['status'];

    if ($id) {
       
        $stmt = mysqli_prepare($db, "UPDATE promotions SET code=?, type=?, value=?, expiry_date=?, status=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "ssdssi", $code, $type, $value, $expiry_date, $status, $id);
        mysqli_stmt_execute($stmt);
        $msg = "อัปเดตข้อมูลเรียบร้อยแล้ว";
    } else {
       
        $stmt = mysqli_prepare($db, "INSERT INTO promotions (code, type, value, expiry_date, status) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssdss", $code, $type, $value, $expiry_date, $status);
        mysqli_stmt_execute($stmt);
        $msg = "เพิ่มโค้ดใหม่เรียบร้อยแล้ว ";
    }
}


if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($db, "DELETE FROM promotions WHERE id=$id");
    $msg = "ลบโค้ดส่วนลดเรียบร้อยแล้ว 🗑";
}


$edit_promo = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = mysqli_query($db, "SELECT * FROM promotions WHERE id=$id");
    $edit_promo = mysqli_fetch_assoc($res);
}

$result = mysqli_query($db, "SELECT * FROM promotions ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการโปรโมชั่น</title>
    <link rel="stylesheet" href="../style.css">

</head>

<body>
    <?php include 'admin_nav.php'; ?>

    <div class="container">
        <h2>จัดการโปรโมชั่น</h2>
        <?php if (!empty($msg)) echo "<p style='color:green;'>$msg</p>"; ?>

        <form method="post">
            <h3><?= $edit_promo ? 'แก้ไขโค้ดส่วนลด #' . $edit_promo['id'] : 'เพิ่มโค้ดส่วนลดใหม่'; ?></h3>
            <input type="hidden" name="id" value="<?= $edit_promo['id'] ?? ''; ?>">

          
            <div class="form-row top-row">
                <div class="form-group">
                    <label>รหัสโค้ด:</label>
                    <input type="text" name="code" value="<?= htmlspecialchars($edit_promo['code'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>ประเภท:</label>
                    <select name="type" required>
                        <option value="fixed" <?= isset($edit_promo) && $edit_promo['type'] == 'fixed' ? 'selected' : ''; ?>>Fixed (บาท)</option>
                        <option value="percentage" <?= isset($edit_promo) && $edit_promo['type'] == 'percentage' ? 'selected' : ''; ?>>Percentage (%)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>มูลค่า:</label>
                    <input type="number" step="0.01" name="value" value="<?= $edit_promo['value'] ?? ''; ?>" required>
                </div>
            </div>

           
            <div class="form-row bottom-row">
                <div class="form-group">
                    <label>วันหมดอายุ:</label>
                    <input type="date" name="expiry_date" value="<?= $edit_promo['expiry_date'] ?? ''; ?>" required>
                </div>

                <div class="form-group">
                    <label>สถานะ:</label>
                    <select name="status">
                        <option value="Active" <?= isset($edit_promo) && $edit_promo['status'] == 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?= isset($edit_promo) && $edit_promo['status'] == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div style="margin-top: 15px;">
                <button type="submit" class="btn btn-primary"><?= $edit_promo ? 'บันทึกการแก้ไข' : 'เพิ่มโค้ดใหม่'; ?></button>
                <?php if ($edit_promo): ?>
                    <a href="promotions.php" class="btn btn-danger">ยกเลิก</a>
                <?php endif; ?>
            </div>
        </form>


        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>โค้ด</th>
                    <th>ประเภท</th>
                    <th>มูลค่า</th>
                    <th>วันหมดอายุ</th>
                    <th>สถานะ</th>
                    <th>วันที่สร้าง</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= $row['id']; ?></td>
                        <td><?= htmlspecialchars($row['code']); ?></td>
                        <td><?= ucfirst($row['type']); ?></td>
                        <td><?= number_format($row['value'], 2); ?></td>
                        <td><?= $row['expiry_date']; ?></td>
                        <td><?= $row['status']; ?></td>
                        <td><?= $row['created_at']; ?></td>
                        <td>
                            <a href="promotions.php?edit=<?= $row['id']; ?>" class="btn btn-secondary">แก้ไข</a>
                            <a href="promotions.php?delete=<?= $row['id']; ?>" class="btn btn-danger" onclick="return confirm('ต้องการลบโค้ดนี้หรือไม่?');">ลบ</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>