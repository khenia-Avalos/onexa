<?php
session_start();

// Configurar manejo de errores (útil para depuración)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Inicializar el carrito SIEMPRE (evita "Undefined array")
if (!isset($_SESSION['carrito']) || !is_array($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

// 2. Cargar archivos necesarios con verificación
require_once 'conexion.php';
if (!isset($conn) || !($conn instanceof mysqli)) {
    die("Error: No se pudo establecer conexión con la base de datos.");
}

require_once 'carrito_functions.php';
if (!function_exists('obtenerCarritoUsuario')) {
    die("Error: La función obtenerCarritoUsuario() no existe.");
}

// 3. Solo proceder si hay un usuario logueado
if (isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id'])) {
    try {
        // 4. Obtener ID del carrito con validación
        $carrito_id = obtenerCarritoUsuario($_SESSION['usuario_id']);
        
        if (empty($carrito_id)) {
            throw new Exception("No se encontró un carrito activo para este usuario.");
        }

        // 5. Consulta segura con manejo de errores
        $query = "
            SELECT producto_id, cantidad, nombre, precio, imagen 
            FROM carrito_items 
            WHERE carrito_id = ?
        ";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception("Error en la consulta: " . $conn->error);
        }

        $stmt->bind_param("i", $carrito_id);
        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $items = ($result) ? $result->fetch_all(MYSQLI_ASSOC) : [];

        // 6. Actualizar el carrito en sesión
        $_SESSION['carrito'] = []; // Limpiar antes de agregar nuevos items
        
        foreach ($items as $item) {
            if (empty($item['producto_id'])) {
                continue; // Si no hay ID, omitir
            }
            
            $_SESSION['carrito'][$item['producto_id']] = [
                'nombre'  => $item['nombre'] ?? 'Sin nombre',
                'precio'  => floatval($item['precio'] ?? 0),
                'imagen'  => $item['imagen'] ?? 'default.jpg',
                'cantidad' => intval($item['cantidad'] ?? 1)
            ];
        }

        $stmt->close();
    } catch (Exception $e) {
        // Registrar error sin romper la ejecución
        error_log("Error en carrito: " . $e->getMessage());
        $_SESSION['error_carrito'] = "Hubo un problema al cargar tu carrito. Por favor, recarga la página.";
    }
}

// 7. Cierre de conexión (opcional, depende de tu arquitectura)
$conn->close();
?>