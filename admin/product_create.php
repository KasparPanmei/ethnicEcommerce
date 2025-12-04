<?php
require_once '../functions.php';
session_start();

// Block non-admin access
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//     header("Location: ../login.php");
//     exit;
// }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $desc = $_POST['description'];

    // Insert product (without image column)
    $stmt = db()->prepare("INSERT INTO products (title, description, price, stock) VALUES (?,?,?,?)");
    $stmt->execute([$title, $desc, $price, $stock]);
    $pid = db()->lastInsertId();

    // Upload image if provided
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png'];
        if (in_array($ext, $allowed)) {
            if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

            $fname = uniqid() . '.' . $ext;
            $target = UPLOAD_DIR . $fname;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                // Save image in product_images table
                $stmt = db()->prepare("INSERT INTO product_images (product_id, filename, is_main) VALUES (?,?,1)");
                $stmt->execute([$pid, $fname]);
            }
        }
    }

    header('Location: products.php');
    exit;
}
?>

<h2>Create Product</h2>
<form action="product_create.php" method="POST" enctype="multipart/form-data">
  <input name="title" required placeholder="Title"><br><br>
  <textarea name="description" placeholder="Description"></textarea><br><br>
  <input name="price" type="number" step="0.01" placeholder="Price"><br><br>
  <input name="stock" type="number" placeholder="No. of stocks"><br><br>
  <input type="file" name="image"><br><br>
  <button>Create</button>
</form>
