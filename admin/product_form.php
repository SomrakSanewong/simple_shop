<?php
include 'auth.php';

// กำหนดไดเรกทอรีสำหรับอัปโหลด (สัมพัทธ์จากไฟล์นี้)
define('UPLOAD_DIR', '../images/');

// ตั้งค่าเริ่มต้น

$name = '';
$description = '';
$price = '';
$category_id = '';
$image_url = '../images/no_photo.png'; // URL เริ่มต้น
$id = 0;
$is_edit = false;
$error = '';

// ดึงรายการหมวดหมู่ทั้งหมดส าหรับ dropdown
$categories_result = mysqli_query($db, "SELECT id, name FROM categories ORDER BY name");

// --- (Check for Update Mode) ---
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];
    $is_edit = true;
    
    $stmt = mysqli_prepare($db, "SELECT * FROM products WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);

    if (!$product) {
        header("location: products.php");
        exit;
    } 

    // ดึงข้อมูลเดิมมาใส่ตัวแปร
    $name = $product['name'];
    $description = $product['description'];
    $price = $product['price'];
    $category_id = $product['category_id'];
    $image_url = $product['image_url'];
}

// --- (Handle Form Submission - Create & Update) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // รับค่าจากฟอร์ม
    $id = (int)$_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category_id = (int)$_POST['category_id'];
    // รับ URL รูปภาพเดิม (ถ้ามี)
    $image_url = $_POST['existing_image_url'] ?? 'images/no_photo.png';

    // ตรวจสอบข้อมูลเบื้องต้น
    if (empty($name) || empty($price) || empty($category_id)) {
        $error = "Name, Price, and Category are required.";
    } 
    
    // --- (ส่วนตรรกะการอัปโหลดไฟล์ใหม่) ---
    // ตรวจสอบว่ามีการอัปโหลดไฟล์ใหม่หรือไม่
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['product_image'];
        $file_name = $file['name'];
        $file_tmp_name = $file['tmp_name'];
        $file_size = $file['size'];
        $file_error = $file['error'];

        // ตรวจสอบนามสกุลไฟล์
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];
    
        if (in_array($file_ext, $allowed_ext)) {
            if ($file_size < 4000000) { // 4MB Limit
                // สร้างชอื่ ไฟล์ใหมท่ ี่ไมซ่ ้ากัน
                $new_file_name = uniqid('prod_', true) . '.' . $file_ext;
                $target_path = UPLOAD_DIR . $new_file_name;
            
                // ย้ายไฟล์ไปยังโฟลเดอร์ uploads
                if (move_uploaded_file($file_tmp_name, $target_path)) {
                    // หากส าเร็จ ให้ใช้ URL ใหม่นี้
                    // เราจะเก็บ path ที่สัมพัทธ์จาก root ของเว็บ (index.php)
                    $image_url = 'images/' . $new_file_name;

                    // (Optional) ลบรูปเก่าทิ้งถ้าไม่ใช่ placeholder
                    if ($is_edit && !empty($_POST['existing_image_url'])
                        && strpos($_POST['existing_image_url'], 'placeholder.com') === false) {
                    @unlink('../' . $_POST['existing_image_url']);
                }
            }
            else {
                $error = "Failed to move uploaded file.";
            }
        }
        else {
            $error = "File is too large (Max 2MB).";
        }
    }
    else {
        $error = "Invalid file type (Only JPG, JPEG, PNG, GIF allowed).";
    }
} 
// --- (จบตรรกะอัปโหลดไฟล์) ---

// ถ้าไม่มีข้อผิดพลาด ให้บันทึกลงฐานข้อมูล
if (empty($error)) {
    if ($id > 0) { // Update Mode
        $stmt = mysqli_prepare($db, "UPDATE products SET name = ?, description = ?, price = ?,category_id = ?, image_url = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "ssdisi", $name, $description, $price, $category_id, $image_url, $id);
    }
    else { // Create Mode
        $stmt = mysqli_prepare($db, "INSERT INTO products (name, description, price, category_id, image_url) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssdis", $name, $description, $price, $category_id, $image_url);
    }

    if (mysqli_stmt_execute($stmt)) {
        header("location: products.php?success=true");
        exit;
    }

    else {
        $error = "Error saving product: " . mysqli_error($db);
    }
  }
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title><?php echo $is_edit ? 'Edit' : 'Add'; ?> Product</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>

<?php include 'admin_nav.php'; ?>

<div class="container">
    <h2><?php echo $is_edit ? 'Edit' : 'Add New'; ?> Product</h2>

<?php if ($error): ?>
    <p class="error"><?php echo $error; ?></p>
<?php endif; ?>

<form action="product_form.php<?php echo $is_edit ? '?id=' . $id : ''; ?>"
    method="post" enctype="multipart/form-data">
<input type="hidden" name="id" value="<?php echo $id; ?>">

<div class="form-group">
    <label for="name">Product Name</label>
    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>" required>
</div>

<div class="form-group">
    <label for="category_id">Category</label>
    <select name="category_id" id="category_id" required>
        <option value="">-- Select Category --</option>
        <?php while ($cat = mysqli_fetch_assoc($categories_result)): ?>
            <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $category_id) ? 'selected' : ''; ?> >
                <?php echo htmlspecialchars($cat['name']); ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>

<div class="form-group">
    <label for="price">Price</label>
    <input type="number" name="price" id="price" value="<?php echo htmlspecialchars($price); ?>"
        step="0.01" required>
    </div>
<div class="form-group">
    <label for="description">Description</label>
    <textarea name="description" id="description" rows="5"><?php echo htmlspecialchars($description);
?></textarea>
    </div><div class="form-group">
        <label>Current Image</label>
        <div>
            <?php
            // ตรวจสอบว่า $image_url เป็น URL ภายนอก หรือไฟล์ที่อัปโหลด
            $display_url = $image_url;
            if (strpos($image_url, 'images/') === 0) {
                // ถ้าเป็นไฟล์ที่อัปโหลด ให้เติม ../ เพื่อให้แสดงผลในหน้า admin ถูกต้อง
                $display_url = '../' . $image_url;
            }
            ?>
            <img src="<?php echo htmlspecialchars($display_url); ?>" alt="Product Image"
                style="max-width: 150px; height: auto; border: 1px solid #ddd;">
            </div>
            <input type="hidden" name="existing_image_url"
                value="<?php echo htmlspecialchars($image_url); ?>">
            </div>

            <div class="form-group">
                <label for="product_image">Upload New Image (Optional)</label>
                <input type="file" name="product_image" id="product_image">
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">บันทึก</button>
                <a href="products.php" class="btn btn-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
</body>
</html>