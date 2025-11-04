<?php
session_start();
include 'db.php';

//ต้องล็อกอินก่อน
if (!isset($_SESSION['user']['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];
$product_id = (int)($_POST['product_id'] ?? 0);

//เพิ่มใน Wishlist
if (isset($_POST['add'])) {
    $stmt = mysqli_prepare($db, "INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
    mysqli_stmt_execute($stmt);
}

//ลบออกจาก Wishlist
elseif (isset($_POST['remove'])) {
    $stmt = mysqli_prepare($db, "DELETE FROM wishlist WHERE user_id=? AND product_id=?");
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
    mysqli_stmt_execute($stmt);
}

//กลับไปยังสินค้าที่ดูอยู่
header("Location: product_view.php?id=" . $product_id);
exit;
