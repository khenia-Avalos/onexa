<?php
header('Content-Type: application/json');
require __DIR__ . '/vendor/autoload.php';

// Debug: Log de inicio
file_put_contents('debug.log', "[" . date('Y-m-d H:i:s') . "] Inicio\n", FILE_APPEND);

try {
    // 1. Cargar .env
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad(); // No falla si .env no existe

    // 2. Validar variables SMTP
    if (!isset($_ENV['SMTP_HOST'], $_ENV['SMTP_USER'], $_ENV['SMTP_PASS'])) {
        throw new Exception('Configuración SMTP incompleta en .env');
    }

    // 3. Validar sesión y CSRF
    session_start();
    if (empty($_SESSION['csrf_token']) || empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        throw new Exception('Token CSRF inválido', 403);
    }

    // 4. Validar datos del formulario
    $data = [
        'nombre' => filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_SPECIAL_CHARS),
        'email' => filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL),
        'mensaje' => filter_input(INPUT_POST, 'mensaje', FILTER_SANITIZE_SPECIAL_CHARS)
    ];
    if (in_array(false, $data, true)) {
        throw new Exception('Datos del formulario inválidos', 400);
    }

    // 5. Configurar PHPMailer (IGUAL que en el test)
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $_ENV['SMTP_HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USER'];
    $mail->Password = $_ENV['SMTP_PASS'];
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->Timeout = 30;
    $mail->SMTPDebug = 3; // Debug detallado
    $mail->Debugoutput = function($str) {
        file_put_contents('mail.log', $str . "\n", FILE_APPEND);
    };

    // 6. Configurar email
    $mail->setFrom($_ENV['SMTP_USER'], $_ENV['MAIL_FROM_NAME'] ?? 'Formulario de contacto');
    $mail->addAddress($_ENV['MAIL_TO'] ?? $_ENV['SMTP_USER']);
    $mail->Subject = 'Nuevo mensaje de contacto';
    $mail->Body = "Nombre: {$data['nombre']}\nEmail: {$data['email']}\nMensaje: {$data['mensaje']}";

    // 7. Enviar
    if (!$mail->send()) {
        throw new Exception($mail->ErrorInfo);
    }

    // 8. Éxito
    echo json_encode(['success' => true, 'message' => '¡Mensaje enviado!']);

} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
    http_response_code($e->getCode() ?: 500);
    echo json_encode(['success' => false, 'message' => 'Error al procesar tu mensaje. Por favor inténtalo nuevamente.']);
}