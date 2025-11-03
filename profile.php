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

    // ✅ ถ้าข้อมูลที่กรอกมาเหมือนเดิมทั้งหมด และไม่มีการใส่รหัสผ่านใหม่
    if ($fullname === $user['fullname'] && $email === $user['email'] && empty($new_password) && empty($confirm_password)) {
        $success = "ไม่มีการเปลี่ยนแปลงข้อมูล";
    } else {
        // ตรวจสอบรหัสผ่านใหม่ (ถ้ามีการกรอก)
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

                // ดึงข้อมูลใหม่จากฐานข้อมูลมาอัปเดต session
                $result = mysqli_query($db, "SELECT * FROM users WHERE id = $user_id");
                $_SESSION['user'] = mysqli_fetch_assoc($result);
                $user = $_SESSION['user'];
            } else {
                $error = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
            }
        }
    }
}


// ดึงข้อมูลหมวดหมู่ทั้งหมดสำหรับเมนู
$categories_result = mysqli_query($db, "SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>My Profile</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .container {
            max-width: 600px;
            margin: 40px auto;
        }

        input[readonly] {
            background: #f8f8f8;
            border: none;
        }

        .btn {
            padding: 8px 16px;
            margin-top: 10px;
            cursor: pointer;
        }

        .btn-edit {
            background: #007bff;
            color: white;
            border: none;
        }

        .btn-save {
            background: #28a745;
            color: white;
            border: none;
        }

        .btn-cancel {
            background: #dc3545;
            color: white;
            border: none;
        }

        .password-group {
            display: none;
        }
    </style>
</head>

<body>
    <?php include 'frontend_nav.php'; ?>

    <div class="container">
        <h2>ข้อมูลส่วนตัว</h2>

        <?php if ($error): ?>
            <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
        <?php elseif ($success): ?>
            <p style="color:green;"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>

        <form method="post" id="profileForm">
            <div>
                <label>ชื่อ:</label><br>
                <input type="text" name="fullname" id="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" readonly required>
            </div>
            <div>
                <label>อีเมล:</label><br>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly required>
            </div>
            <div class="password-group" id="passwordGroup">
                <label>รหัสผ่านใหม่:</label><br>
                <input type="password" name="new_password" id="new_password" placeholder="ถ้าไม่เปลี่ยนให้เว้นว่างไว้"><br>

                <label>ยืนยันรหัสผ่านใหม่:</label><br>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="กรอกรหัสผ่านใหม่อีกครั้ง"><br>
            </div>

            <div id="viewButtons">
                <button type="button" class="btn btn-edit" id="editBtn">แก้ไขข้อมูล</button>
                <a href="logout.php" style="float: right;" class="btn btn-cancel">ออกจากระบบ</a>
            </div>

            <div id="editButtons" style="display:none;">
                <button type="submit" name="save" class="btn btn-save">บันทึก</button>
                <button type="button" class="btn btn-cancel" id="cancelBtn">ยกเลิก</button>
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

        // เมื่อกด "แก้ไขข้อมูล"
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