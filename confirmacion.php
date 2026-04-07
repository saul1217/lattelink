<?php
require_once 'db.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pedido Confirmado — LatteLink</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/confirmacion.css">
</head>
<body class="confirm-page">

  <!-- Confetti -->
  <div class="confetti-bg" id="confetti"></div>

  <div class="page-content">
    <div class="confirm-card">
      <div class="success-ring">✅</div>

      <h1>¡Pedido listo!</h1>
      <p class="sub">Tu orden fue registrada exitosamente.<br>Te avisaremos cuando esté lista para recoger.</p>

      <!-- Número de pedido -->
      <div class="order-num-box">
        <div class="on-label">Número de pedido</div>
        <div class="on-num" id="onNum">#LL-0000</div>
        <div class="on-time" id="onTime"></div>
      </div>

      <!-- Tracker de estado -->
      <div class="tracker">
        <div class="track-step">
          <div class="track-dot done">✓</div>
          <div class="track-label">Pedido recibido</div>
        </div>
        <div class="track-line"></div>
        <div class="track-step">
          <div class="track-dot active">🍳</div>
          <div class="track-label">En preparación</div>
        </div>
        <div class="track-line"></div>
        <div class="track-step">
          <div class="track-dot pending">🔔</div>
          <div class="track-label">Listo para recoger</div>
        </div>
      </div>

      <!-- Detalles -->
      <div class="details-grid" id="detailsGrid"></div>

      <!-- Nota especial -->
      <div class="nota-box" id="notaBox">
        <strong>📝 Nota adicional</strong>
        <span id="notaText"></span>
      </div>

      <!-- Items -->
      <div class="items-recap" id="itemsRecap"></div>

      <!-- Acciones -->
      <div class="actions">
        <a href="menu.php" class="btn btn-gold">Nuevo pedido</a>
        <button class="btn btn-outline" onclick="window.print()">🖨 Imprimir</button>
      </div>
    </div>
  </div>

  <script src="js/app.js"></script>
  <script>
    const order = JSON.parse(localStorage.getItem('ll_lastOrder') || '{}');
    if (!order.num) window.location.href = 'menu.php';

    // Render
    document.getElementById('onNum').textContent  = order.num;
    document.getElementById('onTime').textContent = order.timestamp;

    document.getElementById('detailsGrid').innerHTML = [
      { label:'Estudiante',  value: order.nombre },
      { label:'Matrícula',   value: order.matricula },
      { label:'Recoger a',   value: order.hora },
      { label:'Método de pago', value: order.pago },
      { label:'Artículos',   value: (order.items||[]).reduce((s,i)=>s+i.qty,0) + ' platillo(s)' },
      { label:'Total pagado',value: fmtMXN(order.total) },
    ].map(d => `
      <div class="detail-block">
        <div class="db-label">${d.label}</div>
        <div class="db-value">${d.value}</div>
      </div>`).join('');

    if (order.nota) {
      document.getElementById('notaBox').classList.add('show');
      document.getElementById('notaText').textContent = order.nota;
    }

    if (order.items && order.items.length > 0) {
      document.getElementById('itemsRecap').innerHTML = `
        <div class="recap-header">Detalle del pedido</div>
        ${order.items.map(i => `
          <div class="recap-item">
            <div>
              <div class="ri-name">${i.name} ×${i.qty}</div>
              ${i.opts?.length ? `<div class="ri-opts">${i.opts.join(' · ')}</div>` : ''}
            </div>
            <div class="ri-price">${fmtMXN(i.price * i.qty)}</div>
          </div>`).join('')}
        <div class="recap-total">
          <span>Total</span>
          <span>${fmtMXN(order.total)}</span>
        </div>`;
    }

    // Confetti
    const colors = ['#c8903e','#d4a044','#f0e2c8','#4a7c5f','#c44','#fff','#e8cfa0'];
    const container = document.getElementById('confetti');
    for (let i = 0; i < 55; i++) {
      const d = document.createElement('div');
      const size = Math.random() * 10 + 5;
      d.className = 'dot';
      d.style.cssText = `
        width:${size}px; height:${size}px;
        background:${colors[Math.floor(Math.random()*colors.length)]};
        left:${Math.random()*100}%;
        top:${Math.random()*-20}%;
        animation-duration:${Math.random()*4+3}s;
        animation-delay:${Math.random()*4}s;
        border-radius:${Math.random()>.5?'50%':'3px'};
      `;
      container.appendChild(d);
    }
  </script>
</body>
</html>
