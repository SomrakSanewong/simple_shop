<nav>
    <ul>
        <li style="float: left;"><a href="index.php">Home (All Products)</a></li>
        <li style="float: left;">
            <form action="search.php" method="get" style="display:inline-block; margin-left:20px;">
                <input class="box" type="text" name="query" placeholder="ค้นหาสินค้า..." required>
                <button class="search-box" type="submit">ค้นหา</button>
            </form>
        </li>
        <?php
        if (isset($categories_result)) {
            mysqli_data_seek($categories_result, 0);
            while ($cat = mysqli_fetch_assoc($categories_result)) {
                echo '<li><a href="category.php?id=' . $cat['id'] . '">' . htmlspecialchars($cat['name']) . '</a></li>';
            }
        }

        $cart_count = 0;
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            $cart_count = count($_SESSION['cart']);
        }

        if (isset($_SESSION['user'])):
        ?>
            <li style="float: right;"><a href="profile.php">Profile</a></li>
            <li style="float: right;"><a href="cart_view.php">Cart (<?php echo $cart_count; ?>)</a></li>
            <li style="float: right;"><a href="my_orders.php">History</a></li>
            <li style="float: right;"><a href="my_wishlist.php">Wishlist</a></li>
        <?php else: ?>
            <li style="float: right;"><a href="admin/index.php" target="_blank">Admin Login</a></li>
            <li style="float: right;"><a href="login.php">Login</a></li>
            <li style="float: right;"><a href="cart_view.php">Cart (<?php echo $cart_count; ?>)</a></li>
            <li style="float: right;"><a href="my_wishlist.php">Wishlist</a></li>

        <?php endif; ?>
    </ul>
</nav>