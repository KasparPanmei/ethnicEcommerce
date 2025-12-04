<?php
// config.php

// Database
define('DB_HOST','127.0.0.1');
define('DB_NAME','ecommerce_php');
define('DB_USER','root');
define('DB_PASS','Kaspar@1292'); // XAMPP default on Windows is empty

// Site
define('BASE_URL', 'http://localhost/ethnicEcommerce/'); // change folder if needed

// Razorpay (test keys) - set your keys here
// define('RAZORPAY_KEY_ID', 'rzp_test_xxxxxxx');
// define('RAZORPAY_KEY_SECRET', 'your_secret_here');

// File upload path for admin images (relative to admin folder)
define('UPLOAD_DIR', __DIR__ . '/admin/uploads/');

function db(){
    static $pdo = null;
    if($pdo === null){
        $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }
    return $pdo;
}
