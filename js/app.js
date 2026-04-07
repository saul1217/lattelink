/* ═══════════════════════════════════════════════
   LATTELINK — SHARED APP STATE
   Persiste con localStorage entre páginas
═══════════════════════════════════════════════ */

/* ════ AUTENTICACIÓN ════ */
// La autenticación se maneja con PHP/MySQL (sesiones).
// PHP_USER se inyecta desde PHP en cada página protegida.

function logout() {
  localStorage.removeItem('ll_cart');
  window.location.href = 'logout.php';
}

/* ════ CARRITO ════ */
function getCart() {
  const s = localStorage.getItem('ll_cart');
  return s ? JSON.parse(s) : [];
}

function saveCart(cart) {
  localStorage.setItem('ll_cart', JSON.stringify(cart));
}

function addToCart(item) {
  const cart = getCart();
  cart.push({ ...item, uid: Date.now() + Math.random() });
  saveCart(cart);
}

function removeFromCart(uid) {
  const cart = getCart().filter(i => String(i.uid) !== String(uid));
  saveCart(cart);
}

function clearCart() {
  localStorage.removeItem('ll_cart');
}

function cartTotal() {
  return getCart().reduce((s, i) => s + i.price * i.qty, 0);
}

function cartCount() {
  return getCart().reduce((s, i) => s + i.qty, 0);
}

/* ════ TOAST ════ */
function showToast(msg) {
  let el = document.getElementById('toast');
  if (!el) {
    el = document.createElement('div');
    el.id = 'toast';
    document.body.appendChild(el);
  }
  el.textContent = msg;
  el.classList.add('show');
  clearTimeout(el._timer);
  el._timer = setTimeout(() => el.classList.remove('show'), 2800);
}

/* ════ TOPBAR HELPERS ════ */
function renderTopbar(activePage) {
  const user = (typeof PHP_USER !== 'undefined') ? PHP_USER : null;
  const count = cartCount();

  const pages = {
    menu: { href: 'menu.php', label: 'Menú' },
    carrito: { href: 'carrito.php', label: 'Carrito' },
  };

  const nav = Object.entries(pages).map(([key, p]) =>
    `<a href="${p.href}" class="${activePage === key ? 'active' : ''}">${p.label}</a>`
  ).join('');

  return `
    <nav class="topbar">
      <a class="topbar-brand" href="menu.php">Latte<span class="accent">Link</span></a>
      <div class="topbar-nav">${nav}</div>
      <div class="nav-cart">
        ${user ? `
          <div class="user-pill">
            <div class="av">${user.iniciales || ''}</div>
            <span>${user.nombre.split(' ')[0]}</span>
          </div>
        ` : ''}
        <a href="carrito.php" class="btn-cart">
          🛒 Carrito
          <span class="badge" id="cartBadge">${count}</span>
        </a>
        <button class="btn-logout" onclick="logout()">Salir →</button>
      </div>
    </nav>`;
}

/* ════ FORMAT ════ */
function fmtMXN(n) {
  return '$' + Number(n).toFixed(2);
}

