<?php

session_start();

// Generar token CSRF solo si no existe
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contáctenos - ONEXA</title>
    <link rel="stylesheet" href="stylesoport.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <header>
        <?php include 'partesup.php'; ?>
    </header>

    <main class="container">
        <!-- Hero Section -->
        <section class="contact-hero">
            <h1>Contáctenos</h1>
            <p>Estamos aquí para ayudarte. Elige la opción que mejor se adapte a tus necesidades.</p>
        </section>
        
        <!-- Contact Options -->
        <section class="contact-section">
            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-phone-alt"></i>
                </div>
                <h3>Soporte Telefónico</h3>
                <p><strong>Línea directa:</strong> 1-800-123-4567</p>
                <p><strong>Horario:</strong> Lunes a Viernes, 9:00 AM - 6:00 PM</p>
            </div>
            
            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3>Correo Electrónico</h3>
                <p><strong>Soporte:</strong> soporte@tienda.com</p>
                <p><strong>Ventas:</strong> ventas@tienda.com</p>
                <p>Respuesta en 24 horas hábiles</p>
            </div>
            
            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h3>Chat en Vivo</h3>
                <p><strong>Disponible:</strong> Lunes a Viernes, 8:00 AM - 8:00 PM</p>
                <button class="btnc">Iniciar Chat</button>
            </div>
        </section>
        
        <!-- Contact Form -->
        <section class="contact-form">
            <h2>Envíanos un Mensaje</h2>
            <form id="contactForm">
                <!-- Campo oculto con el token CSRF -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES) ?>">
                
                <div class="form-group">
                    <label for="nombre">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="asunto">Asunto</label>
                    <select id="asunto" name="asunto" class="form-control" required>
                        <option value="">Seleccione un asunto</option>
                        <option value="Soporte Técnico">Soporte Técnico</option>
                        <option value="Consulta de Ventas">Consulta de Ventas</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="mensaje">Mensaje</label>
                    <textarea id="mensaje" name="mensaje" class="form-control" rows="5" required></textarea>
                </div>
                
                <button type="submit" class="btnc">Enviar Mensaje</button>
            </form>
        </section>
    </main>

    <?php include 'footer.php'; ?>

    <!-- Script para mostrar mensaje emergente -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('contactForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Mostrar loader
            Swal.fire({
                title: 'Enviando mensaje',
                html: 'Por favor espera...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            try {
                const formData = new FormData(this);
                const response = await fetch('procesar_contacto.php', {
                    method: 'POST',
                    body: formData
                });
                
                // Verificar si la respuesta es JSON
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await response.text();
                    throw new Error('Respuesta no válida del servidor');
                }
                
                const data = await response.json();
                
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Error al enviar el mensaje');
                }
                
                Swal.fire({
                    title: '¡Éxito!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                });
                this.reset();
            } catch (error) {
                Swal.fire({
                    title: 'Error',
                    text: error.message,
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
                console.error('Error:', error);
            }
        });
    </script>
</body>
</html>