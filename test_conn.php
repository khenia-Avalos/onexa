<?php
$conn = new PDO("mysql:host=localhost;dbname=tienda", "kheniali", "123");
if ($conn) {
    echo "¡Conexión exitosa!";
} else {
    print_r($conn->errorInfo());
}
?>