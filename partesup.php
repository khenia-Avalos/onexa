<?php

require_once 'includes/conexion.php';

//require_once __DIR__ . '/carrito_functions.php';


//FUNCIONALIDAD PARA ACTUALIZAR CARRITO 
function actualizarContadorCarrito() {
  if (!isset($_SESSION['carrito'])) {//utilizo session por usuario mas adelante
      return 0;
  }
  return array_sum(array_column($_SESSION['carrito'], 'cantidad'));
}

$numItems = actualizarContadorCarrito();//cada que recargue la pagina que me actualice

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ONEXA</title>
  
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

    <link rel="stylesheet" href="styles.css" />
    <!-- Segunda hoja de estilos -->
    <link rel="stylesheet" href="styless.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    <script src="/project/script/botonesmenu.js"></script>
</head>
<body>
  <nav>
   
    <div class="wrapper">
       <!-- icono de hamburguesa -->
    <label for="menu-btn" class="btn menu-btn"><i class="fas fa-bars"></i></label>
      
    <div class="logo">
    <a href="index.php">
        <img src="img/logo3.png" alt="Logo">
    </a>
</div>

<div class="lupa">
    <button class="lupa-button">
        <img src="img/lupa.png" alt="Buscar">
    </button>
    
<!-- Capa de fondo gris y barra de búsqueda -->
    <div class="overlay">
        <div class="search-container">
            <form action="resultados_busqueda.php" method="get"><!--method="get": Significa que los términos de búsqueda aparecerán en la URL (como ?q=televisor)-->
                <input type="text" name="q" class="search-input" placeholder="Buscar productos...">
                <button type="submit" class="search-button">
                    <img src="img/lupa.png" alt="Buscar">
                </button>
                <button type="button" class="search-close">
                    <img src="img/cerrar.png" alt="Cerrar búsqueda">
                </button>
            </form>
        </div>
    </div>
</div>
 <!-- x y mostrar icono de hamburguesa --> 
      <input type="radio" name="slider" id="menu-btn">
      <input type="radio" name="slider" id="close-btn">
      <ul class="nav-links">


 <!-- CATEGORIAS Y SUBCATEGORIAS DEL MEGAMENU-->
      <li>
          <a href="#" class="desktop-item">Television</a>
          <input type="checkbox" id="showMegaTelevision">
          <label for="showMegaTelevision" class="mobile-item">Television</label>
          <div class="mega-box">
            <div class="content">
              <div class="row">
                <img src="img/tv.png" alt="">
              </div>
              <div class="row">
                <header>Televisores</header>
                <ul class="mega-links">
                <li><a href="productos.php?categoria=televisores-oled">Televisores OLED</a></li> <!-- en productos.php estan definidos los id de cada categoria-->
                <li><a href="productos.php?categoria=televisores-led">Televisores LED</a></li>
                <li><a href="productos.php?categoria=televisores-inteligentes">Televisores Inteligentes</a>
                </li>
                <li><a href="productos.php?categoria=todos-televisores">Todos los Televisores</a></li>
                </ul>
              </div>
              <div class="row">
                <header>Accesorios</header>
                <ul class="mega-links">
                <li><a href="productos.php?categoria=Accesorios-tv">Accesorios de TV</a></li>
                  <li><a href="productos.php?categoria=Accesorios-proyectores">-Accesorios para proyectores</a></li>
                  <li><a href="productos.php?categoria=Accesorios-cine">-Accesorios para cine en casa y barra de sonido </a></li>
             
                </ul>
              </div>
              <div class="row">
                <header>Cine en casa</header>
                <ul class="mega-links">
                  <li><a href="productos.php?categoria=barras-sonido">-Barras de sonido</a></li>
                  <li><a href="productos.php?categoria=receptores-AV">-Receptores AV</a></li>
                  <li><a href="productos.php?categoria=altavoces-cine">-Altavoces de cine en casa</a></li>
           
                </ul>
              </div>
            </div>
          </div>
        </li>



        

        

        <li>
          <a href="#" class="desktop-item">Audio</a>
          <input type="checkbox" id="showMegaAudio">
          <label for="showMegaAudio" class="mobile-item">Audio</label>
          <div class="mega-box">
            <div class="content">
              <div class="row">
                <img src="img/audio.png" alt="">
              </div>
              <div class="row">
                <header>Auriculares y Audifonos</header>
                <ul class="mega-links">
                  <li><a href="productos.php?categoria=todos-auriculares">-Todos los auriculares</a></li>
                  <li><a href="#">-Auriculares Inalambricos</a></li>
              
                </ul>
              </div>
              <div class="row">
                <header>Audio Profesional</header>
                <ul class="mega-links">
                  <li><a href="#">-Microfonos Profesionales</a></li>
                  <li><a href="#">-Auriculares Profesionales</a></li>
                  <li><a href="#">-Grabadoras digitales portátiles</a></li>
              
                </ul>
              </div>
              <div class="row">
                <header>Soundbars</header>
                <ul class="mega-links">
                  <li><a href="#">-Todas las barras de donido </a></li>
              
                </ul>
              </div>
            </div>
          </div>
        </li>


  





        

        <li>
          <a href="#" class="desktop-item">Imagenes</a>
          <input type="checkbox" id="showMegaImagenes">
          <label for="showMegaImagenes" class="mobile-item">Imágenes</label>
          <div class="mega-box">
            <div class="content">
              <div class="row">
                <img src="img/imagenes.png" alt="">
              </div>
              <div class="row">
                <header>Camaras con lentes intercambiables</header>
                <ul class="mega-links">
                  <li><a href="productos.php?categoria=todas-camaras">-Todas las camaras con lentes intercambiables</a></li>
                  
                </ul>
              </div>
              <div class="row">
                <header>Lentes</header>
                <ul class="mega-links">
                  <li><a href="#">-Montura E sin espejo</a></li>
                  <li><a href="#">-Montura A para DSLR</a></li>
                 
                </ul>
              </div>
              <div class="row">
                <header>Vlog y cámaras compactas</header>
                <ul class="mega-links">
                  <li><a href="#">-Cámaras de vlog </a></li>
                  <li><a href="#">-Cámaras compactas/a></li>
                  <li><a href="#">-Accesorios</a></li>
            
                </ul>
              </div>
            </div>
          </div>
        </li>









        <li>
          <a href="#" class="desktop-item">Movil</a>
          <input type="checkbox" id="showMegaMovil">
          <label for="showMegaMovil" class="mobile-item">Movil</label>
          <div class="mega-box">
            <div class="content">
              <div class="row">
                <img src="img/movil.png" alt="">
              </div>
              <div class="row">
                <header>Telefonos inteligentes</header>
                <ul class="mega-links">
                  <li><a href="productos.php?categoria=todos-telefonos">-Todos los telefonos inteligentes</a></li>
                  <li><a href="#">-Accesorios</a></li>
                 
                </ul>
              </div>
              <div class="row">
                <header>Dispositivos IoT 5G</header>
                <ul class="mega-links">
                  <li><a href="#">-Todos los dispositivos IoT 5G</a></li>
                  <li><a href="#">-Transmisor de datos portatiles</a></li>
                  
                </ul>
              </div>
              
            </div>
          </div>
        </li>




        <li>
          <a href="#" class="desktop-item">Más</a>
          <input type="checkbox" id="showMegaMas">
          <label for="showMegaMas" class="mobile-item">Más</label>
          <div class="mega-box">
            <div class="content">
              <div class="row">
                <img src="img/mas.png" alt="">
              </div>
              <div class="row">
                <header>Equipo de Juego</header>
                <ul class="mega-links">
                  <li><a href="productos.php?categoria=todo-inzone">-Todo INZONE</a></li>
                  <li><a href="#">-Monitores INZONE</a></li>
                  <li><a href="#">-Auriculares INZONE</a></li>
           
                </ul>
              </div>
              <div class="row">
                <header>Acerca de Nosotros</header>
                <ul class="mega-links">
                  <li><a href="#">-Nosotros</a></li>
                  <li><a href="#">-Terminos y condiciones</a></li>
                  <li><a href="#">-Terminos del sitio web</a></li>
                 
                </ul>
              </div>
             
            </div>
          </div>
        </li>








        

         
       
        <label for="close-btn" class="btn close-btn"><i class="fas fa-times"></i></label>
        
       
        <li>
        <a href="#" class="desktop-item">
        <img src="img/ayuda.png" alt="Dropdown Menu" width="30">
    </a>

