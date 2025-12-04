<?php
require_once 'functions.php';
$items = cart_items();

// update qty / remove
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(isset($_POST['update'])){
        foreach($_POST['qty'] as $id => $q){
            $id = intval($id); $q = max(0,intval($q));
            if($q === 0){
                $stmt = db()->prepare("DELETE FROM cart_items WHERE id = ?");
                $stmt->execute([$id]);
            } else {
                $stmt = db()->prepare("UPDATE cart_items SET qty = ? WHERE id = ?");
                $stmt->execute([$q, $id]);
            }
        }
        header('Location: cart.php'); exit;
    }
}
$total = 0; foreach($items as $it) $total += $it['price'] * $it['qty'];
?>
<!doctype html>
<html><head><meta charset="utf-8"/><title>Cart</title><link rel="stylesheet" href="assets/css/style.css"></head><body>
<div class="wrap">
  <h1>Your Cart</h1>
  <?php if(empty($items)): ?>
    <p>Your cart is empty. <a href="index.php">Shop now</a></p>
  <?php else: ?>
    <form method="post">
      <table class="cart-table">
        <thead><tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr></thead>
        <tbody>
          <?php foreach($items as $it): ?>
            <tr>
              <td><?=esc($it['title'])?></td>
              <td>₹<?=number_format($it['price'],2)?></td>
              <td><input type="number" name="qty[<?=esc($it['id'])?>]" value="<?=esc($it['qty'])?>" min="0"></td>
              <td>₹<?=number_format($it['price'] * $it['qty'],2)?></td>
            </tr>
          <?php endforeach;?>
        </tbody>
      </table>
      <p class="total">Total: ₹<?=number_format($total,2)?></p>
      <div class="actions">
        <button type="submit" name="update" class="btn">Update cart</button>
        <a href="checkout.php" class="btn">Proceed to checkout</a>
      </div>
    </form>
  <?php endif; ?>
</div>
</body></html>
