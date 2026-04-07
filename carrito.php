<?php
require_once 'db.php';
requireLogin();
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Carrito — LatteLink</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/carrito.css">
</head>
<body>

  <div id="topbar-mount"></div>

  <div class="page-header page-enter">
    <h1>Tu carrito 🛒</h1>
    <p id="cart-subtitle">Revisa tu pedido antes de continuar</p>
  </div>

  <div class="cart-layout page-enter" id="cart-layout">
    <!-- Rendered by JS -->
  </div>

  <div id="toast"></div>

  <script src="js/app.js"></script>
  <script>
    document.getElementById('topbar-mount').innerHTML = renderTopbar('carrito');

    function render() {
      const cart   = getCart();
      const layout = document.getElementById('cart-layout');
      const badge  = document.getElementById('cartBadge');
      if (badge) badge.textContent = cartCount();

      document.getElementById('cart-subtitle').textContent =
        cart.length === 0 ? 'No tienes artículos aún' :
        `${cartCount()} artículo${cartCount()!==1?'s':''} en tu carrito`;

      if (cart.length === 0) {
        layout.innerHTML = `
          <div class="empty-state">
            <span class="empty-icon">🛒</span>
            <h2>Tu carrito está vacío</h2>
            <p>¡Todavía no has agregado nada! Echa un vistazo al menú.</p>
            <a href="menu.php" class="btn btn-gold">Ir al menú →</a>
          </div>`;
        return;
      }

      const total = cartTotal();

      layout.innerHTML = `
        <div class="cart-col">
          <h2>Artículos (${cartCount()})</h2>
          <div id="items-list">
            ${cart.map(item => `
              <div class="cart-item" id="ci-${item.uid}">
                <div class="ci-img" style="background-image:url('${item.img}')"></div>
                <div class="ci-info">
                  <div class="ci-name">${item.name}</div>
                  <div class="ci-opts">${item.opts && item.opts.length ? item.opts.join(' · ') : 'Sin personalizaciones'}</div>
                </div>
                <div class="ci-right">
                  <div class="ci-price">${fmtMXN(item.price * item.qty)}</div>
                  <div class="qty-inline">
                    <button class="qi-btn" onclick="changeItemQty('${item.uid}', -1)">−</button>
                    <span class="qi-num">${item.qty}</span>
                    <button class="qi-btn" onclick="changeItemQty('${item.uid}', +1)">+</button>
                  </div>
                  <button class="remove-btn" onclick="removeItem('${item.uid}')">✕ Quitar</button>
                </div>
              </div>
            `).join('')}
          </div>
        </div>

        <aside>
          <div class="summary-card">
            <h3>Resumen</h3>
            ${cart.map(i => `
              <div class="sum-row">
                <span>${i.name} ×${i.qty}</span>
                <span>${fmtMXN(i.price * i.qty)}</span>
              </div>`).join('')}
            <div class="sum-row total">
              <span>Total</span>
              <span>${fmtMXN(total)}</span>
            </div>

            <div class="promo-row">
              <input class="promo-input" type="text" placeholder="Código de descuento" id="promoInput">
              <button class="promo-btn" onclick="applyPromo()">Aplicar</button>
            </div>

            <button class="checkout-btn-big" onclick="goCheckout()">
              Pagar — ${fmtMXN(total)} →
            </button>
            <a href="menu.php" class="keep-shopping">← Seguir comprando</a>

            <div class="secure-badges">
              <span class="s-badge">🔒 Seguro</span>
              <span class="s-badge">📋 Sin registro de tarjeta</span>
              <span class="s-badge">✅ UTCH 2026</span>
            </div>
          </div>
        </aside>`;
    }

    function changeItemQty(uid, delta) {
      let cart = getCart();
      const idx = cart.findIndex(i => String(i.uid) === String(uid));
      if (idx === -1) return;
      cart[idx].qty = Math.max(1, Math.min(10, cart[idx].qty + delta));
      saveCart(cart);
      render();
    }

    function removeItem(uid) {
      removeFromCart(uid);
      render();
      showToast('Artículo eliminado');
    }

    function applyPromo() {
      const code = document.getElementById('promoInput')?.value.trim().toUpperCase();
      if (code === 'UTCH10') {
        showToast('🎉 Código UTCH10 aplicado — ¡10% de descuento!');
      } else {
        showToast('❌ Código no válido');
      }
    }

    function goCheckout() {
      if (getCart().length === 0) { showToast('Tu carrito está vacío'); return; }
      window.location.href = 'checkout.php';
    }

    render();
  </script>
</body>
</html>
