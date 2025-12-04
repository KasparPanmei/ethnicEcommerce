<?php
require_once 'config.php';

// simple escape
function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES); }

/* ------------ Authentication -------------- */
function current_user(){
    if(!empty($_SESSION['user_id'])){
        $stmt = db()->prepare("SELECT id,name,email,role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}
function require_login(){
    if(!current_user()){
        header('Location: login.php'); exit;
    }
}
function login_user($user_id){
    $_SESSION['user_id'] = $user_id;
    session_regenerate_id(true);
}

/* ------------ CSRF tokens --------------- */
function csrf_token(){
    if(empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24));
    return $_SESSION['csrf'];
}
function check_csrf($token){
    return !empty($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

/* ------------ Cart helpers -------------- */
function get_cart_id(){
    if(!empty($_SESSION['cart_id'])) return $_SESSION['cart_id'];
    // find active cart for user if logged in
    if(!empty($_SESSION['user_id'])){
        $stmt = db()->prepare("SELECT id FROM carts WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $c = $stmt->fetch(PDO::FETCH_COLUMN);
        if($c){ $_SESSION['cart_id'] = $c; return $c; }
    }
    // else create new cart with session_id
    $sid = session_id();
    $stmt = db()->prepare("INSERT INTO carts (session_id, user_id) VALUES (?, ?)");
    $uid = $_SESSION['user_id'] ?? null;
    $stmt->execute([$sid, $uid]);
    $id = db()->lastInsertId();
    $_SESSION['cart_id'] = $id;
    return $id;
}
function add_to_cart($product_id, $qty=1){
    $cart_id = get_cart_id();
    $stmt = db()->prepare("SELECT price, stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$p) return false;
    if($p['stock'] < $qty) return false;
    // if same product exists increment
    $stmt = db()->prepare("SELECT id, qty FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $stmt->execute([$cart_id, $product_id]);
    $exist = $stmt->fetch(PDO::FETCH_ASSOC);
    if($exist){
        $newQty = $exist['qty'] + $qty;
        $stmt = db()->prepare("UPDATE cart_items SET qty = ? WHERE id = ?");
        $stmt->execute([$newQty, $exist['id']]);
    } else {
        $stmt = db()->prepare("INSERT INTO cart_items (cart_id, product_id, qty, price) VALUES (?,?,?,?)");
        $stmt->execute([$cart_id, $product_id, $qty, $p['price']]);
    }
    return true;
}
function cart_items(){
    $cart_id = get_cart_id();
    $stmt = db()->prepare("SELECT ci.*, p.title FROM cart_items ci JOIN products p ON p.id = ci.product_id WHERE ci.cart_id = ?");
    $stmt->execute([$cart_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
