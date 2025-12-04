<?php
require_once 'functions.php';
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

// If called from client handler (JSON with local_order_id)
if(!empty($data['razorpay_signature']) && !empty($data['razorpay_payment_id']) && !empty($data['razorpay_order_id'])){
    $order_id = $data['razorpay_order_id'];
    $payment_id = $data['razorpay_payment_id'];
    $signature = $data['razorpay_signature'];

    $generated_sig = hash_hmac('sha256', $order_id . '|' . $payment_id, RAZORPAY_KEY_SECRET);
    if(hash_equals($generated_sig, $signature)){
        // update payments table -> find the payment row by gateway_order_id
        $stmt = db()->prepare("UPDATE payments SET gateway_payment_id = ?, status = ? WHERE gateway_order_id = ?");
        $stmt->execute([$payment_id, 'paid', $order_id]);

        // fetch order_id from payments
        $stmt = db()->prepare("SELECT order_id FROM payments WHERE gateway_order_id = ?");
        $stmt->execute([$order_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row){
            db()->prepare("UPDATE orders SET status = ?, payment_ref = ? WHERE id = ?")->execute(['paid', $payment_id, $row['order_id']]);
        }
        echo json_encode(['success'=>true]);
    } else {
        http_response_code(400);
        echo json_encode(['success'=>false,'error'=>'Invalid signature']);
    }
    exit;
}

// Webhook handling (Razorpay will POST JSON and X-Razorpay-Signature header)
$headers = getallheaders();
if(!empty($headers['X-Razorpay-Signature'])){
    $sig = $headers['X-Razorpay-Signature'];
    $body = $raw;
    $expected = hash_hmac('sha256', $body, RAZORPAY_KEY_SECRET);
    if(hash_equals($expected, $sig)){
        // parse event
        $event = $data['event'] ?? '';
        if($event === 'payment.captured'){
            $payload = $data['payload']['payment']['entity'];
            $order_id = $payload['order_id'];
            $payment_id = $payload['id'];
            // update DB similar to above
            $stmt = db()->prepare("UPDATE payments SET gateway_payment_id=?, status=? WHERE gateway_order_id=?");
            $stmt->execute([$payment_id,'paid',$order_id]);
            $stmt = db()->prepare("SELECT order_id FROM payments WHERE gateway_order_id = ?");
            $stmt->execute([$order_id]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            if($r) db()->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute(['paid',$r['order_id']]);
        }
        http_response_code(200);
        echo 'ok';
    } else {
        http_response_code(400);
        echo 'invalid signature';
    }
    exit;
}

http_response_code(400);
echo json_encode(['success'=>false,'error'=>'Bad request']);
