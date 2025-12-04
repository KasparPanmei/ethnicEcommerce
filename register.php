<?php
require_once 'functions.php';
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = trim($_POST['name']); $email = trim($_POST['email']); $pass = $_POST['password'];
    if(filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($pass) >= 6){
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $stmt = db()->prepare("INSERT INTO users (name,email,password) VALUES (?,?,?)");
        try {
            $stmt->execute([$name,$email,$hash]);
            $id = db()->lastInsertId();
            login_user($id);
            header('Location: index.php'); exit;
        } catch(Exception $e){ $err = "Email already used."; }
    } else { $err = "Invalid input."; }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Register</title></head><body>
<form method="post">
  <input name="name" placeholder="Name" required>
  <input name="email" placeholder="Email" required>
  <input name="password" type="password" placeholder="Password" required>
  <button type="submit">Register</button>
  <?php if(!empty($err)) echo "<p>$err</p>"; ?>
</form>
</body></html>
