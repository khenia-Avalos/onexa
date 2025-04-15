<?php
function obtenerCarritoUsuario($usuario_id) {
    global $conn;
    
    // 1. Buscar carrito activo existente
    $stmt = $conn->prepare("SELECT id FROM carritos WHERE usuario_id = ? AND estado = 'activo'");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['id'];
    }
    
    // 2. Crear nuevo carrito si no existe
    $stmt = $conn->prepare("INSERT INTO carritos (usuario_id, estado) VALUES (?, 'activo')");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    
    return $conn->insert_id;
}

function obtenerItemsCarrito($usuario_id) {
    global $conn;
    $carrito_id = obtenerCarritoUsuario($usuario_id);
    
    $stmt = $conn->prepare("
        SELECT producto_id, cantidad, nombre, precio, imagen 
        FROM carrito_items 
        WHERE carrito_id = ?
    ");
    $stmt->bind_param("i", $carrito_id);
    $stmt->execute();
    
    $items = [];
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $items[$row['producto_id']] = [
            'nombre' => $row['nombre'],
            'precio' => $row['precio'],
            'imagen' => $row['imagen'],
            'cantidad' => $row['cantidad']
        ];
    }
    
    return $items;
}

function sincronizarCarrito($usuario_id, $items) {
    global $conn;
    $carrito_id = obtenerCarritoUsuario($usuario_id);
    
    // 1. Limpiar carrito actual
    $stmt = $conn->prepare("DELETE FROM carrito_items WHERE carrito_id = ?");
    $stmt->bind_param("i", $carrito_id);
    $stmt->execute();
    
    // 2. Agregar nuevos items
    foreach ($items as $producto_id => $item) {
        $stmt = $conn->prepare("
            INSERT INTO carrito_items 
            (carrito_id, producto_id, cantidad, nombre, precio, imagen) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "iiisds",
            $carrito_id,
            $producto_id,
            $item['cantidad'],
            $item['nombre'],
            $item['precio'],
            $item['imagen']
        );
        $stmt->execute();
    }
}


?>