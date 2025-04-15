<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "kheniali", "123", "tienda");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$conexion->set_charset("utf8mb4");

// Consulta para obtener los pedidos
$sql = "SELECT * FROM vista_pedidos_completos ORDER BY fecha_pedido DESC";
$resultado = $conexion->query($sql);

$pedidos = [];
if ($resultado) {
    while ($row = $resultado->fetch_assoc()) {
        $pedidos[] = $row;
    }
}
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Pedidos -ONEXAA</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4bb543;
            --warning: #f0ad4e;
            --danger: #d9534f;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: var(--dark);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 20px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            display: flex;
            align-items: center;
        }
        
        .logo i {
            margin-right: 10px;
            font-size: 28px;
        }
        
        .page-title {
            margin: 30px 0;
        }
        
        .page-title h1 {
            font-size: 32px;
            color: var(--dark);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .page-title p {
            color: var(--gray);
            font-size: 16px;
        }
        
        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .card-header {
            background-color: var(--primary);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h3 {
            font-size: 18px;
            font-weight: 600;
        }
        
        .card-body {
            padding: 0;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: var(--dark);
            text-transform: uppercase;
            font-size: 13px;
            position: sticky;
            top: 0;
        }
        
        tr:hover {
            background-color: #f8f9fa;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            gap: 5px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
        }
        
        .btn-sm {
            padding: 5px 8px;
            font-size: 12px;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .actions {
            display: flex;
            gap: 5px;
        }
        
        .no-data {
            text-align: center;
            padding: 40px 0;
            color: var(--gray);
        }
        
        .no-data i {
            font-size: 50px;
            margin-bottom: 20px;
            color: #dee2e6;
        }
        
        footer {
            background-color: var(--dark);
            color: white;
            padding: 20px 0;
            text-align: center;
            margin-top: 40px;
        }
        
        .pedido-details {
            display: none;
            background-color: #f8f9fa;
            padding: 15px;
            border-left: 4px solid var(--primary);
        }
        
        .pedido-details.active {
            display: table-row;
        }
        
        .details-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .detail-group {
            flex: 1;
            min-width: 250px;
        }
        
        .detail-group h4 {
            margin-bottom: 10px;
            color: var(--primary);
        }
        
        .detail-item {
            margin-bottom: 8px;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--dark);
        }
        
        .detail-value {
            color: var(--gray);
        }
        
        .products-table {
            width: 100%;
            margin-top: 15px;
        }
        
        .products-table th {
            background-color: #e9ecef;
        }
        
        .toggle-details {
            cursor: pointer;
            color: var(--primary);
            font-weight: 600;
        }
        
        .toggle-details:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            th, td {
                padding: 8px 10px;
                font-size: 14px;
            }
            
            .btn {
                padding: 6px 8px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container header-content">
            <div class="logo">
                <i class='bx bxs-shopping-bag'></i>
                <span>Pedidos ONEXA</span>
            </div>
        </div>
    </header>
    
    <main class="container">
        <div class="page-title">
            <h1><i class='bx bxs-package'></i> Administración de Pedidos</h1>
            <p>Visualización completa de todos los pedidos registrados en el sistema</p>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3><i class='bx bxs-list-ul'></i> Listado de Pedidos</h3><!-- Pone un subtítulo (h3) con icono de lista (bxs-list-ul).-->
                <div>
                    <button class="btn btn-primary">
                        <i class='bx bxs-download'></i> Exportar
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <?php if (!empty($pedidos)): ?><!--Verifica si hay pedidos para mostrar-->
                        <table>
                            <thead><!--Define las columnas de la tabla (ID, Fecha, Cliente...).-->
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pedidos as $pedido): ?><!--Por cada pedido en la lista $pedidos, repite este bloque-->
                                    <tr class="pedido-row">
                                        <td>#<?= htmlspecialchars($pedido['id_pedido']) ?></td><!--htmlspecialchars(): Convierte caracteres especiales a formato seguro (evita errores).-->
                                        <td><?= htmlspecialchars($pedido['fecha_formateada']) ?></td>
                                        <td>
                                            <span class="toggle-details"><?= htmlspecialchars($pedido['cliente_completo']) ?></span>
                                        </td>
                                        <td>$<?= number_format($pedido['total_pedido'], 2) ?></td>
                                        <td>
                                            <?php 
                                            $badge_class = '';//los estados cambian el color del fondo
                                            if ($pedido['estado_pedido'] == 'completado') {
                                                $badge_class = 'badge-success';
                                            } elseif ($pedido['estado_pedido'] == 'pendiente') {
                                                $badge_class = 'badge-warning';
                                            } else {
                                                $badge_class = 'badge-danger';
                                            }
                                            ?>
                                            <span class="badge <?= $badge_class ?>"><!--Aplica la clase de color según el estado.-->
                                                <?= ucfirst(htmlspecialchars($pedido['estado_pedido'])) ?>
                                            </span>
                                        </td>
                                        <td class="actions">
                                            <button class="btn btn-primary btn-sm" title="Editar">
                                                <i class='bx bxs-edit'></i>
                                            </button>
                                            <button class="btn btn-primary btn-sm" title="Imprimir">
                                                <i class='bx bxs-printer'></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="pedido-details"><!--Fila oculta inicialmente (por CSS: display: none).-->
                                        <td colspan="6">
                                            <div class="details-row">
                                                <div class="detail-group">
                                                    <h4><i class='bx bxs-user'></i> Información del Cliente</h4>
                                                    <div class="detail-item">
                                                        <span class="detail-label">Nombre:</span>
                                                        <span class="detail-value"><?= htmlspecialchars($pedido['cliente_completo']) ?></span>
                                                    </div>
                                                    <div class="detail-item">
                                                        <span class="detail-label">Email:</span>
                                                        <span class="detail-value"><?= htmlspecialchars($pedido['email_cliente']) ?></span>
                                                    </div>
                                                    <div class="detail-item">
                                                        <span class="detail-label">Teléfono:</span>
                                                        <span class="detail-value"><?= htmlspecialchars($pedido['telefono']) ?></span>
                                                    </div>
                                                    <div class="detail-item">
                                                        <span class="detail-label">Dirección:</span>
                                                        <span class="detail-value"><?= htmlspecialchars($pedido['direccion_completa']) ?></span>
                                                    </div>
                                                </div>
                                                
                                                <div class="detail-group">
                                                    <h4><i class='bx bxs-package'></i> Detalles del Pedido</h4>
                                                    <div class="detail-item">
                                                        <span class="detail-label">ID Pedido:</span>
                                                        <span class="detail-value">#<?= htmlspecialchars($pedido['id_pedido']) ?></span>
                                                    </div>
                                                    <div class="detail-item">
                                                        <span class="detail-label">Fecha:</span>
                                                        <span class="detail-value"><?= htmlspecialchars($pedido['fecha_formateada']) ?></span>
                                                    </div>
                                                    <div class="detail-item">
                                                        <span class="detail-label">Estado:</span>
                                                        <span class="detail-value badge <?= $badge_class ?>">
                                                            <?= ucfirst(htmlspecialchars($pedido['estado_pedido'])) ?>
                                                        </span>
                                                    </div>
                                                    <div class="detail-item">
                                                        <span class="detail-label">Total:</span>
                                                        <span class="detail-value">$<?= number_format($pedido['total_pedido'], 2) ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <h4 style="margin-top: 20px;"><i class='bx bxs-cart'></i> Productos</h4>
                                            <table class="products-table">
                                                <thead>
                                                    <tr>
                                                        <th>Producto</th>
                                                        <th>Precio Unitario</th>
                                                        <th>Cantidad</th>
                                                        <th>Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><?= htmlspecialchars($pedido['nombre_producto']) ?></td>
                                                        <td>$<?= number_format($pedido['precio_unitario'], 2) ?></td>
                                                        <td><?= htmlspecialchars($pedido['cantidad']) ?></td>
                                                        <td>$<?= number_format($pedido['subtotal'], 2) ?></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-data">
                            <i class='bx bxs-inbox'></i><!--Si no hay pedidos (else), muestra este mensaje con icono de caja vacía (bxs-inbox)-->
                            <h3>No hay pedidos registrados</h3>
                            <p>No se encontraron pedidos en el sistema</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <footer>
        <div class="container">
            <p>&copy; <?= date('Y') ?> ONEXA. Todos los derechos reservados.</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Mostrar/ocultar detalles del pedido
        document.querySelectorAll('.toggle-details').forEach(element => {//Busca todos los elementos de la página que tengan la clase toggle-details (los nombres de clientes) y por cada uno, haz lo siguiente..
            element.addEventListener('click', function() {//A cada nombre de cliente, añádele un detector de clics, para que cuando lo clicks, ejecute esta función
                const detailsRow = this.closest('tr').nextElementSibling;
                detailsRow.classList.toggle('active');//Cambia el estado de la clase active en la fila de detalles:Si la tiene, la quita (oculta los detalles)
//Si no la tiene, la añade (muestra los detalles)
            });
        });
        
        // Funcionalidad para los botones de acción
        document.querySelectorAll('.btn-primary').forEach(button => {//Busca todos los botones con clase btn-primary (los botones azules) y por cada uno, haz esto
            button.addEventListener('click', function() {//A cada botón, añádele un detector para cuando se haga clic en él
                const action = this.querySelector('i').className;//Guarda las clases del icono en action (ej: bxs-edit)
                
                if (action.includes('bxs-edit')) {//Si las clases del icono incluyen bxs-edit (es el icono de lápiz), entonces..
                    Swal.fire({//Muestra una ventana emergente
                        title: 'Editar Pedido',
                        text: 'Aquí se abriría el formulario para editar el pedido',
                        icon: 'info',
                        confirmButtonText: 'Cerrar'
                    });
                } else if (action.includes('bxs-printer')) {
                    Swal.fire({
                        title: 'Imprimir Factura',
                        text: 'Se abriría el diálogo de impresión para la factura del pedido',
                        icon: 'info',
                        confirmButtonText: 'Cerrar'
                    });
                } else if (action.includes('bxs-download')) {
                    Swal.fire({
                        title: 'Exportar Datos',
                        text: 'Se prepararía la exportación de todos los pedidos',
                        icon: 'info',
                        confirmButtonText: 'Cerrar'
                    });
                }
            });
        });
    </script>
</body>
</html>