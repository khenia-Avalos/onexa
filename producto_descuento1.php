<?php
session_start();

// 1. Conexión a la base de datos
$conexion = new mysqli("localhost", "kheniali", "123", "tienda");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$conexion->set_charset("utf8mb4");

// 2. Obtener el producto con ID 2 (ESPECIFICO QUE LLAME AL PRODUCTO CON ID 2)
$producto_id = 2;

// 3. Consulta para obtener el producto principal
$stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->bind_param("i", $producto_id); // i indica que es un valor entero (id)
$stmt->execute(); // manda la consulta
$result = $stmt->get_result(); // obtener resultados

if ($result->num_rows === 0) {
    die("Producto no encontrado");
}

$producto = $result->fetch_assoc();
$stmt->close();

// 4. Obtener imágenes adicionales
$stmt = $conexion->prepare("SELECT imagen FROM producto_imagenes WHERE producto_id = ? ORDER BY orden");
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$imagenes_result = $stmt->get_result();
$imagenes = $imagenes_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 5. ORGANIZA LA INFORMACION PARA MOSTRARLA EN LA PAGINA
$producto_final = [
    'id' => $producto['id'],
    'nombre' => $producto['nombre'], // Cambiado: sin htmlspecialchars para mostrar comillas directamente
    'descripcion' => htmlspecialchars($producto['descripcion'], ENT_QUOTES, 'UTF-8'), // htmlspecialchars() protege contra código malicioso
    'precio' => (float)$producto['precio'], // Aseguramos que sea float para cálculos
    'imagen_principal' => htmlspecialchars($producto['imagen']),
    'imagenes' => array_map(function($img) { // array_map(): procesa cada imagen en la lista $imagenes
        return ['imagen' => htmlspecialchars($img['imagen'])];
    }, $imagenes)
];

