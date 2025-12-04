<?php
require_once "functions.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $selectedRole = $_POST['role'] ?? '';   // get role from select

    // Fetch user by email
    $stmt = db()->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "User not found";
        exit;
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        echo "Incorrect password";
        exit;
    }

    // Verify role matches user’s actual role
    if ($user['role'] !== $selectedRole) {
        echo "Role mismatch. You selected '$selectedRole' but this account is '{$user['role']}'";
        exit;
    }

    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];

    // Redirect based on role
    if ($user['role'] === "admin") {
        header("Location: ./dashboard.php");
        exit;
    } else {
        header("Location: index.php?msg=hello");
        exit;
    }
}

echo "Invalid access";
