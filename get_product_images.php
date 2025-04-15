<?php
session_start();
require_once 'includes/conexion.php';

$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$productId) {
    echo json_encode([]);
    exit;
}

$conexion = new mysqli("localhost", "kheniali", "123", "tienda");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$stmt = $conexion->prepare("SELECT imagen FROM producto_imagenes WHERE producto_id = ? ORDER BY orden");
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

$images = [];
while ($row = $result->fetch_assoc()) {
    $images[] = ['imagen' => htmlspecialchars($row['imagen'])];
}

echo json_encode($images);
$conexion->close();
?>