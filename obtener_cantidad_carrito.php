<?php
session_start();

// Inicializar variable segura
$totalItems = 0;//Crea una variable con valor 0 por defecto

// Verificar si el carrito existe y es un array
if (isset($_SESSION['carrito']) && is_array($_SESSION['carrito'])) {

    // verifica Si existe la variable $_SESSION['carrito'] Si es un array (no otro tipo de dato) Por qué: Para manejar el carrito solo si está correctamente formado
    $totalItems = array_reduce(
        $_SESSION['carrito'],
        function($sum, $producto) {//sum  Acumulador del total
            return $sum + (isset($producto['cantidad']) ? (int)$producto['cantidad'] : 0);
        },
        0
    );
}

// Mostrar el total 
echo $totalItems;//Devuelve el número total de ítems
?>