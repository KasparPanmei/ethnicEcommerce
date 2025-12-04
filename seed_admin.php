<?php
require_once 'config.php';
$name = 'Admin';
$email = 'kasparpanmeibboy@gmail.com';
$pass = 'Admin@123'; // change immediately!
$hash = password_hash($pass, PASSWORD_BCRYPT);
$stmt = db()->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)");
try {
  $stmt->execute([$name,$email,$hash,'admin']);
  echo "Admin created\n";
} catch(Exception $e){
  echo "Error: ".$e->getMessage();
}