// 6. Manejar agregar al carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_carrito'])) { // formulario se envio al hacer clic en boton añadir al carrito
    // Inicializar carrito si no existe
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }
    
    // Buscar si el producto ya está en el carrito, recorre cada producto ($item) en el carrito si lo encuentra aumenta 1
    $encontrado = false;
    foreach ($_SESSION['carrito'] as &$item) {
        if ($item['id'] == $producto_final['id']) {
            $item['cantidad']++;
            $encontrado = true;
            break;
        }
    }
    
    // Si no está, agregarlo e inicia con cantidad 1
    if (!$encontrado) {
        $_SESSION['carrito'][] = [
            'id' => $producto_final['id'],
            'nombre' => $producto_final['nombre'],
            'precio' => $producto_final['precio'], // Usamos el valor numérico para cálculos
            'imagen' => $producto_final['imagen_principal'],
            'cantidad' => 1
        ];
    }
    
    // Redirigir a la misma página para evitar reenvío del formulario
    header("Location: ".$_SERVER['PHP_SELF']."?id=".$producto_id);
    exit;
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($producto_final['nombre'], ENT_QUOTES, 'UTF-8') ?> - onexa</title>
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="stylePDescuento.css" />
</head>
<body>
    <header>
        <?php include 'partesup.php'; ?>
    </header>

    <div class="product-detail-container">
        <div class="product-gallery">
            <div class="main-image-container" id="zoomContainer"> <!-- muestra la imagen principal grande -->
                <img src="<?= $producto_final['imagen_principal'] ?>" alt="<?= htmlspecialchars($producto_final['nombre'], ENT_QUOTES, 'UTF-8') ?>" class="main-image" id="mainProductImage">
            </div>
            
            <div class="thumbnail-container"> <!-- muestra las miniaturas -->
                <img src="<?= $producto_final['imagen_principal'] ?>" alt="Miniatura 1" class="thumbnail active" onclick="changeImage(this, '<?= $producto_final['imagen_principal'] ?>')">
                <?php foreach ($producto_final['imagenes'] as $imagen): ?> <!-- foreach recorre y muestra las imagenes del producto -->
                    <img src="<?= $imagen['imagen'] ?>" alt="Miniatura" class="thumbnail" onclick="changeImage(this, '<?= $imagen['imagen'] ?>')">
                <?php endforeach; ?> <!-- onclick llama a JS PARA PODER CAMBIAR LA IMAGEN -->
            </div>
        </div>
        
        <div class="product-info">
            <h1 class="product-title"><?= $producto_final['nombre'] ?></h1> <!-- Sin htmlspecialchars para mostrar comillas directamente -->
            <p class="product-price">$<?= number_format($producto_final['precio'], 2) ?></p> <!-- Formateamos solo al mostrar -->
            <div class="product-description">
                <?= nl2br($producto_final['descripcion']) ?> <!-- MANTIENE EL FORMATO DE SALTO DE LINEA -->
            </div>
            <!-- Formulario con campos ocultos que envían toda la información del producto -->
            <form method="POST" class="add-to-cart-form">
                <input type="hidden" name="agregar_carrito" value="1">
                <input type="hidden" name="id" value="<?= $producto_final['id'] ?>">
                <input type="hidden" name="nombre" value="<?= htmlspecialchars($producto_final['nombre'], ENT_QUOTES, 'UTF-8') ?>"> <!-- Escapado para el formulario -->
                <input type="hidden" name="precio" value="<?= $producto_final['precio'] ?>"> <!-- Valor numérico -->
                <input type="hidden" name="imagen" value="<?= $producto_final['imagen_principal'] ?>">
                <button type="submit" class="add-to-cart-btn">Añadir al carrito</button>
            </form>
        </div>
        
        <!-- Sección de especificaciones técnicas -->
        <div class="product-specs">
            <h3 class="specs-title">Especificaciones Técnicas</h3>
            <div class="specs-grid">
                <div class="spec-item">
                    <span class="spec-name">Color:</span>
                    <span class="spec-value">Negro</span>
                </div>
                <div class="spec-item">
                    <span class="spec-name">Material:</span>
                    <span class="spec-value">Goma</span>
                </div>
                <div class="spec-item">
                    <span class="spec-name">Medida:</span>
                    <span class="spec-value">Mouse Pad: 250x210x2 mm</span>
                </div>
                <div class="spec-item">
                    <span class="spec-name">Garantía:</span>
                    <span class="spec-value">3 meses</span>
                </div>
                <div class="spec-item">
                    <span class="spec-name">Número de piezas:</span>
                    <span class="spec-value">4</span>
                </div>
                <div class="spec-item">
                    <span class="spec-name">Conectividad:</span>
                    <span class="spec-value">Teclado: USB 2.0. Mouse: USB 2.0. Audífonos: Conector USB + 2x3.5 mm</span>
                </div>
                <div class="spec-item">
                    <span class="spec-name">Modelo:</span>
                    <span class="spec-value">4 en 1 Gallaxy</span>
                </div>
                <div class="spec-item">
                    <span class="spec-name">MPN:</span>
                    <span class="spec-value">2604774</span>
                </div>
                <div class="spec-item">
                    <span class="spec-name">Código:</span>
                    <span class="spec-value">3707040008</span>
                </div>
                <div class="spec-item">
                    <span class="spec-name">Tipo:</span>
                    <span class="spec-value">Combo Gaming</span>
                </div>
                <div class="spec-item">
                    <span class="spec-name">Marca:</span>
                    <span class="spec-value">RadioShack</span>
                </div>
                <div class="spec-item">
                    <span class="spec-name">Incluye:</span>
                    <span class="spec-value">Mouse, Mouse Pad, Teclado, Audífonos</span>
                </div>
            </div>
            
            <div class="tech-features">
                <h4 class="tech-title">Tecnologías Destacadas</h4>
                <ul>
                    <li><strong>Teclado:</strong> Interruptor de membrana, colores de luz de fondo mezclados, 140 teclas, vida útil hasta 10 millones de pulsaciones, cable de 1.5 m, corriente nominal máx. 200 mA, voltaje nominal 5V +/-0.5V</li>
                    <li><strong>Mouse gaming 6D:</strong> Sensor óptico DPI 800/1200/1600/2400, 6 botones hasta 3 millones de clics, tasa de sondeo 125Hz, iluminación de fondo de 6 colores, potencia nominal 5V, corriente nominal 100mA +/-2.0mA, cable de 1.4 m</li>
                    <li><strong>Audífonos:</strong> Respuesta de frecuencia 20Hz-20kHz, parlante de 50 mm, sensibilidad 97 dB +/-3 dB, 7 colores de fondo, micrófono omnidireccional, sensibilidad del micrófono 37dB +/- 2dB, cable de 2 m</li>
                </ul>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
   
    <script>
        // Función para cambiar la imagen principal
        function changeImage(thumbnail, newImage) { // MANEJA EL CAMBIO DE IMAGEN AL HACER CLIC EN MINIATURA
            // Cambiar la imagen principal
            const mainImage = document.getElementById('mainProductImage'); // OBTIENE LA REFERENCIA DE LA IMG PRINCIPAL POR SU ID
            mainImage.src = newImage; // CAMBIA SU ATRIBUTO SRC POR LA NUEVA RUTA
            
            // Actualizar la miniatura activa
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            thumbnail.classList.add('active'); // SOLO LA MINIATURA CLICKEADA
            
            // Actualizar el campo oculto del formulario POR LA NUEVA IMAGEN
            document.querySelector('input[name="imagen"]').value = newImage;
        }
        
        // Configuración del zoom que sigue el cursor
        document.addEventListener('DOMContentLoaded', function() {
            const mainImage = document.getElementById('mainProductImage');
            const zoomContainer = document.getElementById('zoomContainer');
            
            if (!mainImage || !zoomContainer) return;
            
            // Configurar el zoom al mover el mouse 
            zoomContainer.addEventListener('mousemove', function(e) {
                const rect = zoomContainer.getBoundingClientRect(); // getBoundingClientRect() obtiene las dimensiones y posición del contenedor
                const x = e.clientX - rect.left; // Calcula la posición exacta del cursor dentro del contenedor
                const y = e.clientY - rect.top;
                
                // Calcular posición relativa (0 a 1) Convierte las coordenadas a valores entre 0 y 1
                const relX = x / rect.width;
                const relY = y / rect.height;
                
                // Ajustar el origen de la transformación
                mainImage.style.transformOrigin = `${relX * 100}% ${relY * 100}%`; // Establece el punto de origen del zoom (donde está el cursor)
                
                // Aplicar el zoom
                mainImage.style.transform = 'scale(2)'; // Aplica un zoom de 2x a la imagen
            });
            
            // Quitar el zoom al salir del contenedor
            zoomContainer.addEventListener('mouseleave', function() {
                mainImage.style.transform = 'scale(1)'; // Cuando el cursor sale del contenedor, regresa la imagen a su tamaño normal (1:1)
            });
        });
    </script>
</body>
</html>