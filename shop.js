// Ensure category filter reflects current selection and works on change
document.addEventListener('DOMContentLoaded', function() {
  var catFilter = document.getElementById('category-filter');
  if (catFilter) {
    var urlCat = (new URL(window.location.href)).searchParams.get('category') || 'all';
    catFilter.value = urlCat;
    catFilter.onchange = function() {
      const url = new URL(window.location.href);
      if (this.value === 'all') url.searchParams.delete('category');
      else url.searchParams.set('category', this.value);
      window.location.href = url.toString();
    };
  }
});
// Persistent cart array stored in localStorage
let cart = [];
if (localStorage.getItem('cart')) {
  try {
    cart = JSON.parse(localStorage.getItem('cart'));
    updateCartCount();
    updateCartDropdown();
  } catch {
    cart = [];
  }
}

// Update the cart count badge in navbar
function updateCartCount() {
  const cartCountEl = document.getElementById('cart-count');
  if (cartCountEl) {
    const totalQty = cart.reduce((acc, item) => acc + item.qty, 0);
    cartCountEl.textContent = totalQty;
  }
}

// Update the cart dropdown content
function updateCartDropdown() {
  const itemsDiv = document.getElementById('cart-items');
  const totalDiv = document.getElementById('cart-total');
  if (!itemsDiv || !totalDiv) return;

  if (cart.length === 0) {
    itemsDiv.innerHTML = '<em>Your cart is empty.</em>';
    totalDiv.textContent = '';
    return;
  }

  itemsDiv.innerHTML = cart.map(item => {
    const name = item.name || 'Unknown';
    const qty = item.qty || 1;
    const priceRs = (item.price || 0) * 133;
    const totalPriceRs = (priceRs * qty).toFixed(0);
    const img = item.img || '';
    return `
      <div style="display:flex;align-items:center;gap:12px;justify-content:space-between;padding:7px 0;border-bottom:1px solid #f0f0f0;">
        <img src="${img}" alt="${name}" style="width:44px;height:44px;object-fit:cover;border-radius:8px;border:1px solid #eee;box-shadow:0 1px 4px #8882;">
        <div style="flex:1;min-width:0;">
          <span style="font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block;">${name} <span style="color:#888;font-size:0.98em;">x${qty}</span></span>
        </div>
        <span style="color:#2e8b57;font-weight:600;white-space:nowrap;">Rs ${totalPriceRs}</span>
        <button class='remove-item' data-id='${item.id}' style='background:#e74c3c;border:none;color:#fff;font-weight:700;font-size:1.1em;padding:3px 12px;border-radius:6px;cursor:pointer;margin-left:10px;transition:background 0.2s;'>Remove</button>
      </div>
    `;
  }).join('');

  const total = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
  totalDiv.textContent = `Total: Rs ${(total * 133).toFixed(0)}`;
}

// Show a small notification on adding to cart
function showCartNotification(msg) {
  let cartNotif = document.getElementById('cart-notification');
  if (!cartNotif) {
    cartNotif = document.createElement('div');
    cartNotif.id = 'cart-notification';
    cartNotif.style.position = 'fixed';
    cartNotif.style.top = '18px';
    cartNotif.style.right = '30px';
    cartNotif.style.background = '#2e8b57';
    cartNotif.style.color = '#fff';
    cartNotif.style.padding = '12px 22px';
    cartNotif.style.borderRadius = '8px';
    cartNotif.style.fontWeight = 'bold';
    cartNotif.style.fontSize = '1.08rem';
    cartNotif.style.boxShadow = '0 2px 12px #2e8b5744';
    cartNotif.style.zIndex = 2000;
    cartNotif.style.display = 'none';
    document.body.appendChild(cartNotif);
  }
  cartNotif.textContent = msg;
  cartNotif.style.display = 'block';
  clearTimeout(cartNotif._timeout);
  cartNotif._timeout = setTimeout(() => {
    cartNotif.style.display = 'none';
  }, 1700);
}

