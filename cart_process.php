<?php
session_start();
include 'db.php';

// เพิ่มสินค้า
if (isset($_POST['add_to_cart'])) {
    $id = (int)$_POST['product_id'];
    $quantity = (int)($_POST['quantity'] ?? 1);
    if ($quantity <= 0) $quantity = 1;

    // ตรวจสอบสต็อก
    $stmt = mysqli_prepare($db, "SELECT stock FROM products WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);

    if (!$product) {
        header("Location: index.php?error=notfound");
        exit;
    }

    $stock = (int)$product['stock'];
    $in_cart = (int)($_SESSION['cart'][$id] ?? 0);

    if ($in_cart + $quantity > $stock) {
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?error=stock");
        exit;
    }

    $_SESSION['cart'][$id] = $in_cart + $quantity;

    // กลับไปหน้าเดิม
    if (isset($_SERVER['HTTP_REFERER'])) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        header("Location: index.php");
    }
    exit;
}

// อัปเดตจำนวน 
if (isset($_POST['update_cart'])) {
    if (!empty($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $id => $qty) {
            $id = (int)$id;
            $qty = max(1, (int)$qty);

            $stmt = mysqli_prepare($db, "SELECT stock FROM products WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $product = mysqli_fetch_assoc($result);

            if (!$product) continue;
            $stock = (int)$product['stock'];

            // ถ้า stock = 0 → เก็บสถานะว่าสินค้าหมด
            if ($stock <= 0) {
                $_SESSION['cart_outofstock'][$id] = true;
                $_SESSION['cart_error'][$id] = "สินค้าบางรายการหมดแล้ว";
            }
            // ถ้าเกิน stock → ปรับจำนวน
            elseif ($qty > $stock) {
                $_SESSION['cart'][$id] = $stock;
                unset($_SESSION['cart_outofstock'][$id]); // ยังมีสินค้าอยู่
                $_SESSION['cart_error'][$id] = "สินค้าบางรายการถูกปรับจำนวนเพราะเกิน stock";
            } else {
                $_SESSION['cart'][$id] = $qty;
                unset($_SESSION['cart_outofstock'][$id]); // ยังมีสินค้าอยู่
            }
        }
    }
    header("Location: cart_view.php");
    exit;
}


// ลบสินค้า
if (isset($_GET['remove'])) {
    $id = (int)$_GET['remove'];
    unset($_SESSION['cart'][$id]);
    header("Location: cart_view.php");
    exit;
}

// ล้างตะกร้า
if (isset($_GET['clear'])) {
    unset($_SESSION['cart']);
    header("Location: cart_view.php");
    exit;
}

// fallback
header("Location: index.php");
exit;
