<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About - Local Bazar</title>
  <link rel="stylesheet" href="index.css">
</head>
<body>
  <nav class="navbar">
    <div class="navbar-logo">Local Bazar</div>
    <ul class="navbar-links">
      <li><a href="shop.php">Shop</a></li>
      <li><a href="about.php" class="active">About</a></li>
      <li><a href="contact.php">Contact</a></li>
    </ul>
    <?php if(isset($_SESSION['user_id'])): ?>
      <div style="display:flex;align-items:center;gap:18px;">
        <div style="color:#fff;font-size:1.1rem;">
          Welcome, <b><?= htmlspecialchars($_SESSION['username']) ?></b> (<?= ucfirst($_SESSION['role']) ?>)
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
          var d = document.querySelector('.dropdown-content');
          d.style.display = d.style.display === 'block' ? 'none' : 'block';
        };
        document.body.onclick = function() {
          var d = document.querySelector('.dropdown-content');
          if(d) d.style.display = 'none';
        };
      </script>
    <?php else: ?>
      <div style="display:flex;gap:10px;align-items:center;">
        <a href="login.php" class="btn" style="padding:8px 18px;background:#646cff;color:#fff;border-radius:6px;text-decoration:none;">Login</a>
        <a href="register.php" class="btn" style="padding:8px 18px;background:#4caf50;color:#fff;border-radius:6px;text-decoration:none;">Create Account</a>
      </div>
    <?php endif; ?>
  </nav>
  <main style="margin-top:90px;max-width:900px;margin-left:auto;margin-right:auto;padding:2rem 1rem 3rem 1rem;">
    <section style="background:#fafff4;border-radius:18px;box-shadow:0 4px 24px #2e8b5722;padding:2.5rem 2rem 2rem 2rem;">
      <h1 style="color:#2e8b57;font-size:2.3rem;margin-bottom:1.2rem;text-align:center;">About Local Bazar</h1>
      <p style="font-size:1.15rem;color:#444;line-height:1.7;margin-bottom:1.5rem;text-align:center;">
        Local Bazar is your trusted online marketplace for authentic, locally-made products. We connect artisans, farmers, and small businesses with customers who value quality, tradition, and community.
      </p>
      <h2 style="color:#646cff;font-size:1.3rem;margin-top:2rem;">Our Mission</h2>
      <p style="font-size:1.08rem;color:#333;line-height:1.6;">
        To empower local producers and bring their unique products to a wider audience, while providing customers with a convenient and enjoyable shopping experience.
      </p>
      <h2 style="color:#646cff;font-size:1.3rem;margin-top:2rem;">Why Shop With Us?</h2>
      <ul style="font-size:1.08rem;color:#333;line-height:1.7;margin-left:1.2rem;">
        <li>Wide variety of handmade, organic, and traditional products</li>
        <li>Support for local communities and small businesses</li>
        <li>Secure shopping and fast delivery</li>
        <li>Easy returns and responsive customer support</li>
      </ul>
      <h2 style="color:#646cff;font-size:1.3rem;margin-top:2rem;">Meet the Team</h2>
      <p style="font-size:1.08rem;color:#333;line-height:1.6;">
        We are a passionate group of entrepreneurs, technologists, and community advocates dedicated to making local shopping accessible to everyone.
      </p>
      <div style="margin-top:2.5rem;text-align:center;">
        <a href="shop.php" class="hero-btn">Start Shopping</a>
      </div>
    </section>
  </main>
  <footer class="footer">
    <div id="about">&copy; 2025 Local Bazar. All rights reserved.</div>
    <div id="contact">Contact: support@localbazar.com</div>
  </footer>
</body>
</html>
