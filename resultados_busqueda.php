<?php
session_start();

// 1. Conexión a la base de datos con manejo de errores
$conexion = new mysqli("localhost", "kheniali", "123", "tienda");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$conexion->set_charset("utf8mb4");

// 2. Obtener término de búsqueda
$termino = isset($_GET['q']) ? trim($_GET['q']) : '';
//Obtiene el término de búsqueda de la URL (parámetro 'q')
//Elimina espacios en blanco con trim()
//Si no hay término, asigna cadena vacía

// 3. Consulta SQL para buscar productos
//--evitar mostrar productos duplicados
//--busca coincidencias parciales
//--Busca productos que coincidan con el término en nombre o descripción--
if (!empty($termino)) {
    $sql = "SELECT p.* 
            FROM productos p
            INNER JOIN (
                SELECT MIN(id) as min_id
                FROM productos
                WHERE nombre LIKE CONCAT('%', ?, '%') 
                OR descripcion LIKE CONCAT('%', ?, '%')
                GROUP BY 
                    LOWER(REPLACE(REPLACE(REPLACE(TRIM(nombre), ' ', ''), '.', ''), '-', '')),
                    LOWER(REPLACE(REPLACE(REPLACE(TRIM(descripcion), ' ', ''), '.', ''), '-', '')),
                    FORMAT(precio, 2),
                    SUBSTRING_INDEX(imagen, '/', -1)
            ) as unique_ids ON p.id = unique_ids.min_id
            ORDER BY p.id ASC";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ss", $termino, $termino);//Asigno los valores reales a los parámetros de la consulta. Las 'ss' indican que son dos strings (texto
    $stmt->execute();
    $productos = $stmt->get_result();// Estos resultados quedan almacenados en la variable $productos para poder usarlos después."
} else {
    $productos = [];
}

// 4. Procesamiento seguro de datos
$productos_finales = [];
if ($productos) {
    while ($producto = $productos->fetch_assoc()) {
        // Obtener imágenes adicionales
        $stmt = $conexion->prepare("SELECT imagen FROM producto_imagenes WHERE producto_id = ? ORDER BY orden");
        $stmt->bind_param("i", $producto['id']);
        $stmt->execute();
        $imagenes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // Preparar datos para la vista
        $productos_finales[] = [
            'id' => $producto['id'],
            'nombre' => htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8'),
            'descripcion' => htmlspecialchars($producto['descripcion'], ENT_QUOTES, 'UTF-8'),
            'precio' => (float)$producto['precio'],
            'imagen' => htmlspecialchars($producto['imagen']),
            'imagenes' => array_map(function($img) {
                return ['imagen' => htmlspecialchars($img['imagen'])];
            }, $imagenes)
        ];
    }
}

// 5. Inicializar carrito si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados de búsqueda</title>
    <link rel="stylesheet" href="styles.css" />

</head>
<body>
    <header>
        <?php include 'partesup.php'; ?>
    </header>
    <div class="search-results-header">
    <h1 class="resultados-titulo">Resultados para: "<?php echo htmlspecialchars($termino); ?>"</h1>
    <?php if (empty($productos_finales)): ?>
        <p class="no-results">No se encontraron productos que coincidan con tu búsqueda.</p>
    <?php endif; ?>
</div>
    <div class="container-items">
        <?php foreach ($productos_finales as $producto): ?>
            <div class="item">
                <img src="<?= $producto['imagen'] ?>" alt="<?= $producto['nombre'] ?>" loading="lazy">
                <div class="info-product">
                    <h2><?= $producto['nombre'] ?></h2>
                    <p class="price">$<?= number_format($producto['precio'], 2) ?></p>
                    <button class="ver-detalles" 
                        data-id="<?= $producto['id'] ?>"
                        data-nombre="<?= $producto['nombre'] ?>"
                        data-descripcion="<?= $producto['descripcion'] ?>"
                        data-precio="<?= $producto['precio'] ?>"
                        data-imagen="<?= $producto['imagen'] ?>"
                        data-imagenes='<?= json_encode($producto['imagenes'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>'>
                        Ver detalles
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<!--Muestra cada producto en una tarjeta

Usa loading="lazy" para carga diferida de imágenes

Formatea el precio con 2 decimales

