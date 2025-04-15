<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);//Activa mostrar todos los errores de PHP para desarrollo

// Manejar peticiones AJAX primero
if ($_SERVER['REQUEST_METHOD'] === 'POST') { //Detecta cuando llega una petición POST
    if (session_status() === PHP_SESSION_NONE) {
        session_start();//Inicia la sesión si no estaba activa
    }
    
    if (!isset($_SESSION['carrito'])) {//Detecta acciones específicas para el carritos
        $_SESSION['carrito'] = [];//Crea el carrito en sesión si no existe
    }
    
    $respuesta = ['success' => false];
    
    try {
        if (isset($_POST['accion_carrito'])) {
            switch ($_POST['accion_carrito']) {
                case 'actualizar'://Recibe ID del producto y nueva cantidad Actualiza la cantidad en el carrito de sesión  Devuelve éxito
                    if (isset($_POST['id'], $_POST['cantidad']) && isset($_SESSION['carrito'][$_POST['id']])) {
                        $_SESSION['carrito'][$_POST['id']]['cantidad'] = (int)$_POST['cantidad'];
                        $respuesta = ['success' => true];
                    }
                    break;
                    
                case 'eliminar'://Recibe ID del producto a eliminar Quita el producto del carrito Devuelve éxito
                    if (isset($_POST['id']) && isset($_SESSION['carrito'][$_POST['id']])) {
                        unset($_SESSION['carrito'][$_POST['id']]);
                        $respuesta = ['success' => true];
                    }
                    break;
            }
        } elseif (isset($_POST['procesar_pago'])) {//Detecta cuando se envía el formulario de pago
            if (empty($_SESSION['carrito'])) {//Rechaza si el carrito está vacío
                throw new Exception('El carrito está vacío');
            }
            
            $camposRequeridos = ['firstName', 'lastName', 'email', 'address', 'city', 'state', 'postalCode', 'phone'];
            foreach ($camposRequeridos as $campo) {
                if (empty($_POST[$campo])) {
                    throw new Exception("El campo $campo es requerido");//Valida que todos los campos obligatorios estén completos
                }
            }
            
            try {
                $db = new PDO("mysql:host=localhost;dbname=tienda", "kheniali", "123");
                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $db->beginTransaction();

                // 1. Insertar o actualizar cliente
                //Guarda los datos del cliente o los actualiza si ya existe
                $stmtCliente = $db->prepare("INSERT INTO clientes 
                    (nombre, apellido, email, direccion, ciudad, provincia, codigo_postal, telefono) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    nombre = VALUES(nombre), 
                    apellido = VALUES(apellido),
                    direccion = VALUES(direccion),
                    ciudad = VALUES(ciudad),
                    provincia = VALUES(provincia),
                    codigo_postal = VALUES(codigo_postal),
                    telefono = VALUES(telefono)");
                    //Usa marcadores de posición (?) para seguridad contra inyección SQ ,ON DUPLICATE KEY UPDATE actualiza el registro si el email ya existe
                
                $stmtCliente->execute([
                    $_POST['firstName'],
                    $_POST['lastName'],
                    $_POST['email'],
                    $_POST['address'],
                    $_POST['city'],
                    $_POST['state'],
                    $_POST['postalCode'],
                    $_POST['phone']
                ]);//Ejecuta la consulta con los datos del formulario ,Los valores se asignan en orden a los marcadores de posición
                

                $id_cliente = $db->lastInsertId();
                if ($id_cliente == 0) {
                    $stmt = $db->prepare("SELECT id FROM clientes WHERE email = ?");
                    $stmt->execute([$_POST['email']]);
                    $id_cliente = $stmt->fetchColumn();
                }//Intenta obtener el ID del cliente insertado Si es 0 (cuando se actualiza un cliente existente), busca el ID usando el email

                // 2. Insertar pedido
                //Calcula el total del pedido sumando precio × cantidad de cada producto ,Usa operador null coalescente (??) para valores por defecto
                $total = 0;
                foreach ($_SESSION['carrito'] as $producto) {
                    $total += ($producto['precio'] ?? 0) * ($producto['cantidad'] ?? 1);
                }
//Inserta el pedido principal con estado "pendiente"

//Guarda el ID generado para usar en los detalles
                $stmtPedido = $db->prepare("INSERT INTO pedidos 
                    (id_cliente, total, estado, fecha_pedido) 
                    VALUES (?, ?, 'pendiente', NOW())");
                $stmtPedido->execute([$id_cliente, $total]);
                $id_pedido = $db->lastInsertId();

                // 3. Insertar detalles del pedido (CON NOMBRE DE PRODUCTO)
                $stmtDetalle = $db->prepare("INSERT INTO detalles_pedido 
                    (id_pedido, id_producto, nombre_producto, cantidad, precio_unitario) 
                    VALUES (?, ?, ?, ?, ?)");
                
                foreach ($_SESSION['carrito'] as $id_producto => $producto) {
                    $stmtDetalle->execute([
                        $id_pedido,
                        $id_producto,
                        $producto['nombre'] ?? 'Producto sin nombre',
                        $producto['cantidad'] ?? 1,
                        $producto['precio'] ?? 0
                    ]);
                }
//Prepara consulta para insertar detalles del pedido

//Por cada producto en el carrito, inserta una línea con:

//ID del pedido principal

//ID y nombre del producto

//Cantidad y precio unitario
                $db->commit();

                $_SESSION['ultimo_pedido'] = $id_pedido;
                $_SESSION['carrito'] = [];
                //Confirma la transacción si todo salió bien

//Guarda el ID del pedido en sesión

//Vacía el carrito
                $respuesta = [
                    'success' => true,
                    'id_pedido' => $id_pedido,
                    'message' => 'Pago procesado correctamente'
                ];
            } catch (PDOException $e) {
                $db->rollBack();
                $respuesta = [
                    'success' => false,
                    'message' => 'Error al procesar el pago: ' . $e->getMessage()
                ];
            }
        }
    } catch (Exception $e) {
        $respuesta['message'] = $e->getMessage();
    }
    
    header('Content-Type: application/json');
    echo json_encode($respuesta);
    exit;//Establece el tipo de contenido como JSON para que el navegador sepa cómo interpretar la respuesta
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);//Hace que PHP reporte TODOS los errores
ini_set('display_errors', 1);

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];//Verifica si no existe la variable de sesión 'carrito' Si no existe, la inicializa como un array vacío
}

$carrito = $_SESSION['carrito'];

function calcularTotal($carrito) {
    $total = 0;
    foreach ($carrito as $producto) {
        $total += ($producto['precio'] ?? 0) * ($producto['cantidad'] ?? 1);
    }
    return number_format($total, 2);
}//Recibe el array del carrito como parámetro .Inicializa $total en 0,Recorre cada producto en el carrito:Suma al total el precio del producto multiplicado por su cantidad
//Usa el operador ?? para valores por defecto (0 para precio, 1 para cantidad) y Devuelve el total formateado con 2 decimales usando number_format()

function obtenerProductosRecomendados() {
    $conexion = new mysqli("localhost", "kheniali", "123", "tienda");
    if ($conexion->connect_error) return [];
    
    $conexion->set_charset("utf8mb4");
    $result = $conexion->query("SELECT * FROM recomendados ORDER BY RAND() LIMIT 5");
    $recomendados = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $recomendados[] = [
                'id' => $row['id'],
                'nombre' => htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8'),
                'descripcion' => htmlspecialchars($row['descripcion'], ENT_QUOTES, 'UTF-8'),
                'precio' => (float)$row['precio'],
                'imagen' => htmlspecialchars($row['imagen'])
            ];
        }
    }
    
    $conexion->close();
    return $recomendados;
}

