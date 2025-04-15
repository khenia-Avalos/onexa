<?php
session_start();
header('Content-Type: application/json');

// Verifica el método y los datos
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;
$cantidad = $data['cantidad'] ?? 1;

if ($id === null) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

if (isset($_SESSION['carrito'][$id])) {
    $_SESSION['carrito'][$id]['cantidad'] = (int)$cantidad;
    
    // Calcular el nuevo total y subtotal
    $subtotal = $_SESSION['carrito'][$id]['precio'] * $cantidad;
    $total = 0;
    foreach ($_SESSION['carrito'] as $producto) {
        $total += $producto['precio'] * $producto['cantidad'];
    }
    
    echo json_encode([
        'success' => true,
        'subtotal' => number_format($subtotal, 2),
        'total' => number_format($total, 2),
        'totalItems' => array_sum(array_column($_SESSION['carrito'], 'cantidad'))
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Producto no encontrado en el carrito'
    ]);
}
?>