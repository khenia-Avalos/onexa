<?php
session_start();
header('Content-Type: application/json');

// Conexión directa (sin includes)
$host = 'localhost';
$dbname = 'tienda';
$username = 'kheniali';
$password = '123';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);//"Intenta abrir la puerta a la base de datos con las credenciales"

    //"Le dice que muestre errores claros si algo falla"
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener datos POST (100% seguro)
    //toma el usuario y contraseña que escribió la persona"

//"Si no enviaron nada, usa texto vacío para evitar errores ESTO SOLO PASA EN ESTA PAGINA 
    $usuario = $_POST['usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    // Validación básica
    if (empty($usuario) || empty($contrasena)) {
        throw new Exception("Usuario y contraseña son obligatorios");
    }

    // Consulta directa (sin funciones externas)
    //Pregunta a la base: ¿Existe un usuario con este nombre o correo?
    $stmt = $conn->prepare("SELECT id, nombre_user, contrasena_user FROM usuarios WHERE nombre_user = ? OR correo_user = ?");
    $stmt->execute([$usuario, $usuario]);
    $user = $stmt->fetch();

    if ($user && password_verify($contrasena, $user['contrasena_user'])) {//"Si encontró al usuario y la contraseña coincide..."
        $_SESSION['usuario_id'] = $user['id'];//Guarda el ID del usuario en la sesión (como un carnet virtual)"

       // "Devuelve: 'Todo bien, ve a la página principal
        echo json_encode(['success' => true, 'redirect' => 'index.php']);
    } else {
        throw new Exception("Usuario o contraseña incorrectos");
    }

} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>