$recomendados = obtenerProductosRecomendados();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <link rel="stylesheet" href="stylec.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<header>
    <?php include 'partesup.php'; ?>
</header>

<div class="checkout-container">
    <section class="checkout-section">
        <div class="customer-info">
            <h2 class="section-title">Información del Cliente</h2>
            <form id="customerForm">
                <div class="name-fields">
                    <div class="form-group">
                        <label for="firstName" class="form-label">Primer Nombre</label>
                        <input type="text" id="firstName" name="firstName" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="lastName" class="form-label">Apellido</label>
                        <input type="text" id="lastName" name="lastName" class="form-input" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" id="email" name="email" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="address" class="form-label">Dirección</label>
                    <input type="text" id="address" name="address" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="city" class="form-label">Ciudad</label>
                    <input type="text" id="city" name="city" class="form-input" required>
                </div>
                
                <div class="name-fields">
                    <div class="form-group">
                        <label for="state" class="form-label">Provincia/Estado</label>
                        <input type="text" id="state" name="state" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="postalCode" class="form-label">Código Postal</label>
                        <input type="text" id="postalCode" name="postalCode" class="form-input" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="phone" class="form-label">Teléfono</label>
                    <input type="tel" id="phone" name="phone" class="form-input" required>
                </div>
                
                <button type="submit" class="pay-btn">Pagar</button>
            </form>
        </div>
    </section>
    
    <section class="checkout-section">
        <div class="cart-summary">
            <h2 class="section-title">Resumen del Pedido</h2>
        
            <?php if (empty($carrito)): ?>
                <p>Tu carrito está vacío.</p>
            <?php else: ?>
                <?php foreach ($carrito as $id => $producto): ?>
                    <!--Si está vacío, muestra un mensajeSi tiene productos, itera a través de ellos-->
                    <div class="cart-item">
                        <img src="<?= htmlspecialchars($producto['imagen'] ?? 'https://via.placeholder.com/100') ?>" 
                             alt="<?= htmlspecialchars($producto['nombre'] ?? 'Producto') ?>" 
                             class="cart-item-image">
                             <!--Usa htmlspecialchars para seguridad contra XSS ,Proporciona una imagen por defecto si no hay una específica-->
                        
                        <div class="cart-item-details">
                            <h3 class="cart-item-title"><?= htmlspecialchars($producto['nombre'] ?? 'Producto sin nombre') ?></h3>
                            <p><?= htmlspecialchars($producto['descripcion'] ?? 'Sin descripción') ?></p>
                            <p class="cart-item-price">$<?= number_format($producto['precio'] ?? 0, 2) ?></p>
                            
