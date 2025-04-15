<?php
// Iniciar buffer para evitar corrupción de JSON
ob_start();//l buffer actúa como seguro de calidad, asegurando que solo llegue al navegador el JSON limpio que quieres enviar, sin contaminación accidental.

// Configuración de errores
ini_set('display_errors', 0);//oculta errores en produccion 
error_reporting(E_ALL);//pero los reporta internamente
header('Content-Type: application/json');//la respuesta sera json

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require __DIR__ . '/vendor/autoload.php'; // Carga Composer

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load(); // Carga variables de entorno


//Importo las herramientas necesarias para enviar correos electrónicos de forma segura y profesional, estos estan en el archivo implementado


// Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);//Verifico que el formulario se envió correctamente, y si no, devuelvo un error claro.
    exit;
}

// Sanitizar entradas Limpio cada dato recibido para eliminar posibles códigos maliciosos,
//  como si pasara cada valor por un filtro de seguridad
$nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$asunto_form = filter_input(INPUT_POST, 'asunto', FILTER_SANITIZE_STRING);
$mensaje = filter_input(INPUT_POST, 'mensaje', FILTER_SANITIZE_STRING);

// Validaciones Compruebo que no falte información esencial, como un guardia que revisa documentos incompletos
if (empty($nombre) || empty($email) || empty($mensaje)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
    exit;
}
//Verifico que el email tenga un formato correcto
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email no válido']);
    exit;
}

// Configurar PHPMailer
$mail = new PHPMailer(true);

try {
    // 1. Configuración SMTP Simple Mail Transfer Protocol)
    //"Preparo el servicio de correo con todos los datos de conexión segura
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = getenv('MAIL_USERNAME');
$mail->Password = getenv('MAIL_PASSWORD');
 // Contraseña de aplicación para permitir que programas o aplicaciones accedan a tu cuenta de forma segura, sin usar tu contraseña principal.
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;//El puerto 587 es el puerto estándar para el envío de correos con envío seguro mediante STARTTLS.
    $mail->CharSet = 'UTF-8';
    $mail->SMTPDebug = 3; // muestra todo lo que ocurre detrás de escenas cuando intentas enviar un correo.

    // 2. Configurar remitente y destinatario
    $mail->setFrom($email, $nombre); // Email del formulario
    $mail->addAddress('kheniavalos@gmail.com'); // Tu email de destino
    $mail->addReplyTo($email, $nombre); // Para responder al cliente

    // 3. Contenido del correo
    $mail->isHTML(true);
    $mail->Subject = mb_encode_mimeheader("Contacto: $asunto_form", 'UTF-8');
    $mail->Body = "
        <h2>Nuevo mensaje de contacto</h2>
        <p><strong>Nombre:</strong> $nombre</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Asunto:</strong> $asunto_form</p>
        <p><strong>Mensaje:</strong></p>
        <p>".nl2br(htmlspecialchars($mensaje))."</p>
    ";
    $mail->AltBody = strip_tags($mensaje);

    // 4. Enviar y verificar
    //ntento enviar el correo y, si tiene éxito, devuelvo un mensaje positivo; si falla, lanzo un err
    if ($mail->send()) {
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => '¡Mensaje enviado! Gracias por contactarnos. Te responderemos en 24-48 horas.'
        ]);
        exit;
    }
    throw new Exception('Error en el envío');

} catch (Exception $e) {
    error_log('Error PHPMailer: ' . $e->getMessage() . ' - ' . ($mail->ErrorInfo ?? ''));
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al enviar el mensaje. Por favor, contáctanos por teléfono.'
    ]);
    exit;
}
?>