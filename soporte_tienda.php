<?php
session_start();

// Inicializar carrito si no existe
//if (!isset($_SESSION['carrito'])) {
 //   $_SESSION['carrito'] = [];
//}

//$carrito = $_SESSION['carrito'];

// Función para calcular el total
//function calcularTotal($carrito) {
  //  $total = 0;
   // foreach ($carrito as $producto) {
   //     $precio = isset($producto['precio']) ? $producto['precio'] : 0;
    //    $cantidad = isset($producto['cantidad']) ? $producto['cantidad'] : 1;
    //    $total += $precio * $cantidad;
 //   }
 //   return number_format($total, 2);
//}



?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contáctenos -ONEXA</title>
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
                    <i class="fas fa-phone-alt"></i> <!-- Icono de teléfono de Font Awesome -->
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
            <form action="procesar_contacto.php" method="POST" id="contactForm">   <!-- envia los datos a este script, lo procesa con id unico -->
                <div class="form-group">
                    <label for="nombre">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" class="form-control" required><!-- el required es para campos obligatorios -->
                </div>
                
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="asunto">Asunto</label>
                    <select id="asunto" name="asunto" class="form-control" required><!-- crea el menu desplegable  -->
                        <option value="">Seleccione un asunto</option><!-- opciones disponivbles  -->
                        <option value="soporte Tecnico">Soporte Técnico</option>
                        <option value="Consukta de ventas">Consulta de Ventas</option>
                     
                        <option value="otro">Otro</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="mensaje">Mensaje</label>
                    <textarea id="mensaje" name="mensaje" class="form-control" required></textarea>
                </div>
                
                <button type="submit" class="btnc">Enviar Mensaje</button>
            </form>
        </section>
    </main>

    <?php include 'footer.php'; ?>

    <!-- Script para mostrar mensaje emergente -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Validación del formulario
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validación básica
            const nombre = document.getElementById('nombre').value;//.value: Obtiene el valor actual que el usuario ha ingresado en ese campo
            const email = document.getElementById('email').value;
            const mensaje = document.getElementById('mensaje').value;
            
            if (!nombre || !email || !mensaje) {
                Swal.fire('Error', 'Por favor complete todos los campos requeridos', 'error');
                return;//i el nombre está vacío o el email está vacío o el mensaje está vacío, entonces detiene el proceso
            }
            
            // Envío del formulario
            const formData = new FormData(this);//FormData: Objeto que recoge todos los datos del formulario
            
            fetch('procesar_contacto.php', {//fetch: API moderna para hacer peticiones HTTP
                method: 'POST',
                body: formData//onfigura el método POST y envía formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({//Muestra mensaje de éxito con SweetAlert si data.success es true para mejor estilo
                        title: '¡Mensaje enviado!',
                        text: 'Gracias por contactar a ONEXA. Uno de nuestros representantes se pondrá en contacto contigo dentro de 24 a 48 horas.',
                        icon: 'success',
                        confirmButtonText: 'Aceptar'
                    });
                    document.getElementById('contactForm').reset();//borra los datos ingresados en el form 
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message || 'Hubo un error al enviar el mensaje. Por favor inténtalo nuevamente.',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            })
            .catch(error => {//catch: Captura cualquier error en la petición
                Swal.fire({
                    title: 'Error',
                    text: 'Hubo un problema al enviar el mensaje. Por favor inténtalo nuevamente.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar'
                });
            });
        });
    </script>
</body>
</html>