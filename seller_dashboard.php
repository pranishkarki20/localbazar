<?php
// Always start session to avoid undefined $_SESSION
if (session_status() === PHP_SESSION_NONE) session_start();
// DB connection (fix for undefined $conn)
$conn = new mysqli('localhost', 'root', '', 'localbazar');
if ($conn->connect_error) {
    die('DB Error: ' . $conn->connect_error);
}
// Always define $message to avoid undefined variable warning
$message = '';

// Handle product removal
if (isset($_POST['remove_product']) && isset($_POST['pid'])) {
    $pid = intval($_POST['pid']);
    $merchant = isset($_SESSION['username']) ? $_SESSION['username'] : '';
    // Remove product only if it belongs to this merchant
    $stmt = $conn->prepare('DELETE FROM products WHERE id=? AND merchant=?');
    if ($stmt) {
        $stmt->bind_param('is', $pid, $merchant);
        if ($stmt->execute()) {
            $message = 'Product removed!';
        } else {
            $message = 'Error removing product.';
        }
        $stmt->close();
    }
}

// Add product with image upload
if (isset($_POST['name']) && isset($_POST['price']) && isset($_POST['category'])) {
    $name = trim($_POST['name']);
    $price = floatval($_POST['price']);
    $category = $_POST['category'];
    $img_path = '';

    if (isset($_FILES['img_upload']) && $_FILES['img_upload']['error'] === UPLOAD_ERR_OK) {
        $img_tmp = $_FILES['img_upload']['tmp_name'];
        $img_name = basename($_FILES['img_upload']['name']);
        $target_dir = 'img/';
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $img_path = $target_dir . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', $img_name);
        move_uploaded_file($img_tmp, $img_path);
    }

    if ($name && $price && $category && $img_path) {
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $merchant = isset($_SESSION['username']) ? $_SESSION['username'] : '';
        $stmt = $conn->prepare('INSERT INTO products (name, price, category, img, description, merchant) VALUES (?, ?, ?, ?, ?, ?)');
        if (!$stmt) {
            die('Prepare failed (Insert): ' . $conn->error);
        }
        $stmt->bind_param('sdssss', $name, $price, $category, $img_path, $description, $merchant);
        if ($stmt->execute()) {
            $message = 'Product added!';
        } else {
            $message = 'Error adding product.';
        }
        $stmt->close();
    } else {
        $message = 'Fill all fields and upload an image.';
    }
}

// Handle set/unset sale for seller's products
if (isset($_POST['set_sale']) && isset($_POST['pid'])) {
    $pid = intval($_POST['pid']);
    $on_sale = isset($_POST['on_sale']) ? 1 : 0;
    $sale_percent = $on_sale ? max(0, min(100, intval($_POST['sale_percent']))) : 0;
    $merchant = isset($_SESSION['username']) ? $_SESSION['username'] : '';
    $stmt = $conn->prepare('UPDATE products SET on_sale=?, discount_percent=? WHERE id=? AND merchant=?');
    $stmt->bind_param('iiis', $on_sale, $sale_percent, $pid, $merchant);
    $stmt->execute();
    $stmt->close();
    $message = $on_sale ? 'Product marked as On Sale!' : 'Product removed from Sale.';
}

// List products by this seller
// Get seller's brand name
$merchant = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$brand_name = '';
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $brand_stmt = $conn->prepare('SELECT brand_name FROM users WHERE id=?');
    $brand_stmt->bind_param('i', $uid);
    $brand_stmt->execute();
    $brand_stmt->bind_result($brand_name);
    $brand_stmt->fetch();
    $brand_stmt->close();
}
$stmt = $conn->prepare('SELECT * FROM products WHERE merchant = ?');
if (!$stmt) {
    die('Prepare failed (Select): ' . $conn->error);
}
$stmt->bind_param('s', $merchant);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Seller Dashboard</title>
    <link rel="stylesheet" href="index.css">
</head>
<body>

