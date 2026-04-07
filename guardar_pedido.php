<?php
require_once 'db.php';
requireLogin();

// Solo aceptar POST con JSON
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Leer JSON del body
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

try {
    $user = $_SESSION['user'];

    // Generar número de pedido
    $numeroPedido = '#LL-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);

    // Insertar pedido
    $stmt = $pdo->prepare("
        INSERT INTO pedidos (numero_pedido, usuario_id, nombre_cliente, matricula, email, hora_recogida, metodo_pago, nota, total)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $numeroPedido,
        $user['id'],
        $input['nombre'] ?? $user['nombre'],
        $input['matricula'] ?? $user['matricula'],
        $input['email'] ?? null,
        $input['hora'] ?? null,
        $input['pago'] ?? 'Efectivo al recoger',
        $input['nota'] ?? null,
        $input['total'] ?? 0,
    ]);

    $pedidoId = $pdo->lastInsertId();

    // Insertar items del pedido
    $stmtItem = $pdo->prepare("
        INSERT INTO pedido_items (pedido_id, producto_id, nombre, precio, cantidad, opciones)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    foreach (($input['items'] ?? []) as $item) {
        // Buscar el producto por su código para obtener el ID
        $stmtProd = $pdo->prepare("SELECT id FROM productos WHERE codigo = ?");
        $stmtProd->execute([$item['id'] ?? '']);
        $productoId = $stmtProd->fetchColumn() ?: null;

        $opciones = '';
        if (!empty($item['opts']) && is_array($item['opts'])) {
            $opciones = implode(' · ', $item['opts']);
        }

        $stmtItem->execute([
            $pedidoId,
            $productoId,
            $item['name'] ?? 'Sin nombre',
            $item['price'] ?? 0,
            $item['qty'] ?? 1,
            $opciones,
        ]);
    }

    echo json_encode([
        'success'       => true,
        'numero_pedido' => $numeroPedido,
        'pedido_id'     => $pedidoId,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Error al guardar el pedido: ' . $e->getMessage(),
    ]);
}
?>
