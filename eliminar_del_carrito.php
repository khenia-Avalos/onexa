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

if ($id === null) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

if (isset($_SESSION['carrito'][$id])) {
    unset($_SESSION['carrito'][$id]);
    
    // Calcular el nuevo total
    $total = 0;
    foreach ($_SESSION['carrito'] as $producto) {
        $total += $producto['precio'] * $producto['cantidad'];
    }
    
    echo json_encode([
        'success' => true,
        'total' => number_format($total, 2),
        'totalItems' => count($_SESSION['carrito'])
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Producto no encontrado en el carrito'
    ]);
}
?>