// Listen globally for clicks to handle add-to-cart, remove-item, login, logout
document.addEventListener('click', e => {
  // Add to Cart
  if (e.target.classList.contains('add-to-cart')) {
    const card = e.target.closest('.product-card');
    if (!card) return;

    const id = +e.target.dataset.id;
    const nameEl = card.querySelector('h3, h4');
    const name = nameEl ? nameEl.textContent.trim() : 'Unknown';
    const imgEl = card.querySelector('img');
    const img = imgEl ? imgEl.src : '';
    let price = 0;
    // Detect if this is an on-sale product
    const onsale = card.classList.contains('onsale-card');
    if (onsale) {
      // Get the discounted price from the .price span (the second span)
      const priceSpans = card.querySelectorAll('.price span');
      if (priceSpans.length > 1) {
        // Discounted price is in the second span
        const discountedText = priceSpans[1].textContent.replace(/[^\d]/g, '');
        price = Math.round(parseInt(discountedText, 10) / 133) || 0;
      }
    } else {
      // Regular product
      const priceEl = card.querySelector('.price');
      if (priceEl) {
        const priceText = priceEl.textContent.replace(/[^\d]/g, '');
        price = Math.round(parseInt(priceText, 10) / 133) || 0;
      }
    }

    let merchant = '';
    let category = '';
    const merchantEl = card.querySelector('.merchant');
    const categoryEl = card.querySelector('.category');
    if (merchantEl) merchant = merchantEl.textContent.trim();
    if (categoryEl) category = categoryEl.textContent.trim();

    const existing = cart.find(item => item.id === id);
    if (existing) {
      existing.qty++;
    } else {
      cart.push({ id, name, img, price, merchant, category, qty: 1 });
    }

    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    updateCartDropdown();
    showCartNotification(`Added "${name}" to cart!`);
  }
  // Remove from Cart
  if (e.target.classList.contains('remove-item')) {
    const id = +e.target.dataset.id;
    cart = cart.filter(item => item.id !== id);
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    updateCartDropdown();
  }

  // Login button
  if (e.target.id === 'login-btn') {
    localStorage.setItem('localbazar', 'true');
    alert('Logged in successfully!');
    updateLoginStatus();
  }

  // Logout button
  if (e.target.id === 'logout-btn') {
    localStorage.removeItem('localbazar');
    alert('Logged out successfully!');
    updateLoginStatus();
  }
});

// --------- Cart Dropdown Toggle and Checkout Button ---------

const cartBtn = document.getElementById('cart-btn');
const cartDropdown = document.getElementById('cart-dropdown');
const checkoutBtn = document.getElementById('checkout-btn');

if (cartBtn && cartDropdown) {
  cartBtn.addEventListener('click', () => {
    if (cartDropdown.style.display === 'block') {
      cartDropdown.style.display = 'none';
    } else {
      updateCartDropdown();
      cartDropdown.style.display = 'block';
    }
  });

  // Hide cart dropdown when clicking outside the cart button or dropdown
  document.addEventListener('click', (e) => {
    if (!cartBtn.contains(e.target) && !cartDropdown.contains(e.target)) {
      cartDropdown.style.display = 'none';
    }
  });
}

if (checkoutBtn) {
  checkoutBtn.addEventListener('click', () => {
    if (typeof window.isBuyer !== 'undefined' && !window.isBuyer) {
      alert('You must create an account and log in as a buyer to place an order.');
      window.location.href = 'register.php';
      return;
    }
    if (cart.length === 0) {
      alert('Your cart is empty!');
      return;
    }
    // Show checkout modal
    showCheckoutModal();
  });
}

