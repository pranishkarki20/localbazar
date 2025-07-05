<?php
session_start();
// Only allow admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}
$conn = new mysqli('localhost', 'root', '', 'localbazar');
if ($conn->connect_error) {
    die('DB Error: ' . $conn->connect_error);
}
// Handle add product
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $category = $_POST['category'];
    $img_path = '';
    if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
        $img_tmp = $_FILES['img']['tmp_name'];
        $img_name = basename($_FILES['img']['name']);
        $target_dir = 'img/';
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $img_path = $target_dir . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', $img_name);
        move_uploaded_file($img_tmp, $img_path);
    }
    $stmt = $conn->prepare('INSERT INTO products (name, price, category, img, seller_id) VALUES (?, ?, ?, ?, 0)');
    $stmt->bind_param('sdss', $name, $price, $category, $img_path);
    if ($stmt->execute()) {
        $message = 'Product added!';
    } else {
        $message = 'Error adding product.';
    }
}
// Handle delete product
if (isset($_GET['delete'])) {
    $pid = intval($_GET['delete']);
    $conn->query('DELETE FROM products WHERE id=' . $pid);
    $message = 'Product deleted.';
}
$products = $conn->query('SELECT * FROM products');

// Handle set/unset sale
if (isset($_GET['sale']) && isset($_GET['pid'])) {
    $pid = intval($_GET['pid']);
    $set = intval($_GET['sale']) === 1 ? 1 : 0;
    $conn->query('UPDATE products SET on_sale=' . $set . ' WHERE id=' . $pid);
    $message = $set ? 'Product marked as On Sale!' : 'Product removed from Sale.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel - Local Bazar</title>
  <link rel="stylesheet" href="index.css">
</head>
<body>
  <nav class="navbar">
    <div class="navbar-logo">Local Bazar Admin</div>
    <ul class="navbar-links">
      <li><a href='admin.php'>Dashboard</a></li>
      <li><a href='admin_orders.php'>Orders</a></li>
      <li><a href='admin_complaints.php'>Complaints</a></li>
      <li><a href='logout.php'>Logout</a></li>
    </ul>
  </nav>
  <main style="max-width:900px;margin:40px auto 0 auto;">
    <h1 style="color:#2e8b57;">Admin Panel</h1>
    <div style="color:green;"> <?= htmlspecialchars($message) ?> </div>
    <h2>Add Product</h2>
    <form method="post" enctype="multipart/form-data" style="background:#f3f4ff;padding:18px 18px 10px 18px;border-radius:14px;box-shadow:0 2px 8px rgba(100,108,255,0.08);display:flex;flex-direction:column;gap:12px;max-width:400px;">
      <input type="hidden" name="add_product" value="1">
      <label>Product Name</label>
      <input type="text" name="name" required>
      <label>Price (Rs)</label>
      <input type="number" name="price" min="1" required>
      <label>Category</label>
      <input type="text" name="category" required>
      <label>Product Image</label>
      <input type="file" name="img" accept="image/*" required>
      <button type="submit">Add Product</button>
    </form>
    <h2 style="margin-top:2em;">All Products</h2>
    <table border="1" cellpadding="8" style="width:100%;margin-top:10px;background:#fff;">
      <tr style="background:#2e8b57;color:#fff;"><th>ID</th><th>Name</th><th>Price</th><th>Category</th><th>Image</th><th>Seller</th><th>Sale</th><th>Action</th></tr>
      <?php $users = [];
      $userq = $conn->query('SELECT id, username FROM users');
      while($u = $userq->fetch_assoc()) $users[$u['id']] = $u['username'];
      $products->data_seek(0); while($row = $products->fetch_assoc()): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td>Rs <?= number_format($row['price'],0) ?></td>
        <td><?= htmlspecialchars($row['category']) ?></td>
        <td><img src="<?= htmlspecialchars($row['img']) ?>" alt="" style="width:60px;height:40px;object-fit:cover;"></td>
        <td><?= isset($users[$row['seller_id']]) ? htmlspecialchars($users[$row['seller_id']]) : 'Admin' ?></td>
        <td>
          <?php if (!isset($row['on_sale']) || !$row['on_sale']): ?>
            <a href="admin.php?sale=1&pid=<?= $row['id'] ?>" style="color:#e74c3c;font-weight:600;">Set On Sale</a>
          <?php else: ?>
            <span style="color:#e74c3c;font-weight:700;">On Sale</span> |
            <a href="admin.php?sale=0&pid=<?= $row['id'] ?>" style="color:#888;">Remove</a>
          <?php endif; ?>
        </td>
        <td><a href="admin.php?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this product?')">Delete</a></td>
      </tr>
      <?php endwhile; ?>
    </table>
  </main>
</body>
</html>