<nav class="navbar" style="margin-bottom:40px;">
    <div class="navbar-logo">Local Bazar</div>
    <div style="color:#fff;font-size:1.1rem;">
        Welcome, <b><?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Seller' ?></b> (Seller)
    </div>
    <a href="seller_dashboard.php" style="color:#fff;background:#2e8b57;padding:8px 18px;border-radius:6px;text-decoration:none;margin-right:10px;">Dashboard</a>
    <a href="orders_seller.php" style="color:#fff;background:#646cff;padding:8px 18px;border-radius:6px;text-decoration:none;margin-right:10px;">My Orders</a>
    <a href="logout.php" style="color:#fff;background:#e74c3c;padding:8px 18px;border-radius:6px;text-decoration:none;">Logout</a>
</nav>

<div style="max-width:900px;margin:0 auto;">
    <div class="auth-card" style="margin:0 auto 2rem auto;max-width:420px;">
        <h3 style="color:#2e8b57;">Add Product</h3>
        <form method="post" enctype="multipart/form-data" class="product-form">
          <h2 class="form-title">Add New Product</h2>
          <div class="form-group">
            <label for="name">Product Name</label>
            <input type="text" id="name" name="name" required placeholder="e.g. Organic Honey">
          </div>
          <div class="form-group">
            <label for="price">Price (Rs)</label>
            <input type="number" id="price" name="price" min="1" required placeholder="e.g. 250">
          </div>
          <div class="form-group">
            <label for="category">Category</label>
            <select id="category" name="category" required>
              <option value="">Select Category</option>
              <option value="pickles">Pickles</option>
              <option value="handicraft">Handicraft</option>
              <option value="pottery">Pottery</option>
              <option value="masala">Masala</option>
              <option value="painting">Painting</option>
              <option value="tea">Tea</option>
              <option value="snacks">Snacks</option>
              <option value="other">Other</option>
            </select>
          </div>
          <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" required placeholder="Short product description" rows="3"></textarea>
          </div>
          <div class="form-group">
            <label for="img_upload">Product Image</label>
            <input type="file" id="img_upload" name="img_upload" accept="image/*" required onchange="previewImage(event)">
            <div class="img-preview-wrapper">
              <img id="imgPreview" src="#" alt="Image Preview" style="display:none;">
            </div>
          </div>
          <button type="submit" class="submit-btn">Add Product</button>
        </form>
        <div style="color:green;margin-top:10px;"><?= $message ?></div>
        <style>
        .product-form {
          max-width: 420px;
          margin: 0 auto 30px auto;
          padding: 28px 28px 18px 28px;
          background: #fff;
          border-radius: 14px;
          box-shadow: 0 4px 24px #2e8b5744;
          display: flex;
          flex-direction: column;
          gap: 18px;
        }
        .form-title {
          margin-bottom: 8px;
          color: #2e8b57;
          font-size: 1.5rem;
          text-align: center;
        }
        .form-group {
          display: flex;
          flex-direction: column;
          gap: 6px;
        }
        .form-group label {
          font-weight: 600;
          color: #2e8b57;
          margin-bottom: 2px;
        }
        .form-group input[type="text"],
        .form-group input[type="number"] {
          padding: 10px 12px;
          border-radius: 7px;
          border: 1px solid #ccc;
          font-size: 1.08rem;
          transition: border 0.2s;
        }
        .form-group input:focus {
          border: 1.5px solid #646cff;
          outline: none;
        }
        .form-group input[type="file"] {
          padding: 8px 0;
        }
        .img-preview-wrapper {
          width: 100%;
          min-height: 40px;
          display: flex;
          align-items: center;
          justify-content: center;
          background: #f3f4ff;
          border-radius: 8px;
          margin-top: 6px;
          box-shadow: 0 2px 8px rgba(100,108,255,0.06);
        }
        #imgPreview {
          display: none;
          width: 100%;
          max-height: 180px;
          object-fit: contain;
          border-radius: 8px;
        }
        .submit-btn {
          margin-top: 10px;
          background: linear-gradient(90deg,#2e8b57 60%,#646cff 100%);
          color: #fff;
          font-weight: 700;
          font-size: 1.1rem;
          padding: 12px 0;
          border-radius: 8px;
          border: none;
          cursor: pointer;
          transition: background 0.2s;
        }
        .submit-btn:hover {
          background: linear-gradient(90deg,#646cff 60%,#2e8b57 100%);
        }
        </style>
        <script>
        function previewImage(event) {
          const input = event.target;
          const preview = document.getElementById('imgPreview');
          if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
              preview.src = e.target.result;
              preview.style.display = 'block';
            };
            reader.readAsDataURL(input.files[0]);
          } else {
            preview.src = '#';
            preview.style.display = 'none';
          }
        }
        </script>
    </div>

    <h3 style="color:#2e8b57;text-align:center;margin-bottom:1.5rem;">Your Products</h3>
    <div style="text-align:center;margin-bottom:1.2rem;font-size:1.08em;color:#646cff;">
      <b>Your Brand:</b> <span style="color:#2e8b57;"><?= htmlspecialchars($brand_name) ?></span>
    </div>
    <div class="seller-products-grid">
    <?php while ($row = $result->fetch_assoc()): ?>
      <?php 
        // Defensive: ensure $row is not null and all keys exist
        if (!$row) continue;
        $discount = isset($row['discount_percent']) ? (int)$row['discount_percent'] : 0;
        if (!isset($row['discount_percent'])) $discount = 0;
        $isSale = !empty($row['on_sale']) && $discount > 0;
        $finalPrice = $isSale ? $row['price'] * (1 - $discount/100) : $row['price'];
      ?>
      <div class="seller-product-card<?= $isSale ? ' sale' : '' ?>">
        <?php if ($isSale): ?>
          <div class="sale-badge">On Sale</div>
        <?php endif; ?>
        <img src="<?= htmlspecialchars($row['img']) ?>" alt="img" class="prod-img">
        <h4 class="prod-title"> <?= htmlspecialchars($row['name']) ?> </h4>
        <div class="prod-price">
          <?php if ($isSale): ?>
            <span class="old-price">Rs <?= number_format($row['price'], 2) ?></span>
            <span class="new-price">Rs <?= number_format($finalPrice, 2) ?></span>
            <span class="discount">(<?= $discount ?>% OFF)</span>
          <?php else: ?>
            <span class="new-price">Rs <?= number_format($row['price'], 2) ?></span>
          <?php endif; ?>
        </div>
        <div class="prod-category">Category: <b><?= htmlspecialchars($row['category']) ?></b></div>
        <div class="prod-desc">
          <span style="color:#2e8b57;font-weight:600;">Brand: <?= htmlspecialchars($brand_name) ?></span><br>
          <?= htmlspecialchars(mb_strimwidth($row['description'], 0, 60, '...')) ?>
        </div>
        <form method="post" class="prod-action-form">
          <input type="hidden" name="pid" value="<?= $row['id'] ?>">
          <button type="submit" name="remove_product" value="1" class="remove-btn">Remove</button>
        </form>
        <form method="post" class="prod-action-form sale-form">
          <input type="hidden" name="pid" value="<?= $row['id'] ?>">
          <label class="sale-label">Sale Option:
            <input type="radio" name="on_sale" value="1" <?= $row['on_sale'] ? 'checked' : '' ?> onclick="this.form.sale_percent.style.display='inline-block';"> Sale
            <input type="radio" name="on_sale" value="0" <?= !$row['on_sale'] ? 'checked' : '' ?> onclick="this.form.sale_percent.style.display='none';"> Normal
          </label>
          <input type="number" name="sale_percent" min="1" max="90" value="<?= $row['sale_percent'] ?>" placeholder="% Off" class="sale-input" style="display:<?= $row['on_sale'] ? 'inline-block' : 'none' ?>;">
          <button type="submit" name="set_sale" class="save-btn">Save</button>
        </form>
        <?php if ($row['on_sale']): ?>
          <div class="sale-info">On Sale: <?= (int)$row['sale_percent'] ?>% OFF</div>
        <?php endif; ?>
      </div>
    <?php endwhile; ?>
    </div>

    <style>
    .seller-products-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      gap: 2rem;
      justify-items: center;
      margin-bottom: 2rem;
    }
    .seller-product-card {
      background: #fff;
      border-radius: 18px;
      box-shadow: 0 4px 24px #2e8b5744;
      padding: 1.7rem 1.2rem 1.2rem 1.2rem;
      max-width: 290px;
      width: 100%;
      text-align: center;
      position: relative;
      transition: box-shadow 0.2s, transform 0.2s;
      margin-bottom: 0.5rem;
    }
    .seller-product-card:hover {
      box-shadow: 0 8px 32px #646cff44;
      transform: translateY(-4px) scale(1.025);
    }
    .sale-badge {
      position: absolute;
      top: 16px;
      left: 16px;
      background: linear-gradient(90deg,#e74c3c 60%,#ffcc70 100%);
      color: #fff;
      font-weight: 700;
      font-size: 0.98rem;
      padding: 4px 14px;
      border-radius: 8px;
      box-shadow: 0 2px 8px #e74c3c33;
      z-index: 2;
      letter-spacing: 1px;
    }
    .prod-img {
      width: 120px;
      height: 90px;
      object-fit: cover;
      border-radius: 10px;
      margin-bottom: 1rem;
      box-shadow: 0 2px 8px #2e8b5744;
    }
    .prod-title {
      color: #2e8b57;
      font-size: 1.13rem;
      margin-bottom: 0.5rem;
      font-weight: 700;
    }
    .prod-price {
      font-size: 1.13rem;
      font-weight: 600;
      margin-bottom: 0.3rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5em;
    }
    .old-price {
      text-decoration: line-through;
      color: #888;
      font-size: 0.98em;
    }
    .new-price {
      color: #e74c3c;
      font-weight: 700;
      font-size: 1.08em;
      margin-left: 8px;
    }
    .discount {
      color: #2e8b57;
      font-size: 0.98em;
      margin-left: 6px;
    }
    .prod-category {
      color: #888;
      font-size: 0.98rem;
      margin-bottom: 0.3rem;
    }
    .prod-desc {
      color: #444;
      font-size: 0.97rem;
      margin-bottom: 0.7rem;
      min-height: 1.5em;
    }
    .prod-action-form {
      display: inline-block;
      margin: 0 4px 6px 4px;
    }
    .remove-btn {
      background: #e74c3c;
      color: #fff;
      padding: 6px 14px;
      border-radius: 6px;
      border: none;
      font-weight: 600;
      cursor: pointer;
      margin-bottom: 4px;
      transition: background 0.2s;
    }
    .remove-btn:hover {
      background: #b92d2d;
    }
    .sale-form {
      margin-top: 0.2em;
    }
    .sale-label {
      font-size: 0.98em;
      color: #2e8b57;
      font-weight: 600;
      margin-right: 6px;
    }
    .sale-input {
      width: 60px;
      margin-left: 8px;
      padding: 3px 6px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 0.98em;
      transition: border 0.2s;
    }
    .sale-input:focus {
      border: 1.5px solid #646cff;
      outline: none;
    }
    .save-btn {
      margin-left: 8px;
      background: #646cff;
      color: #fff;
      padding: 4px 12px;
      border-radius: 6px;
      border: none;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s;
    }
    .save-btn:hover {
      background: #2e8b57;
    }
    .sale-info {
      color: #e74c3c;
      font-weight: 700;
      margin-top: 6px;
      font-size: 0.98em;
    }
    </style>
</div>
</body>
</html>
