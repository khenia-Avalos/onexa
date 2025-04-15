<?php
$servername = "localhost";
$username = "kheniali";
$password = "123";
$dbname = "tienda";

// Crear conexión 
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión, SI HAY UN ERROR MANDARA UN MENSAJE JSON
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Error de conexión: ' . $conn->connect_error]));
}

// Establecer el charset, PREVIENE PROBLEMAS CON CARACTERES ESPECIALES
$conn->set_charset("utf8mb4");

// Función para ejecutar consultas seguras MANDA A LLAMAR .. A LA BD
function ejecutarConsulta($sql, $params = [], $types = "") {
    global $conn;  // Cambiado de $conexion a $conn
    
    $stmt = $conn->prepare($sql);
  
}
?>