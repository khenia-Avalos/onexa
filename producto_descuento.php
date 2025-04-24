<?php
session_start();


$conexion = new mysqli("localhost", "kheniali", "123", "tienda");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$conexion->set_charset("utf8mb4");

// 2. Obtener el producto con ID 1 
$producto_id = 1; //  cambiar a $_GET['id']  para dinamido 

// 3. Consulta para obtener el producto principal
$stmt = $conexion->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Producto no encontrado");
}

$producto = $result->fetch_assoc();
$stmt->close();


$stmt = $conexion->prepare("SELECT imagen FROM producto_imagenes WHERE producto_id = ? ORDER BY orden");
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$imagenes_result = $stmt->get_result();
$imagenes = $imagenes_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();


$precio_limpio = str_replace(',', '', $producto['precio']);
$precio_float = (float)$precio_limpio;

$producto_final = [
    'id' => $producto['id'],
    'nombre' => $producto['nombre'], 
    'descripcion' => htmlspecialchars($producto['descripcion'], ENT_QUOTES, 'UTF-8'),
    'precio' => $precio_float, 
    'precio_formateado' => number_format($precio_float, 2), 
    'imagen_principal' => htmlspecialchars($producto['imagen']),
    'imagenes' => array_map(function($img) {
        return ['imagen' => htmlspecialchars($img['imagen'])];
    }, $imagenes)
];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_carrito'])) {
   
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = [];
    }
    
    // Buscar si el producto ya está en el carrito
    $encontrado = false;
    foreach ($_SESSION['carrito'] as &$item) {
        if ($item['id'] == $producto_final['id']) {
            $item['cantidad']++;
            $encontrado = true;
            break;
        }
    }
    
    // Si no está, agregarlo
    if (!$encontrado) {
        $_SESSION['carrito'][] = [
            'id' => $producto_final['id'],
            'nombre' => $producto_final['nombre'],
            'precio' => $producto_final['precio'], 
            'imagen' => $producto_final['imagen_principal'],
            'cantidad' => 1
        ];
    }
    
  
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
    <title><?= htmlspecialchars($producto_final['nombre'], ENT_QUOTES, 'UTF-8') ?> - Tienda Online</title>
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="stylePDescuento.css" />
</head>
<body>
    <header>
        <?php include 'partesup.php'; ?>
    </header>

    <div class="product-detail-container">
        <div class="product-gallery">
            <div class="main-image-container" id="zoomContainer">
                <img src="<?= $producto_final['imagen_principal'] ?>" alt="<?= htmlspecialchars($producto_final['nombre'], ENT_QUOTES, 'UTF-8') ?>" class="main-image" id="mainProductImage">
            </div>
            
            <div class="thumbnail-container">
                <img src="<?= $producto_final['imagen_principal'] ?>" alt="Miniatura 1" class="thumbnail active" onclick="changeImage(this, '<?= $producto_final['imagen_principal'] ?>')">
                <?php foreach ($producto_final['imagenes'] as $imagen): ?>
                    <img src="<?= $imagen['imagen'] ?>" alt="Miniatura" class="thumbnail" onclick="changeImage(this, '<?= $imagen['imagen'] ?>')">
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="product-info">
            <h1 class="product-title"><?= $producto_final['nombre'] ?></h1>
            <p class="product-price">$<?= $producto_final['precio_formateado'] ?></p>
            <div class="product-description">
                <?= nl2br($producto_final['descripcion']) ?>
            </div>
            
            <form method="POST" class="add-to-cart-form">
                <input type="hidden" name="agregar_carrito" value="1">
                <input type="hidden" name="id" value="<?= $producto_final['id'] ?>">
                <input type="hidden" name="nombre" value="<?= htmlspecialchars($producto_final['nombre'], ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="precio" value="<?= $producto_final['precio'] ?>">
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
        function changeImage(thumbnail, newImage) {
        
            const mainImage = document.getElementById('mainProductImage');
            mainImage.src = newImage;
            
            // Actualizar la miniatura activa
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            thumbnail.classList.add('active');
            
          
            document.querySelector('input[name="imagen"]').value = newImage;
        }
        
        // Configuración del zoom que sigue el cursor
        document.addEventListener('DOMContentLoaded', function() {
            const mainImage = document.getElementById('mainProductImage');
            const zoomContainer = document.getElementById('zoomContainer');
            
            if (!mainImage || !zoomContainer) return;
            
            // Configurar el zoom al mover el mouse
            zoomContainer.addEventListener('mousemove', function(e) {
                const rect = zoomContainer.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                // Calcular posición relativa (0 a 1)
                const relX = x / rect.width;
                const relY = y / rect.height;
                
          
                mainImage.style.transformOrigin = `${relX * 100}% ${relY * 100}%`;
                
             
                mainImage.style.transform = 'scale(2)';
            });
            
            // Quitar el zoom al salir del contenedor
            zoomContainer.addEventListener('mouseleave', function() {
                mainImage.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>