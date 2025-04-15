<?php
session_start();
header('Content-Type: application/json');

// Configuración de la base de datos
$db_host = 'localhost';
$db_name = 'tienda';
$db_user = 'kheniali';
$db_pass = '123';

try {
    // Conexión a la base de datos
    $db = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Obtener datos JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['cliente']) || !isset($input['carrito'])) {
        throw new Exception('Datos de pago incompletos');
    }
    
    $cliente = $input['cliente'];
    $carrito = $input['carrito'];
    $total = $input['total'] ?? 0;
    
    // Validar datos básicos
    $requiredFields = ['firstName', 'lastName', 'email', 'address', 'city', 'state', 'postalCode', 'phone'];
    foreach ($requiredFields as $field) {
        if (empty($cliente[$field])) {
            throw new Exception("El campo $field es requerido");
        }
    }
    
    if (empty($carrito)) {
        throw new Exception("El carrito está vacío");
    }
    
    // Sanitizar datos
    $nombre = htmlspecialchars(trim($cliente['firstName']));
    $apellido = htmlspecialchars(trim($cliente['lastName']));
    $email = filter_var(trim($cliente['email']), FILTER_SANITIZE_EMAIL);
    $direccion = htmlspecialchars(trim($cliente['address']));
    $ciudad = htmlspecialchars(trim($cliente['city']));
    $provincia = htmlspecialchars(trim($cliente['state']));
    $codigo_postal = htmlspecialchars(trim($cliente['postalCode']));
    $telefono = htmlspecialchars(trim($cliente['phone']));
    
    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("El email proporcionado no es válido");
    }
    
    // Iniciar transacción
    $db->beginTransaction();
    
    // 1. Verificar si el cliente ya existe
    $stmtCheckCliente = $db->prepare("SELECT id_cliente FROM clientes WHERE email = :email LIMIT 1");
    $stmtCheckCliente->execute([':email' => $email]);
    $clienteExistente = $stmtCheckCliente->fetch(PDO::FETCH_ASSOC);
    
    if ($clienteExistente) {
        $id_cliente = $clienteExistente['id_cliente'];
        
        // Actualizar datos del cliente existente (opcional)
        $stmtUpdateCliente = $db->prepare("UPDATE clientes SET 
                                         nombre = :nombre,
                                         apellido = :apellido,
                                         direccion = :direccion,
                                         ciudad = :ciudad,
                                         provincia = :provincia,
                                         codigo_postal = :codigo_postal,
                                         telefono = :telefono
                                         WHERE id_cliente = :id_cliente");
        $stmtUpdateCliente->execute([
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':direccion' => $direccion,
            ':ciudad' => $ciudad,
            ':provincia' => $provincia,
            ':codigo_postal' => $codigo_postal,
            ':telefono' => $telefono,
            ':id_cliente' => $id_cliente
        ]);
    } else {
        // Insertar nuevo cliente
        $stmtCliente = $db->prepare("INSERT INTO clientes 
                                   (nombre, apellido, email, direccion, ciudad, provincia, codigo_postal, telefono) 
                                   VALUES (:nombre, :apellido, :email, :direccion, :ciudad, :provincia, :codigo_postal, :telefono)");
        $stmtCliente->execute([
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':email' => $email,
            ':direccion' => $direccion,
            ':ciudad' => $ciudad,
            ':provincia' => $provincia,
            ':codigo_postal' => $codigo_postal,
            ':telefono' => $telefono
        ]);
        $id_cliente = $db->lastInsertId();
    }
    
    // 2. Insertar pedido
    $stmtPedido = $db->prepare("INSERT INTO pedidos 
                              (id_cliente, total, estado) 
                              VALUES (:id_cliente, :total, 'pendiente')");
    $stmtPedido->execute([
        ':id_cliente' => $id_cliente,
        ':total' => $total
    ]);
    $id_pedido = $db->lastInsertId();
    
    // 3. Insertar detalles del pedido
    $stmtDetalle = $db->prepare("INSERT INTO detalles_pedido 
                               (id_pedido, id_producto, nombre_producto, precio_unitario, cantidad, subtotal) 
                               VALUES (:id_pedido, :id_producto, :nombre_producto, :precio_unitario, :cantidad, :subtotal)");
    
    foreach ($carrito as $producto) {
        // Asegurar que los datos del producto estén completos
        $id_producto = isset($producto['id']) ? $producto['id'] : 0;
        $nombre_producto = isset($producto['nombre']) ? $producto['nombre'] : 'Producto sin nombre';
        $precio_unitario = isset($producto['precio']) ? (float)$producto['precio'] : 0;
        $cantidad = isset($producto['cantidad']) ? (int)$producto['cantidad'] : 1;
        $subtotal = $precio_unitario * $cantidad;
        
        $stmtDetalle->execute([
            ':id_pedido' => $id_pedido,
            ':id_producto' => $id_producto,
            ':nombre_producto' => $nombre_producto,
            ':precio_unitario' => $precio_unitario,
            ':cantidad' => $cantidad,
            ':subtotal' => $subtotal
        ]);
    }
    
    // Confirmar transacción
    $db->commit();
    
    // Limpiar carrito
    $_SESSION['carrito'] = [];
    
// Después de insertar el pedido:
$id_pedido = $db->lastInsertId(); // Esto devuelve un número (ej: 3, 4, 5...)

echo json_encode([
    'success' => true,
    'message' => 'Pago exitoso',
    'id_pedido' => $id_pedido, // <- Número, no texto
    'id_cliente' => $id_cliente
]);


} catch (Exception $e) {
    // Revertir transacción si hay error
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>