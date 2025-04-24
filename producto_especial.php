<?php
session_start();
$conexion = new mysqli("localhost", "kheniali", "123", "tienda");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$conexion->set_charset("utf8mb4");

$sql_coleccion = "SELECT * FROM coleccion ORDER BY RAND() LIMIT 5";//mando a llamar 5 productos especificamente 
$result_coleccion = $conexion->query($sql_coleccion);//guarda esos productos en coleccion

$coleccion = [];
if ($result_coleccion) {
    while ($row = $result_coleccion->fetch_assoc()) {
        $coleccion[] = [
            'id' => $row['id'],
            'nombre' => htmlspecialchars($row['nombre'], ENT_QUOTES, 'UTF-8'),
            'descripcion' => htmlspecialchars($row['descripcion'], ENT_QUOTES, 'UTF-8'),
            'descripcion2' => isset($row['descripcion2']) ? htmlspecialchars($row['descripcion2'], ENT_QUOTES, 'UTF-8') : '',
            'precio' => (float)$row['precio'],
            'imagen' => htmlspecialchars($row['imagen'])
        ];
    }
}
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Producto Especial</title>
    <link rel="stylesheet" href="stylec.css" />
</head>
<body>


<header>
    <?php include 'partesup.php'; ?>
</header>

<!-- Banner de video   que se reproduce en bucle infinito-->
<div class="video-banner">
    <video autoplay muted loop>/
        <source src="img/video.mp4" type="video/mp4">
      
    </video>
</div>

<!-- Texto banner  -->
<div class="text-banner">
    <h2>El sonido premium sin cables</h2>
    <p>Redescubre la emoción de la música con los audífonos inalámbricos Sony.</p>
</div>

<!-- Sección Sony -->
<div class="sony-style-section">
    <div class="sony-text-content">
        <h2>Sonido premium inalámbrico</h2>
        <p>Los audífonos Sony están diseñados para ofrecer una experiencia de audio excepcional.</p>
        <div class="sony-discount-banner">
            <strong>AHORRE $25 DE DESCUENTO</strong>
        </div>
    </div>
    <div class="sony-image-content">
        <img src="img/audifono.png" alt="Audífonos Sony premium" loading="lazy">
    </div>
</div>
<!-- SECCION DE LOS PRODUCTOS DE COLECCION-->
<div class="recomendados-container">
    <h2 class="recomendados-title">Descubre nuestra colección de audifonos</h2>
    <div class="recomendados-grid">
        <?php foreach ($coleccion as $producto): ?><!-- por cada lista de productos( se encuentra en coleccion)haz lo siguiente -->
            <div class="recomendado-item">
                <img src="<?= $producto['imagen'] ?>" alt="<?= $producto['nombre'] ?>" loading="lazy">
                <div class="recomendado-info">
                    <h3><?= $producto['nombre'] ?></h3>
                    <p class="recomendado-price">$<?= number_format($producto['precio'], 2) ?></p>
                    <div class="recomendado-actions">
                        <a href="detalle_producto.php?id=<?= $producto['id'] ?>" class="ver-mas-btn">Ver detalles</a><!-- me lleva al producto por el id-->
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>


<?php include 'footer.php'; ?>


</body>
</html>