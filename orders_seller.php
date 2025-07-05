<?php
// orders_seller.php - Show orders for the logged-in seller's products
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header('Location: login.php');
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'localbazar');
if ($conn->connect_error) {
    die('DB Error: ' . $conn->connect_error);
}

// COMMENT OUT or REMOVE after running once on your DB server
// $conn->query("ALTER TABLE orders ADD COLUMN ready_to_pick TINYINT(1) DEFAULT 0");
// Mark order as ready to pick or update status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ready_pick'])) {
        $order_id = intval($_POST['ready_pick']);
        $stmt = $conn->prepare("UPDATE orders SET ready_to_pick=1 WHERE id=?");
        if ($stmt) {
            $stmt->bind_param('i', $order_id);
            $stmt->execute();
            $stmt->close();
        } else {
            die('Prepare failed: ' . $conn->error);
        }
    }
    if (isset($_POST['picked'])) {
        $order_id = intval($_POST['picked']);
        // Add picked column if not present
        $conn->query("ALTER TABLE orders ADD COLUMN picked TINYINT(1) DEFAULT 0");
        $stmt = $conn->prepare("UPDATE orders SET picked=1 WHERE id=?");
        if ($stmt) {
            $stmt->bind_param('i', $order_id);
            $stmt->execute();
            $stmt->close();
        } else {
            die('Prepare failed: ' . $conn->error);
        }
    }
    if (isset($_POST['order_id'], $_POST['status'])) {
        $oid = intval($_POST['order_id']);
        $status = $_POST['status'];
        $allowed = ['pending','processing','shipped','delivered','cancelled'];
        if (in_array($status, $allowed)) {
            $stmt = $conn->prepare('UPDATE orders SET status=? WHERE id=?');
            $stmt->bind_param('si', $status, $oid);
            $stmt->execute();
            $stmt->close();
            header('Location: orders_seller.php');
            exit;
        }
    }
}

// Make sure products table has seller_id column; adjust if you use a different column for seller
// Show product orders for this seller
$merchant = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$stmt = $conn->prepare('SELECT o.id, p.name, o.qty, o.order_date, p.img, u.username as buyer, IFNULL(o.ready_to_pick,0) as ready_to_pick, o.status, IFNULL(o.picked,0) as picked FROM orders o JOIN products p ON o.product_id=p.id JOIN users u ON o.user_id=u.id WHERE p.merchant=? ORDER BY o.order_date DESC');
if (!$stmt) {
    die('Prepare failed: ' . $conn->error);
}
$stmt->bind_param('s', $merchant);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Orders - Local Bazar</title>
    <link rel="stylesheet" href="index.css">
</head>
<body style="background: linear-gradient(120deg, #f0fff5 60%, #e0f7f4 100%); min-height:100vh;">
    <nav class="navbar" style="margin-bottom:40px;">
        <div class="navbar-logo">Local Bazar</div>
        <div style="color:#fff;font-size:1.1rem;">Welcome, <b><?= htmlspecialchars($_SESSION['username']) ?></b> (Seller)</div>
        <a href="shop.php" style="color:#fff;background:#646cff;padding:8px 18px;border-radius:6px;text-decoration:none;">Home</a>
        <a href="logout.php" style="color:#fff;background:#e74c3c;padding:8px 18px;border-radius:6px;text-decoration:none;">Logout</a>
    </nav>
    <div style="max-width:1000px;margin:0 auto;">
        <h2 style="color:#2e8b57;text-align:center;">Orders for Your Products</h2>
        <div style="display:flex;flex-wrap:wrap;gap:2rem;justify-content:center;">
            <?php while($row = $result->fetch_assoc()): ?>
                <div style="background:#fff;border-radius:16px;box-shadow:0 4px 24px #ffcc7022;padding:1.5rem 1.2rem;max-width:340px;width:100%;text-align:center;position:relative;">
                    <img src="<?= htmlspecialchars($row['img']) ?>" alt="img" style="width:120px;height:90px;object-fit:cover;border-radius:10px;margin-bottom:1rem;box-shadow:0 2px 8px #2e8b5744;">
                    <h3 style="color:#2e8b57;font-size:1.2rem;margin-bottom:0.5rem;"><?= htmlspecialchars($row['name']) ?></h3>
                    <div style="color:#888;font-size:1.05rem;margin-bottom:0.3rem;">Quantity: <b><?= (int)$row['qty'] ?></b></div>
                    <div style="color:#888;font-size:1.05rem;margin-bottom:0.5rem;">Buyer: <b><?= htmlspecialchars($row['buyer']) ?></b></div>
                    <div style="color:#888;font-size:0.98rem;">Ordered on: <b><?= htmlspecialchars($row['order_date']) ?></b></div>
                    <form method="post" style="margin-top:1rem;">
                        <input type="hidden" name="order_id" value="<?= htmlspecialchars($row['id']) ?>">
                        <label for="status-<?= htmlspecialchars($row['id']) ?>" style="font-size:0.98em;">Status:</label>
                        <select name="status" id="status-<?= htmlspecialchars($row['id']) ?>" style="padding:4px 8px;border-radius:6px;" <?= ($row['ready_to_pick'] && isset($row['picked']) && $row['picked']) ? 'disabled' : '' ?> >
                          <?php foreach(['pending','processing','shipped','delivered','cancelled'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($row['ready_to_pick']?'disabled':'') ?> <?= $row['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                          <?php endforeach; ?>
                        </select>
                        <button type="submit" style="margin-left:6px;padding:4px 10px;border-radius:6px;background:#2e8b57;color:#fff;border:none;">Update</button>
                    </form>
                    <?php if(!$row['ready_to_pick']): ?>
                        <form method="post" style="margin-top:0.7rem;">
                            <button type="submit" name="ready_pick" value="<?= htmlspecialchars($row['id']) ?>" style="background:#4caf50;color:#fff;padding:8px 18px;border-radius:6px;border:none;font-weight:600;cursor:pointer;">Mark as Ready to Pick</button>
                        </form>
                    <?php elseif (!isset($row['picked']) || !$row['picked']): ?>
                        <form method="post" style="margin-top:0.7rem;">
                            <button type="submit" name="picked" value="<?= htmlspecialchars($row['id']) ?>" style="background:#2e8b57;color:#fff;padding:8px 18px;border-radius:6px;border:none;font-weight:600;cursor:pointer;">Mark as Picked</button>
                        </form>
                    <?php else: ?>
                        <div style="margin-top:1rem;color:#2e8b57;font-weight:600;">Product has been picked</div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
