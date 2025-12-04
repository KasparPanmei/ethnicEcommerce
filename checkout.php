<?php
require_once 'functions.php';
require_login();
$user = current_user();
$items = cart_items();
if(empty($items)){ header('Location: cart.php'); exit; }

$total = 0; foreach($items as $it) $total += $it['price'] * $it['qty'];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // basic address insert (should validate)
    $line1 = trim($_POST['line1'] ?? '');
    $city  = trim($_POST['city'] ?? '');
    $stmt = db()->prepare("INSERT INTO addresses (user_id,line1,city,state,postal,country,phone) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$user['id'],$line1,$_POST['city'],$_POST['state'],$_POST['postal'],$_POST['country'],$_POST['phone']]);
    $addr_id = db()->lastInsertId();

    // create order
    $stmt = db()->prepare("INSERT INTO orders (user_id,address_id,total,payment_gateway,status) VALUES (?,?,?,?,?)");
    $stmt->execute([$user['id'],$addr_id,$total,'razorpay','pending']);
    $order_id = db()->lastInsertId();

    // create order_items & reduce stock
    foreach($items as $it){
        $stmt = db()->prepare("INSERT INTO order_items (order_id,product_id,qty,unit_price) VALUES (?,?,?,?)");
        $stmt->execute([$order_id,$it['product_id'],$it['qty'],$it['price']]);
        $stmt2 = db()->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
        $stmt2->execute([$it['qty'],$it['product_id']]);
    }

    // clear cart
    $cid = get_cart_id();
    db()->prepare("DELETE FROM cart_items WHERE cart_id = ?")->execute([$cid]);

    // Create Razorpay order via HTTP (no SDK)
    $amount_paise = intval($total * 100);
    $post = json_encode([
        'amount' => $amount_paise,
        'currency' => 'INR',
        'receipt' => "order_rcpt_$order_id",
        'payment_capture' => 1
    ]);
    $url = "https://api.razorpay.com/v1/orders";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ":" . RAZORPAY_KEY_SECRET);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $resp = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if($resp === false || $httpcode >= 400){
        // handle error
        die("Payment gateway error");
    }
    $data = json_decode($resp, true);
    // store payment row
    $stmt = db()->prepare("INSERT INTO payments (order_id,gateway,gateway_order_id,amount,currency,status) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$order_id,'razorpay',$data['id'],$total,'INR','created']);

    // send values to client for checkout
    // minimal page to call Razorpay checkout with options
    ?>
    <!doctype html>
    <html><head><meta charset="utf-8"><title>Checkout</title></head>
    <body>
      <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
      <script>
        var options = {
          "key": "<?=RAZORPAY_KEY_ID?>",
          "amount": "<?=$amount_paise?>",
          "currency": "INR",
          "name": "My Shop",
          "description": "Order #<?=$order_id?>",
          "order_id": "<?=esc($data['id'])?>",
          "handler": function (response){
            // send to server to verify & finalize
            fetch('razorpay_verify.php', {
              method: 'POST',
              headers: {'Content-Type':'application/json'},
              body: JSON.stringify({
                razorpay_order_id: response.razorpay_order_id,
                razorpay_payment_id: response.razorpay_payment_id,
                razorpay_signature: response.razorpay_signature,
                local_order_id: <?=$order_id?>
              })
            }).then(r=>r.json()).then(j=>{
               if(j.success) { window.location = 'account.php'; }
               else { alert('Payment verification failed'); }
            });
          },
          "prefill": {
            "name": "<?=esc($user['name'])?>",
            "email": "<?=esc($user['email'])?>"
          }
        };
        var rzp = new Razorpay(options);
        rzp.open();
      </script>
    </body></html>
    <?php
    exit;
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Checkout</title><link rel="stylesheet" href="assets/css/style.css"></head>
<body>
<div class="wrap">
  <h1>Checkout</h1>
  <form method="post">
    <input type="hidden" name="csrf" value="<?=csrf_token()?>">
    <label>Address line1<input name="line1" required></label>
    <label>City<input name="city" required></label>
    <label>State<input name="state"></label>
    <label>Postal<input name="postal"></label>
    <label>Country<input name="country" value="India"></label>
    <label>Phone<input name="phone"></label>
    <button type="submit" class="btn">Pay ₹<?=number_format($total,2)?></button>
  </form>
</div>
</body></html>
