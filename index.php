<?php
require_once 'functions.php';
$user = current_user();

// simple fetch (main image)
$stmt = db()->query("SELECT p.id,p.title,p.price, pi.filename FROM products p LEFT JOIN product_images pi ON pi.product_id = p.id AND pi.is_main = 1 WHERE p.is_active = 1 GROUP BY p.id");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Shop - Home</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <header class="site-header">
    <div class="wrap">
      <a href="index.php" class="logo">My Shop</a>
      <nav>
        <?php if($user): ?>
          <a href="account.php">Hi, <?=esc($user['name'])?></a>
          <a href="logout.php">Logout</a>
        <?php else: ?>
          <a href="login.php">Login</a>
          <a href="register.php">Register</a>
        <?php endif;?>
        <a href="cart.php">Cart</a>
      </nav>
    </div>
  </header>

  <main class="wrap">
    <h1>Products</h1>
    <div class="grid">
      <?php foreach($products as $p): ?>
        <div class="card">
          <div class="thumb">
            <img src="admin/uploads/<?=esc($p['filename'] ? $p['filename'] : 'placeholder.png')?>" alt="<?=esc($p['title'])?>">
          </div>
          <h3><?=esc($p['title'])?></h3>
          <p class="price">₹<?=number_format($p['price'],2)?></p>
          <a href="product.php?id=<?=esc($p['id'])?>" class="btn">View</a>
        </div>
      <?php endforeach;?>
    </div>
  </main>
</body>
</html>
