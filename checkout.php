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
  <title>Pago — LatteLink</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/checkout.css">
</head>
<body>

  <div id="topbar-mount"></div>

  <div class="page-header">
    <h1>Finalizar pedido</h1>
    <p>Revisa los detalles y elige cómo pagar</p>
  </div>

  <div class="steps">
    <div class="step done"><span class="sn">✓</span> Menú</div>
    <div class="step done"><span class="sn">✓</span> Carrito</div>
    <div class="step active"><span class="sn">3</span> Pago</div>
    <div class="step"><span class="sn">4</span> Confirmación</div>
  </div>

  <div class="ck-layout page-enter">

    <!-- ══ Columna izquierda ══ -->
    <div>

      <!-- 1. Datos del estudiante (prellenados desde PHP) -->
      <div class="ck-card">
        <div class="ck-card-title">
          <span class="step-dot">1</span>
          Tus datos
        </div>
        <div class="two-col">
          <div class="field">
            <label>Nombre completo</label>
            <input type="text" id="ckNombre" placeholder="Fernando Ávila" value="<?php echo htmlspecialchars($user['nombre']); ?>">
          </div>
          <div class="field">
            <label>Matrícula</label>
            <input type="text" id="ckMatricula" placeholder="A00123456" value="<?php echo htmlspecialchars($user['matricula']); ?>">
          </div>
        </div>
        <div class="field">
          <label>Correo institucional <span style="color:var(--muted);font-weight:400;">(opcional)</span></label>
          <input type="email" id="ckEmail" placeholder="a00123456@utch.edu.mx">
        </div>
      </div>

      <!-- 2. Hora de recogida -->
      <div class="ck-card">
        <div class="ck-card-title">
          <span class="step-dot">2</span>
          ¿A qué hora recoges?
        </div>
        <p style="font-size:.85rem;color:var(--muted);margin-bottom:1rem;">Selecciona el horario más conveniente. Los marcados no están disponibles.</p>
        <div class="time-grid" id="timeGrid"></div>
      </div>

      <!-- 3. Método de pago -->
      <div class="ck-card">
        <div class="ck-card-title">
          <span class="step-dot">3</span>
          Método de pago
        </div>

        <!-- Efectivo al recoger -->
        <label class="pay-option active" onclick="selectPay('efectivo', this)">
          <input type="radio" name="pago" value="efectivo" checked>
          <div class="radio-visual"></div>
          <span class="pay-icon">💵</span>
          <div class="pay-info">
            <strong>Efectivo al recoger</strong>
            <span>Paga en caja cuando tu pedido esté listo. Sin cargos extra.</span>
          </div>
        </label>

        <!-- Tarjeta -->
        <label class="pay-option" onclick="selectPay('tarjeta', this)">
          <input type="radio" name="pago" value="tarjeta">
          <div class="radio-visual"></div>
          <span class="pay-icon">💳</span>
          <div class="pay-info">
            <strong>Tarjeta de débito o crédito</strong>
            <span>Visa, Mastercard, American Express. Seguro y sin redirecciones.</span>
          </div>
        </label>

        <!-- Campos tarjeta -->
        <div class="card-panel" id="cardPanel">
          <div class="field">
            <label>Número de tarjeta</label>
            <input type="text" id="cardNum" placeholder="1234  5678  9012  3456" maxlength="19">
          </div>
          <div class="two-col">
            <div class="field">
              <label>Vencimiento</label>
              <input type="text" id="cardExp" placeholder="MM/AA" maxlength="5">
            </div>
            <div class="field">
              <label>CVV</label>
              <input type="text" id="cardCvv" placeholder="123" maxlength="4">
            </div>
          </div>
          <div class="field">
            <label>Titular de la tarjeta</label>
            <input type="text" id="cardHolder" placeholder="Como aparece en la tarjeta">
          </div>
        </div>

        <!-- Transferencia SPEI -->
        <label class="pay-option" onclick="selectPay('spei', this)">
          <input type="radio" name="pago" value="spei">
          <div class="radio-visual"></div>
          <span class="pay-icon">🏦</span>
          <div class="pay-info">
            <strong>Transferencia SPEI</strong>
            <span>Recibirás la CLABE al confirmar el pedido.</span>
          </div>
        </label>

        <!-- Info SPEI -->
        <div class="clabe-box" id="clabe-box">
          <p>Realiza tu transferencia a esta CLABE:</p>
          <div class="clabe-num">014 077 00123456789 0</div>
          <p>Banco: UTCH Cafetería · Beneficiario: LatteLink S.C.</p>
          <p>Monto exacto: <strong id="clabeTotal"></strong></p>
        </div>

        <!-- Pago con app universitaria -->
        <label class="pay-option" onclick="selectPay('app', this)">
          <input type="radio" name="pago" value="app">
          <div class="radio-visual"></div>
          <span class="pay-icon">📱</span>
          <div class="pay-info">
            <strong>App UTCH Campus</strong>
            <span>Paga con tu saldo de la tarjeta universitaria.</span>
          </div>
        </label>

      </div>

      <!-- 4. Nota especial -->
      <div class="ck-card">
        <div class="ck-card-title">
          <span class="step-dot">4</span>
          Notas adicionales <span style="font-size:.78rem;font-weight:400;color:var(--muted);">(Opcional)</span>
        </div>
        <div class="field">
          <textarea id="nota" placeholder="Alergias, sin picante, para llevar, etc." rows="3" style="resize:vertical;"></textarea>
        </div>
      </div>

    </div>

    <!-- ══ Resumen lateral ══ -->
    <aside>
      <div class="order-sum">
        <h3>Tu pedido</h3>
        <div id="osSummary"></div>
        <div class="os-total" id="osTotal">
          <span>Total</span>
          <span>—</span>
        </div>
        <button class="place-btn" onclick="placeOrder()">Confirmar pedido ✓</button>
        <div style="font-size:.75rem;color:var(--muted);text-align:center;margin-top:10px;">
          🔒 Transacción protegida · UTCH 2026
        </div>
      </div>
    </aside>

  </div>

  <div id="toast"></div>

  <script src="js/app.js"></script>
  <script>
    document.getElementById('topbar-mount').innerHTML = renderTopbar('checkout');

    // Redirigir si el carrito está vacío
    if (getCart().length === 0) window.location.href = 'carrito.php';

    // ── Resumen ──
    function renderSummary() {
      const cart  = getCart();
      const total = cartTotal();
      document.getElementById('osSummary').innerHTML = cart.map(i => `
        <div class="os-item">
          <div>
            <div class="os-name">${i.name} ×${i.qty}</div>
            ${i.opts?.length ? `<div class="os-opts">${i.opts.join(' · ')}</div>` : ''}
          </div>
          <div class="os-price">${fmtMXN(i.price * i.qty)}</div>
        </div>`).join('');
      document.getElementById('osTotal').innerHTML = `<span>Total</span><span>${fmtMXN(total)}</span>`;
      document.getElementById('clabeTotal').textContent = fmtMXN(total);
    }
    renderSummary();

    // ── Horarios ──
    const slots = [
      '7:30','8:00','8:30','9:00','9:30',
      '10:00','10:30','11:00','11:30','12:00',
      '12:30','1:00','1:30','2:00','2:30',
    ];
    const busy = ['8:00','9:30','12:00'];
    let selectedTime = null;

    document.getElementById('timeGrid').innerHTML = slots.map(t => {
      const isBusy = busy.includes(t);
      return `<button class="time-btn ${isBusy?'busy':''}"
        ${isBusy?'disabled':''}
        data-time="${t}"
        onmousedown="${isBusy?'':'pickTime(this)'}">
        ${t} ${isBusy?'🚫':''}
      </button>`;
    }).join('');

    function pickTime(btn) {
      document.querySelectorAll('.time-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      selectedTime = btn.dataset.time;
    }

    // ── Pago ──
    let payMethod = 'efectivo';

    function selectPay(method, el) {
      payMethod = method;
      document.querySelectorAll('.pay-option').forEach(o => o.classList.remove('active'));
      el.classList.add('active');
      document.getElementById('cardPanel').classList.toggle('show', method === 'tarjeta');
      document.getElementById('clabe-box').classList.toggle('show', method === 'spei');
    }

    // Card number formatting
    document.getElementById('cardNum').addEventListener('input', function() {
      this.value = this.value.replace(/\D/g,'').replace(/(.{4})/g,'$1  ').trim().slice(0,21);
    });
    document.getElementById('cardExp').addEventListener('input', function() {
      this.value = this.value.replace(/\D/g,'').replace(/^(\d{2})(\d)/,'$1/$2').slice(0,5);
    });

    // ── Confirmar pedido (envía a PHP via AJAX) ──
    function placeOrder() {
      const nombre    = document.getElementById('ckNombre').value.trim();
      const matricula = document.getElementById('ckMatricula').value.trim();
      const email     = document.getElementById('ckEmail').value.trim();

      if (!nombre)    { showToast('⚠️ Ingresa tu nombre'); return; }
      if (!matricula) { showToast('⚠️ Ingresa tu matrícula'); return; }
      if (!selectedTime) { showToast('⚠️ Selecciona una hora de recogida'); return; }

      if (payMethod === 'tarjeta') {
        const num  = document.getElementById('cardNum').value.replace(/\s/g,'');
        const exp  = document.getElementById('cardExp').value;
        const cvv  = document.getElementById('cardCvv').value;
        const hold = document.getElementById('cardHolder').value.trim();
        if (num.length < 15 || !exp || cvv.length < 3 || !hold) {
          showToast('⚠️ Completa los datos de tu tarjeta');
          return;
        }
      }

      const payLabels = {
        efectivo: 'Efectivo al recoger',
        tarjeta:  'Tarjeta de crédito/débito',
        spei:     'Transferencia SPEI',
        app:      'App UTCH Campus',
      };

      const orderData = {
        nombre,
        matricula,
        email,
        hora:       selectedTime,
        pago:       payLabels[payMethod],
        total:      cartTotal(),
        items:      getCart(),
        nota:       document.getElementById('nota').value.trim(),
      };

      // Enviar al servidor PHP
      fetch('guardar_pedido.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(orderData),
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // Guardar en localStorage para la página de confirmación
          const fullOrder = {
            ...orderData,
            num:       data.numero_pedido,
            timestamp: new Date().toLocaleString('es-MX'),
          };
          localStorage.setItem('ll_lastOrder', JSON.stringify(fullOrder));
          clearCart();
          window.location.href = 'confirmacion.php';
        } else {
          showToast('❌ Error: ' + (data.error || 'No se pudo guardar el pedido'));
        }
      })
      .catch(err => {
        showToast('❌ Error de conexión al servidor');
        console.error(err);
      });
    }
  </script>
</body>
</html>
