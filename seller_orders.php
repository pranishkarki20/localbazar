<?php
// File removed as unnecessary by user request.
        if ($stmt->execute()) {
            $message = 'Order status updated!';
        } else {
            $message = 'Error updating status.';
        }
        $stmt->close();
    }
}
// Fetch orders for this seller with product and buyer info
$stmt = $conn->prepare('SELECT o.id, o.qty, o.order_time, o.status, o.address, p.name AS product_name, p.img, u.username AS buyer_name, u.email AS buyer_email FROM orders o JOIN products p ON o.product_id=p.id JOIN users u ON o.buyer_id=u.id WHERE o.seller_id=? ORDER BY o.order_time DESC');
if (!$stmt) { die('Prepare failed: ' . $conn->error); }
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seller Orders - Local Bazar</title>
    <link rel="stylesheet" href="index.css">
</head>
<body style="background: linear-gradient(120deg, #f0fff5 60%, #e0f7f4 100%); min-height:100vh;">
<nav class="navbar" style="margin-bottom:40px;">
    <div class="navbar-logo">Local Bazar</div>
    <div style="color:#fff;font-size:1.1rem;">Welcome, <b><?= htmlspecialchars($_SESSION['username']) ?></b> (Seller)</div>
    <a href="seller_dashboard.php" style="color:#fff;background:#646cff;padding:8px 18px;border-radius:6px;text-decoration:none;">Dashboard</a>
    <a href="logout.php" style="color:#fff;background:#e74c3c;padding:8px 18px;border-radius:6px;text-decoration:none;">Logout</a>
</nav>
<div style="max-width:1000px;margin:0 auto;">
<h2 style="color:#2e8b57;text-align:center;">Orders Received</h2>
<div style="color:green;text-align:center;"> <?= $message ?> </div>
<div style="display:flex;flex-wrap:wrap;gap:2rem;justify-content:center;">
<?php while($row = $result->fetch_assoc()): ?>
  <div style="background:#fff;border-radius:16px;box-shadow:0 4px 24px #ffcc7022;padding:1.5rem 1.2rem;max-width:340px;width:100%;text-align:center;position:relative;">
    <img src="<?= htmlspecialchars($row['img']) ?>" alt="img" style="width:120px;height:90px;object-fit:cover;border-radius:10px;margin-bottom:1rem;box-shadow:0 2px 8px #2e8b5744;">
    <h3 style="color:#2e8b57;font-size:1.2rem;margin-bottom:0.5rem;"> <?= htmlspecialchars($row['product_name']) ?> </h3>
    <div style="color:#888;font-size:1.05rem;margin-bottom:0.5rem;">Quantity: <b><?= (int)$row['qty'] ?></b></div>
    <div style="color:#888;font-size:0.98rem;">Ordered on: <b><?= htmlspecialchars($row['order_time']) ?></b></div>
    <div style="color:#2e8b57;font-size:1.01rem;margin:0.5rem 0;">Buyer: <b><?= htmlspecialchars($row['buyer_name']) ?></b> (<a href="mailto:<?= htmlspecialchars($row['buyer_email']) ?>">Email</a>)</div>
    <div style="color:#646cff;font-size:1.01rem;margin-bottom:0.5rem;">Address: <b><?= htmlspecialchars($row['address']) ?></b></div>
    <form method="post" style="margin-top:0.7rem;">
      <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
      <select name="status" style="padding:6px 10px;border-radius:6px;">
        <option value="pending"<?= $row['status']==='pending'?' selected':''; ?>>Pending</option>
        <option value="ready"<?= $row['status']==='ready'?' selected':''; ?>>Ready</option>
        <option value="collected"<?= $row['status']==='collected'?' selected':''; ?>>Collected</option>
      </select>
      <button type="submit" style="background:#2e8b57;color:#fff;padding:6px 16px;border:none;border-radius:6px;margin-left:8px;">Update</button>
    </form>
    <div style="color:#888;font-size:0.98rem;margin-top:0.5rem;">Status: <b><?= htmlspecialchars(ucfirst($row['status'])) ?></b></div>
  </div>
<?php endwhile; ?>
</div>
</div>
</body>
</html>
