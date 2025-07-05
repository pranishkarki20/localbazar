<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: login.php?redirect=shop.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli('localhost', 'root', '', 'localbazar_orders');
    if ($conn->connect_error) {
        die('DB Error: ' . $conn->connect_error);
    }
    $buyer_id = $_SESSION['user_id'];
    $product_id = intval($_POST['product_id']);
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $status = 'Pending';
    $created_at = date('Y-m-d H:i:s');

    // Get seller_id from products table
    $stmt = $conn->prepare('SELECT seller_id FROM products WHERE id=?');
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $stmt->bind_result($seller_id);
    if ($stmt->fetch()) {
        $stmt->close();
        // Insert order
        $stmt2 = $conn->prepare('INSERT INTO orders (product_id, buyer_id, seller_id, quantity, status, created_at) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt2->bind_param('iiiiss', $product_id, $buyer_id, $seller_id, $quantity, $status, $created_at);
        if ($stmt2->execute()) {
            header('Location: shop.php?order=success');
            exit;
        } else {
            $error = 'Order failed: ' . $conn->error;
        }
        $stmt2->close();
    } else {
        $error = 'Invalid product.';
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - Local Bazar</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
<div class="auth-card">
    <h2>Checkout</h2>
    <?php if(isset($error)): ?>
        <div class="error"> <?= htmlspecialchars($error) ?> </div>
    <?php endif; ?>
    <a href="shop.php">Back to Shop</a>
</div>
</body>
</html>
