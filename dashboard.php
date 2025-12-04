<?php
session_start();
require_once "config.php";
require_once "functions.php";

// Block access if admin is not logged in
// if(!isset($_SESSION['admin_logged_in'])) {
//     header("Location: ./login.php");
//     exit;
// }

// Fetch Admin Name
$name = $_SESSION['admin_name'] ?? "Admin";

// Fetch Dashboard Counts
try {
    // Total Products
    $stmt = db()->query("SELECT COUNT(*) FROM products");
    $total_products = $stmt->fetchColumn();

    // Total Orders
    $stmt = db()->query("SELECT COUNT(*) FROM orders");
    $total_orders = $stmt->fetchColumn();

    // Total Users
    $stmt = db()->query("SELECT COUNT(*) FROM users");
    $total_users = $stmt->fetchColumn();

} catch (Exception $e) {
    $total_products = $total_orders = $total_users = 0;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial;
            background: #f4f4f4;
            margin: 0;
        }
        .navbar {
            background: #333;
            padding: 15px;
            color: #fff;
            display: flex;
            justify-content: space-between;
        }
        .navbar a {
            color: yellow;
            text-decoration: none;
        }
        .container {
            padding: 20px;
        }
        h2 {
            margin-bottom: 20px;
        }

        /* CARD CONTAINER */
        .grid {
            display: flex;
            flex-wrap: wrap;
        }
        .card {
            width: 260px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            margin: 10px;
        }
        .card h1 {
            font-size: 42px;
            margin: 10px 0;
        }
        .card a {
            color: #007bff;
            text-decoration: none;
        }

        .small-text {
            color: gray;
        }
    </style>
</head>
<body>

<div class="navbar">
    <div><strong>Admin Dashboard</strong></div>
    <div>
        Welcome, <?php echo $name; ?> |
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>Overview</h2>

    <div class="grid">

        <!-- Products Card -->
        <div class="card">
            <h3>Products</h3>
            <h1><?php echo $total_products; ?></h1>
            <p class="small-text">total products available</p>
            <a href="./admin/products.php">→ Manage Products</a><br>
            <a href="./admin/product_create.php">→ Add Product</a>
        </div>

        <!-- Orders Card -->
        <div class="card">
            <h3>Orders</h3>
            <h1><?php echo $total_orders; ?></h1>
            <p class="small-text">customer orders received</p>
            <a href="orders.php">→ View Orders</a>
        </div>

        <!-- Users Card -->
        <div class="card">
            <h3>Users</h3>
            <h1><?php echo $total_users; ?></h1>
            <p class="small-text">registered customers</p>
            <a href="users.php">→ View Users</a>
        </div>

    </div>
</div>

</body>
</html>
