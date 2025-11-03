<?php
include 'auth.php'; // ตรวจสอบสิทธิ์ก่อน
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>

<?php include 'admin_nav.php'; ?>

<div class="container">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</h2>
    <p>This is the admin dashboard. You can manage categories and products from the navigation menu.</p>
</div>

</body>
</html>