<?php
// buyer_dashboard.php - Buyer can view/purchase products
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: login.php');
    exit;
}
$conn = new mysqli('localhost', 'root', '', 'localbazar');
if ($conn->connect_error) {
    die('DB Error: ' . $conn->connect_error);
}
$message = '';
// Handle purchase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $buyer_id = $_SESSION['user_id'];
    // Get seller_id from products table
    $stmt = $conn->prepare('SELECT id, merchant FROM products WHERE id=?');
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $stmt->bind_result($pid, $merchant);
    if ($stmt->fetch()) {
        $stmt->close();
        // Get seller's user id
        $stmt2 = $conn->prepare('SELECT id FROM users WHERE username=? AND role="seller"');
        $stmt2->bind_param('s', $merchant);
        $stmt2->execute();
        $stmt2->bind_result($seller_id);
        if ($stmt2->fetch()) {
            $stmt2->close();
            // Insert order
            $stmt3 = $conn->prepare('INSERT INTO orders (buyer_id, seller_id, product_id, qty, status) VALUES (?, ?, ?, ?, "pending")');
            $stmt3->bind_param('iiii', $buyer_id, $seller_id, $product_id, $quantity);
            if ($stmt3->execute()) {
                $message = 'Order placed!';
            } else {
                $message = 'Error placing order.';
            }
            $stmt3->close();
        } else {
            $message = 'Seller not found.';
        }
    } else {
        $message = 'Product not found.';
    }
}
// List all products
$result = $conn->query('SELECT * FROM products');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Buyer Dashboard</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <a href="logout.php">Logout</a>
    <h3>Products</h3>
    <div style="color:green;"> <?= $message ?> </div>
    <table border="1" cellpadding="8">
        <tr><th>Name</th><th>Price</th><th>Category</th><th>Image</th><th>Buy</th></tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td>Rs <?= number_format($row['price'], 2) ?></td>
            <td><?= htmlspecialchars($row['category']) ?></td>
            <td><img src="<?= $row['img'] ?>" alt="img" width="60"></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                    <input type="number" name="quantity" value="1" min="1" style="width:50px;">
                    <button type="submit">Buy</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