<!--Muestra nombre, descripción y precio,Formatea el precio con 2 decimales-->

                            <div class="cart-item-actions">
                                <select class="quantity-select cantidad" data-id="<?= htmlspecialchars($id) ?>">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <option value="<?= $i ?>" <?= ($i == ($producto['cantidad'] ?? 1)) ? 'selected' : '' ?>>
                                            <?= $i ?>
                                        </option>
                                    <?php endfor; ?>
                                </select>

<!--Permite seleccionar cantidades del 1 al 5-->

                                <button class="trash-btn eliminar" data-id="<?= htmlspecialchars($id) ?>" title="Eliminar">
                                    <img src="img/eliminar.png" alt="Eliminar" class="trash-icon">
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="cart-total">
                    <strong>Total: $<?= calcularTotal($carrito) ?></strong><!--Llama a la función calcularTotal() que suma todos los productos-->
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<div class="recomendados-container">
    <h2 class="recomendados-title">Productos Recomendados</h2>
    <div class="recomendados-grid">
        <?php foreach ($recomendados as $producto): ?>
            <div class="recomendado-item">
                <img src="<?= htmlspecialchars($producto['imagen']) ?>" 
                     alt="<?= htmlspecialchars($producto['nombre']) ?>" 
                     loading="lazy"><!--loading="lazy" mejora el rendimiento cargando imágenes solo cuando son visibles-->
                <div class="recomendado-info">
                    <h3><?= htmlspecialchars($producto['nombre']) ?></h3>
                    <p class="recomendado-price">$<?= number_format($producto['precio'], 2) ?></p>
                    <div class="recomendado-actions">
                        <a href="detalle_producto.php?id=<?= $producto['id'] ?>" class="ver-mas-btn">Ver detalles</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
<script>
// Variable global para el carrito
const carritoActual = <?= json_encode($carrito) ?>;

// Función para calcular y actualizar el total en la UI
function actualizarTotal() {
    const total = Object.values(carritoActual).reduce((sum, item) => {
        return sum + (item.precio || 0) * (item.cantidad || 1);
    }, 0);
    
    document.querySelector('.cart-total strong').textContent = `Total: $${total.toFixed(2)}`;
    return total;
}

// Función para actualizar el carrito en el servidor
async function manejarAccionCarrito(accion, datos = {}) {
    try {
        const formData = new FormData();
        formData.append('accion_carrito', accion);
        
        if (accion === 'actualizar') {
            formData.append('id', datos.id);
            formData.append('cantidad', datos.cantidad);
        } else if (accion === 'eliminar') {
            formData.append('id', datos.id);
        }
        
        const response = await fetch('cart.php', {
            method: 'POST',
            body: formData
        });
        
        return await response.json();
    } catch (error) {
        console.error('Error:', error);
        return { success: false, message: 'Error de conexión' };
    }
}

