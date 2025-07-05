<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shop - Local Bazar</title>
  <link rel="stylesheet" href="index.css">
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


  <header class="hero">
    <div class="hero-slideshow" id="hero-slideshow">
      <div class="hero-slide active">
        <img src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80" alt="Voucher 1">
        <div class="slide-content">
          <h2>Get 20% OFF on Your First Order!</h2>
          <p>Use code <b>WELCOME20</b> at checkout. Limited time only.</p>
          <a href="#onsale-products" class="voucher-btn">Shop Now</a>
        </div>
      </div>
      <div class="hero-slide">
        <img src="https://images.unsplash.com/photo-1519125323398-675f0ddb6308?auto=format&fit=crop&w=1200&q=80" alt="Voucher 2">
        <div class="slide-content">
          <h2>Flash Sale: Up to 50% OFF</h2>
          <p>Selected products only. Hurry, while stocks last!</p>
          <a href="#onsale-products" class="voucher-btn">View Deals</a>
        </div>
      </div>
      <div class="hero-slide">
        <img src="https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=1200&q=80" alt="Voucher 3">
        <div class="slide-content">
          <h2>Free Shipping on Orders Over Rs 1000</h2>
          <p>Shop more, save more. No code needed.</p>
          <a href="#products" class="voucher-btn">Browse Products</a>
        </div>
      </div>
      <div class="hero-slideshow-controls" id="hero-slideshow-controls">
        <span class="hero-slideshow-dot active"></span>
        <span class="hero-slideshow-dot"></span>
        <span class="hero-slideshow-dot"></span>
      </div>
    </div>
  </header>

  <main>
    <section class="shop-controls" style="text-align:center;margin:30px 0 10px 0;">
      <input id="shop-search" type="text" placeholder="Search products..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" style="padding:0.7rem 1.2rem;width:60vw;max-width:400px;border-radius:8px;border:1px solid #ccc;">
      <select id="category-filter" style="padding:0.7rem 1.2rem;border-radius:8px;border:1px solid #ccc;margin-left:10px;">
        <option value="all"<?= (!isset($_GET['category']) || $_GET['category']==='all') ? ' selected' : '' ?>>All Categories</option>
        <option value="pickles"<?= (isset($_GET['category']) && $_GET['category']==='pickles') ? ' selected' : '' ?>>Pickles</option>
        <option value="handicraft"<?= (isset($_GET['category']) && $_GET['category']==='handicraft') ? ' selected' : '' ?>>Handicraft</option>
        <option value="pottery"<?= (isset($_GET['category']) && $_GET['category']==='pottery') ? ' selected' : '' ?>>Pottery</option>
        <option value="masala"<?= (isset($_GET['category']) && $_GET['category']==='masala') ? ' selected' : '' ?>>Masala</option>
        <option value="painting"<?= (isset($_GET['category']) && $_GET['category']==='painting') ? ' selected' : '' ?>>Painting</option>
        <option value="tea"<?= (isset($_GET['category']) && $_GET['category']==='tea') ? ' selected' : '' ?>>Tea</option>
        <option value="snacks"<?= (isset($_GET['category']) && $_GET['category']==='snacks') ? ' selected' : '' ?>>Snacks</option>
        <option value="food"<?= (isset($_GET['category']) && $_GET['category']==='food') ? ' selected' : '' ?>>Food</option>
        <option value="fashion"<?= (isset($_GET['category']) && $_GET['category']==='fashion') ? ' selected' : '' ?>>Fashion</option>
        <option value="food"<?= (isset($_GET['category']) && $_GET['category']==='food') ? ' selected' : '' ?>>Food</option>
      </select>
    </section>

    <section id="csu-section" style="max-width:1300px;margin:0 auto 30px auto;text-align:center;background:#f3f4ff;padding:18px 0 10px 0;border-radius:10px;box-shadow:0 2px 8px rgba(100,108,255,0.06);">
      <h2 style="color:#646cff;margin-bottom:10px;">Flash</h2>
      <div id="csu-content" style="color:#444;font-size:1.1rem;">Share your feedback, product requests, or see the latest shop updates here!</div>
    </section>

    <!-- On Sale Products -->
    <section id="onsale-products" class="products-section">
      <?php
        $conn = new mysqli('localhost', 'root', '', 'localbazar');
        if ($conn->connect_error) die('DB Error: ' . $conn->connect_error);

        $category = $_GET['category'] ?? 'all';
        $search = $_GET['search'] ?? '';
        $where = $params = [];
        $types = '';

        if ($category !== 'all') {
          $where[] = 'category = ?';
          $params[] = $category;
          $types .= 's';
        }
        if ($search) {
          $where[] = 'name LIKE ?';
          $params[] = "%$search%";
          $types .= 's';
        }

        // Pagination for On Sale products
        $limit = isset($_GET['limit_onsale']) ? intval($_GET['limit_onsale']) : 8;
        $sql = 'SELECT * FROM products WHERE on_sale=1 AND discount_percent>0';
        if ($where) $sql .= ' AND ' . implode(' AND ', $where);
        $sql .= ' LIMIT ?';
        $stmt = $conn->prepare($sql);
        if ($params) {
          $types_with_limit = $types . 'i';
          $params_with_limit = array_merge($params, [$limit]);
          $stmt->bind_param($types_with_limit, ...$params_with_limit);
        } else {
          $stmt->bind_param('i', $limit);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
          echo '<p style="text-align:center;">No products on sale right now.</p>';
        }

        while($row = $result->fetch_assoc()):
      ?>
        <?php
        // Fetch brand_name for this merchant
        $brand_name = '';
        if (!empty($row['merchant'])) {
          $brand_stmt = $conn->prepare('SELECT brand_name FROM users WHERE username=? AND role="seller"');
          $brand_stmt->bind_param('s', $row['merchant']);
          $brand_stmt->execute();
          $brand_stmt->bind_result($brand_name);
          $brand_stmt->fetch();
          $brand_stmt->close();
        }
        ?>
        <div class="product-card onsale-card product-toggle" style="border:2px solid #e74c3c;box-shadow:0 2px 12px #e74c3c22;position:relative;cursor:pointer;">
          <div class="product-main">
            <div style="position:absolute;top:0;left:0;width:100%;background:#e74c3c;color:#fff;font-weight:700;font-size:1.05em;padding:6px 0;border-radius:8px 8px 0 0;text-align:center;">🔥 On Sale</div>
            <img src="<?= $row['img'] ?>" alt="<?= htmlspecialchars($row['name']) ?>" style="margin-top:32px;">
            <h3><?= htmlspecialchars($row['name']) ?></h3>
            <div class="price">
              <span style="text-decoration:line-through;color:#888;">Rs <?= number_format($row['price'], 0) ?></span>
              <span style="color:#e74c3c;font-weight:700;margin-left:8px;">Rs <?= number_format($row['price'] * (1 - $row['discount_percent']/100), 0) ?></span>
              <span style="color:#2e8b57;font-size:0.98em;margin-left:6px;">(<?= $row['discount_percent'] ?>% OFF)</span>
            </div>
            <div style="font-size:0.95rem;color:#888;">Category: <?= htmlspecialchars($row['category']) ?></div>
            <div style="font-size:0.95rem;color:#888;">Brand: <b><?= htmlspecialchars($brand_name ?: $row['merchant']) ?></b></div>
            <button class="add-to-cart" data-id="<?= $row['id'] ?>">Add to Cart</button>
          </div>
          <div class="product-desc" style="display:none;">
            <?= htmlspecialchars($row['description']) ?>
            <div style="font-size:0.95rem;color:#888;">Brand: <b><?= htmlspecialchars($brand_name ?: $row['merchant']) ?></b></div>
            <button class="add-to-cart" data-id="<?= $row['id'] ?>" style="margin-top:18px;">Add to Cart</button>
          </div>
        </div>
      <?php endwhile; ?>
      <!-- Show More button removed as requested -->
    </section>

    <!-- Regular Products -->
    <section id="products" class="products-section">
      
      <?php
        // Pagination for Regular products
        $limit2 = isset($_GET['limit_regular']) ? intval($_GET['limit_regular']) : 8;
        $sql = 'SELECT * FROM products WHERE (on_sale=0 OR discount_percent=0 OR discount_percent IS NULL)';
        if ($where) $sql .= ' AND ' . implode(' AND ', $where);
        $sql .= ' LIMIT ?';
        $stmt = $conn->prepare($sql);
        if ($params) {
          $types_with_limit2 = $types . 'i';
          $params_with_limit2 = array_merge($params, [$limit2]);
          $stmt->bind_param($types_with_limit2, ...$params_with_limit2);
        } else {
          $stmt->bind_param('i', $limit2);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) echo '<p style="text-align:center;">No regular products found.</p>';

        while($row = $result->fetch_assoc()):
      ?>
        <?php
        // Fetch brand_name for this merchant
        $brand_name = '';
        if (!empty($row['merchant'])) {
          $brand_stmt = $conn->prepare('SELECT brand_name FROM users WHERE username=? AND role="seller"');
          $brand_stmt->bind_param('s', $row['merchant']);
          $brand_stmt->execute();
          $brand_stmt->bind_result($brand_name);
          $brand_stmt->fetch();
          $brand_stmt->close();
        }
        ?>
        <div class="product-card product-toggle" style="cursor:pointer;">
          <div class="product-main">
            <img src="<?= htmlspecialchars($row['img']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
            <h3><?= htmlspecialchars($row['name']) ?></h3>
            <div class="price">Rs <?= number_format($row['price'], 0) ?></div>
            <div style="font-size:0.95rem;color:#888;">Category: <?= htmlspecialchars($row['category']) ?></div>
            <div style="font-size:0.95rem;color:#888;">Brand: <b><?= htmlspecialchars($brand_name ?: $row['merchant']) ?></b></div>
            <button class="add-to-cart" data-id="<?= $row['id'] ?>">Add to Cart</button>
          </div>
          <div class="product-desc" style="display:none;">
            <?= htmlspecialchars($row['description']) ?>
            <div style="font-size:0.95rem;color:#888;">Brand: <b><?= htmlspecialchars($brand_name ?: $row['merchant']) ?></b></div>
            <button class="add-to-cart" data-id="<?= $row['id'] ?>" style="margin-top:18px;">Add to Cart</button>
          </div>
        </div>
      <?php endwhile; ?>
      <!-- Show More button removed as requested -->
      <?php $conn->close(); ?>
  </main>

  <footer class="footer">
    <div id="about">&copy; 2025 Local Bazar. All rights reserved.</div>
    <div id="contact">Contact: support@localbazar.com</div>
  </footer>

  <!-- Checkout Modal -->
  <div id="checkout-modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:#00000088;align-items:center;justify-content:center;z-index:999;">
    <div style="background:#fff;padding:24px;border-radius:10px;width:90%;max-width:400px;position:relative;">
      <h2 style="margin-bottom:16px;">Checkout</h2>
      <form id="checkout-form">
        <input name="phone" type="text" placeholder="Phone Number" required style="width:100%;padding:10px;margin-bottom:12px;border:1px solid #ccc;border-radius:6px;">
        <textarea name="address" placeholder="Delivery Address" required style="width:100%;padding:10px;height:80px;border:1px solid #ccc;border-radius:6px;"></textarea>
        <button type="submit" style="margin-top:12px;background:#2e8b57;color:#fff;padding:10px 20px;border-radius:6px;border:none;font-weight:600;cursor:pointer;width:100%;">Place Order</button>
      </form>
      <button id="close-modal-btn" style="position:absolute;top:10px;right:10px;background:none;border:none;font-size:20px;cursor:pointer;">×</button>
    </div>
  </div>

  <script>
    window.isBuyer = <?php echo (isset($_SESSION['user_id']) && $_SESSION['role'] === 'buyer') ? 'true' : 'false'; ?>;
  </script>
  <script src="shop.js"></script>
  <script>
    // Hero Slideshow Logic
    (function() {
      const slides = document.querySelectorAll('.hero-slide');
      const dots = document.querySelectorAll('.hero-slideshow-dot');
      let current = 0;
      let timer = null;

      function showSlide(idx) {
        slides.forEach((slide, i) => {
          slide.classList.toggle('active', i === idx);
        });
        dots.forEach((dot, i) => {
          dot.classList.toggle('active', i === idx);
        });
        current = idx;
      }

      function nextSlide() {
        showSlide((current + 1) % slides.length);
      }

      function startTimer() {
        timer = setInterval(nextSlide, 5000);
      }

      function stopTimer() {
        if (timer) clearInterval(timer);
      }

      dots.forEach((dot, i) => {
        dot.addEventListener('click', () => {
          stopTimer();
          showSlide(i);
          startTimer();
        });
      });

      showSlide(0);
      startTimer();
    })();
  </script>
  <script>
    // Toggle product description on card click
    document.querySelectorAll('.product-toggle').forEach(function(card) {
      card.addEventListener('click', function(e) {
        // Prevent toggle if add-to-cart button is clicked
        if (e.target.classList.contains('add-to-cart')) return;
        const main = card.querySelector('.product-main');
        const desc = card.querySelector('.product-desc');
        if (main.style.display !== 'none') {
          main.style.display = 'none';
          desc.style.display = 'block';
        } else {
          main.style.display = '';
          desc.style.display = 'none';
        }
      });
    });
  </script>
  <script>
    // Filter logic
    document.getElementById('category-filter').onchange = function(e) {
      const url = new URL(window.location.href);
      if (this.value === 'all') url.searchParams.delete('category');
      else url.searchParams.set('category', this.value);
      // If Ctrl or Cmd is pressed, open in new tab
      if (e.ctrlKey || e.metaKey) {
        window.open(url.toString(), '_blank');
      } else {
        window.location.href = url.toString();
      }
    };
    document.getElementById('shop-search').addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        const val = this.value.trim();
        const url = new URL(window.location.href);
        if (val) url.searchParams.set('search', val);
        else url.searchParams.delete('search');
        // If Ctrl or Cmd is pressed, open in new tab
        if (e.ctrlKey || e.metaKey) {
          window.open(url.toString(), '_blank');
        } else {
          window.location.href = url.toString();
        }
      }
    });

    // Checkout modal
    const checkoutBtn = document.getElementById('checkout-btn');
    const checkoutModal = document.getElementById('checkout-modal');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const checkoutForm = document.getElementById('checkout-form');

    if (checkoutBtn && checkoutModal && closeModalBtn && checkoutForm) {
      checkoutBtn.onclick = () => checkoutModal.style.display = 'flex';
      closeModalBtn.onclick = () => checkoutModal.style.display = 'none';
      window.onclick = e => { if (e.target === checkoutModal) checkoutModal.style.display = 'none'; };

      checkoutForm.onsubmit = function(e) {
        e.preventDefault();
        const phone = checkoutForm.phone.value.trim();
        const address = checkoutForm.address.value.trim();
        if (!phone || !address) return alert('Please enter phone and address.');
        // Get cart from localStorage
        let cart = [];
        try {
          cart = JSON.parse(localStorage.getItem('cart') || '[]');
        } catch (err) {
          cart = [];
        }
        if (!cart.length) return alert('Your cart is empty!');
        fetch('place_order.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ phone, address, cart })
        }).then(res => res.json()).then(data => {
          if (data.success) {
            alert('Order placed successfully!');
            checkoutModal.style.display = 'none';
            localStorage.removeItem('cart');
            location.reload();
          } else {
            alert('Order failed: ' + (data.message || 'Try again.'));
          }
        }).catch(() => alert('Failed to place order. Try again.'));
      };
    }
  </script>
</body>
</html>
