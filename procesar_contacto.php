<?php
declare(strict_types=1);
header('Content-Type: application/json');
ob_start();

// Iniciar sesión para validar CSRF
session_start();

require __DIR__ . '/vendor/autoload.php';

try {
    // Validar método de solicitud
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido', 405);
    }

    // Validar token CSRF
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || 
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        throw new Exception('Token CSRF inválido', 403);
    }

    // Cargar variables de entorno
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();

    // Validar configuración SMTP
    $requiredEnvVars = ['SMTP_USER', 'SMTP_PASS', 'MAIL_TO'];
    foreach ($requiredEnvVars as $var) {
        if (empty($_ENV[$var])) {
            throw new Exception("Configuración SMTP incompleta: falta $var");
        }
    }

    // Validar campos del formulario
    $requiredFields = ['nombre', 'email', 'asunto', 'mensaje'];
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("El campo $field es requerido");
        }
    }

   
    $nombre = htmlspecialchars($_POST['nombre'], ENT_QUOTES, 'UTF-8');
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $asunto = htmlspecialchars($_POST['asunto'], ENT_QUOTES, 'UTF-8');
    $mensaje = htmlspecialchars($_POST['mensaje'], ENT_QUOTES, 'UTF-8');

    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("El email proporcionado no es válido");
    }

    // Configurar PHPMailer
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $_ENV['SMTP_USER'];
    $mail->Password = $_ENV['SMTP_PASS'];
    $mail->SMTPSecure = $_ENV['SMTP_SECURE'] ?? 'tls';
    $mail->Port = $_ENV['SMTP_PORT'] ?? 587;
    
    // Solo en desarrollo activz depuracion de envio
    if ($_ENV['APP_ENV'] === 'development') {
        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str, $level) {
            file_put_contents('mail_debug.log', date('[Y-m-d H:i:s]')." [$level] $str\n", FILE_APPEND);
        };
    }

    // Configurar remitente y destinatario
    $mail->setFrom($_ENV['SMTP_USER'], $_ENV['MAIL_FROM_NAME'] ?? 'Formulario de Contacto');
    $mail->addAddress($_ENV['MAIL_TO']);
    $mail->addReplyTo($email, $nombre);

    // Asunto y cuerpo del mensaje
    $mail->Subject = "Nuevo mensaje de contacto: $asunto";
    
    $mail->Body = "
        <h1>Nuevo mensaje de contacto</h1>
        <p><strong>Nombre:</strong> $nombre</p>
        <p><strong>Email:</strong> $email</p>
        <p><strong>Asunto:</strong> $asunto</p>
        <p><strong>Mensaje:</strong></p>
        <p>".nl2br($mensaje)."</p>
        <hr>
        <p>Enviado el ".date('d/m/Y H:i:s')."</p>
    ";
    
    $mail->AltBody = strip_tags("
        Nuevo mensaje de contacto:
        Nombre: $nombre
        Email: $email
        Asunto: $asunto
        Mensaje:
        $mensaje
        Enviado el ".date('d/m/Y H:i:s')
    );

    // Enviar email
    ob_end_clean();
    
    if ($mail->send()) {
        echo json_encode([
            'success' => true,
            'message' => '¡Gracias por contactarnos! Te responderemos pronto.'
        ]);
    } else {
        throw new Exception("Error al enviar el mensaje: ".$mail->ErrorInfo);
    }

} catch (Exception $e) {
    ob_end_clean();
    http_response_code($e->getCode() >= 400 ? $e->getCode() : 500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    error_log('Contact Form Error: '.$e->getMessage());
}