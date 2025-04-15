<?php
session_start();
require_once '../includes/database.php';

// Verificar permisos de administrador
if (!isset($_SESSION['es_admin']) || !$_SESSION['es_admin']) {
    header("Location: ../login.php");
    exit;
}

$producto_id = isset($_GET['producto_id']) ? intval($_GET['producto_id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Eliminar especificaciones existentes
    $stmt = $conexion->prepare("DELETE FROM especificaciones WHERE producto_id = ?");
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $stmt->close();
    
    // Insertar nuevas especificaciones
    if (isset($_POST['especs']) {
        $stmt = $conexion->prepare("INSERT INTO especificaciones (producto_id, categoria, nombre, valor, orden) VALUES (?, ?, ?, ?, ?)");
        
        $orden = 0;
        foreach ($_POST['especs'] as $espec) {
            if (!empty($espec['nombre']) && !empty($espec['valor'])) {
                $stmt->bind_param("isssi", $producto_id, $espec['categoria'], $espec['nombre'], $espec['valor'], $orden);
                $stmt->execute();
                $orden++;
            }
        }
        $stmt->close();
    }
    
    $_SESSION['mensaje'] = "Especificaciones actualizadas correctamente";
    header("Location: especificaciones.php?producto_id=$producto_id");
    exit;
}

// Obtener producto
$stmt = $conexion->prepare("SELECT id, nombre FROM productos WHERE id = ?");
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$producto = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Obtener especificaciones existentes
$stmt = $conexion->prepare("SELECT categoria, nombre, valor FROM especificaciones WHERE producto_id = ? ORDER BY orden");
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$especificaciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrar Especificaciones</title>
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .espec-container {
            margin-bottom: 30px;
        }
        .espec-form {
            display: grid;
            grid-template-columns: 1fr 2fr 3fr;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }
        .espec-actions {
            margin-top: 20px;
        }
        .btn-add {
            background: #28a745;
            color: white;
        }
        .btn-remove {
            background: #dc3545;
            color: white;
        }
    </style>
</head>
<body>
    <?php include 'admin_header.php'; ?>
    
    <div class="container">
        <h1>Administrar Especificaciones: <?= htmlspecialchars($producto['nombre']) ?></h1>
        
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success"><?= $_SESSION['mensaje'] ?></div>
            <?php unset($_SESSION['mensaje']); ?>
        <?php endif; ?>
        
        <form method="POST" id="especForm">
            <input type="hidden" name="producto_id" value="<?= $producto_id ?>">
            
            <div id="especsContainer">
                <?php if (!empty($especificaciones)): ?>
                    <?php foreach ($especificaciones as $index => $espec): ?>
                        <div class="espec-form">
                            <input type="text" name="especs[<?= $index ?>][categoria]" value="<?= htmlspecialchars($espec['categoria']) ?>" placeholder="Categoría" required>
                            <input type="text" name="especs[<?= $index ?>][nombre]" value="<?= htmlspecialchars($espec['nombre']) ?>" placeholder="Nombre" required>
                            <input type="text" name="especs[<?= $index ?>][valor]" value="<?= htmlspecialchars($espec['valor']) ?>" placeholder="Valor" required>
                            <button type="button" class="btn-remove" onclick="removeEspec(this)">Eliminar</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="espec-form">
                        <input type="text" name="especs[0][categoria]" placeholder="Categoría" required>
                        <input type="text" name="especs[0][nombre]" placeholder="Nombre" required>
                        <input type="text" name="especs[0][valor]" placeholder="Valor" required>
                        <button type="button" class="btn-remove" onclick="removeEspec(this)">Eliminar</button>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="espec-actions">
                <button type="button" class="btn-add" onclick="addEspec()">+ Añadir Especificación</button>
                <button type="submit" class="btn-save">Guardar Cambios</button>
                <a href="productos.php" class="btn-cancel">Volver a Productos</a>
            </div>
        </form>
    </div>
    
    <script>
        let especCount = <?= count($especificaciones) ?: 1 ?>;
        
        function addEspec() {
            const container = document.getElementById('especsContainer');
            const div = document.createElement('div');
            div.className = 'espec-form';
            div.innerHTML = `
                <input type="text" name="especs[${especCount}][categoria]" placeholder="Categoría" required>
                <input type="text" name="especs[${especCount}][nombre]" placeholder="Nombre" required>
                <input type="text" name="especs[${especCount}][valor]" placeholder="Valor" required>
                <button type="button" class="btn-remove" onclick="removeEspec(this)">Eliminar</button>
            `;
            container.appendChild(div);
            especCount++;
        }
        
        function removeEspec(btn) {
            const form = btn.closest('.espec-form');
            form.remove();
        }
    </script>
</body>
</html>