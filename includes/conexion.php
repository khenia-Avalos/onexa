<?php
$servername = "localhost";
$username = "kheniali";
$password = "123";
$dbname = "tienda";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Error de conexión: ' . $conn->connect_error]));
}
$conn->set_charset("utf8mb4");

// Función para ejecutar consultas seguras MANDA A LLAMAR .. A LA BD
function ejecutarConsulta($sql, $params = [], $types = "") {
    global $conn;  // Cambiado de $conexion a $conn
    
    $stmt = $conn->prepare($sql);
  
}
?>