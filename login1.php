<?php

session_start();//Esto inicia o continúa una sesión en el servidor, permitiendo guardar información del usuario entre páginas.



$_SESSION['carrito'] = $_SESSION['carrito'] ?? [];


$cart_count = count($_SESSION['carrito']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión - ONEXA</title>
   
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-grow: 1;
        }
        
        .row {
            display: flex;
            flex-wrap: wrap;
            width: 100%;
            justify-content: center;
        }
        
        .col-md-6 {
            width: 100%;
            max-width: 500px;
            padding: 0 15px;
            padding-top: 100px;
        }
        
        .login-container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 50px;
            margin-bottom: 50px;
            width: 100%;
        }
        
        .logo1 {
            max-width: 150px;
            margin-bottom: 20px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mb-4 {
            margin-bottom: 1.5rem;
        }
        
        .mb-3 {
            margin-bottom: 1rem;
        }
        
        .mt-3 {
            margin-top: 1rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        
        .form-control {
            display: block;
            width: 100%;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        
        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            border: 1px solid transparent;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            transition: all 0.15s ease-in-out;
            cursor: pointer;
        }
        
        .btn-primary {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }
        
        .btn-primary:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }
        
        .w-100 {
            width: 100%;
        }
        
        .alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            padding: 0.75rem 1.25rem;
            border: 1px solid transparent;
            border-radius: 0.25rem;
            animation: fadeIn 0.5s, fadeOut 0.5s 2.5s;
        }
        
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        
        .d-none {
            display: none;
        }
        
        a {
            color: #007bff;
            text-decoration: none;
        }
        
        a:hover {
            color: #0056b3;
            text-decoration: underline;
        }
        
        @keyframes fadeIn { 
            from { opacity: 0; } 
            to { opacity: 1; } 
        }
        
        @keyframes fadeOut { 
            from { opacity: 1; } 
            to { opacity: 0; } 
        }
        
        .fa-spinner {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <?php include 'partesup.php'; ?>

    <div class="container"> <!--Contenedor principal: Organiza el contenido en una estructura responsive que se adapta a diferentes tamaños de pantalla.-->
        <div class="row">
            <div class="col-md-6">
                <div class="login-container">
                    <div class="text-center mb-4">
                        <img src="img/logo3.png" alt="ONEXA Logo" class="logo1">
                        <h2>Iniciar Sesión</h2>
                    </div>

                    <form id="loginForm"> <!--Contenedor principal con ID para manipulación con JavaScript.-->
                        <div id="error-message" class="alert alert-danger d-none"></div> <!--mensaje de error oculto.-->

                        <div class="mb-3">
                            <label for="usuario" class="form-label">Usuario o Correo</label>
                            <input type="text" class="form-control" id="usuario" name="usuario" required>
                        </div>

                        <div class="mb-3">
                            <label for="contrasena" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="contrasena" name="contrasena" required> <!--Input de tipo password (oculta los caracteres) para la contraseña.-->
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-sign-in-alt"></i> Ingresar
                        </button>

                        <div class="mt-3 text-center">
                            <p>¿No tienes cuenta? <a href="registro1.php">Regístrate aquí</a></p>
                            <p><a href="">¿Olvidaste tu contraseña?</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
 
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    $(document).ready(function() {
        // Variable para almacenar el último conteo
        let lastCartCount = <?php echo $cart_count; ?>;
        
       


        function updateCartCounter() {
            $.ajax({
                url: window.location.href,
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Cache-Control': 'no-cache'
                },
                success: function(data) {
                    
                    const tempDiv = $('<div>').html(data);
                    const newCounter = tempDiv.find('#cart-counter').text();
                    
                    if (newCounter && newCounter != lastCartCount) {
                        $('#cart-counter').text(newCounter);//entonces actualiza el número que ve el usuario en la página rea
                        lastCartCount = newCounter;//Y guarda este nuevo número como referencia para la próxima vez
                    }
                },
                error: function() {
                    console.log('Error al actualizar contador');
                }
            });
        }
        
        // Actualizar cada 5 segundos
        setInterval(updateCartCounter, 5000);//revisa cuántos productos hay en el carrito

      
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();//Cuando hagan clic en 'Ingresar', no envíes el formulario de la forma normal
            
            const $btn = $(this).find('button[type="submit"]');
            $btn.html('<i class="fas fa-spinner fa-spin"></i> Verificando...').prop('disabled', true);

         
         
         //Envía los datos del formulario (usuario y coAntraseña) a login.php
            $.ajax({
                url: 'login.php',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {//Si el servidor dice 'todo bien', redirige al usuario a su cuenta
                    if (response.success) {
                        window.location.href = response.redirect;
                    } else {
                        $('#error-message').text(response.message).removeClass('d-none');
                    }
                },
                error: function() {
                    $('#error-message').text('Error de conexión').removeClass('d-none');
                },
                complete: function() {
                    $btn.html('<i class="fas fa-sign-in-alt"></i> Ingresar').prop('disabled', false);
                }
            });
        });
    });
    </script>
</body>
</html>