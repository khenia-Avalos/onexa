<?php
session_start();


$host = 'localhost';
$dbname = 'tienda';
$username = 'kheniali';
$password = '123';


try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['registro'])) {
   
    $nombre = htmlspecialchars(trim($_POST['nombre_user']));
    $correo = filter_var(trim($_POST['correo_user']), FILTER_SANITIZE_EMAIL);
    $contrasena = $_POST['contrasena_user'];
    
    // Validaciones
    $errores = [];
    
    if (empty($nombre)) {
        $errores[] = "El nombre de usuario es requerido";
    }
    
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El correo electrónico no es válido";
    }
    
    if (strlen($contrasena) < 8) {
        $errores[] = "La contraseña debe tener al menos 8 caracteres";
    }
    
    // Si no hay errores, proceder con el registro
    if (empty($errores)) {
        try {
            // Verificar si el correo ya existe
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE correo_user = ?");
            $stmt->execute([$correo]);
            
            if ($stmt->rowCount() > 0) {
                $errores[] = "Este correo electrónico ya está registrado";
            } else {
                // Hash de la contraseña
                $contrasena_hash = password_hash($contrasena, PASSWORD_DEFAULT);
                
                // Insertar nuevo usuario
                $stmt = $conn->prepare("INSERT INTO usuarios (nombre_user, contrasena_user, correo_user) VALUES (?, ?, ?)");
                $stmt->execute([$nombre, $contrasena_hash, $correo]);
                
                
                $_SESSION['registro_exitoso'] = true;
                
                // Redirigir para evitar reenvío del formulario
                header("Location: login1.php");
                exit();
            }
        } catch(PDOException $e) {
            $errores[] = "Error al registrar el usuario: " . $e->getMessage();
        }
    }
    
  
    if (!empty($errores)) {
        $_SESSION['errores_registro'] = $errores;
        header("Location: login1.php");
        exit();
    }
}


header("Location: login1.php");
exit();
?>