<!-- BOTONES DESPUES DEL MEGAMENU -->
    <input type="checkbox" id="showDrop">
    <label for="showDrop" class="mobile-item">Soporte de la tienda
        <img src="img/ayuda.png" alt="Dropdown Menu" width="30">
    </label>
          <ul class="drop-menu">
            <li><a href="soporte_tienda.php">Soporte de tienda</a></li>
            <li><a href="soporte_productos.php">Soporte de productos</a></li>
          </ul>
        </li>



        <li>
    <a href="#" class="desktop-item">
        <img src="img/cuenta.png" alt="Dropdown Menu" width="30">
    </a>
    <input type="checkbox" id="showDropCuenta"> <!-- Cambiado a un id único -->
    <label for="showDropCuenta" class="mobile-item">Mi cuenta
        <img src="img/cuenta.png" alt="Dropdown Menu" width="30">
    </label>
    <ul class="drop-menu">
        <li><a href="login1.php"><button class="crear-cuenta">Iniciar Sesión/Registrarse</button></a></li>
        <li><a href="#">Historial de Pedidos</a></li>
    </ul>
</li>


<li>
        <a href="#" class="desktop-item">
        <img src="img/cr.png" alt="Dropdown Menu" width="30">
    </a>
    <input type="checkbox" id="showDropidioma">
    <label for="showDropidioma" class="mobile-item">Idioma
        <img src="img/cr.png" alt="Dropdown Menu" width="30">
    </label>
          <ul class="drop-menu">
            <li><a href="#">English</a></li>
           
          </ul>
        </li>
      </ul>
    </div>
  </nav>


  <div class="container-icon">
    <div class="cart-wrapper">
        <!-- Envuelve el icono en un enlace -->
        <a href="cart.php" class="container-cart-icon" id="cart-icon">
            <img src="img/bolsa.png" alt="Carrito de compras" class="icon-cart" width="40" height="40">
            <div class="count-products">
                <span id="contador-productos"><?php echo $numItems; ?></span>
            </div>
        </a>
    </div>
</div>



</body>
</html>