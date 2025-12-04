<?php
require_once 'functions.php';
$id = intval($_GET['id'] ?? 0);
if(!$id) { header('Location: index.php'); exit; }
$stmt = db()->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$p){ header('Location: index.php'); exit; }
$imgStmt = db()->prepare("SELECT filename FROM product_images WHERE product_id = ? ORDER BY is_main DESC");
$imgStmt->execute([$id]); $images = $imgStmt->fetchAll(PDO::FETCH_COLUMN);

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $qty = max(1,intval($_POST['qty'] ?? 1));
    add_to_cart($id, $qty);
    header('Location: cart.php'); exit;
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/><title><?=esc($p['title'])?></title>
<link rel="stylesheet" href="assets/css/style.css"></head>
<body>
  <div class="wrap">
    <a href="index.php">← Back to products</a>
    <div class="product-detail">
      <div class="gallery">
        <?php foreach($images as $img): ?>
          <img src="admin/uploads/<?=esc($img)?>" alt="">
        <?php endforeach;?>
      </div>
      <div class="info">
        <h1><?=esc($p['title'])?></h1>
        <p><?=nl2br(esc($p['description']))?></p>
        <p class="price">₹<?=number_format($p['price'],2)?></p>
        <form method="post">
          <label>Quantity <input type="number" name="qty" value="1" min="1" max="<?=esc($p['stock'])?>"></label>
          <button type="submit" class="btn">Add to cart</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