// Función para procesar pago
async function procesarPago(datosCliente) {
    try {
        const formData = new FormData();
        formData.append('procesar_pago', 'true');
        
        for (const key in datosCliente) {
            formData.append(key, datosCliente[key]);
        }
        
        const response = await fetch('cart.php', {
            method: 'POST',
            body: formData
        });
        
        return await response.json();
    } catch (error) {
        console.error('Error:', error);
        return { success: false, message: error.message };
    }
}

// Manejar cambio de cantidad
document.querySelectorAll('.quantity-select').forEach(select => {
    select.addEventListener('change', async function() {
        const id = this.dataset.id;
        const cantidad = parseInt(this.value);
        
        if (carritoActual[id]) {
            const precioUnitario = carritoActual[id].precio || 0;
            const oldCantidad = carritoActual[id].cantidad || 1;
            
            // Actualizar localmente primero para mejor UX
            carritoActual[id].cantidad = cantidad;
            
            // Calcular diferencia para actualizar el total
            const diferencia = (cantidad - oldCantidad) * precioUnitario;
            const totalElement = document.querySelector('.cart-total strong');
            const currentTotal = parseFloat(totalElement.textContent.replace('Total: $', ''));
            const newTotal = currentTotal + diferencia;
            
            // Actualizar el total en la UI inmediatamente
            totalElement.textContent = `Total: $${newTotal.toFixed(2)}`;
            
            // Enviar la actualización al servidor
            const resultado = await manejarAccionCarrito('actualizar', {id, cantidad});
            
            if (!resultado.success) {
                // Revertir cambios si falla
                carritoActual[id].cantidad = oldCantidad;
                totalElement.textContent = `Total: $${currentTotal.toFixed(2)}`;
                this.value = oldCantidad;
                
                Toastify({
                    text: "Error al actualizar la cantidad",
                    duration: 2000,
                    backgroundColor: "#f44336",
                }).showToast();
            }
        }
    });
});

// Manejar eliminación de productos
document.querySelectorAll('.eliminar').forEach(button => {
    button.addEventListener('click', async function() {
        const id = this.dataset.id;
        
        // Mostrar confirmación antes de eliminar (opcional)
        const confirmacion = await Swal.fire({//libreria de js para estilo del mensaje
            title: '¿Eliminar producto?',
            text: "¿Estás seguro de que quieres eliminar este producto del carrito?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });
        
        if (!confirmacion.isConfirmed) return;
        
        const resultado = await manejarAccionCarrito('eliminar', {id});
        
        if (resultado.success) {
            delete carritoActual[id];
            this.closest('.cart-item').remove();
            
            // Actualizar el total
            actualizarTotal();
            
            // Mostrar notificación
            Toastify({
                text: "Producto eliminado",
                duration: 2000,
                backgroundColor: "#4CAF50",
            }).showToast();
            
            // Recargar solo si el carrito queda vacío
            if (Object.keys(carritoActual).length === 0) {
                location.reload();
            }
        } else {
            Toastify({
                text: resultado.message || "Error al eliminar el producto",
                duration: 2000,
                backgroundColor: "#f44336",
            }).showToast();
        }
    });
});

// Procesar pago
document.getElementById('customerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (Object.keys(carritoActual).length === 0) {
        Swal.fire('Error', 'El carrito está vacío', 'error');
        return;
    }

    // Validar campos requeridos
    let formValid = true;
    this.querySelectorAll('[required]').forEach(field => {
        if (!field.value.trim()) {
            field.style.borderColor = 'red';
            formValid = false;
        } else {
            field.style.borderColor = '';
        }
    });
    
    if (!formValid) {
        Swal.fire('Error', 'Por favor completa todos los campos obligatorios', 'error');
        return;
    }

    // Deshabilitar botón y mostrar spinner
    const btn = this.querySelector('.pay-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

    try {
        const formData = new FormData(this);
        const datosCliente = Object.fromEntries(formData);
        const resultado = await procesarPago(datosCliente);

        if (resultado.success) {
            window.location.href = `confirmacion_pago.php?id=${resultado.id_pedido}`;
        } else {
            Swal.fire('Error', resultado.message || 'Hubo un problema al procesar el pago', 'error');
        }
    } catch (error) {
        Swal.fire('Error', error.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = 'Pagar';
    }
});

// Inicializar el total al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    actualizarTotal();
});
</script>
</body>
</html>
