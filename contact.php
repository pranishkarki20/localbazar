<?php

// contact.php - Customer complaint/contact form
session_start();
// Only allow buyers and sellers
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['buyer', 'seller'])) {
    header('Location: login.php');
    exit;
}
$conn = new mysqli('localhost', 'root', '', 'localbazar');
if ($conn->connect_error) {
    die('DB Error: ' . $conn->connect_error);
}
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$message = '';
// Fetch user's complaints
$my_complaints = $conn->query("SELECT * FROM complaints WHERE user_id=$user_id AND role='$role' ORDER BY created_at DESC");
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $complaint = trim($_POST['complaint']);
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    if ($name && $email && $subject && $complaint) {
        $stmt = $conn->prepare('INSERT INTO complaints (user_id, role, name, email, phone, subject, complaint, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
        if (!$stmt) {
            $message = 'Prepare failed: ' . $conn->error;
        } else {
            $stmt->bind_param('issssss', $user_id, $role, $name, $email, $phone, $subject, $complaint);
            if ($stmt->execute()) {
                $message = 'Your complaint has been submitted!';
            } else {
                $message = 'Error submitting complaint.';
            }
            $stmt->close();
        }
    } else {
        $message = 'Please fill all fields.';
    }
    // Refresh complaints after submit
    $my_complaints = $conn->query("SELECT * FROM complaints WHERE user_id=$user_id AND role='$role' ORDER BY created_at DESC");
}
// Fetch user's phone from users table
$phone = '';
$stmt = $conn->prepare("SELECT phone FROM users WHERE id=?");
if ($stmt) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($phone);
    $stmt->fetch();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact / Complaint - Local Bazar</title>
    <link rel="stylesheet" href="index.css">
    <style>
        .contact-form {
            background: #fff;
            max-width: 420px;
            margin: 40px auto;
            border-radius: 18px;
            box-shadow: 0 4px 24px #646cff22;
            padding: 2.2rem 1.5rem 1.5rem 1.5rem;
        }
        .contact-form h2 {
            color: #2e8b57;
            margin-bottom: 1.2rem;
        }
        .contact-form input, .contact-form textarea {
            width: 100%;
            padding: 0.8rem 1rem;
            margin: 0.7rem 0;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 1.1rem;
            background: #f8f8ff;
        }
        .contact-form textarea {
            min-height: 90px;
            resize: vertical;
        }
        .contact-form button {
            width: 100%;
            background: linear-gradient(90deg, #2e8b57 60%, #646cff 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.9rem 0;
            font-size: 1.1rem;
            font-weight: 600;
            margin-top: 1rem;
            cursor: pointer;
        }
        .contact-form .msg {
            margin: 0.7rem 0;
            color: #2e8b57;
            font-weight: 600;
        }
        .complaints-list {
            margin: 30px auto 0 auto;
            max-width: 600px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 8px #646cff22;
            padding: 1.2rem 1.2rem 0.7rem 1.2rem;
        }
        .complaint-item {
            border-bottom: 1px solid #eee;
            padding: 10px 0 8px 0;
        }
        .complaint-item:last-child { border-bottom: none; }
        .complaint-status {
            font-size: 0.98em;
            font-weight: 600;
            color: #888;
        }
        .complaint-status.resolved { color: #2e8b57; }
        .complaint-status.unresolved { color: #e74c3c; }
    </style>
</head>
<body>
  <nav class="navbar">
    <div class="navbar-logo">Local Bazar</div>
    <ul class="navbar-links">
      <li><a href="shop.php" class="active">Shop</a></li>
      <li><a href="about.php">About</a></li>
      <li><a href="contact.php">Contact</a></li>
    </ul>

    <div id="cart-btn" style="position:relative;cursor:pointer;margin-left:18px;">
      <span style="font-size:1.5rem;color:#fff;">🛒</span>
      <span id="cart-count" style="position:absolute;top:-10px;right:-12px;background:linear-gradient(90deg,#e74c3c 60%,#646cff 100%);color:#fff;font-size:1.05rem;padding:3px 9px;border-radius:50%;font-weight:700;box-shadow:0 2px 8px #e74c3c44;">0</span>
      <div id="cart-dropdown" style="display:none;position:absolute;right:0;top:120%;background:#fff;min-width:320px;box-shadow:0 8px 32px #2e8b5744;border-radius:14px;z-index:100;padding:22px 22px 18px 22px;">
        <div id="cart-items" style="max-height:260px;overflow-y:auto;"></div>
        <div id="cart-total" style="margin-top:16px;font-weight:700;font-size:1.15rem;color:#2e8b57;"></div>
        <button id="checkout-btn" style="margin-top:18px;background:linear-gradient(90deg,#2e8b57 60%,#646cff 100%);color:#fff;padding:12px 0;border-radius:8px;border:none;font-weight:700;cursor:pointer;width:100%;font-size:1.1rem;box-shadow:0 2px 8px #2e8b5744;transition:background 0.2s, color 0.2s;">Checkout</button>
      </div>
    </div>

    <?php if(isset($_SESSION['user_id'])): ?>
      <div style="display:flex;align-items:center;gap:18px;">
        <div style="color:#fff;font-size:1.1rem;">
          Welcome, <b><?= htmlspecialchars($_SESSION['username']) ?></b> 
        </div>
        <div class="dropdown" style="position:relative;">
          <button style="background:#fff;color:#2e8b57;padding:8px 18px;border-radius:6px;border:none;font-weight:600;cursor:pointer;">Account ▼</button>
          <div class="dropdown-content" style="display:none;position:absolute;right:0;top:110%;background:#fff;min-width:180px;box-shadow:0 4px 16px #2e8b5744;border-radius:8px;z-index:100;">
            <?php if($_SESSION['role']==='buyer'): ?>
              <a href="orders_buyer.php" style="display:block;padding:12px 18px;color:#2e8b57;text-decoration:none;">My Orders</a>
            <?php else: ?>
              <a href="seller_dashboard.php" style="display:block;padding:12px 18px;color:#2e8b57;text-decoration:none;">List Products</a>
              <a href="orders_seller.php" style="display:block;padding:12px 18px;color:#2e8b57;text-decoration:none;">Product Orders</a>
            <?php endif; ?>
            <a href="logout.php" style="display:block;padding:12px 18px;color:#e74c3c;text-decoration:none;">Logout</a>
          </div>
        </div>
      </div>
      <script>
        document.querySelector('.dropdown button').onclick = function(e) {
          e.stopPropagation();
          const d = document.querySelector('.dropdown-content');
          d.style.display = d.style.display === 'block' ? 'none' : 'block';
        };
        document.body.onclick = function() {
          const d = document.querySelector('.dropdown-content');
          if (d) d.style.display = 'none';
        };
      </script>
    <?php else: ?>
      <div style="display:flex;gap:10px;align-items:center;">
        <a href="login.php?redirect=shop.php" class="btn" style="padding:8px 18px;background:#646cff;color:#fff;border-radius:6px;text-decoration:none;">Login</a>
        <a href="register.php" class="btn" style="padding:8px 18px;background:#4caf50;color:#fff;border-radius:6px;text-decoration:none;">Create Account</a>
      </div>
    <?php endif; ?>
  </nav>
    <div class="contact-form">
        <h2>Contact / Complaint</h2>
        <?php if ($message): ?>
            <div class="msg"> <?= htmlspecialchars($message) ?> </div>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="name" placeholder="Your Name" required>
            <input type="email" name="email" placeholder="Your Email" required>
            <input type="text" name="phone" placeholder="Your Phone" value="<?= htmlspecialchars($phone) ?>" readonly required>
            <input type="text" name="subject" placeholder="Subject" required>
            <textarea name="complaint" placeholder="Your complaint or message..." required></textarea>
            <button type="submit">Submit</button>
        </form>
    </div>
    <?php if ($my_complaints && $my_complaints->num_rows > 0): ?>
    <div class="complaints-list">
        <h3 style="color:#646cff;margin-bottom:10px;">Your Complaints</h3>
        <?php while($row = $my_complaints->fetch_assoc()): ?>
        <div class="complaint-item">
            <div><b>Subject:</b> <?= htmlspecialchars($row['subject']) ?></div>
            <div><b>Message:</b> <?= nl2br(htmlspecialchars($row['complaint'])) ?></div>
            <div class="complaint-status <?= $row['is_resolved'] ? 'resolved' : 'unresolved' ?>">
                Status: <?= $row['is_resolved'] ? 'Resolved' : 'Unresolved' ?>
            </div>
            <div style="font-size:0.92em;color:#888;">Submitted: <?= htmlspecialchars($row['created_at']) ?></div>
        </div>
        <?php endwhile; ?>
    </div>
    <?php endif; ?>
</body>
</html>
