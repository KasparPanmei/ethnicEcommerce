<?php
require_once '../functions.php';
session_start();

// Block non-admin access
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//     header("Location: ../login.php");
//     exit;
// }

// Fetch all products with main image
$stmt = db()->query("
    SELECT p.id, p.title, p.price, p.stock, pi.filename AS image
    FROM products p
    LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_main = 1
    ORDER BY p.id DESC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>All Products</h2>
<table border="1" cellpadding="8" cellspacing="0">
    <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Image</th>
        <th>Actions</th>
    </tr>

    <?php foreach ($products as $p): ?>
    <tr>
        <td><?= $p['id'] ?></td>
        <td><?= htmlspecialchars($p['title']) ?></td>
        <td><?= number_format($p['price'], 2) ?></td>
        <td><?= $p['stock'] ?></td>
        <td>
            <?php if (!empty($p['image']) && file_exists(UPLOAD_DIR . $p['image'])): ?>
                <img src="uploads/<?= $p['image'] ?>" width="80">
            <?php else: ?>
                No Image
            <?php endif; ?>
        </td>
        <td>
            <a href="product_edit.php?id=<?= $p['id'] ?>">Edit</a> |
            <a href="product_delete.php?id=<?= $p['id'] ?>" onclick="return confirm('Delete?')">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
