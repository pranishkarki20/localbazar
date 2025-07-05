// cart.js - Shared cart logic for Local Bazar
// This file should be included before index.js and shop.js

document.addEventListener('DOMContentLoaded', function() {
  // --- Cart Notification Element ---
  let cartNotif = document.createElement('div');
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
  cartNotif.style.display = 'none';
  cartNotif.style.zIndex = 2000;
  document.body.appendChild(cartNotif);

  window.showCartNotification = function(msg) {
    cartNotif.textContent = msg;
    cartNotif.style.display = 'block';
    clearTimeout(cartNotif._timeout);
    cartNotif._timeout = setTimeout(() => {
      cartNotif.style.display = 'none';
    }, 1700);
  };

  // --- Cart Logic ---
  window.cart = [];
  if (localStorage.getItem('cart')) {
    try {
      window.cart = JSON.parse(localStorage.getItem('cart'));
    } catch {
      window.cart = [];
    }
    updateCartCount();
    if (typeof updateCartDropdown === 'function') updateCartDropdown();
  }

  window.updateCartCount = function() {
    const cartCountEl = document.getElementById('cart-count');
    if (cartCountEl) {
      cartCountEl.textContent = window.cart.reduce((a, c) => a + c.qty, 0);
    }
  };

  window.updateCartDropdown = function() {
    const itemsDiv = document.getElementById('cart-items');
    const totalDiv = document.getElementById('cart-total');
    if (!itemsDiv || !totalDiv) return;
    if (window.cart.length === 0) {
      itemsDiv.innerHTML = '<em>Your cart is empty.</em>';
      totalDiv.textContent = '';
      return;
    }
    itemsDiv.innerHTML = window.cart.map(item => {
      const name = item.name || 'Unknown';
      const qty = item.qty || 1;
      const priceRs = (item.price || 0) * 133;
      const totalPriceRs = (priceRs * qty).toFixed(0);
      return `
        <div style="display:flex;align-items:center;justify-content:space-between;padding:7px 0;border-bottom:1px solid #f0f0f0;">
          <span style="font-weight:500;">${name} <span style="color:#888;font-size:0.98em;">x${qty}</span></span>
          <span style="color:#2e8b57;font-weight:600;">Rs ${totalPriceRs}</span>
          <button class='remove-item' data-id='${item.id}' style='background:#e74c3c;border:none;color:#fff;font-weight:700;font-size:1.1em;padding:3px 12px;border-radius:6px;cursor:pointer;margin-left:10px;transition:background 0.2s;'>Remove</button>
        </div>
      `;
    }).join('');
    const total = window.cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
    totalDiv.textContent = `Total: Rs ${(total * 133).toFixed(0)}`;
  };

  // Unified click handler for add/remove cart
  document.addEventListener('click', function(e) {
    // Add to cart
    if (e.target.classList.contains('add-to-cart')) {
      e.preventDefault();
      const btn = e.target;
      const id = +btn.dataset.id;
      let prod = {
        id: id,
        name: btn.dataset.name || 'Unknown',
        img: btn.dataset.img || '',
        price: parseFloat(btn.dataset.price) || 0,
        category: btn.dataset.category || '',
        merchant: btn.dataset.merchant || '',
      };
      let found = window.cart.find(i => i.id === id);
      if (found) found.qty++;
      else window.cart.push({ ...prod, qty: 1 });
      localStorage.setItem('cart', JSON.stringify(window.cart));
      updateCartCount();
      if (typeof updateCartDropdown === 'function') updateCartDropdown();
      showCartNotification(`Added "${prod.name}" to cart!`);
      window.dispatchEvent(new Event('cart-updated'));
      return;
    }
    // Remove item from cart
    if (e.target.classList.contains('remove-item')) {
      const id = +e.target.dataset.id;
      window.cart = window.cart.filter(i => i.id !== id);
      localStorage.setItem('cart', JSON.stringify(window.cart));
      updateCartCount();
      if (typeof updateCartDropdown === 'function') updateCartDropdown();
      window.dispatchEvent(new Event('cart-updated'));
      return;
    }
  });

  // --- Cart Dropdown Toggle (shared for all pages) ---
  const cartBtn = document.getElementById('cart-btn');
  const cartDropdown = document.getElementById('cart-dropdown');
  if (cartBtn && cartDropdown) {
    cartBtn.addEventListener('click', e => {
      cartDropdown.style.display = cartDropdown.style.display === 'block' ? 'none' : 'block';
      if (typeof updateCartDropdown === 'function') updateCartDropdown();
    });
    document.addEventListener('click', e => {
      if (!cartBtn.contains(e.target) && !cartDropdown.contains(e.target)) {
        cartDropdown.style.display = 'none';
      }
    });
    window.addEventListener('scroll', () => { cartDropdown.style.display = 'none'; });
    window.addEventListener('resize', () => { cartDropdown.style.display = 'none'; });
  }

  // Sync cart across tabs/pages
  window.addEventListener('cart-updated', () => {
    try {
      window.cart = JSON.parse(localStorage.getItem('cart')) || [];
    } catch { window.cart = []; }
    updateCartCount();
    if (typeof updateCartDropdown === 'function') updateCartDropdown();
  });
  window.addEventListener('storage', (e) => {
    if (e.key === 'cart') {
      try {
        window.cart = JSON.parse(localStorage.getItem('cart')) || [];
      } catch { window.cart = []; }
      updateCartCount();
      if (typeof updateCartDropdown === 'function') updateCartDropdown();
    }
  });
});