Prepara botones de detalles con todos los datos necesarios en atributos data-*-->



    <!-- Modal de detalles con zoom interactivo -->
    <div class="modal">
        <div class="modal-content">
            <div class="image-gallery-container">
                <div class="gallery"></div>
                <div class="main-image-container">
                    <img src="" alt="Producto" id="main-product-image">
                </div>
            </div>
            <div class="product-info">
                <h2 class="product-title"></h2>
                <p class="product-description"></p>
                <p class="modal-price"></p>
                <div class="modal-buttons">
                    <button class="regresar">Regresar</button>
                    <form action="agregar_al_carrito.php" method="POST" class="form-agregar-carrito" onsubmit="event.preventDefault(); agregarAlCarrito(this);">
                        <input type="hidden" name="id" value="">
                        <input type="hidden" name="nombre" value="">
                        <input type="hidden" name="precio" value="">
                        <input type="hidden" name="cantidad" value="1">
                        <input type="hidden" name="imagen" value="">
                        <button type="submit" class="agregar">Añadir al carrito</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {//-Espera a que el HTML esté completamente cargado antes de ejecutar el código JavaScript
       //Prepara todas las referencias a elementos  que se usarán repetidamente
        const modal = document.querySelector('.modal');
        const modalImg = document.getElementById('main-product-image');
        const modalTitle = document.querySelector('.product-title');
        const modalDesc = document.querySelector('.product-description');
        const modalPrice = document.querySelector('.modal-price');
        const gallery = document.querySelector('.gallery');
        const form = document.querySelector('.form-agregar-carrito');
        const mainImageContainer = document.querySelector('.main-image-container');

        // Botones "Ver detalles"
        document.querySelectorAll('.ver-detalles').forEach(btn => {
            btn.addEventListener('click', function() {
                try {
                    // Extrae la información del producto de los atributos data
                    const producto = {
                        id: this.dataset.id,
                        nombre: this.dataset.nombre,
                        descripcion: this.dataset.descripcion,
                        precio: this.dataset.precio,
                        imagen: this.dataset.imagen,
                        imagenes: JSON.parse(this.dataset.imagenes)
                    };

                    // Actualizar modal Rellena los campos del modal con los datos del producto
                    modalTitle.textContent = producto.nombre;
                    modalDesc.textContent = producto.descripcion;
                    modalPrice.textContent = '$' + parseFloat(producto.precio).toFixed(2);
                    modalImg.src = producto.imagen;

                    // Actualizar formulario
                    form.querySelector('input[name="id"]').value = producto.id;
                    form.querySelector('input[name="nombre"]').value = producto.nombre;
                    form.querySelector('input[name="precio"]').value = producto.precio;
                    form.querySelector('input[name="imagen"]').value = producto.imagen;

                    // Crear galería
                    gallery.innerHTML = '';
                    
                    // Función para miniaturasCrea miniaturas clickables para cada imagen del producto
                    const addThumbnail = (src, active = false) => {
                        const thumb = document.createElement('img');
                        thumb.src = src;
                        thumb.className = 'gallery-thumbnail' + (active ? ' active' : '');
                        
                        thumb.addEventListener('click', function() {
                            modalImg.src = src;
                              // ... actualiza imagen principal y formulario
                            form.querySelector('input[name="imagen"]').value = src;
                            document.querySelectorAll('.gallery-thumbnail').forEach(t => t.classList.remove('active'));
                            this.classList.add('active');
                        });
                        
                        gallery.appendChild(thumb);
                    };

                    // Imagen principal
                    addThumbnail(producto.imagen, true);//Añade todas las imágenes a la galería
                    
                    // Imágenes adicionales
                    producto.imagenes.forEach(img => {
                        if (img.imagen) addThumbnail(img.imagen);
                    });

                    // Mostrar modal
                    modal.style.display = 'flex';
                    
                } catch (e) {
                    console.error("Error al mostrar detalles:", e);
                    alert("Error al cargar los detalles. Por favor revisa la consola para más información.");
                }
            });
        });

        // Efecto de zoom que sigue el cursor
        mainImageContainer.addEventListener('mousemove', function(e) {
            if (window.innerWidth <= 768) return; // No hacer zoom en móviles
            
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const xPercent = (x / rect.width) * 100;
            const yPercent = (y / rect.height) * 100;
            
            // Ajuste dinámico del origen del zoom
            modalImg.style.transformOrigin = `${xPercent}% ${yPercent}%`;
            
            // Intensidad del zoom (2x)
            modalImg.style.transform = 'scale(2)';
        });

        // Resetear zoom al salir
        mainImageContainer.addEventListener('mouseleave', function() {
            modalImg.style.transform = 'scale(1)';
        });

        // Cerrar modal
        document.querySelector('.regresar').addEventListener('click', () => {
            modal.style.display = 'none';
            modalImg.style.transform = 'scale(1)'; // Resetear zoom al cerrar
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
                modalImg.style.transform = 'scale(1)'; // Resetear zoom al cerrar
            }
        });
    });

    function agregarAlCarrito(form) {
        fetch(form.action, {
            method: 'POST',
            body: new URLSearchParams(new FormData(form))
        })
        .then(response => {
            // Cierra el modal y recarga
            document.querySelector('.modal').style.display = 'none';
            location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrió un error al agregar al carrito');
        });
    }
    </script>
</body>
</html>