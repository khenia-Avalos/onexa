<?php
// Configuración de SMTP (NUNCA expongas esto en público)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'kheniavalos@gmail.com');
define('SMTP_PASS', 'ldpn jmvl jcko pmuw'); // REVOCA esta contraseña inmediatamente
define('SMTP_SECURE', 'tls');
define('SMTP_PORT', 587);

// Configuración de correo
define('MAIL_FROM_NAME', 'Formulario de Contacto');
define('MAIL_TO_ADDRESS', 'kheniavalos@gmail.com');
define('MAIL_CHARSET', 'UTF-8');

// Configuración de seguridad
define('MAX_MESSAGE_LENGTH', 2000);
define('MAX_NAME_LENGTH', 100);
define('MAX_SUBJECT_LENGTH', 100);

// Configuración CSRF (MEJORADA)
define('CSRF_TOKEN_SECRET', 'c7813c269f640da63e5b0ad1c80ddedf5ece5fbba1eb588861fc2e0fa0832059');
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hora de validez