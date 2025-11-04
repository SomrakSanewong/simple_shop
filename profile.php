<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if ($fullname === $user['fullname'] && $email === $user['email'] && empty($new_password) && empty($confirm_password)) {
        $success = "ไม่มีการเปลี่ยนแปลงข้อมูล";
    } else {
        if (!empty($new_password) || !empty($confirm_password)) {
            if ($new_password !== $confirm_password) {
                $error = "รหัสผ่านใหม่ไม่ตรงกัน";
            } elseif (strlen($new_password) < 6) {
                $error = "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร";
            } else {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET fullname=?, email=?, password=? WHERE id=?");
                $stmt->bind_param("sssi", $fullname, $email, $hashed, $user_id);
            }
        } else {
            $stmt = $db->prepare("UPDATE users SET fullname=?, email=? WHERE id=?");
            $stmt->bind_param("ssi", $fullname, $email, $user_id);
        }

        if (!$error && isset($stmt)) {
            if ($stmt->execute()) {
                $success = "อัปเดตข้อมูลเรียบร้อยแล้ว";
                $result = mysqli_query($db, "SELECT * FROM users WHERE id = $user_id");
                $_SESSION['user'] = mysqli_fetch_assoc($result);
                $user = $_SESSION['user'];
            } else {
                $error = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
            }
        }
    }
}

$categories_result = mysqli_query($db, "SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php include 'frontend_nav.php'; ?>

    <div class="container">
        <h2>ข้อมูลส่วนตัว</h2>

        <?php if ($error): ?>
            <div class="alert error"><?= htmlspecialchars($error); ?></div>
        <?php elseif ($success): ?>
            <div class="alert success"><?= htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="post" id="profileForm">
            <div class="form-group">
                <label>ชื่อ:</label>
                <input type="text" name="fullname" id="fullname" value="<?= htmlspecialchars($user['fullname']); ?>" readonly required>
            </div>

            <div class="form-group">
                <label>อีเมล:</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']); ?>" readonly required>
            </div>

            <div class="password-group" id="passwordGroup" style="display:none;">
                <label>รหัสผ่านใหม่:</label>
                <input type="password" name="new_password" id="new_password" placeholder="ถ้าไม่เปลี่ยนให้เว้นว่างไว้">

                <label>ยืนยันรหัสผ่านใหม่:</label>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="กรอกรหัสผ่านใหม่อีกครั้ง">
            </div>

            <div id="viewButtons">
                <button type="button" class="btn" id="editBtn">แก้ไขข้อมูล</button>
                <a href="logout.php" class="btn btn-danger" style="float:right;">ออกจากระบบ</a>
            </div>

            <div id="editButtons" style="display:none;">
                <button type="submit" name="save" class="btn btn-success">บันทึก</button>
                <button type="button" class="btn btn-danger" id="cancelBtn">ยกเลิก</button>
            </div>
        </form>
    </div>

    <script>
        const editBtn = document.getElementById('editBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const fullname = document.getElementById('fullname');
        const email = document.getElementById('email');
        const passwordGroup = document.getElementById('passwordGroup');
        const viewButtons = document.getElementById('viewButtons');
        const editButtons = document.getElementById('editButtons');

        editBtn.addEventListener('click', () => {
            fullname.removeAttribute('readonly');
            email.removeAttribute('readonly');
            passwordGroup.style.display = 'block';
            viewButtons.style.display = 'none';
            editButtons.style.display = 'block';
        });

        cancelBtn.addEventListener('click', () => {
            window.location.reload();
        });
    </script>
</body>
</html>
