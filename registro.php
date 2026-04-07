<?php
session_start();

// Si ya tiene sesión activa, redirigir al menú
if (isset($_SESSION['user'])) {
    header('Location: menu.php');
    exit;
}

// Procesar registro
$errors = [];
$success = false;
$form = [
    'matricula' => '',
    'nombre'    => '',
    'carrera'   => '',
    'email'     => '',
    'password'  => '',
    'password2' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'db.php';

    $form['matricula'] = trim($_POST['matricula'] ?? '');
    $form['nombre']    = trim($_POST['nombre'] ?? '');
    $form['carrera']   = trim($_POST['carrera'] ?? '');
    $form['email']   = trim($_POST['email'] ?? '');
    $form['password']  = $_POST['password'] ?? '';
    $form['password2'] = $_POST['password2'] ?? '';

    // Validaciones
    if (empty($form['matricula'])) {
        $errors[] = 'La matrícula es obligatoria.';
    }
    if (empty($form['nombre'])) {
        $errors[] = 'El nombre es obligatorio.';
    }
    if (empty($form['carrera'])) {
        $errors[] = 'Selecciona una carrera.';
    }
    if (empty($form['email'])) {
        $errors[] = 'el email es obligatorio.';
    }
    if (strlen($form['password']) < 4) {
        $errors[] = 'La contraseña debe tener al menos 4 caracteres.';
    }
    if ($form['password'] !== $form['password2']) {
        $errors[] = 'Las contraseñas no coinciden.';
    }

    // Verificar matrícula única
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE matricula = ?");
        $stmt->execute([$form['matricula']]);
        if ($stmt->fetch()) {
            $errors[] = 'Esta matrícula ya está registrada.';
        }
    }

    // Insertar usuario
    if (empty($errors)) {
        // Generar iniciales del nombre
        $palabras = explode(' ', $form['nombre']);
        $iniciales = '';
        foreach ($palabras as $p) {
            if (!empty($p)) {
                $iniciales .= mb_strtoupper(mb_substr($p, 0, 1));
            }
            if (strlen($iniciales) >= 2) break;
        }
        if (strlen($iniciales) < 2) {
            $iniciales = mb_strtoupper(mb_substr($form['nombre'], 0, 2));
        }

        $hash = password_hash($form['password'], PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO usuarios (matricula, password, nombre, carrera, iniciales, email)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $form['matricula'],
            $hash,
            $form['nombre'],
            $form['carrera'],
            $iniciales,
            $form['email'],
        ]);

        header('Location: index.php?registro=ok');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LatteLink — Crear cuenta</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Syne:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/auth.css">
</head>
<body class="auth-page">

  <!-- ══ PANEL VISUAL (izquierda) ══ -->
  <div class="panel-visual">
    <div class="bg" style="background-image:url('img/hero_registro.jpg')"></div>
    <div class="content">
      <div class="logo-top">Latte<span class="accent">Link</span></div>

      <div class="panel-copy">
        <h1>Únete a<br>LatteLink<br>hoy.</h1>
        <p>Crea tu cuenta estudiantil y empieza a ordenar tus platillos favoritos sin esperas.</p>
        <div class="panel-badges">
          <span class="panel-badge">📝 Registro rápido</span>
          <span class="panel-badge">🔒 Contraseña segura</span>
          <span class="panel-badge">🚀 Ordena al instante</span>
        </div>
      </div>

      <div class="panel-footer">© 2026 LatteLink · Universidad Tecnológica de Chihuahua</div>
    </div>
  </div>

  <!-- ══ FORMULARIO (derecha) ══ -->
  <div class="panel-form">
    <div class="form-inner">
      <div class="form-header">
        <h2>Crear cuenta ✨</h2>
        <p>Regístrate con tu información estudiantil UTCH</p>
      </div>

      <form class="form-body" method="POST" action="registro.php">
        <?php if (!empty($errors)): ?>
          <div class="error-msg">
            <?php foreach ($errors as $e): ?>
              ❌ <?php echo htmlspecialchars($e); ?><br>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="field">
          <label>Matrícula</label>
          <input type="text" name="matricula" id="inputMatricula" placeholder="ej. A00123456" autocomplete="username" value="<?php echo htmlspecialchars($form['matricula']); ?>" required>
        </div>

        <div class="field">
          <label>Nombre completo</label>
          <input type="text" name="nombre" id="inputNombre" placeholder="ej. Fernando Ávila" autocomplete="name" value="<?php echo htmlspecialchars($form['nombre']); ?>" required>
        </div>

        <div class="field">
          <label>Correo</label>
          <input type="text" name="email" id="inputEmail" placeholder="ej. saul@.hotmail.com" autocomplete="email" value="<?php echo htmlspecialchars($form['email']); ?>" required>
        </div>

        <div class="field">
          <label>Carrera</label>
          <select name="carrera" id="inputCarrera" required>
            <option value="" disabled <?php echo empty($form['carrera']) ? 'selected' : ''; ?>>Selecciona tu carrera</option>
            <option value="TSU en TIC" <?php echo $form['carrera'] === 'TSU en TIC' ? 'selected' : ''; ?>>TSU en Tecnologías de la Información</option>
            <option value="TSU en Mecatrónica" <?php echo $form['carrera'] === 'TSU en Mecatrónica' ? 'selected' : ''; ?>>TSU en Mecatrónica</option>
            <option value="TSU en Administración" <?php echo $form['carrera'] === 'TSU en Administración' ? 'selected' : ''; ?>>TSU en Administración</option>
            <option value="TSU en Procesos Industriales" <?php echo $form['carrera'] === 'TSU en Procesos Industriales' ? 'selected' : ''; ?>>TSU en Procesos Industriales</option>
            <option value="TSU en Energías Renovables" <?php echo $form['carrera'] === 'TSU en Energías Renovables' ? 'selected' : ''; ?>>TSU en Energías Renovables</option>
            <option value="TSU en Dn" <?php echo $form['carrera'] === 'TSU en Dn' ? 'selected' : ''; ?>>TSU en Dn</option>
            <option value="Otra" <?php echo $form['carrera'] === 'Otra' ? 'selected' : ''; ?>>Otra</option>
          </select>
        </div>

        <div class="field">
          <label>Contraseña</label>
          <input type="password" name="password" id="inputPass" placeholder="••••••••" autocomplete="new-password" required minlength="4">
          <div class="pw-hint">Mínimo 4 caracteres</div>
        </div>

        <div class="field">
          <label>Confirmar contraseña</label>
          <input type="password" name="password2" id="inputPass2" placeholder="••••••••" autocomplete="new-password" required>
        </div>

        <button type="submit" class="submit-btn">Crear mi cuenta →</button>

        <div class="login-link">
          ¿Ya tienes cuenta? <a href="index.php">Iniciar sesión</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Toast -->
  <div id="toast"></div>

  <script>
    // Validación en tiempo real: contraseñas coinciden
    const pass1 = document.getElementById('inputPass');
    const pass2 = document.getElementById('inputPass2');

    pass2.addEventListener('input', () => {
      if (pass2.value && pass1.value !== pass2.value) {
        pass2.style.borderColor = '#e57373';
      } else {
        pass2.style.borderColor = '';
      }
    });

    // Submit con Enter en último campo
    pass2.addEventListener('keydown', e => {
      if (e.key === 'Enter') document.querySelector('form').submit();
    });
  </script>
</body>
</html>
