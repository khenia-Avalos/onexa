<?php
session_start();
require_once 'includes/conexion.php';
require_once 'carrito_functions.php';//ESTE NO 

// Verificar si hay que mostrar mensaje de bienvenida
//$mostrarBienvenida = isset($_GET['login']) && $_GET['login'] == 'success';
//$nombreUsuario = $_SESSION['usuario_nombre'] ?? '';

require_once './partesup.php';
?>

<!-- Contenido de la página principal /funcionamiento de usuario ,muestra mensja
<div class="main-content">
    <?php if ($mostrarBienvenida): ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            title: '¡Bienvenido!',
            text: '<?php echo $nombreUsuario; ?>',
            icon: 'success',
            confirmButtonText: 'Continuar'
        });
    });
    </script>
    <?php endif; ?>
    
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>-->

<?php require_once './parteinferior.php'; ?>





<script src="/project/script/botonesmenu.js"></script>
<script src="/project/script/main.js"></script>
</body>
</html>