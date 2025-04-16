<?php
session_start();

// Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$carrito = $_SESSION['carrito'];

// Generar token CSRF (SOLUCIÓN AL ERROR)
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Función para calcular el total
function calcularTotal($carrito) {
    $total = 0;
    foreach ($carrito as $producto) {
        $precio = isset($producto['precio']) ? $producto['precio'] : 0;
        $cantidad = isset($producto['cantidad']) ? $producto['cantidad'] : 1;
        $total += $precio * $cantidad;
    }
    return number_format($total, 2);
}

// Conexión a la base de datos para obtener productos recomendados
$conexion = new mysqli("localhost", "kheniali", "123", "tienda");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$conexion->set_charset("utf8mb4");

// Consulta para obtener 5 productos recomendados
$sql_recomendados = "SELECT * FROM recomendados ORDER BY RAND() LIMIT 5";
$result_recomendados = $conexion->query($sql_recomendados);

$recomendados = [];
if ($result_recomendados) {
    while ($row = $result_recomendados->fetch_assoc()) {
        $recomendados[] = [
            'id' => $row['id'],
            'nombre' => htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8'),
            'descripcion' => htmlspecialchars($row['descripcion'], ENT_QUOTES, 'UTF-8'),
            'precio' => (float)$row['precio'],
            'imagen' => htmlspecialchars($row['imagen']),
            'imagenes' => [
                ['imagen' => htmlspecialchars($row['imagen'])]
            ]
        ];
    }
}
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soporte para Productos - ANOEXA</title>
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
            <h1>Soporte Técnico para Productos</h1>
            <p>Nuestro equipo de soporte especializado está listo para ayudarte con cualquier consulta sobre nuestros productos.</p>
        </section>
        
        <!-- Contact Options -->
        <section class="contact-section">
            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-phone-alt"></i>
                </div>
                <h3>Soporte Telefónico</h3>
                <p><strong>Asistencia técnica:</strong> 1-800-123-4567</p>
                <p><strong>Horario:</strong> Lunes a Viernes, 9:00 AM - 6:00 PM</p>
                <p>Especializado en soporte para productos</p>
            </div>
            
            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3>Soporte por Email</h3>
                <p><strong>Soporte técnico:</strong> soporte.productos@anoexa.com</p>
                <p><strong>Garantías:</strong> garantias@anoexa.com</p>
                <p>Respuesta en 24 horas hábiles</p>
            </div>
            
            <div class="contact-card">
                <div class="contact-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h3>Chat de Soporte</h3>
                <p><strong>Disponible:</strong> Lunes a Viernes, 8:00 AM - 8:00 PM</p>
                <p>Asistencia en tiempo real para problemas con productos</p>
                <button class="btnc">Iniciar Chat</button>
            </div>
        </section>
        
        <!-- Contact Form -->
        <section class="contact-form">
            <h2>Formulario de Soporte para Productos</h2>
            <p>Por favor completa este formulario para recibir asistencia sobre nuestros productos. Incluye el número de serie o modelo si es aplicable.</p>
            <form id="contactForm">
                <!-- CAMPO AÑADIDO PARA SOLUCIONAR EL ERROR CSRF -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES); ?>">
                
                <div class="form-group">
                    <label for="nombre">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="asunto">Tipo de Consulta</label>
                    <select id="asunto" name="asunto" class="form-control" required>
                        <option value="">Seleccione el tipo de soporte</option>
                        <option value="soporte">Soporte Técnico</option>
                        <option value="garantia">Consulta sobre Garantía</option>
                        <option value="funcionamiento">Problemas de Funcionamiento</option>
                        <option value="otro">Otra consulta sobre productos</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="mensaje">Detalles del Problema</label>
                    <textarea id="mensaje" name="mensaje" class="form-control" placeholder="Por favor describe el problema con tu producto, incluyendo modelo y número de serie si es posible" required></textarea>
                </div>
                
                <button type="submit" class="btnc">Enviar Solicitud de Soporte</button>
            </form>
        </section>
    </main>

    <?php include 'footer.php'; ?>

    <!-- Script para mostrar mensaje emergente -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validación básica
            const nombre = document.getElementById('nombre').value;
            const email = document.getElementById('email').value;
            const mensaje = document.getElementById('mensaje').value;
            
            if (!nombre || !email || !mensaje) {
                Swal.fire('Error', 'Por favor complete todos los campos requeridos', 'error');
                return;
            }
            
            // Envío del formulario
            const formData = new FormData(this);
            
            fetch('procesar_contacto.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: '¡Solicitud enviada!',
                        text: 'Gracias por contactar al soporte técnico de ANOEXA. Hemos recibido tu consulta sobre el producto y uno de nuestros especialistas se pondrá en contacto contigo dentro de 24 a 48 horas.',
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    });
                    document.getElementById('contactForm').reset();
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'Hubo un error al enviar tu solicitud de soporte. Por favor inténtalo nuevamente.',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error',
                    text: 'Hubo un problema al enviar tu solicitud. Por favor inténtalo nuevamente.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            });
        });
    </script>
</body>
</html>