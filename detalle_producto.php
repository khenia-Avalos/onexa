<?php
session_start();

// 1. Conexión a la base de datos
$conexion = new mysqli("localhost", "kheniali", "123", "tienda");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$conexion->set_charset("utf8mb4");

// 2. Obtener el ID del producto desde la URL
$producto_id = isset($_GET['id']) ? intval($_GET['id']) : 0; 

if ($producto_id <= 0) {
    die("ID de producto inválido");
}

// 3. Consulta para obtener el producto principal (incluyendo descripcion y descripcion2)
$stmt = $conexion->prepare("SELECT id, nombre, descripcion, descripcion2, precio, imagen FROM productos WHERE id = ?");
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Producto no encontrado");
}

$producto = $result->fetch_assoc();//Guarda los datos en un array
$stmt->close();

// 4. Obtener imágenes adicionales
$stmt = $conexion->prepare("SELECT imagen FROM producto_imagenes WHERE producto_id = ? ORDER BY orden");
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$imagenes_result = $stmt->get_result();
$imagenes = $imagenes_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 5. Obtener especificaciones técnicas
$stmt = $conexion->prepare("SELECT categoria, nombre, valor FROM especificaciones WHERE producto_id = ? ORDER BY categoria, orden");//Trae las especificaciones agrupadas por categoría Filtra solo las especificaciones del producto con ID que le vamos a pasar.
//Ordena los resultados primero por categoría y luego por su orden definido.
$stmt->bind_param("i", $producto_id);//Asocia el valor de $producto_id al ? en la consulta. La "i" indica que es un número entero.
$stmt->execute();
$espec_result = $stmt->get_result();//Obtiene los resultados de la consulta ejecutada.

$especificaciones = [];//Crea un array vacío para almacenar las especificaciones organizadas.
while ($row = $espec_result->fetch_assoc()) {//Recorre cada fila de resultados uno por uno.
    if (!isset($especificaciones[$row['categoria']])) {// // Si la categoría de esta fila NO existe en el array $especificaciones..
        $especificaciones[$row['categoria']] = [];//     // Entonces crea un array vacío para esa categoría
    }
    $especificaciones[$row['categoria']][] = [
        // Añade a esa categoría un nuevo array con:
        'nombre' => htmlspecialchars($row['nombre']),//   // El nombre escapado para seguridad HTML
        'valor' => htmlspecialchars($row['valor'])//
        // El valor escapado para seguridad HTML
    ];
}
$stmt->close();

// 6. Construir array final - CON DESCRIPCIÓN PARA EL CARRITO
$producto_final = array(
    'id' => $producto['id'],
    'nombre' => htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8'),
    'descripcion' => htmlspecialchars($producto['descripcion'], ENT_QUOTES, 'UTF-8'), // Asegurar que tenemos la descripción
    'descripcion2' => isset($producto['descripcion2']) ? htmlspecialchars($producto['descripcion2'], ENT_QUOTES, 'UTF-8') : '',
    'precio' => (float)$producto['precio'], // Convertir a float
    'precio_formateado' => number_format($producto['precio'], 2), // Versión formateada para mostrar
    'imagen_principal' => htmlspecialchars($producto['imagen']),
    'especificaciones' => $especificaciones,
    'imagenes' => array_map(function($img) {
        return array('imagen' => htmlspecialchars($img['imagen']));
    }, $imagenes)
);

// 7. Manejar carrito - AHORA CON DESCRIPCIÓN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_carrito'])) {
    if (!isset($_SESSION['carrito'])) {//Verificar si existe el carrito:
        $_SESSION['carrito'] = array();
    }
    
    $encontrado = false;
    foreach ($_SESSION['carrito'] as &$item) {
        if ($item['id'] == $producto_final['id']) {
            $item['cantidad']++;// Si ya está, suma 1 a la cantidad

            $encontrado = true;
            break;
        }
    }
    //añade nuevo producto
    if (!$encontrado) {
        $_SESSION['carrito'][] = array(
            'id' => $producto_final['id'],
            'nombre' => $producto_final['nombre'],
            'descripcion' => $producto_final['descripcion'], // Añadimos la descripción aquí
            'precio' => $producto_final['precio'], // Usamos el float
            'imagen' => $producto_final['imagen_principal'],
            'cantidad' => 1
        );
    }
    
    header("Location: ".$_SERVER['PHP_SELF']."?id=".$producto_id);//Evita que el usuario reenvíe el formulario al recargar
    exit;
}

$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $producto_final['nombre'] ?> - ONEXa</title>
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="stylePDescuento.css" />
    
</head>
<body>
    <header>
        <?php include 'partesup.php'; ?>
    </header>

    <div class="product-detail-container">
        <!-- Galería de imágenes -->
       <!-- changeImage(): Al hacer clic en una miniatura, cambia la imagen principal.