/* ════ MENÚ DE LA CAFETERÍA ════ */
const MENU = {

  desayunos: {
    label: '🍳 Desayunos',
    items: [
      {
        id: 'D1', name: 'Huevos al gusto', price: 45,
        desc: 'Huevos frescos a tu estilo con frijoles, arroz y tortillas de harina recién dsadsadsahechas.',
        img: 'img/huevo.jpg', badge: 'Clásico',
        opts: [
          { label: 'Preparación', req: true, choices: ['Estrellados', 'Revueltos', 'Rancheros', 'A la mexicana', 'Divorciados', 'Motuleños', 'Con jamón', 'Con chorizo', 'Con nopales'] },
          { label: 'Acompañamiento', req: false, choices: ['Frijoles negros', 'Frijoles charros', 'Papas fritas', 'Sin acompañamiento'] },
        ]
      },
      {
        id: 'D2', name: 'Machaca con huevo', price: 52,
        desc: 'Machaca norteña salteada con huevo, chile verde, cebolla y tomate. Tortillas de harina.',
        img: 'img/machacawebo.jpg', badge: 'Norteño',
        opts: [
          { label: 'Huevo', req: true, choices: ['Revuelto con machaca', 'Estrellado aparte'] },
          { label: 'Tortilla', req: false, choices: ['Harina', 'Maíz', 'Ambas'] },
        ]
      },
      {
        id: 'D3', name: 'Hotcakes (3 pzas)', price: 42,
        desc: 'Esponjosos, dorados a la mantequilla, con miel de maple y mantequilla extra.',
        img: 'img/hotcakes.jpg', badge: null,
        opts: [
          { label: 'Extras', req: false, choices: ['Miel de maple', 'Cajeta', 'Mermelada', 'Chispas de chocolate'] },
          { label: '¿Con qué lo acompañas?', req: false, choices: ['Sin nada más', '+ Huevos ($20)', '+ Tocino ($18)'] },
        ]
      },
      {
        id: 'D4', name: 'Avena con Fruta', price: 32,
        desc: 'Avena cremosa con leche, canela, miel de abeja y fruta de temporada.',
        img: 'img/avenaconfruta.jpg', badge: 'Healthy',
        opts: [
          { label: 'Fruta', req: true, choices: ['Plátano y fresa', 'Mango y papaya', 'Manzana y canela', 'Mix tropical'] },
          { label: 'Endulzante', req: false, choices: ['Miel de abeja', 'Azúcar', 'Sin azúcar'] },
        ]
      },
    ]
  },

  burritos: {
    label: '🌯 Burritos',
    items: [
      {
        id: 'B1', name: 'Burrito Clásico', price: 45,
        desc: 'Tortilla de harina extra grande con guisado a elegir, frijoles refritos y queso Chihuahua.',
        img: 'img/burrito_clasico.jpg', badge: '⭐ Favorito',
        opts: [
          { label: 'Guisado', req: true, choices: ['Carne asada', 'Picadillo', 'Pollo adobado', 'Rajas con queso', 'Papas con chorizo', 'Frijoles con queso', 'Machaca', 'Chile colorado', 'Bistec a la mexicana'] },
          { label: 'Picante', req: false, choices: ['Sin chile', 'Poco', 'Medio', 'Mucho 🔥'] },
        ]
      },
      {
        id: 'B2', name: 'Burrito Campesino', price: 48,
        desc: 'Huevo, queso Menonita, chile verde asado, salsa casera y frijoles charros.',
        img: 'img/burrito_campesino.jpeg', badge: null,
        opts: [
          { label: 'Tipo de huevo', req: true, choices: ['Revuelto', 'Estrellado', 'Con jamón', 'Con chorizo'] },
        ]
      },
      {
        id: 'B3', name: 'Burrito Supremo', price: 58,
        desc: 'Doble tortilla, guisado, arroz, frijoles, pico de gallo, guacamole y crema agria.',
        img: 'img/burrito_supremo.jpg', badge: '🔥 Especial',
        opts: [
          { label: 'Guisado', req: true, choices: ['Carne asada', 'Barbacoa', 'Pollo a la plancha', 'Carnitas', 'Picadillo especial'] },
          { label: 'Extras', req: false, choices: ['+ Guacamole', '+ Jalapeños', '+ Extra crema', 'Sin extras'] },
        ]
      },
      {
        id: 'B4', name: 'Burrito Verde', price: 50,
        desc: 'Salsa verde tatemada, guisado de pollo o rajas, papas doradas y queso fresco.',
        img: 'img/burrito_verde.jpg', badge: null,
        opts: [
          { label: 'Guisado', req: true, choices: ['Pollo en salsa verde', 'Rajas con crema', 'Nopales con chile', 'Espinacas con queso'] },
        ]
      },
    ]
  },

  tacos: {
    label: '🌮 Tacos',
    items: [
      {
        id: 'T1', name: 'Tacos de Guisado (3 pzas)', price: 38,
        desc: 'Tortillas de maíz recién hechas con guisado a elegir y acompañamientos.',
        img: 'img/tacogui.jpg', badge: 'Popular',
        opts: [
          { label: 'Guisado', req: true, choices: ['Carne asada', 'Picadillo', 'Frijoles con queso', 'Rajas con crema', 'Papa con chorizo', 'Nopales', 'Pollo rojo'] },
          { label: 'Salsa', req: false, choices: ['Verde', 'Roja', 'Sin salsa'] },
          { label: '¿Con qué sirvo?', req: false, choices: ['Cebolla y cilantro', 'Solo', 'Con guacamole +$10'] },
        ]
      },
      {
        id: 'T2', name: 'Tacos de Canasta (3 pzas)', price: 30,
        desc: 'Clásicos tacos sudados rellenos de papa, frijoles o chicharrón prensado.',
        img: 'img/tacosca.jpg', badge: null,
        opts: [
          { label: 'Relleno', req: true, choices: ['Papa', 'Frijoles', 'Chicharrón prensado', 'Mix de los tres'] },
        ]
      },
      {
        id: 'T3', name: 'Taco Dorado (2 pzas)', price: 32,
        desc: 'Taco frito y crujiente con relleno de tu elección. Con lechuga, crema y queso.',
        img: 'img/tacosdo.jpg', badge: null,
        opts: [
          { label: 'Relleno', req: true, choices: ['Pollo', 'Papa', 'Res deshebrada', 'Frijoles'] },
          { label: 'Toppings', req: false, choices: ['Lechuga y crema', 'Solo crema', 'Con todo (lechuga, crema, salsa)'] },
        ]
      },
    ]
  },

  tortas: {
    label: '🥪 Tortas y Sándwiches',
    items: [
      {
        id: 'S1', name: 'Torta Ahogada', price: 48,
        desc: 'Pan birote relleno de frijoles y carnitas, bañada en salsa roja picante. Estilo Guadalajara.',
        img: 'img/torta_jamon.jpg', badge: '🌶 Picosa',
        opts: [
          { label: 'Relleno', req: true, choices: ['Carnitas', 'Pollo desebrado', 'Milanesa'] },
          { label: 'Picante de la salsa', req: false, choices: ['Suave', 'Media', 'Muy picante 🔥'] },
        ]
      },
      {
        id: 'S2', name: 'Torta de Milanesa', price: 52,
        desc: 'Milanesa crocante, aguacate, tomate, cebolla, jalapeño, mayonesa y queso amarillo.',
        img: 'img/torta_mila.jpg', badge: 'Favorita',
        opts: [
          { label: 'Milanesa', req: true, choices: ['Res', 'Pollo', 'Cerdo'] },
          { label: 'Extras', req: false, choices: ['Con jalapeño', 'Sin jalapeño', '+ Tocino ($15)'] },
        ]
      },
      {
        id: 'S3', name: 'Sándwich Cubano', price: 55,
        desc: 'Pan baguette, jamón de pierna, cerdo asado, queso suizo, mostaza y pepinos encurtidos.',
        img: 'img/sancu.jpg', badge: null,
        opts: [
          { label: 'Pan', req: true, choices: ['Baguette', 'Telera', 'Bolillo'] },
        ]
      },
      {
        id: 'S4', name: 'Sándwich Club', price: 50,
        desc: 'Triple decker con pollo, tocino, lechuga, tomate, aguacate y mayonesa especial.',
        img: 'img/wrap.jpg', badge: null,
        opts: [
          { label: 'Pan', req: true, choices: ['Integral', 'Blanco', 'Centeno'] },
          { label: 'Extras', req: false, choices: ['Sin aguacate', 'Extra tocino +$12', 'Normal'] },
        ]
      },
    ]
  },

  chilaquiles: {
    label: '🥗 Chilaquiles',
    items: [
      {
        id: 'C1', name: 'Chilaquiles Rojos', price: 50,
        desc: 'Totopos bañados en salsa roja casera. Con pollo deshebrado, crema, queso y cebolla.',
        img: 'img/chilaro.jpg', badge: 'Clásico',
        opts: [
          { label: 'Proteína', req: true, choices: ['Pollo deshebrado', 'Huevo revuelto', 'Sin proteína'] },
          { label: 'Picante', req: false, choices: ['Normal', 'Poco', 'Extra picante 🌶️'] },
        ]
      },
      {
        id: 'C2', name: 'Chilaquiles Verdes', price: 50,
        desc: 'Tomatillo fresco tatemado, pollo o huevo, queso cotija y aguacate en rebanadas.',
        img: 'img/chilaver.jpg', badge: null,
        opts: [
          { label: 'Proteína', req: true, choices: ['Pollo deshebrado', 'Huevo estrellado', 'Sin proteína'] },
          { label: 'Picante', req: false, choices: ['Normal', 'Poco', 'Extra picante 🌶️'] },
        ]
      },
      {
        id: 'C3', name: 'Chilaquiles Divorciados', price: 58,
        desc: 'Mitad roja, mitad verde. Huevo estrellado en cada sección y queso fresco gratinado.',
        img: 'img/chiladiv.jpg', badge: '💘 Los mejores',
        opts: [
          { label: 'Proteína extra', req: false, choices: ['Con pollo +$10', 'Solo huevo', 'Sin proteína'] },
        ]
      },
      {
        id: 'C4', name: 'Chilaquiles con Mole', price: 58,
        desc: 'Salsa de mole negro tradicional, pollo deshebrado, ajonjolí, cebolla y crema.',
        img: 'img/chilamole.jpg', badge: 'Especial',
        opts: [
          { label: 'Proteína', req: true, choices: ['Pollo deshebrado', 'Sin proteína'] },
        ]
      },
    ]
  },

  quesadillas: {
    label: '🧀 Quesadillas',
    items: [
      {
        id: 'Q1', name: 'Quesadilla Sencilla', price: 28,
        desc: 'Tortilla de harina con queso Chihuahua fundido y tu relleno a elegir.',
        img: 'img/qs.jpg', badge: null,
        opts: [
          { label: 'Relleno', req: true, choices: ['Solo queso', 'Queso y flor de calabaza', 'Queso y hongo', 'Queso y chorizo', 'Queso y rajas', 'Queso y pollo'] },
        ]
      },
      {
        id: 'Q2', name: 'Quesadilla Sincronizada', price: 42,
        desc: 'Doble tortilla con jamón, queso, frijoles y crema. Crocante por fuera.',
        img: 'img/qssin.jpg', badge: 'Popular',
        opts: [
          { label: 'Extra', req: false, choices: ['Sin extras', '+ Aguacate', '+ Jalapeño'] },
        ]
      },
      {
        id: 'Q3', name: 'Quesadilla Norteña XL', price: 52,
        desc: 'Tortilla extra grande, doble queso Chihuahua y relleno especial. Con crema y salsa.',
        img: 'img/qsxl.jpg', badge: 'XL',
        opts: [
          { label: 'Relleno', req: true, choices: ['Machaca con queso', 'Carne asada y queso', 'Pollo y queso', 'Solo doble queso'] },
          { label: 'Salsa', req: false, choices: ['Verde', 'Roja', 'Sin salsa'] },
        ]
      },
    ]
  },

  sopas: {
    label: '🍲 Sopas y Caldos',
    items: [
      {
        id: 'SO1', name: 'Sopa del Día', price: 35,
        desc: 'Sopa casera preparada cada mañana. Pregunta al mesero por el sabor de hoy.',
        img: 'img/sopa.jpg', badge: 'Casera',
        opts: [
          { label: 'Tamaño', req: true, choices: ['Chica', 'Grande'] },
        ]
      },
      {
        id: 'SO2', name: 'Caldo de Res', price: 48,
        desc: 'Caldo humeante con chamorro, verduras, elote y chile de árbol. Con tortillas.',
        img: 'img/caldores.jpg', badge: 'Nutritivo',
        opts: [
          { label: 'Picante', req: false, choices: ['Sin chile', 'Con chile de árbol', 'Extra chile 🌶️'] },
        ]
      },
      {
        id: 'SO3', name: 'Menudo', price: 55,
        desc: 'Menudo norteño con pancita de res, orégano, limón y cebolla. Los sábados es rojo.',
        img: 'img/menudo.jpg', badge: 'Sábados',
        opts: [
          { label: 'Con qué acompañas', req: false, choices: ['Tortillas de maíz', 'Tortillas de harina', 'Pan bolillo'] },
        ]
      },
      {
        id: 'SO4', name: 'Pozole Rojo', price: 52,
        desc: 'Maíz cacahuazintle, cerdo, rábano, lechuga, orégano y tostadas. Tradicional.',
        img: 'img/posole.jpg', badge: null,
        opts: [
          { label: 'Tamaño', req: true, choices: ['Mediano', 'Grande'] },
          { label: 'Picante', req: false, choices: ['Normal', 'Extra salsas'] },
        ]
      },
    ]
  },

  minipizzas: {
    label: '🍕 Minipizzas',
    items: [
      {
        id: 'P1', name: 'Minipizza Pepperoni', price: 38,
        desc: 'Masa crujiente, salsa de tomate artesanal, mozzarella y pepperoni importado.',
        img: 'img/pizzapp.jpg', badge: 'Clásica',
        opts: [
          { label: 'Tamaño', req: true, choices: ['Personal (1 pza)', 'Doble (2 pzas)'] },
          { label: 'Extra', req: false, choices: ['Normal', '+ Queso extra', '+ Jalapeños'] },
        ]
      },
      {
        id: 'P2', name: 'Pizza 4 Quesos', price: 42,
        desc: 'Mozzarella, gouda, manchego y queso Chihuahua fundidos a la perfección.',
        img: 'img/pizza4q.jpg', badge: '🧀 Especial',
        opts: [
          { label: 'Tamaño', req: true, choices: ['Personal (1 pza)', 'Doble (2 pzas)'] },
        ]
      },
      {
        id: 'P3', name: 'Pizza Vegetariana', price: 40,
        desc: 'Champiñones, pimientos, cebolla, aceitunas, espinacas y mozzarella.',
        img: 'img/pizza.jpg', badge: '🌿 Veggie',
        opts: [
          { label: 'Tamaño', req: true, choices: ['Personal (1 pza)', 'Doble (2 pzas)'] },
          { label: '¿Sin qué?', req: false, choices: ['Con todo', 'Sin aceitunas', 'Sin cebolla'] },
        ]
      },
      {
        id: 'P4', name: 'Pizza BBQ Pollo', price: 44,
        desc: 'Salsa BBQ ahumada, pollo a la parrilla, cebolla morada y mozzarella.',
        img: 'img/pizzapollo.jpg', badge: 'Popular',
        opts: [
          { label: 'Tamaño', req: true, choices: ['Personal (1 pza)', 'Doble (2 pzas)'] },
        ]
      },
    ]
  },

  snacks: {
    label: '🍟 Snacks y Antojitos',
    items: [
      {
        id: 'SN1', name: 'Papas a la Francesa', price: 28,
        desc: 'Papas doradas crujientes, aderezadas con sal y especias de la casa.',
        img: 'img/empanada.jpg', badge: null,
        opts: [
          { label: 'Tamaño', req: true, choices: ['Chica', 'Grande'] },
          { label: 'Sabor', req: false, choices: ['Natural', 'Queso', 'Salsa valentina', 'Mix de especias'] },
        ]
      },
      {
        id: 'SN2', name: 'Elote en Vaso', price: 22,
        desc: 'Granos de elote dulce con mayonesa, queso Cotija, limón y chile piquín.',
        img: 'img/elote.jpg', badge: '🌽 Fresh',
        opts: [
          { label: 'Extra', req: false, choices: ['Normal', 'Extra mayonesa', 'Extra queso', 'Extra limón'] },
          { label: 'Picante', req: false, choices: ['Sin chile', 'Poco', 'Mucho 🔥'] },
        ]
      },
      {
        id: 'SN3', name: 'Tostadas (2 pzas)', price: 30,
        desc: 'Tostadas crujientes con frijoles, pollo o atún, lechuga, crema y queso.',
        img: 'img/tostadas.jpg', badge: null,
        opts: [
          { label: 'Relleno', req: true, choices: ['Pollo', 'Atún', 'Frijoles con queso', 'Ceviche de atún'] },
        ]
      },
      {
        id: 'SN4', name: 'Nachos con Queso', price: 35,
        desc: 'Totopos dorados con salsa de queso cheddar, jalapeños y crema ácida.',
        img: 'img/huevos_rancheros.jpg', badge: '🧀 Snack',
        opts: [
          { label: 'Extra', req: false, choices: ['Normal', '+ Guacamole', '+ Frijoles', '+ Pollo', '+ Todo'] },
        ]
      },
      {
        id: 'SN5', name: 'Fruit Cup', price: 25,
        desc: 'Mezcla de fruta fresca de temporada con chamoy, chile y limón. Servida fría.',
        img: 'img/fruta.jpg', badge: '🍓 Fresh',
        opts: [
          { label: 'Condimento', req: false, choices: ['Solo fruta', 'Con chamoy', 'Con chile y limón', 'Con todo'] },
        ]
      },
    ]
  },

  postres: {
    label: '🍰 Postres y Dulces',
    items: [
      {
        id: 'PT1', name: 'Pastel del Día', price: 32,
        desc: 'Rebanada generosa del pastel casero preparado cada mañana. ¡Pregunta el sabor!',
        img: 'img/pastel.jpg', badge: 'Casero',
        opts: [
          { label: 'Extra', req: false, choices: ['Solo', 'Con helado de vainilla +$12', 'Con café'] },
        ]
      },
      {
        id: 'PT2', name: 'donas', price: 28,
        desc: 'donas de chocolate.',
        img: 'img/donas.jpg', badge: null,
        opts: [
          { label: 'Extra', req: false, choices: ['Solo', 'Con crema batida', 'Con fruta'] },
        ]
      },
      {
        id: 'PT3', name: 'Churros (3 pzas)', price: 25,
        desc: 'Churros crujientes recién fritos con azúcar y canela. Con chocolate para dip.',
        img: 'img/churros.jpg', badge: '🍬 Favorito',
        opts: [
          { label: 'Dip', req: false, choices: ['Chocolate', 'Cajeta', 'Nutella', 'Sin dip'] },
        ]
      },
      {
        id: 'PT4', name: 'Gelatina de Mosaico', price: 18,
        desc: 'Gelatina colorida casera de cuadros, con leche condensada. Siempre fresca.',
        img: 'img/gela.jpg', badge: null,
        opts: []
      },
      {
        id: 'PT5', name: 'Dona simpson', price: 20,
        desc: 'Dona esponjosa con glaseado de azúcar. También disponible con chocolate.',
        img: 'img/donasi.jpg', badge: null,
        opts: [
          { label: 'Glaseado', req: true, choices: ['Azúcar blanca', 'Chocolate', 'Fresa', 'Vainilla con chispas'] },
        ]
      },
    ]
  },

  bebidas_calientes: {
    label: '☕ Bebidas Calientes',
    items: [
      {
        id: 'BC1', name: 'Café Americano', price: 22,
        desc: 'Café de grano 100% mexicano recién molido. Cargado, suave o doble shot.',
        img: 'img/cafe_americano.jpg', badge: null,
        opts: [
          { label: 'Intensidad', req: true, choices: ['Suave', 'Normal', 'Doble shot'] },
          { label: 'Azúcar', req: false, choices: ['Sin azúcar', '1 cuchara', '2 cucharas', 'Sacarina'] },
        ]
      },
      {
        id: 'BC2', name: 'Latte / Capuchino', price: 30,
        desc: 'Espresso con leche cremosa vaporizada. Arte latte incluido.',
        img: 'img/capuccino.jpg', badge: '☕ Especial',
        opts: [
          { label: 'Tipo', req: true, choices: ['Latte', 'Capuchino', 'Flat White', 'Macchiato'] },
          { label: 'Leche', req: false, choices: ['Entera', 'Descremada', 'Sin lactosa', 'Avena (vegetal)'] },
          { label: 'Azúcar/Jarabe', req: false, choices: ['Sin azúcar', 'Azúcar normal', 'Vainilla', 'Caramelo', 'Avellana'] },
        ]
      },
      {
        id: 'BC3', name: 'Chocolate Caliente', price: 28,
        desc: 'Chocolate mexicano de Oaxaca, espumoso y espeso. Acompañado de churro.',
        img: 'img/latte.jpg', badge: null,
        opts: [
          { label: 'Leche', req: true, choices: ['Entera', 'Descremada', 'Avena (vegetal)'] },
          { label: 'Extra', req: false, choices: ['Con churro +$8', 'Solo', 'Con nata'] },
        ]
      },
      {
        id: 'BC4', name: 'Té de Hierbas', price: 18,
        desc: 'Selección de tés naturales. Servido en tetera individual con miel de abeja.',
        img: 'img/te.jpg', badge: '🍵 Natural',
        opts: [
          { label: 'Sabor', req: true, choices: ['Manzanilla', 'Menta', 'Jengibre con limón', 'Canela', 'Tila (relajante)'] },
        ]
      },
    ]
  },

  bebidas_frias: {
    label: '🧃 Bebidas Frías',
    items: [
      {
        id: 'BF1', name: 'Agua Fresca', price: 18,
        desc: 'Preparada cada mañana con fruta natural de temporada y sin colorantes.',
        img: 'img/agua.jpg', badge: '🌿 Natural',
        opts: [
          { label: 'Sabor', req: true, choices: ['Jamaica', 'Horchata', 'Limón', 'Sandía', 'Pepino con chía', 'Tamarindo'] },
          { label: 'Tamaño', req: true, choices: ['Vaso (350ml)', 'Copo grande (600ml)'] },
        ]
      },
      {
        id: 'BF2', name: 'Jugo Natural', price: 26,
        desc: 'Jugos 100% exprimidos al momento. Sin azúcar añadida.',
        img: 'img/jugo_naranja.jpg', badge: '🍊 Fresco',
        opts: [
          { label: 'Fruta', req: true, choices: ['Naranja', 'Zanahoria con naranja', 'Verde (apio, pepino, limón)', 'Betabel con manzana', 'Toronja con miel'] },
          { label: 'Tamaño', req: true, choices: ['Chico (400ml)', 'Grande (600ml)'] },
        ]
      },
      {
        id: 'BF3', name: 'Licuado', price: 28,
        desc: 'Licuado cremoso con leche o agua, fruta natural y tu toque de miel.',
        img: 'img/licuado.jpg', badge: null,
        opts: [
          { label: 'Fruta', req: true, choices: ['Plátano', 'Fresa', 'Mango', 'Papaya', 'Guayaba', 'Mix tropical'] },
          { label: 'Base', req: true, choices: ['Leche entera', 'Leche descremada', 'Agua', 'Leche de avena'] },
          { label: 'Extra', req: false, choices: ['Sin extra', '+ Granola', '+ Chía', '+ Proteína +$15'] },
        ]
      },
      {
        id: 'BF4', name: 'Frappé de Café', price: 35,
        desc: 'Café helado batido con leche, hielo y tu sabor favorito. Cremoso y refrescante.',
        img: 'img/frappe.jpg', badge: '☕ Helado',
        opts: [
          { label: 'Sabor', req: true, choices: ['Caramelo', 'Vainilla', 'Moka', 'Avellana', 'Clásico espresso'] },
          { label: 'Extra', req: false, choices: ['Con chantilly', 'Sin chantilly'] },
        ]
      },
      {
        id: 'BF5', name: 'Refresco / Agua Mineral', price: 15,
        desc: 'Bebidas embotelladas frías. Variedad de marcas disponibles.',
        img: 'img/aguami.jpg', badge: null,
        opts: [
          { label: 'Tipo', req: true, choices: ['Coca-Cola', 'Sprite', 'Agua mineral con gas', 'Agua natural', 'Sidral', 'Peñafiel'] },
        ]
      },
    ]
  },

  combos: {
    label: '🎁 Combos del Día',
    items: [
      {
        id: 'CO1', name: 'Combo Estudiantil', price: 65,
        desc: '1 Burrito clásico + 1 Agua fresca + 1 Gelatina o fruta. El más pedido.',
        img: 'img/hero_menu.jpg', badge: '💰 Ahorra $18',
        opts: [
          { label: 'Burrito de', req: true, choices: ['Carne asada', 'Picadillo', 'Pollo', 'Rajas con queso', 'Frijoles con queso'] },
          { label: 'Agua fresca', req: true, choices: ['Jamaica', 'Horchata', 'Limón', 'Sandía'] },
          { label: 'Postre', req: true, choices: ['Gelatina de mosaico', 'Fruit cup', 'Dona'] },
        ]
      },
      {
        id: 'CO2', name: 'Combo Desayuno Completo', price: 72,
        desc: 'Huevos al gusto + Café americano + Pan tostado. Todo para comenzar bien el día.',
        img: 'img/burrito_clasico.jpg', badge: '☀️ Mañanero',
        opts: [
          { label: 'Huevos', req: true, choices: ['Estrellados', 'Revueltos', 'Con chorizo', 'A la mexicana'] },
          { label: 'Café', req: true, choices: ['Americano', 'Con leche', 'Capuchino'] },
        ]
      },
      {
        id: 'CO3', name: 'Combo Pizza + Refresco', price: 50,
        desc: '2 Minipizzas a tu elección más un refresco de 355ml. ¡Perfecto para la tarde!',
        img: 'img/pizzasoda.jpg', badge: '🍕+🥤',
        opts: [
          { label: 'Pizza', req: true, choices: ['Pepperoni', '4 Quesos', 'Vegetariana', 'BBQ Pollo'] },
          { label: 'Refresco', req: true, choices: ['Coca-Cola', 'Sprite', 'Agua mineral', 'Sidral'] },
        ]
      },
      {
        id: 'CO4', name: 'Combo Snack + Bebida', price: 42,
        desc: 'Papas chicas o nachos + Refresco o agua. Ideal para el recreo.',
        img: 'img/combohue.jpg', badge: '⚡ Rápido',
        opts: [
          { label: 'Snack', req: true, choices: ['Papas a la francesa (chica)', 'Nachos con queso', 'Tostadas (2 pzas)'] },
          { label: 'Bebida', req: true, choices: ['Refresco', 'Agua natural', 'Agua fresca'] },
        ]
      },
    ]
  },
};
