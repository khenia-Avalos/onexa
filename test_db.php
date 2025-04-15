<?php
$host = 'localhost';
$dbname = 'tienda';
$username = 'kheniali';
$password = '123';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Prueba consulta
    $stmt = $conn->query("SELECT 1");
    echo "✅ Conexión exitosa. Versión MySQL: " . $conn->getAttribute(PDO::ATTR_SERVER_VERSION);
} catch(PDOException $e) {
    die("❌ Error: " . $e->getMessage());
}
?>