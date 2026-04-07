<?php
// ═══════════════════════════════════════════════
//  LATTELINK — CONEXIÓN A BASE DE DATOS (XAMPP)
// ═══════════════════════════════════════════════

$host     = 'localhost';
$dbname   = 'lattelink_db';
$username = 'root';
$password = '';
$charset  = 'utf8mb4';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=$charset",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die("❌ Error de conexión a la base de datos: " . $e->getMessage());
}

// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Función helper: verificar que el usuario esté logueado
function requireLogin() {
    if (!isset($_SESSION['user'])) {
        header('Location: index.php');
        exit;
    }
}
?>
