<?php
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

class SecureMailer {
    public static function send($data) {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // Configuración SMTP desde .env
            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USER'];
            $mail->Password = $_ENV['SMTP_PASS'];
            $mail->SMTPSecure = $_ENV['SMTP_PORT'] == 465 ? 'ssl' : 'tls';
            $mail->Port = $_ENV['SMTP_PORT'];
            
            // Configuración del email
            $mail->setFrom($_ENV['SMTP_USER'], $_ENV['MAIL_FROM_NAME']);
            $mail->addAddress($_ENV['MAIL_TO']);
            $mail->Subject = 'Contacto ONEXA: ' . ($data['asunto'] ?? 'Consulta');
            $mail->Body = "
                <h2>Nuevo mensaje</h2>
                <p><strong>Nombre:</strong> {$data['nombre']}</p>
                <p><strong>Email:</strong> {$data['email']}</p>
                <p><strong>Mensaje:</strong></p>
                <p>" . nl2br(htmlspecialchars($data['mensaje'])) . "</p>
            ";
            
            return $mail->send();
            
        } catch (Exception $e) {
            error_log("Error al enviar: " . $e->getMessage());
            return false;
        }
    }
}