<?php
include 'db.php'; 

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = []; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    // --- 2.1 จัดการ "เพิ่มสินค้า" ---
    if ($action == 'add_to_cart') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $quantity_to_add = (int)($_POST['quantity'] ?? 0);

        if ($product_id > 0 && $quantity_to_add > 0) {
            $stock_stmt = mysqli_prepare($db, "SELECT stock, name FROM products WHERE id = ?");
            mysqli_stmt_bind_param($stock_stmt, "i", $product_id);
            mysqli_stmt_execute($stock_stmt);
            $product = mysqli_fetch_assoc(mysqli_stmt_get_result($stock_stmt));
            
            if ($product) {
                $new_total_quantity = ($_SESSION['cart'][$product_id] ?? 0) + $quantity_to_add;

                // แก้ไข: ใช้ปีกกา {} เพื่อให้มั่นใจว่ารันโค้ดถูกบล็อก
                if ($new_total_quantity > $product['stock']) {
                    $_SESSION['notification'] = "error|Stock for " . htmlspecialchars($product['name']) . " is not enough (Available: {$product['stock']}).";
                } else {
                    $_SESSION['cart'][$product_id] = $new_total_quantity;
                    $_SESSION['notification'] = "success|Added " . htmlspecialchars($product['name']) . " to cart.";
                }
            } else 
                $_SESSION['notification'] = "error|Product not found.";
        }
        
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit;
    }

    // --- 2.2 Logic การอัปเดต/Checkout ---
    elseif ($action == 'update_cart' || $action == 'proceed_to_checkout') {
        $quantities = $_POST['quantities'] ?? [];  
        $error_flag = false;
        
        if (is_array($quantities) && !empty($quantities)) {
            $id_list_string = implode(',', array_map('intval', array_keys($quantities)));
            $result = mysqli_query($db, "SELECT id, stock, name FROM products WHERE id IN ($id_list_string)");
            $products_db = [];
            while ($row = mysqli_fetch_assoc($result)) $products_db[$row['id']] = $row;
            
            foreach ($quantities as $product_id => $quantity) {
                $product_id = (int)$product_id;
                $quantity = (int)$quantity;
                
                if (isset($products_db[$product_id])) {
                    $product = $products_db[$product_id];
                    if ($quantity <= 0) unset($_SESSION['cart'][$product_id]);
                    elseif ($quantity > $product['stock']) {
                        $_SESSION['cart'][$product_id] = $product['stock'];
                        $error_flag = true;
                    } else $_SESSION['cart'][$product_id] = $quantity;
                } else unset($_SESSION['cart'][$product_id]);
            }
        }
        
        // --- 2.3 การ Redirect ---
        if ($action == 'update_cart') {
            if ($error_flag) $_SESSION['notification'] = "error|Not enough stock for some items. Quantities adjusted.";
            elseif (!isset($_SESSION['notification'])) $_SESSION['notification'] = "success|Cart updated successfully.";
            header('Location: cart_view.php');
            exit;
        }

        if ($action == 'proceed_to_checkout') {
            if ($error_flag) {
                 $_SESSION['notification'] = "error|Not enough stock for some items. Quantities adjusted. Please review your cart before checking out.";
                 header('Location: cart_view.php');
            } else {
                $_SESSION['notification'] = "success|Proceeding to checkout successful!";
                header('Location: index.php');
            }
            exit;
        }
    }
}

// -----------------------------------------------------------------
// 3. จัดการคำสั่ง GET (ลบสินค้า)
// -----------------------------------------------------------------
elseif (isset($_GET['action']) && $_GET['action'] == 'remove_from_cart') {
    $product_id = (int)($_GET['id'] ?? 0);
    if ($product_id > 0 && isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        $_SESSION['notification'] = "success|Item removed from cart.";
    }
    header('Location: cart_view.php');
    exit;
}

// -----------------------------------------------------------------
// 4. ถ้ามาหน้านี้ตรงๆ ให้กลับไปหน้าหลัก
// -----------------------------------------------------------------
header('Location: index.php');
exit;