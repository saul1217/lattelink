<?php
require_once 'db.php';
requireLogin();

// Cargar categorías y productos desde la BD
$cats = $pdo->query("SELECT * FROM categorias ORDER BY id")->fetchAll();
$prods = $pdo->query("SELECT p.*, c.clave AS cat_clave FROM productos p JOIN categorias c ON p.categoria_id = c.id ORDER BY c.id, p.id")->fetchAll();

// Agrupar productos por categoría
$menuData = [];
foreach ($cats as $cat) {
    $menuData[$cat['clave']] = [
        'label' => $cat['nombre'],
        'items' => []
    ];
}
foreach ($prods as $p) {
    $menuData[$p['cat_clave']]['items'][] = $p;
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menú — LatteLink Cafetería UTCH</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/menu.css">
</head>
<body>

  <!-- TOPBAR -->
  <div id="topbar-mount"></div>

  <!-- HERO -->
  <div class="hero page-enter">
    <div class="hero-inner">
      <div class="hero-eyebrow">Menú del día</div>
      <h1>¿Qué se te<br>antoja hoy?</h1>
      <p>Elige tus platillos favoritos. Los preparamos y te avisamos cuando estén listos para recoger.</p>
      <div class="hero-chips">
        <span class="hero-chip">⏱ Sin filas</span>
        <span class="hero-chip">🔔 Notificación</span>
        <span class="hero-chip">💳 Pago fácil</span>
        <span class="hero-chip">✅ Pedido seguro</span>
      </div>
    </div>
  </div>

  <!-- SHOP LAYOUT -->
  <div class="shop-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar"></aside>

    <!-- CONTENIDO PRINCIPAL -->
    <main class="content-area" id="content-area"></main>

  </div><!-- /shop-layout -->

  <!-- MODAL -->
  <div class="overlay" id="overlay" onclick="handleOverlayClick(event)">
    <div class="modal" id="modal">
      <div class="modal-hero" id="modalHero">
        <button class="modal-close" onclick="closeModal()">✕</button>
      </div>
      <div class="modal-body">
        <h2 class="modal-name"  id="modalName"></h2>
        <div class="modal-price-big" id="modalPriceBig"></div>
        <p class="modal-desc" id="modalDesc"></p>
        <div id="modalOpts"></div>
        <div class="qty-row">
          <span class="qty-label">Cantidad</span>
          <div class="qty-ctrl">
            <button class="qty-btn" onclick="changeQty(-1)">−</button>
            <span class="qty-num" id="qtyNum">1</span>
            <button class="qty-btn" onclick="changeQty(+1)">+</button>
          </div>
        </div>
        <button class="modal-add" onclick="confirmAdd()">
          <span>Agregar al carrito</span>
          <span class="add-total" id="modalTotal"></span>
        </button>
      </div>
    </div>
  </div>

  <!-- LIGHTBOX -->
  <div class="lightbox-overlay" id="lightboxOverlay" onclick="closeLightbox()">
    <button class="lightbox-close" onclick="closeLightbox()">✕</button>
    <img id="lightboxImg" src="" alt="Imagen ampliada">
  </div>

  <!-- TOAST -->
  <div id="toast"></div>

  <script src="js/app.js"></script>
  <script>
    // Inyectar datos del usuario PHP en JS
    const PHP_USER = <?php echo json_encode($user); ?>;

    // Inyectar menú desde PHP (base de datos) al objeto MENU de JS
    const MENU_DB = <?php
      $jsMenu = [];
      foreach ($menuData as $clave => $cat) {
          $jsItems = [];
          foreach ($cat['items'] as $p) {
              $jsItems[] = [
                  'id'    => $p['codigo'],
                  'name'  => $p['nombre'],
                  'price' => (float)$p['precio'],
                  'desc'  => $p['descripcion'],
                  'img'   => $p['imagen_url'],
                  'badge' => $p['badge'],
                  'opts'  => json_decode($p['opciones'], true) ?: [],
              ];
          }
          $jsMenu[$clave] = [
              'label' => $cat['label'],
              'items' => $jsItems,
          ];
      }
      echo json_encode($jsMenu, JSON_UNESCAPED_UNICODE);
    ?>;

    // Sobreescribir el MENU del app.js con los datos de la BD
    Object.keys(MENU_DB).forEach(k => { MENU[k] = MENU_DB[k]; });

    document.getElementById('topbar-mount').innerHTML = renderTopbar('menu');

    /* ── Estado modal ── */
    let _item = null, _qty = 1;

    /* ── Renderizar sidebar y secciones ── */
    const catKeys = Object.keys(MENU);

    function buildSidebar() {
      const sb = document.getElementById('sidebar');
      sb.innerHTML = `<div class="sidebar-title">Categorías</div>` +
        catKeys.map((k, i) => {
          const isDivider = (k === 'bebidas_calientes') ? '<div class="sidebar-divider"></div>' : '';
          return `${isDivider}<button class="sidebar-link ${i===0?'active':''}" id="sb-${k}" onclick="switchCat('${k}')">${MENU[k].label}</button>`;
        }).join('');
    }

    function buildContent() {
      const area = document.getElementById('content-area');
      area.innerHTML = catKeys.map((k, i) => {
        const cat = MENU[k];
        return `
          <section class="cat-section ${i===0?'active':''}" id="sec-${k}">
            <div class="cat-header">
              <div>
                <h2>${cat.label}</h2>
                <p>${cat.items.length} platillos disponibles</p>
              </div>
              <span class="cat-count">${cat.items.length} opciones</span>
            </div>
            <div class="items-grid">
              ${cat.items.map(item => `
                <div class="item-card" onclick="openModal('${k}','${item.id}')">
                  <div class="card-img" style="background-image:url('${item.img}')" onclick="event.stopPropagation(); openLightbox('${item.img}')">
                    ${item.badge ? `<span class="card-badge">${item.badge}</span>` : ''}
                  </div>
                  <div class="card-body">
                    <div class="card-name">${item.name}</div>
                    <div class="card-desc">${item.desc}</div>
                    <div class="card-footer">
                      <div class="card-price">$${item.price} <small>MXN</small></div>
                      <button class="add-btn" onclick="event.stopPropagation();openModal('${k}','${item.id}')">+ Agregar</button>
                    </div>
                  </div>
                </div>
              `).join('')}
            </div>
          </section>`;
      }).join('');
    }

    function switchCat(key) {
      document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
      document.getElementById('sb-' + key)?.classList.add('active');
      document.querySelectorAll('.cat-section').forEach(s => s.classList.remove('active'));
      document.getElementById('sec-' + key)?.classList.add('active');
    }

    buildSidebar();
    buildContent();

    /* ── MODAL ── */
    function openModal(catKey, itemId) {
      const item = MENU[catKey].items.find(i => i.id === itemId);
      if (!item) return;
      _item = { ...item, catKey };
      _qty  = 1;

      document.getElementById('modalHero').style.backgroundImage = `url('${item.img}')`;
      document.getElementById('modalHero').onclick = function(e) { e.stopPropagation(); openLightbox(item.img); };
      document.getElementById('modalName').textContent    = item.name;
      document.getElementById('modalPriceBig').textContent = '$' + item.price + ' MXN';
      document.getElementById('modalDesc').textContent    = item.desc;
      document.getElementById('qtyNum').textContent       = 1;

      const optsEl = document.getElementById('modalOpts');
      optsEl.innerHTML = (item.opts || []).map((grp, gi) => `
        <div class="opt-block">
          <div class="opt-label-row">
            <span class="opt-label">${grp.label}</span>
            ${grp.req ? '<span class="opt-req">Requerido</span>' : ''}
          </div>
          <div class="opt-pills">
            ${grp.choices.map((c, ci) => `
              <button class="opt-pill ${ci===0?'sel':''}" onclick="pickOpt(this,${gi})">${c}</button>
            `).join('')}
          </div>
        </div>
      `).join('');

      updateModalTotal();
      document.getElementById('overlay').classList.add('open');
      document.body.style.overflow = 'hidden';
    }

    function closeModal() {
      document.getElementById('overlay').classList.remove('open');
      document.body.style.overflow = '';
      _item = null;
    }

    function handleOverlayClick(e) {
      if (e.target === document.getElementById('overlay')) closeModal();
    }

    function pickOpt(btn, gi) {
      btn.closest('.opt-pills').querySelectorAll('.opt-pill').forEach(p => p.classList.remove('sel'));
      btn.classList.add('sel');
    }

    function changeQty(d) {
      _qty = Math.max(1, Math.min(10, _qty + d));
      document.getElementById('qtyNum').textContent = _qty;
      updateModalTotal();
    }

    function updateModalTotal() {
      if (!_item) return;
      document.getElementById('modalTotal').textContent = fmtMXN(_item.price * _qty);
    }

    function confirmAdd() {
      if (!_item) return;

      const opts = [];
      document.querySelectorAll('#modalOpts .opt-pills').forEach((grp, gi) => {
        const sel = grp.querySelector('.opt-pill.sel');
        if (sel) opts.push(sel.textContent.trim());
      });

      addToCart({
        id:    _item.id,
        name:  _item.name,
        price: _item.price,
        img:   _item.img,
        opts,
        qty:   _qty,
      });

      document.getElementById('cartBadge').textContent = cartCount();
      closeModal();
      showToast(`✓ ${_item.name} agregado al carrito`);
    }

    /* ── LIGHTBOX ── */
    function openLightbox(imgUrl) {
      document.getElementById('lightboxImg').src = imgUrl;
      document.getElementById('lightboxOverlay').classList.add('open');
      document.body.style.overflow = 'hidden';
    }
    function closeLightbox() {
      document.getElementById('lightboxOverlay').classList.remove('open');
      document.getElementById('lightboxImg').src = '';
      // Solo restaurar scroll si el modal no está abierto
      if (!document.getElementById('overlay').classList.contains('open')) {
        document.body.style.overflow = '';
      }
    }
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') closeLightbox();
    });

    document.addEventListener('DOMContentLoaded', () => {
      const badge = document.getElementById('cartBadge');
      if (badge) badge.textContent = cartCount();
    });
  </script>
</body>
</html>
