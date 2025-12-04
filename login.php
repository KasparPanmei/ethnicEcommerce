<?php
require_once 'config.php';
require_once 'functions.php';

// If already logged in, redirect
if (current_user()) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: ./dashboard.php");
        exit;
    } else {
        header("Location: index.php?msg=hello");
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<body>

<h2>Login</h2>

<form action="login_process.php" method="post">

    <label>Email</label><br>
    <input name="email" required><br><br>

    <label>Password</label><br>
    <input type="password" name="password" required><br><br>

    <label>Login As:</label><br>
    <select name="role" required>
        <option value="user">User</option>
        <option value="admin">Admin</option>
    </select><br><br>

    <button type="submit">Login</button>
</form>

</body>
</html>
