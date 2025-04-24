<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <style>
     body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            color: #333;
            background-color: #f8f9fa;
            line-height: 1.6;
        }
        
        header {
            background-color: #343a40;
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: relative;
        }
        
        #cart-counter {
            background-color: #dc3545;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
            margin-left: 5px;
        }
        
        main {
            flex: 1;
            padding: 2rem 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .container {
            width: 100%;
            max-width: 500px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 2rem;
            margin: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #495057;
        }
        
        input[type="text"],
        input[type="password"],
        input[type="email"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        button[type="submit"] {
            width: 100%;
            padding: 0.75rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-3 {
            margin-top: 1rem;
        }
        
        a {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <header>
        <?php 
   
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        include 'partesup.php'; 
        ?>
    </header>

    <main>
        <div class="container">
            <h2 class="text-center mb-4">Registro de Usuario</h2>
            
            <form action="registro.php" method="POST">
                <div class="form-group">
                    <label for="nombre_user">Nombre de usuario:</label>
                    <input type="text" class="form-control" id="nombre_user" name="nombre_user" required>
                </div>
                
                <div class="form-group">
                    <label for="contrasena_user">Contraseña:</label>
                    <input type="password" class="form-control" id="contrasena_user" name="contrasena_user" required minlength="8">
                </div>
                
                <div class="form-group">
                    <label for="correo_user">Correo electrónico:</label>
                    <input type="email" class="form-control" id="correo_user" name="correo_user" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block" name="registro">Registrarse</button>
            </form>
            
            <p class="mt-3 text-center">¿Ya tienes una cuenta? <a href="login1.php">Inicia sesión aquí</a></p>
        </div>
    </main>

    <script>

    function updateCartCounter() {
      
        const xhr = new XMLHttpRequest();
        xhr.open('GET', window.location.href, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('Cache-Control', 'no-cache');//no me des información vieja (no-cache), quiero datos frescos"


        
        xhr.onload = function() {
            if (this.status === 200) {
                // Crear un parser de HTML para extraer el contador
                const parser = new DOMParser();
                const htmlDoc = parser.parseFromString(this.responseText, 'text/html');
                const counterElement = htmlDoc.getElementById('cart-counter');
                
                if (counterElement) {
                    // Actualizar solo si el valor cambió
                    const currentCounter = document.getElementById('cart-counter');
                    if (currentCounter && currentCounter.textContent !== counterElement.textContent) {
                        currentCounter.textContent = counterElement.textContent;//"Comparo el número viejo con el nuevo diferentes, actualizo lo que ve el usuario"
                    }
                }
            }
        };
        
        xhr.send();
    }

    // Actualizar inmediatamente al cargar
    document.addEventListener('DOMContentLoaded', function() {
        updateCartCounter();
        
        // Actualizar periódicamente (cada 3 segundos)
        setInterval(updateCartCounter, 3000);//Y sigue chequeando cada 3 segundos
        
       
        window.addEventListener('focus', updateCartCounter);
    });
    </script>
</body>
</html>