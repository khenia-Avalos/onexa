<?php
session_start();


$db_host = 'localhost';
$db_name = 'tienda';
$db_user = 'kheniali';
$db_pass = '123';

// Obtener ID del pedido de la URL o de la sesión, con fallback a null
$id_pedido = $_GET['id'] ?? $_SESSION['ultimo_pedido'] ?? null;


if (!is_numeric($id_pedido)) {
    die("Error: ID de pedido inválido");//Validación de seguridad para el ID del pedido
}

try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
    $stmtPedido = $db->prepare("SELECT p.*, c.nombre, c.apellido, c.email  
                               FROM pedidos p
                               JOIN clientes c ON p.id_cliente = c.id_cliente
                               WHERE p.id_pedido = :id_pedido");
    $stmtPedido->execute([':id_pedido' => $id_pedido]);
    $pedido = $stmtPedido->fetch(PDO::FETCH_ASSOC);
    
    if (!$pedido) {
        die("No se encontró el pedido especificado");
    }
    
    $stmtProductos = $db->prepare("SELECT dp.* 
                                  FROM detalles_pedido dp
                                  WHERE dp.id_pedido = :id_pedido");
    $stmtProductos->execute([':id_pedido' => $id_pedido]);
    $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);
    
    $total = $pedido['total'] ?? array_reduce($productos, function($sum, $item) {//Calcula el total sumando los subtotales de cada producto si no viene en $pedido
        return $sum + ($item['precio_unitario'] * $item['cantidad']);
    }, 0);
    
} catch (PDOException $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>ONEXA - Confirmación de Compra</title>
    <link rel="stylesheet" href="confirmacion_pago.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="confirmacion-container">
        <div class="logo-onexa">
            <h2>ONEXA</h2>
            <div class="slogan">Soluciones tecnológicas de calidad</div>
        </div>
        
        <h1>Confirmación de Compra</h1>
        <div class="mensaje-confirmacion">
            <i class="fas fa-check-circle"></i> ¡Gracias por tu compra, <?= htmlspecialchars($pedido['nombre'] ?? 'Cliente') ?>!
        </div>
        
        <div class="seccion-factura">
            <h3>Información del Cliente</h3>
            <div class="info-cliente">
                <div>
                    <p><strong>Nombre:</strong> <?= htmlspecialchars($pedido['nombre'] . ' ' . $pedido['apellido']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($pedido['email']) ?></p>
                </div>
                <div>
                    <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])) ?></p>
                    <p><strong>Estado:</strong> <span style="color: #28a745;"><?= htmlspecialchars($pedido['estado']) ?></span></p>
                </div>
            </div>
        </div>
        
        <div class="seccion-factura">
            <h3>Detalles de la Compra</h3>
            <table class="tabla-productos">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td><?= htmlspecialchars($producto['nombre_producto']) ?></td>
                        <td><?= htmlspecialchars($producto['cantidad']) ?></td>
                        <td>$<?= number_format($producto['precio_unitario'], 2) ?></td>
                        <td>$<?= number_format($producto['cantidad'] * $producto['precio_unitario'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="total-factura">
                <span class="total-text">TOTAL:</span>
                <span class="total-amount">$<?= number_format($total, 2) ?></span>
            </div>
        </div>
        
        <div class="info-envio">
            <h3>Información de Envío</h3>
            <p><i class="fas fa-truck"></i> Tu pedido está siendo preparado y será enviado pronto.</p>
            <p><i class="fas fa-envelope"></i> Hemos enviado los detalles a <?= htmlspecialchars($pedido['email']) ?></p>
        </div>
        
        <div class="pie-factura">
            <p>Gracias por elegir <strong>ONEXA</strong>. Para cualquier consulta, contacta a nuestro servicio al cliente.</p>
        </div>
        
        <div class="acciones">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Imprimir Comprobante
            </button>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-store"></i> Volver a la Tienda
            </a>
        </div>
    </div>
</body>
</html>