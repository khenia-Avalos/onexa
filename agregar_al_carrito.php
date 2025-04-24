<?php
session_start();
header('Content-Type: application/json');

require_once 'includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['cantidad'])) {//verifica que Existan los parámetros id y cantidad en los datos enviados
    $producto_id = (int)$_POST['id'];
    $cantidad = (int)$_POST['cantidad'];

    try {
        // Obtener producto
        $stmt = $conn->prepare("
            (SELECT id, nombre, precio, imagen, descripcion FROM productos WHERE id = ?)
            UNION
            (SELECT id, nombre, precio, imagen, descripcion FROM recomendados WHERE id = ?)
            LIMIT 1
            
        ");

       
        $stmt->bind_param("ii", $producto_id, $producto_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $producto = $result->fetch_assoc();

        if (!$producto) {
            throw new Exception("Producto no encontrado");
        }

        // Inicializar carrito si no existe
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }

        //Si el producto no está en el carrito, lo agrega 
        if (!isset($_SESSION['carrito'][$producto_id])) {
            $_SESSION['carrito'][$producto_id] = [
                'id' => $producto_id,
                'nombre' => $producto['nombre'],
                'precio' => $producto['precio'],
                'imagen' => $producto['imagen'],
                'descripcion' => $producto['descripcion'],
                'cantidad' => 0
            ];
        }
        $_SESSION['carrito'][$producto_id]['cantidad'] += $cantidad;

       //respuesta succ
        echo json_encode([
            'success' => true,
            'totalItems' => array_sum(array_column($_SESSION['carrito'], 'cantidad')),
            'totalPrice' => calcularTotalCarrito($_SESSION['carrito']),
            'message' => 'Producto agregado al carrito'
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos'
    ]);
}

function calcularTotalCarrito($carrito) {
    $total = 0;
    foreach ($carrito as $item) {
        $total += $item['precio'] * $item['cantidad'];
    }
    return number_format($total, 2);
}
//Recorre todos los productos del carrito


?>
