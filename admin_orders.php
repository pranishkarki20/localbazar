<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
$conn = new mysqli('localhost', 'root', '', 'localbazar');
if ($conn->connect_error) {
    die('DB Error: ' . $conn->connect_error);
}
$message = '';
$where = '';
if (isset($_GET['buyer']) && $_GET['buyer'] !== '') {
    $buyer = $conn->real_escape_string($_GET['buyer']);
    $where = "WHERE u.username LIKE '%$buyer%'";
}
// Handle cancel order
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $oid = intval($_GET['cancel']);
    $conn->query("UPDATE orders SET status='cancelled' WHERE id=$oid");
    $message = 'Order cancelled.';
}
$sql = "SELECT o.*, u.username as buyer_name, p.name as product_name, p.seller_id, s.username as seller_name FROM orders o JOIN users u ON o.buyer_id=u.id JOIN products p ON o.product_id=p.id LEFT JOIN users s ON p.seller_id=s.id $where ORDER BY o.id DESC";
$orders = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <title>Admin Orders - Local Bazar</title>
  <link rel='stylesheet' href='index.css'>
</head>
<body>
  <nav class='navbar'>
    <div class='navbar-logo'>Local Bazar Admin</div>
    <ul class='navbar-links'>
      <li><a href='admin.php'>Dashboard</a></li>
      <li><a href='admin_orders.php' class='active'>Orders</a></li>
      <li><a href='logout.php'>Logout</a></li>
    </ul>
  </nav>
  <main style='max-width:1100px;margin:40px auto 0 auto;'>
    <h1 style='color:#2e8b57;'>All Orders</h1>
    <form method='get' style='margin-bottom:18px;'>
      <input type='text' name='buyer' placeholder='Search by buyer username' value='<?= isset($_GET['buyer']) ? htmlspecialchars($_GET['buyer']) : '' ?>' style='padding:8px 14px;border-radius:7px;border:1px solid #ccc;font-size:1.08rem;'>
      <button type='submit' style='padding:8px 18px;border-radius:7px;background:#2e8b57;color:#fff;font-weight:600;'>Search</button>
    </form>
    <?php if(isset($message)): ?><div style='color:green;'><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <table border='1' cellpadding='8' style='width:100%;margin-top:10px;background:#fff;'>
      <tr style='background:#2e8b57;color:#fff;'><th>Order ID</th><th>Product</th><th>Buyer</th><th>Buyer ID</th><th>Seller</th><th>Quantity</th><th>Phone</th><th>Address</th><th>Action</th></tr>
      <?php while($row = $orders->fetch_assoc()): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['product_name']) ?></td>
        <td><?= htmlspecialchars($row['buyer_name']) ?></td>
        <td><?= $row['buyer_id'] ?></td>
        <td><?= $row['seller_name'] ? htmlspecialchars($row['seller_name']) : 'Admin' ?></td>
        <td><?= $row['quantity'] ?></td>
        <td><?= htmlspecialchars($row['phone']) ?></td>
        <td><?= htmlspecialchars($row['address']) ?></td>
        <td><a href='admin_orders.php?cancel=<?= $row['id'] ?>' onclick="return confirm('Cancel this order?')">Cancel</a></td>
      </tr>
      <?php endwhile; ?>
    </table>
  </main>
</body>
</html>