function showCheckoutModal() {
  // Create overlay
  const overlay = document.createElement('div');
  overlay.style.position = 'fixed';
  overlay.style.top = 0;
  overlay.style.left = 0;
  overlay.style.width = '100vw';
  overlay.style.height = '100vh';
  overlay.style.background = 'rgba(0,0,0,0.25)';
  overlay.style.zIndex = 9999;
  overlay.style.display = 'flex';
  overlay.style.alignItems = 'center';
  overlay.style.justifyContent = 'center';
  overlay.id = 'checkout-overlay';

  // Modal box
  const modal = document.createElement('div');
  modal.style.background = '#fff';
  modal.style.maxWidth = '400px';
  modal.style.margin = '0';
  modal.style.padding = '2rem';
  modal.style.borderRadius = '12px';
  modal.style.boxShadow = '0 4px 32px rgba(0,0,0,0.18)';
  modal.style.position = 'relative';

  // Order summary
  let summary = '<h2>Order Summary</h2><ul style="padding-left:18px;">';
  cart.forEach(item => {
    summary += `<li>${item.name} x${item.qty} - Rs ${(item.price * item.qty * 133).toFixed(0)}</li>`;
  });
  summary += '</ul>';
  summary += `<div style="margin:10px 0 18px 0;font-weight:bold;">Total: Rs ${(cart.reduce((a,c)=>a+c.price*c.qty,0)*133).toFixed(0)}</div>`;

  // Form with phone number
  const form = `
    <form id="order-form">
      <label>Phone Number:<br><input type="tel" name="phone" required pattern="[0-9]{10,15}" placeholder="Enter phone number" style="width:100%;margin-bottom:10px;"></label><br>
      <label>Delivery Address:<br><textarea name="address" required style="width:100%;margin-bottom:10px;"></textarea></label><br>
      <button type="submit" style="background:#646cff;color:#fff;padding:0.7rem 1.5rem;border:none;border-radius:6px;font-size:1rem;cursor:pointer;">Place Order</button>
      <button type="button" id="close-checkout" style="margin-left:10px;background:#eee;color:#333;padding:0.7rem 1.5rem;border:none;border-radius:6px;font-size:1rem;cursor:pointer;">Cancel</button>
    </form>
  `;

  modal.innerHTML = summary + form;
  overlay.appendChild(modal);
  document.body.appendChild(overlay);

  // Close modal
  document.getElementById('close-checkout').onclick = () => overlay.remove();

  // Handle form submit
  document.getElementById('order-form').onsubmit = function(e) {
    e.preventDefault();
    const phone = this.phone.value.trim();
    const address = this.address.value.trim();
    if (!phone) return alert('Please enter your phone number.');
    if (!address) return alert('Please enter delivery address.');
    // Optionally, add more phone validation here
    fetch('place_order.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ cart, phone, address })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        overlay.remove();
        alert('Order placed! Seller will be notified.');
        cart = [];
        localStorage.removeItem('cart');
        updateCartCount();
        updateCartDropdown();
      } else {
        alert('Order failed: ' + (data.message || 'Try again.'));
      }
    })
    .catch(() => alert('Order failed. Please try again.'));
  };
}

// Update login status UI
function updateLoginStatus() {
  const statusEl = document.getElementById('login-status');
  const isLoggedIn = localStorage.getItem('localbazar') === 'true';
  if (!statusEl) return;
  if (isLoggedIn) {
    statusEl.textContent = 'Logged in';
    document.getElementById('login-btn').style.display = 'none';
    document.getElementById('logout-btn').style.display = 'inline-block';
  } else {
    statusEl.textContent = 'Not logged in';
    document.getElementById('login-btn').style.display = 'inline-block';
    document.getElementById('logout-btn').style.display = 'none';
  }
}

// Initialize UI states on page load
updateLoginStatus();
updateCartCount();

// Remove any background image from body or main containers if set by JS (none found in this file)
// If you want to set your own image, do it in your CSS, e.g.:
// body { background-image: url('img/yourimage.jpg'); background-size:cover; }
