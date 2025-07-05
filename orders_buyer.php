<?php
// orders_buyer.php - Show orders for the logged-in buyer
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'buyer') {
    header('Location: login.php');
    exit;
}
// Connect to the new orders database
// Use the main localbazar database (fix for DB error)
$conn = new mysqli('localhost', 'root', '', 'localbazar');
if ($conn->connect_error) {
    die('DB Error: ' . $conn->connect_error);
}
// Handle order cancellation
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['cancel_order']) &&
    is_numeric($_POST['cancel_order']) &&
    isset($_SESSION['user_id'])
) {
    $oid = intval($_POST['cancel_order']);
    // Only allow cancelling if the order belongs to this user and is not already cancelled
    $stmt = $conn->prepare('UPDATE orders SET status="cancelled" WHERE id=? AND user_id=? AND status!="cancelled"');
    $stmt->bind_param('ii', $oid, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
    header('Location: orders_buyer.php');
    exit;
}
// Get orders with product info, brand, and status
// Original query (before patch): join products on product_id, join users on products.merchant = users.username
$stmt = $conn->prepare('SELECT o.id, p.name, o.qty, o.order_date, p.img, o.status, u.brand_name, o.ready_to_pick, o.picked FROM orders o JOIN products p ON o.product_id=p.id JOIN users u ON p.merchant=u.username WHERE o.user_id=? ORDER BY o.order_date DESC');
if (!$stmt) {
    die('Prepare failed: ' . $conn->error);
}
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="index.css">
    <style>
      .orders-container {
        display: flex;
        flex-wrap: wrap;
        gap: 2rem;
        justify-content: center;
      }
      .order-card {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 4px 24px #646cff22;
        padding: 1.7rem 1.3rem 1.2rem 1.3rem;
        max-width: 340px;
        width: 100%;
        text-align: center;
        position: relative;
        transition: box-shadow 0.2s, transform 0.2s;
        border: 2px solid #f3f4ff;
      }
      .order-card:hover {
        box-shadow: 0 8px 32px #646cff44;
        transform: translateY(-4px) scale(1.025);
        border-color: #646cff;
      }
      .order-img {
        width: 120px;
        height: 90px;
        object-fit: cover;
        border-radius: 12px;
        margin-bottom: 1rem;
        box-shadow: 0 2px 8px #2e8b5744;
        border: 1.5px solid #e0e0e0;
      }
      .order-title {
        color: #2e8b57;
        font-size: 1.22rem;
        margin-bottom: 0.4rem;
        font-weight: 700;
      }
      .order-brand {
        color: #646cff;
        font-size: 1.05rem;
        margin-bottom: 0.2rem;
        font-weight: 600;
      }
      .order-qty {
        color: #888;
        font-size: 1.05rem;
        margin-bottom: 0.4rem;
      }
      .order-date {
        color: #888;
        font-size: 0.98rem;
        margin-bottom: 0.2rem;
      }
      .order-status {
        font-size: 1.05rem;
        margin-top: 0.5rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4em;
      }
      .order-status .status-icon {
        font-size: 1.2em;
        vertical-align: middle;
      }
      .order-actions {
        margin-top: 0.8rem;
      }
      .order-cancel-btn {
        background: linear-gradient(90deg,#e74c3c 60%,#646cff 100%);
        color: #fff;
        padding: 8px 18px;
        border-radius: 8px;
        border: none;
        font-weight: 600;
        cursor: pointer;
        font-size: 1rem;
        box-shadow: 0 2px 8px #e74c3c22;
        transition: background 0.2s;
      }
      .order-cancel-btn:hover {
        background: linear-gradient(90deg,#646cff 60%,#e74c3c 100%);
      }
      .order-title[title] {
        cursor: help;
        border-bottom: 1px dotted #888;
      }
      @media (max-width: 700px) {
        .orders-container { gap: 1rem; }
        .order-card { max-width: 98vw; padding: 1.1rem 0.5rem; }
        .order-title { font-size: 1.05rem; }
      }
    </style>
</head>
<body style="background: linear-gradient(120deg, #f0fff5 60%, #e0f7f4 100%); min-height:100vh;">
    <nav class="navbar" style="margin-bottom:40px;">
        <div class="navbar-logo">Local Bazar</div>
        <div style="color:#fff;font-size:1.1rem;">Welcome, <b><?= htmlspecialchars($_SESSION['username']) ?></b> (Buyer)</div>
        <a href="shop.php" style="color:#fff;background:#646cff;padding:8px 18px;border-radius:6px;text-decoration:none;">Home</a>
        <a href="logout.php" style="color:#fff;background:#e74c3c;padding:8px 18px;border-radius:6px;text-decoration:none;">Logout</a>
    </nav>
    <div style="max-width:1000px;margin:0 auto 40px auto;">
      <div style="background:#fff;border-radius:18px;box-shadow:0 4px 32px #646cff22;padding:2.5rem 1.5rem 2.2rem 1.5rem;">
        <h2 style="color:#2e8b57;text-align:center;margin-bottom:2.2rem;">My Orders</h2>
        <?php if ($result->num_rows === 0): ?>
          <div style="text-align:center;color:#888;font-size:1.15rem;padding:2.5rem 0;">
            <span style="font-size:2.2rem;">🛒</span><br>
            You have not placed any orders yet.<br>
            <a href="shop.php" style="color:#fff;background:#2e8b57;padding:10px 28px;border-radius:8px;text-decoration:none;font-weight:600;display:inline-block;margin-top:18px;">Go to Shop</a>
          </div>
        <?php else: ?>
        <div class="orders-container">
        <?php while($row = $result->fetch_assoc()): ?>
          <?php $status = strtolower($row['status']); ?>
          <div class="order-card">
            <img src="<?= htmlspecialchars($row['img']) ?>" alt="img" class="order-img">
            <div class="order-title" title="<?= htmlspecialchars($row['name']) ?> - <?= htmlspecialchars($row['brand_name']) ?>"> <?= htmlspecialchars($row['name']) ?> </div>
            <div class="order-brand">Brand: <b><?= htmlspecialchars($row['brand_name']) ?></b></div>
            <div class="order-qty">Quantity: <b><?= (int)$row['qty'] ?></b></div>
            <div class="order-date">Ordered on: <b><?= htmlspecialchars($row['order_date']) ?></b></div>
            <?php if (isset($row['ready_to_pick']) && $row['ready_to_pick']): ?>
              <?php if (isset($row['picked']) && $row['picked']): ?>
                <div style="margin-top:0.7rem;color:#2e8b57;font-weight:700;font-size:1.08em;">Your product has been picked!</div>
              <?php else: ?>
                <div style="margin-top:0.7rem;color:#27ae60;font-weight:700;font-size:1.08em;">Your order is <span style="color:#2e8b57;">ready to pick</span>!</div>
              <?php endif; ?>
            <?php endif; ?>
            <div class="order-actions">
            <?php if ($status !== 'cancelled' && (!isset($row['ready_to_pick']) || !$row['ready_to_pick'])): ?>
              <form method="post" style="display:inline;">
                <input type="hidden" name="cancel_order" value="<?= (int)$row['id'] ?>">
                <button type="submit" class="order-cancel-btn" title="Cancel this order" style="display:inline-flex;align-items:center;gap:0.5em;">
                  <span style="font-size:1.2em;line-height:1;">❌</span> Cancel Order
                </button>
              </form>
            <?php elseif ($status === 'cancelled'): ?>
              <div style="margin-top:0.7rem;color:#e74c3c;font-weight:600;">Cancelled</div>
            <?php endif; ?>
            </div>
          </div>
        <?php endwhile; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
</body>
</html>
