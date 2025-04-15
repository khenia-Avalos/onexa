<?php
session_start();
header('Content-Type: application/json');

require_once 'includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['cantidad'])) {//verifica que Existan los parámetros id y cantidad en los datos enviados
    $producto_id = (int)$_POST['id'];
    $cantidad = (int)$_POST['cantidad'];//Convierte los valores a enteros 

    try {
        // Obtener producto con todos los campos necesarios
        $stmt = $conn->prepare("
            (SELECT id, nombre, precio, imagen, descripcion FROM productos WHERE id = ?)
            UNION
            (SELECT id, nombre, precio, imagen, descripcion FROM recomendados WHERE id = ?)
            LIMIT 1
            
        ");

        //Busca en la tabla productos

//También busca en recomendados (productos destacados)

//UNION combina resultados

//LIMIT 1 asegura un solo resultado
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

        // Agregar/actualizar en sesión con todos los campos
        //Si el producto no está en el carrito, lo agrega con todos sus datos e incrementa cantidad si si
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

        // Respuesta exitosa con todos los datos necesarios
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

//Multiplica precio por cantidad de cada uno

//Suma todos los subtotales

//Formatea el total con 2 decimales
?>