active: Clase CSS que resalta la miniatura seleccionada.-->
        <div class="product-gallery">
            <div class="main-image-container" id="zoomContainer">
                <img src="<?= $producto_final['imagen_principal'] ?>" alt="<?= $producto_final['nombre'] ?>" class="main-image" id="mainProductImage">
            </div>
            
            <div class="thumbnail-container">
                <img src="<?= $producto_final['imagen_principal'] ?>" alt="Miniatura 1" class="thumbnail active" onclick="changeImage(this, '<?= $producto_final['imagen_principal'] ?>')">
                <?php foreach ($producto_final['imagenes'] as $imagen): ?>
                    <img src="<?= $imagen['imagen'] ?>" alt="Miniatura" class="thumbnail" onclick="changeImage(this, '<?= $imagen['imagen'] ?>')">
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Información del producto -->
        <div class="product-info">
            <h1 class="product-title"><?= $producto_final['nombre'] ?></h1>
            <p class="product-price">$<?= $producto_final['precio_formateado'] ?></p>
            <div class="product-description">
                <?= nl2br($producto_final['descripcion']) ?>
            </div>
            
            <!-- Detalles adicionales aquí -->
            <?php if (!empty($producto_final['descripcion2'])): ?>
                <div class="additional-details">
                    <h3 style="font-size: 1.2em; margin-bottom: 10px;">Detalles Adicionales</h3>
                    <div class="tech-features">
                        <?= nl2br($producto_final['descripcion2']) ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="add-to-cart-form">
                <input type="hidden" name="agregar_carrito" value="1">
                <input type="hidden" name="id" value="<?= $producto_final['id'] ?>">
                <input type="hidden" name="nombre" value="<?= $producto_final['nombre'] ?>">
                <input type="hidden" name="descripcion" value="<?= htmlspecialchars($producto_final['descripcion']) ?>"> <!-- Añadido descripción -->
                <input type="hidden" name="precio" value="<?= $producto_final['precio'] ?>">
                <input type="hidden" name="imagen" value="<?= $producto_final['imagen_principal'] ?>">
                <button type="submit" class="add-to-cart-btn">Añadir al carrito</button>
            </form>
        </div><!--Al enviar el formulario, PHP añade el producto a $_SESSION['carrito'], Envía el ID, nombre, precio, etc. sin mostrarlos al usuario-->
        
        <!-- Especificaciones en acordeón -->
        <div class="accordion" id="specsAccordion">
            <h3 class="specs-title">Especificaciones Técnicas</h3>
            
            <?php if (!empty($producto_final['especificaciones'])): ?><!--Verifica si hay especificaciones técnicas para mostrar.-->
                <?php foreach ($producto_final['especificaciones'] as $categoria => $especs): ?><!--repite sobre cada categoría de especificaciones (ej: "Pantalla", "Sonido").-->
                    <div class="accordion-item">
                        <div class="accordion-header" onclick="toggleAccordion(this)">
                            <span><?= htmlspecialchars($categoria) ?></span><!--Muestra el nombre de la categoría (ej: "Pantalla").-->
                            <span class="accordion-icon">▼</span>
                        </div>
                        <div class="accordion-content">
                            <div class="specs-grid"><!--Diseño en cuadrícula para las especificaciones.-->
                                <?php foreach ($especs as $spec): ?><!--repire sobre cada especificación dentro de la categoría.-->
                                    <div class="spec-item">
                                        <span class="spec-name"><?= $spec['nombre'] ?>:</span>
                                        <span class="spec-value"><?= $spec['valor'] ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-specs">No hay especificaciones disponibles para este producto.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // Función para el acordeón
        function toggleAccordion(header) {//Define la función que maneja la expansión/contracción.
            const item = header.parentElement;
            const content = header.nextElementSibling;
            const icon = header.querySelector('.accordion-icon');
            
            // Cerrar todos los demás acordeones
            document.querySelectorAll('.accordion-item').forEach(accordion => {//Selecciona todos los ítems del acordeón.
                if (accordion !== item) {//Cierra otros acordeones abiertos (excepto el actual).
                    accordion.querySelector('.accordion-content').classList.remove('active');
                    accordion.querySelector('.accordion-icon').classList.remove('rotated');
                }
            });
            
            // Alternar el actual
            content.classList.toggle('active');
            icon.classList.toggle('rotated');
        }
        
        // Función para cambiar la imagen principal
        function changeImage(thumbnail, newImage) {//Función para cambiar la imagen principal al hacer clic en una miniatura.
            const mainImage = document.getElementById('mainProductImage');
            mainImage.src = newImage;//Actualiza la fuente de la imagen principal.
            
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            thumbnail.classList.add('active');
        }
        
        // Configuración del zoom
        document.addEventListener('DOMContentLoaded', function() {
            const mainImage = document.getElementById('mainProductImage');
            const zoomContainer = document.getElementById('zoomContainer');
            
            if (mainImage && zoomContainer) {
                zoomContainer.addEventListener('mousemove', function(e) {
                    const rect = zoomContainer.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    const relX = x / rect.width;
                    const relY = y / rect.height;
                    
                    mainImage.style.transformOrigin = `${relX * 100}% ${relY * 100}%`;
                    mainImage.style.transform = 'scale(2)';
                });
                
                zoomContainer.addEventListener('mouseleave', function() {
                    mainImage.style.transform = 'scale(1)';
                });
            }
        });
    </script>
</body>
</html>