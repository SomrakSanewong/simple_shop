<?php
session_start();
include '../db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);

    // ใช้ prepared statement ป้องกัน SQL Injection
    $stmt = mysqli_prepare($db, "SELECT id FROM admin WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        // ✅ ข้ามการตรวจสอบรหัสผ่าน (ไม่ต้องใช้ password)
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $username;

        header("Location: index.php");
        exit;
    } else {
        $error = "ไม่พบชื่อผู้ใช้ในระบบ";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="login-form">
        <h2>เข้าสู่ระบบผู้ดูแล</h2>

        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form action="login.php" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <!-- 🔹 ตัดส่วนรหัสผ่านออก -->
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">เข้าสู่ระบบ</button>
            </div>
        </form>
    </div>
</body>
</html>
