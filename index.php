<?php
session_start();

// Si ya tiene sesión activa, redirigir al menú
if (isset($_SESSION['user'])) {
    header('Location: menu.php');
    exit;
}

// Procesar login
$error = false;
$registro_exitoso = isset($_GET['registro']) && $_GET['registro'] === 'ok';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'db.php';

    $matricula = trim($_POST['matricula'] ?? '');
    $password  = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE matricula = ?");
    $stmt->execute([$matricula]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id'        => $user['id'],
            'matricula' => $user['matricula'],
            'nombre'    => $user['nombre'],
            'carrera'   => $user['carrera'],
            'iniciales' => $user['iniciales'],
        ];
        header('Location: menu.php');
        exit;
    } else {
        $error = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LatteLink — Iniciar sesión</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/style.css">

  <link rel="stylesheet" href="css/auth.css">
</head>
<body class="auth-page">

  <!-- ══ PANEL VISUAL (izquierda) ══ -->
  <div class="panel-visual">
    <div class="bg" style="background-image:url('img/hero_login.jpg')"></div>
    <div class="content">
      <div class="logo-top">Latte<span class="accent">Link</span></div>

      <div class="panel-copy">
        <h1>Ordena antes.<br>Disfruta<br>sin filas.</h1>
        <p>El sistema de pedidos anticipados de la cafetería universitaria. Sin esperas, sin complicaciones, sin perderte clase.</p>
        <div class="panel-badges">
          <span class="panel-badge">🌯 Burritos</span>
          <span class="panel-badge">🌮 Tacos</span>
          <span class="panel-badge">🥗 Chilaquiles</span>
          <span class="panel-badge">🍕 Minipizzas</span>
          <span class="panel-badge">☕ Café</span>
          <span class="panel-badge">🍰 Postres</span>
        </div>
      </div>

      <div class="panel-footer">© 2026 LatteLink · Universidad Tecnológica de Chihuahua</div>
    </div>
  </div>

  <!-- ══ FORMULARIO (derecha) ══ -->
  <div class="panel-form">
    <div class="form-inner">
      <div class="form-header">
        <h2>Bienvenido 👋</h2>
        <p>Inicia sesión con tu cuenta estudiantil UTCH</p>
      </div>

      <form class="form-body" method="POST" action="index.php">
        <div class="success-msg <?php echo $registro_exitoso ? 'show' : ''; ?>">✅ ¡Cuenta creada exitosamente! Ahora inicia sesión.</div>
        <div class="error-msg <?php echo $error ? 'show' : ''; ?>" id="errMsg">❌ Matrícula o contraseña incorrectos. Intenta de nuevo.</div>

        <div class="field">
          <label>Matrícula</label>
          <input type="text" name="matricula" id="inputUser" placeholder="ej. A00123456" autocomplete="username" value="<?php echo htmlspecialchars($_POST['matricula'] ?? ''); ?>">
        </div>

        <div class="field">
          <label>Contraseña</label>
          <input type="password" name="password" id="inputPass" placeholder="••••••••" autocomplete="current-password">
        </div>

        <button type="submit" class="submit-btn">Entrar al menú →</button>

        <div class="register-link">
          ¿No tienes cuenta? <a href="registro.php">Crear cuenta</a>
        </div>

        
      </form>
    </div>
  </div>

  <!-- Toast -->
  <div id="toast"></div>

  <script>
    function fillDemo() {
      document.getElementById('inputUser').value = 'A00123456';
      document.getElementById('inputPass').value = 'utch2026';
      document.getElementById('inputPass').focus();
    }

    document.getElementById('inputPass').addEventListener('keydown', e => {
      if (e.key === 'Enter') document.querySelector('form').submit();
    });
    document.getElementById('inputUser').addEventListener('keydown', e => {
      if (e.key === 'Enter') document.getElementById('inputPass').focus();
    });
  </script>
</body>
</html>
