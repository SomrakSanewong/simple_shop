<?php
include 'auth.php';


if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($db, "DELETE FROM users WHERE id = $id");
    header("Location: user.php");
    exit;
}

// ดึงข้อมูลผู้ใช้ทั้งหมด
$result = mysqli_query($db, "SELECT id, fullname, email, created_at, role FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายชื่อผู้ใช้</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>
    <?php include 'admin_nav.php'; ?>
    <div class="container">
        <h2>รายชื่อผู้ใช้ทั้งหมด</h2>  
        <table border="1" cellpadding="6" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ชื่อ</th>
                    <th>อีเมล</th>
                    <th>สิทธิ์ผู้ใช้</th>
                    <th>วันที่สมัคร</th>
                    <th>การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['role']); ?></td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            <td>
                                <a class="btn btn-danger" href="user.php?delete=<?php echo $row['id']; ?>"
                                    onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบผู้ใช้นี้?');">
                                    ลบ
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" align="center">ไม่มีข้อมูลผู้ใช้</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>