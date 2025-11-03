<?php
include 'auth.php';

$name = '';
$id = 0;
$is_edit = false;
$error = '';

// --- (Check for Update Mode) ---
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $is_edit = true;

    $stmt = mysqli_prepare($db, "SELECT name FROM categories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $category = mysqli_fetch_assoc($result);
    if (!$category) {
        header("location: categories.php");
        exit;
    }
    $name = $category['name'];
}

// --- (Handle Form Submission - Create & Update) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $id = (int)$_POST['id'];
    if (empty($name)) {
        $error = "Category name is required.";
    }
    else {
        if ($id > 0) { // Update Mode
            $stmt = mysqli_prepare($db, "UPDATE categories SET name = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $name, $id);
        } else { // Create Mode
            $stmt = mysqli_prepare($db, "INSERT INTO categories (name) VALUES (?)");
            mysqli_stmt_bind_param($stmt, "s", $name);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            header("location: categories.php?success=true");
            exit;
        } else {
            $error = "Error saving category: " . mysqli_error($db);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title><?php echo $is_edit ? 'Edit' : 'Add'; ?> Category</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>

    <?php include 'admin_nav.php'; ?>
    <div class="container">
        <h2><?php echo $is_edit ? 'แก้ไข' : 'เพิ่ม'; ?>หมวดสินค้า</h2>
        
        <?php if ($error) { ?>
            <p class="error"><?php echo $error; ?></p>
        <?php } ?>

        <form action="category_form.php<?php echo $is_edit ? '?id=' . $id : ''; ?>" method="post">
            <input type="hidden" name="id" value="<?php echo $id; ?>">

            <div class="form-group">
                <label for="name">ชื่อหมวดสินค้า</label>
                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">บันทึก</button>
                <a href="categories.php" class="btn btn-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
</body>
</html>