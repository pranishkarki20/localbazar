document.addEventListener('DOMContentLoaded', function() {
  // Cart logic is now handled by cart.js
  const saleEnd = new Date();
  saleEnd.setHours(saleEnd.getHours() + 2); // Sale ends in 2 hours from page load
  function updateTimer() {
    const now = new Date();
    let diff = Math.max(0, saleEnd - now);
    const hours = String(Math.floor(diff / 3.6e6)).padStart(2, '0');
    const mins = String(Math.floor((diff % 3.6e6) / 6e4)).padStart(2, '0');
    const secs = String(Math.floor((diff % 6e4) / 1000)).padStart(2, '0');
    document.getElementById('timer').textContent = `${hours}:${mins}:${secs}`;
    if (diff <= 0) clearInterval(timerInterval);
  }
  const timerInterval = setInterval(updateTimer, 1000);
  updateTimer();

  // --- Sample Products ---
  const products = [
    { id: 1, name: 'Wireless Earbuds', price: 39.99, img: 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=400&q=80', merchant: 'GadgetPro' },
    { id: 2, name: 'Smart Watch', price: 59.99, img: 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=400&q=80', merchant: 'TimeTech' },
    { id: 3, name: 'Bluetooth Speaker', price: 29.99, img: 'https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=400&q=80', merchant: 'SoundWave' },
    { id: 4, name: 'Fitness Tracker', price: 24.99, img: 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80', merchant: 'FitLife' },
    { id: 5, name: 'VR Headset', price: 89.99, img: 'https://images.unsplash.com/photo-1519125323398-675f0ddb6308?auto=format&fit=crop&w=400&q=80', merchant: 'VRWorld' },
    { id: 6, name: 'Portable Charger', price: 19.99, img: 'https://images.unsplash.com/photo-1519125323398-675f0ddb6308?auto=format&fit=crop&w=400&q=80', merchant: 'PowerUp' },
  ];

  // --- Render Products ---
  function renderProducts() {
    const section = document.getElementById('products');
    section.innerHTML = '';
    products.forEach(prod => {
      const card = document.createElement('div');
      card.className = 'product-card';
      card.innerHTML = `
        <img src="${prod.img}" alt="${prod.name}" class="product-img" data-id="${prod.id}">
        <h3>${prod.name}</h3>
        <div class="price">$${prod.price.toFixed(2)}</div>
        <button class="add-to-cart" data-id="${prod.id}">Add to Cart</button>
      `;
      section.appendChild(card);
    });
    // Add search bar and filter
    if (!document.getElementById('search-bar')) {
      const searchDiv = document.createElement('div');
      searchDiv.style.textAlign = 'center';
      searchDiv.innerHTML = `
        <input id="search-bar" type="text" placeholder="Search products..." style="margin:20px 0 30px 0;padding:0.7rem 1.2rem;width:60vw;max-width:400px;border-radius:8px;border:1px solid #ccc;">
      `;
      section.parentNode.insertBefore(searchDiv, section);
      document.getElementById('search-bar').addEventListener('input', filterProducts);
    }
  }

  function filterProducts() {
    const val = document.getElementById('search-bar').value.toLowerCase();
    const cards = document.querySelectorAll('.product-card');
    products.forEach((prod, i) => {
      if (prod.name.toLowerCase().includes(val)) {
        cards[i].style.display = '';
      } else {
        cards[i].style.display = 'none';
      }
    });
  }


  renderProducts();

  // Cart logic is now handled by cart.js

  // Cart logic is now handled by cart.js

  // Only handle product details modal here
  document.addEventListener('click', function(e) {
    // Product details modal
    const card = e.target.closest('.product-card');
    if (card && !e.target.classList.contains('add-to-cart') && e.target.classList.contains('product-img')) {
      const img = card.querySelector('.product-img');
      if (img) {
        const id = +img.dataset.id;
        showProductDetails(id);
      }
      return;
    }
  });

  // --- Product Details Modal ---
  function showProductDetails(id) {
    const prod = products.find(p => p.id === id);
    if (!prod) return;
    const overlay = document.createElement('div');
    overlay.style.position = 'fixed';
    overlay.style.top = 0;
    overlay.style.left = 0;
    overlay.style.width = '100vw';
    overlay.style.height = '100vh';
    overlay.style.background = 'rgba(0,0,0,0.25)';
    overlay.style.zIndex = 9999;
    overlay.id = 'product-details-overlay';
    const modal = document.createElement('div');
    modal.style.background = '#fff';
    modal.style.maxWidth = '350px';
    modal.style.margin = '60px auto';
    modal.style.padding = '2rem';
    modal.style.borderRadius = '12px';
    modal.style.boxShadow = '0 4px 32px rgba(0,0,0,0.18)';
    modal.style.position = 'relative';
    modal.innerHTML = `
      <img src="${prod.img}" alt="${prod.name}" style="width:100%;border-radius:8px;margin-bottom:12px;">
      <h2>${prod.name}</h2>
      <div style="color:#646cff;font-weight:bold;font-size:1.2rem;">$${prod.price.toFixed(2)}</div>
      <div style="margin:10px 0 18px 0;">Sold by: <b>${prod.merchant}</b></div>
      <button class="add-to-cart" data-id="${prod.id}" style="background:#646cff;color:#fff;padding:0.7rem 1.5rem;border:none;border-radius:6px;font-size:1rem;cursor:pointer;">Add to Cart</button>
      <button id="close-product-details" style="margin-left:10px;background:#eee;color:#333;padding:0.7rem 1.5rem;border:none;border-radius:6px;font-size:1rem;cursor:pointer;">Close</button>
    `;
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    document.getElementById('close-product-details').onclick = () => overlay.remove();
  }

  // Cart dropdown toggle is now handled by cart.js

  // --- Checkout & User Account Creation ---
  function promptUserAccount() {
    let role = 'buyer'; // Only buyer can checkout
    let name = '';
    return { role, name };
  }
  const checkoutBtn = document.getElementById('checkout-btn');
  if (checkoutBtn) {
    checkoutBtn.addEventListener('click', () => {
      // Require login as buyer (window.isBuyer injected by PHP)
      if (typeof window.isBuyer !== 'undefined' && !window.isBuyer) {
        alert('You must create an account and log in as a buyer to place an order.');
        window.location.href = 'register.php';
        return;
      }
      if (cart.length === 0) return alert('Cart is empty!');
      // Create a new page (modal) with order summary and form
      showCheckoutPage();
    });
  }

  // --- Checkout Page Modal ---
  function showCheckoutPage() {
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

    // Order summary (Rs, like shop.js)
    let summary = '<h2>Order Summary</h2><ul style="padding-left:18px;">';
    cart.forEach(item => {
      summary += `<li>${item.name} x${item.qty} - Rs ${(item.price * item.qty * 133).toFixed(0)}</li>`;
    });
    summary += '</ul>';
    summary += `<div style="margin:10px 0 18px 0;font-weight:bold;">Total: Rs ${(cart.reduce((a,c)=>a+c.price*c.qty,0)*133).toFixed(0)}</div>`;

    // Form with phone number (like shop.js)
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
      // Save order to buyer and seller accounts in localStorage (simulate)
      const order = {
        phone,
        address,
        items: cart.map(item => ({...item})),
        total: cart.reduce((a,c)=>a+c.price*c.qty,0),
        date: new Date().toLocaleString(),
        id: Date.now()
      };
      let buyerOrders = JSON.parse(localStorage.getItem('buyerOrders') || '[]');
      buyerOrders.push(order);
      localStorage.setItem('buyerOrders', JSON.stringify(buyerOrders));
      // Save to seller (group by merchant)
      let sellerOrders = JSON.parse(localStorage.getItem('sellerOrders') || '{}');
      cart.forEach(item => {
        if (!sellerOrders[item.merchant]) sellerOrders[item.merchant] = [];
        sellerOrders[item.merchant].push({
          orderId: order.id,
          product: item.name,
          qty: item.qty,
          amount: item.price * item.qty,
          total: (item.price * item.qty),
          date: order.date,
          buyerPhone: phone,
          address: address
        });
      });
      localStorage.setItem('sellerOrders', JSON.stringify(sellerOrders));
      overlay.remove();
      cart = [];
      updateCartCount();
      updateCartDropdown();
      localStorage.removeItem('cart');
      alert('Order placed! Seller will be notified.');
    };
  }

  // --- Simulate sending order to merchant email ---
  function sendOrderToMerchant(data) {
    // In a real app, this would send an email to the merchant.
    // Here, we just log the order to the console.
    const order = {
      customer: data.name,
      email: data.email,
      address: data.address,
      items: cart,
      total: cart.reduce((a,c)=>a+c.price*c.qty,0)
    };
    console.log('Order sent to merchant:', order);
  }
});
  const val = document.getElementById('search-bar').value.toLowerCase();
  const cards = document.querySelectorAll('.product-card');
  products.forEach((prod, i) => {
    if (prod.name.toLowerCase().includes(val)) {
      cards[i].style.display = '';
    } else {
      cards[i].style.display = 'none';
    }
  });
