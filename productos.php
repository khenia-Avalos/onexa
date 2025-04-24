<?php
session_start();
require_once 'includes/conexion.php';


$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : 'default';//Obtiene la categoría seleccionada desde la URL o usa 'default' si no se especifica

// Definir rangos de IDs según la categoría
$rangos = [
    'televisores-oled' => ['min' => 1, 'max' => 5],
    'televisores-led' => ['min' => 6, 'max' => 10],
    'televisores-inteligentes' => ['min' => 11, 'max' => 15],
'todos-televisores' => ['min' => 1, 'max' => 15],


'Accesorios-tv' => ['min' => 16, 'max' => 20],
'Accesorios-proyectores' => ['min' => 21, 'max' => 25],
'Accesorios-cine' => ['min' => 26, 'max' => 30],



'barras-sonido' => ['min' => 31, 'max' => 35],
'receptores-AV' => ['min' => 36, 'max' => 40],
'altavoces-cine' => ['min' => 41, 'max' => 45],


'todos-auriculares' => ['min' => 46, 'max' => 70],


'todas-camaras' => ['min' => 71, 'max' => 95],




'todos-telefonos' => ['min' => 96, 'max' => 120],


'todo-inzone' => ['min' => 121, 'max' => 135],



    'default' => ['min' => 1, 'max' => 135] // Valor por defecto
];

// Obtener el rango adecuado
$rango = $rangos[$categoria] ?? $rangos['default'];
$min_id = $rango['min'];
$max_id = $rango['max'];


$conexion = new mysqli("localhost", "kheniali", "123", "tienda");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$conexion->set_charset("utf8mb4");

//--Busca en la tabla productos dentro de un rango específico de IDs


$sql = "SELECT p.* 
        FROM productos p
        INNER JOIN (
            SELECT MIN(id) as min_id
            FROM productos
            WHERE id BETWEEN ? AND ?
            GROUP BY 
                LOWER(REPLACE(REPLACE(REPLACE(TRIM(nombre), ' ', ''), '.', ''), '-', '')),
                LOWER(REPLACE(REPLACE(REPLACE(TRIM(descripcion), ' ', ''), '.', ''), '-', '')),
                FORMAT(precio, 2),
                SUBSTRING_INDEX(imagen, '/', -1)
        ) as unique_ids ON p.id = unique_ids.min_id
        WHERE p.id BETWEEN ? AND ?
        ORDER BY p.id ASC";
//--Solo incluye productos cuyos IDs coincidan con los IDs mínimos de cada grupo

$stmt = $conexion->prepare($sql);
$stmt->bind_param("iiii", $min_id, $max_id, $min_id, $max_id);
$stmt->execute();
$productos = $stmt->get_result();

if (!$productos) {
    die("Error en la consulta: " . $conexion->error);
}

// Procesamiento de productos (igual que antes)
$productos_finales = [];
while ($producto = $productos->fetch_assoc()) {
   
    $stmt_img = $conexion->prepare("SELECT imagen FROM producto_imagenes WHERE producto_id = ? ORDER BY orden");
    $stmt_img->bind_param("i", $producto['id']);
    $stmt_img->execute();
    $imagenes = $stmt_img->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_img->close();
    
    $descripcion2 = $producto['descripcion2'] ?? '';
    
    $productos_finales[] = [
        'id' => $producto['id'],
        'nombre' => htmlspecialchars($producto['nombre'], ENT_QUOTES, 'UTF-8'),//Organiza los datos del producto aplicando seguridad contra XSS
        'descripcion' => htmlspecialchars($producto['descripcion'], ENT_QUOTES, 'UTF-8'),
        'descripcion2' => htmlspecialchars($descripcion2, ENT_QUOTES, 'UTF-8'),
        'precio' => (float)$producto['precio'],
        'imagen' => htmlspecialchars($producto['imagen']),
        'imagenes' => array_map(function($img) {
            return ['imagen' => htmlspecialchars($img['imagen'])];
        }, $imagenes)
    ];
}

// Inicializar carrito
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
    <title>Tienda Online</title>
    <link rel="stylesheet" href="styles.css" />
    
