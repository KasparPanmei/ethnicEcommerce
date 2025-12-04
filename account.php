<?php
require_once 'functions.php';
require_login();
$user = current_user();
$stmt = db()->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user['id']]); $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html><html><head><meta charset="utf-8"><title>Account</title></head><body>
<h1>Orders for <?=esc($user['name'])?></h1>
<?php foreach($orders as $o): ?>
  <div>
    <h3>Order #<?=esc($o['id'])?> — <?=esc($o['status'])?> — ₹<?=number_format($o['total'],2)?></h3>
    <small><?=esc($o['created_at'])?></small>
    <a href="order_view.php?id=<?=esc($o['id'])?>">View</a>
  </div>
<?php endforeach;?>
</body></html>