</head>
<body>
    <header>
        <?php include 'partesup.php'; ?>
    </header>

    <div class="slider">
  <ul>
    <li><img id="sli" src="img/tele.png" alt=""></li>
    <li><img id="sli" src="img/sonido.gif" alt=""></li>
    <li><img id="sli" src="img/cambaner.png" alt=""></li>
  </ul>
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
                        data-descripcion2="<?= $producto['descripcion2'] ?>" 
                        data-precio="<?= $producto['precio'] ?>"
                        data-imagen="<?= $producto['imagen'] ?>"
                        data-imagenes='<?= json_encode($producto['imagenes'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>'>
                        Ver detalles
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

 
<div class="modal">
    <div class="modal-content split-modal">
        <!-- Lado izquierdo (original) -->
        <div class="left-section">
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
        
        <!-- Lado derecho (solo descripcion2) -->
        <div class="right-section">
    <div class="product-description2-container">
        <h3>Especificaciones:</h3>
        <div class="product-description2"></div>
       
        <a href="#" class="ver-mas-btn" id="verMasBtn">Ver más especificaciones</a>
    </div>
</div>
    </div>
</div>



    <?php include 'footer.php'; ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.querySelector('.modal');
        const modalImg = document.getElementById('main-product-image');
        const modalTitle = document.querySelector('.product-title');
        const modalDesc = document.querySelector('.product-description');
        const modalDesc2 = document.querySelector('.product-description2'); 
        const modalPrice = document.querySelector('.modal-price');
        const gallery = document.querySelector('.gallery');
        const form = document.querySelector('.form-agregar-carrito');
        const mainImageContainer = document.querySelector('.main-image-container');

        // Botones "Ver detalles"
        document.querySelectorAll('.ver-detalles').forEach(btn => {
            btn.addEventListener('click', function() {
                try {
                    // Configura los botones de detalles para mostrar información completa del producto
                    const producto = {
                        id: this.dataset.id,
                        nombre: this.dataset.nombre,
                        descripcion: this.dataset.descripcion,
                        descripcion2: this.dataset.descripcion2 || "No hay especificaciones adicionales", 

                        precio: this.dataset.precio,
                        imagen: this.dataset.imagen,
                        imagenes: JSON.parse(this.dataset.imagenes)
                    };

                           // Actualizar el botón "Ver más" con el ID del producto
            const verMasBtn = document.getElementById('verMasBtn');
            verMasBtn.href = `detalle_producto.php?id=${producto.id}`;//CREA LA URL DINAMICAMENTE
                  
                    modalTitle.textContent = producto.nombre;
                    modalDesc.textContent = producto.descripcion;
                                modalDesc2.innerHTML = producto.descripcion2.replace(/\n/g, '<br>'); // Lado derecho (con saltos de línea)

                    modalPrice.textContent = '$' + parseFloat(producto.precio).toFixed(2);
                    modalImg.src = producto.imagen;

                    // Actualizar formulario
                    form.querySelector('input[name="id"]').value = producto.id;
                    form.querySelector('input[name="nombre"]').value = producto.nombre;
                    form.querySelector('input[name="precio"]').value = producto.precio;
                    form.querySelector('input[name="imagen"]').value = producto.imagen;

                    // Crear galería
                    gallery.innerHTML = '';
                 
                    const addThumbnail = (src, active = false) => {
                        const thumb = document.createElement('img');
                        thumb.src = src;
                        thumb.className = 'gallery-thumbnail' + (active ? ' active' : '');
                          // Cambia la imagen principal al hacer clic
                        thumb.addEventListener('click', function() {
                            modalImg.src = src;
                            form.querySelector('input[name="imagen"]').value = src;
                            document.querySelectorAll('.gallery-thumbnail').forEach(t => t.classList.remove('active'));
                            this.classList.add('active');
                        });
                        
                        gallery.appendChild(thumb);
                    };

                    // Imagen principal
                    addThumbnail(producto.imagen, true);
                    
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

    
        document.querySelector('.regresar').addEventListener('click', () => {
            modal.style.display = 'none';
            modalImg.style.transform = 'scale(1)'; // Resetear zoom al cerrar
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
                modalImg.style.transform = 'scale(1)'; 
            }
        });
    });


//Envía datos del producto al servidor para agregarlo al carrito
function agregarAlCarrito(form) {
    fetch(form.action, {// URL del archivo PHP que procesará la solicitud (agregar_al_carrito.php)
        method: 'POST',
        body: new URLSearchParams(new FormData(form))//  form dataCrea un objeto con todos los campos del formulario
        //Convierte los datos a formato x-www-form-urlencoded
    })
    .then(response => {
       
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