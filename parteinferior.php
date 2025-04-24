</main> 
<!-- SECCION HERO -->
<section class="hero-section">
    <div class="hero-content">
      <h1 class="hero-title">Sumergete en el sonido Perfecto</h1>
      <p class="hero-subtitle">Auriculares con calidad de estudio, comodidad extrema y un diseño que enamora. ¡Vive cada nota como nunca antes! </p>
      <a href="producto_especial.php" class="hero-button">Ver más</a>

      
    </div>
  </section>

  <section class="collection">
    <h2>Compra la mejor tecnología</h2> 
    <div class="swiper mySwiper">
        <div class="swiper-wrapper">
            <?php
           
            $conexion = new mysqli("localhost", "kheniali", "123", "tienda");
            if ($conexion->connect_error) {
                die("Error de conexión: " . $conexion->connect_error);
            }

     
            //busca los ultimos 6 productos y los ordena por fecha de actualizacion  
            $sql = "SELECT * FROM productop WHERE stock > 0 ORDER BY fecha_actualizacion DESC LIMIT 6";
            $resultado = $conexion->query($sql);

            if ($resultado->num_rows > 0) {
                while($producto = $resultado->fetch_assoc()) {
                   
                    $nombre = htmlspecialchars($producto['nombre']);
                    $descripcion = htmlspecialchars($producto['descripcion']);
                    $descripcion2 = htmlspecialchars($producto['descripcion2']);
                    $precio = number_format($producto['precio'], 2);
                    $imagen = htmlspecialchars($producto['imagen']);
                    $id = $producto['id'];
            ?>
            <div class="content swiper-slide">
                <img src="<?php echo $imagen; ?>" alt="<?php echo $nombre; ?>"> <!-- manda a llamar de los datps que recorrio -->
                <div class="text-content">
                    <h3><?php echo $nombre; ?></h3>
                
                    <div class="product-price">$<?php echo $precio; ?></div>
                    <!-- GUARDA LOS DATOS DEL PRODUCTO EN LOS ATRIBUTOS DATA , crea un boton html para cada producto internamente , bucle-->
                    <button class="btn1 ver-detalles" 
                    
    data-id="<?php echo $id; ?>"
    data-nombre="<?php echo $nombre; ?>"
    data-descripcion="<?php echo $descripcion; ?>"
    data-descripcion2="<?php echo htmlspecialchars($descripcion2, ENT_QUOTES); ?>"
    data-precio="<?php echo $precio; ?>"
    data-imagen="<?php echo $imagen; ?>"
    data-stock="<?php echo $stock; ?>">
    Ver detalles
</button>
                </div>
            </div>
            <?php
                }
            } else {
                echo "<div class='no-products'>No hay productos disponibles</div>"; 
            }
            
            $conexion->close();
            ?>
        </div>
        <!-- BOTONES PARA ADELANTAR Y VICEVERSA  -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
</section>


<!-- Modal dividido con descripción2 a la derecha -->
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
                <p class="product-description"></p><!-- TODO ESTO SE LLENA CON EL JAVA -->
                <p class="modal-price"></p>
                <div class="modal-buttons">
                    <button class="regresar">Regresar</button>
                    <!-- FORMULARIO PARA AÑADIR AL CARRITO , LOS ENVIA DE MANERA OCULTA -->
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
        <div class="right-section">
    <div class="product-description2-container">
        <h3>Especificaciones:</h3>
        <div class="product-description2">
          
        </div>
        <a href="#" class="ver-mas-btn" id="verMasBtn">Ver más especificaciones</a><!-- JALA DIRECTAMENTE EL ID DEL PRODUCTO SELECCIONADO , SE MANEJA EN EL JAVA  -->
    </div>
</div>
    </div>
</div>



  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

  <script src="botonesmenu.js"></script>

  <!-- SECCIONES DE FOTO CON BOTON -->
  <section class="product-section">
    <!-- Primera sección -->
    <div class="product-div">
      <div class="image-container">
        <img src="img/camarapro2.jpg" alt="SEL400800G">
        <div class="gradient-overlay"></div>
        <div class="text-content1">
          <h2>Camara Profesional</h2>
          <p>Aprovecha </p>
          <a href="producto_descuento1.php" class="shop-button">Comprar Ahora</a>
        </div>
      </div>
    </div>

    <!-- Segunda sección -->
    <div class="product-div">
      <div class="image-container">
        <img src="img/gaming.gif" alt="SEL16F18G">
        <div class="gradient-overlay"></div>
        <div class="text-content1">
          <h2>Combo Gaming </h2>
          <p>Aprovecha las ofertas</p>
          <a href="producto_descuento.php" class="shop-button">Comprar Ahora</a>
        </div>
      </div>
    </div>
  </section>


  <section class="cards-container">
    <!-- Card 1 -->
    <div class="card">
      <div class="card-icon">🚚</div>
      <h1 class="card-title">Envío gratuito y devoluciones fáciles</h1>
      <p class="card-description">
      Muchos artículos llegan en 2 días o menos. Se comparten las fechas 
      de entrega estimadas para que puedas anticipar cuándo llegarán 
      (no se garantizan). Si decides que tu producto no es el adecuado,
       puedes devolverlo fácilmente. Consulta la política de devoluciones
        para obtener más información.
      </p>
      <a href="terminos_condiciones.php" class="card-button">Saber más</a>
    </div>

    <!-- Card 2 -->
    <div class="card">
      <div class="card-icon">💰</div>
      <h3 class="card-title">A partir del 0% APR con Affirm</h3>
      <p class="card-description">
      Opciones de financiamiento disponibles desde 0% APR o 4 pagos fáciles.
      </p>
      <a href="affirm.php" class="card-button">Saber más</a>
    </div>

    <!-- Card 3 -->
    <div class="card">
      <div class="card-icon">💸</div>
      <h3 class="card-title">Hasta un 5% de reembolso en Mis Puntos Onexa</h3>
      <p class="card-description">
      Regístrate para obtener una cuenta My Sony GRATIS y gana hasta un 5 % de
       reembolso en puntos para una compra futura y además obtén otros 
       beneficios y ofertas exclusivos.
      </p>
      <a href="membresia.php" class="card-button">Saber más</a>
    </div>

    <!-- Card 4 -->
    <div class="card">
      <div class="card-icon">❓</div>
      <h3 class="card-title">Consultas con expertos 1:1</h3>
      <p class="card-description">
      Ya sea que necesite preguntas o consejos para disfrutar 
      al máximo de su nuevo equipo, ofrecemos consultas virtuales gratuitas 
      individuales de 30 minutos con expertos en productos que puede reservar 
      en cualquier momento.      </p>
      <a href="soporte_tienda.php" class="card-button">Saber más</a>
    </div>
  </section>



  <!-- SECCION DE LAS PREGUNTAS FRECUENTES -->
  <section class="tech-faqs">
    <h2 class="faq-main-title">¿Tienes dudas? Te ayudamos</h2>
    <p class="faq-subtitle">Resuelve tus inquietudes sobre compras, envíos y garantías</p>
    
    <div class="faq-container">
        <!-- FAQ 1 -->
        <div class="faq-item">
            <input type="checkbox" id="faq1" class="faq-toggle">    <!-- hay un checkbox invisible con css que al hacer clic despliega y el segundo clig guarda -->
            <label for="faq1" class="faq-question">
                <span class="faq-icon">📦</span>
                <h3>¿Cuánto tardan los envíos?</h3>
                <span class="faq-arrow">⌄</span>
            </label>
            <div class="faq-answer">
                <p>Los envíos estándar tardan <strong>3-5 días hábiles</strong> en zonas urbanas. Para áreas remotas, el plazo puede extenderse a 7 días. Ofrecemos envío express en 24/48 hrs con costo adicional.</p>
            </div>
        </div>
        
        <!-- FAQ 2 -->
        <div class="faq-item">
            <input type="checkbox" id="faq2" class="faq-toggle">
            <label for="faq2" class="faq-question">
                <span class="faq-icon">🔋</span>
                <h3>¿Qué incluye la garantía?</h3>
                <span class="faq-arrow">⌄</span>
            </label>
            <div class="faq-answer">
                <p>Todos nuestros productos electrónicos incluyen <strong>garantía de 1 año</strong> contra defectos de fabricación. La garantía cubre reparación o reemplazo, pero no daños por mal uso. <a href="#">Ver términos completos</a>.</p>
            </div>
        </div>
        
        <!-- FAQ 3 -->
        <div class="faq-item">
            <input type="checkbox" id="faq3" class="faq-toggle">
            <label for="faq3" class="faq-question">
                <span class="faq-icon">💳</span>
                <h3>¿Aceptan pagos a plazos?</h3>
                <span class="faq-arrow">⌄</span>
            </label>
            <div class="faq-answer">
                <p>Sí, ofrecemos <strong>hasta 12 meses sin intereses</strong> con tarjetas participantes. También aceptamos PayPal, criptomonedas y transferencias bancarias.</p>
            </div>
        </div>
        
        <!-- FAQ 4 -->
        <div class="faq-item">
            <input type="checkbox" id="faq4" class="faq-toggle">
            <label for="faq4" class="faq-question">
                <span class="faq-icon">🔄</span>
                <h3>¿Puedo devolver un producto?</h3>
                <span class="faq-arrow">⌄</span>
            </label>
            <div class="faq-answer">
                <p>Aceptamos devoluciones dentro de los <strong>14 días</strong> posteriores a la compra. El producto debe estar en su empaque original y sin uso. <a href="#">Solicitar devolución</a>.</p>
            </div>
        </div>
        
        <!-- FAQ 5 -->
        <div class="faq-item">
            <input type="checkbox" id="faq5" class="faq-toggle">
            <label for="faq5" class="faq-question">
                <span class="faq-icon">📱</span>
                <h3>¿Dónde veo el manual de mi dispositivo?</h3>
                <span class="faq-arrow">⌄</span>
            </label>
            <div class="faq-answer">
                <p>Todos los manuales están disponibles en <a href="#">nuestra sección de soporte</a>. También puedes escanear el código QR incluido en la caja del producto.</p>
            </div>
        </div>
    </div>
    
    <div class="faq-cta">
        <p>¿No encontraste tu respuesta?</p>
        <a href="soporte_tienda.php" class="contact-button">Contáctanos</a>
    </div>
</section>
<?php include 'footer.php'; ?>




<script>
document.addEventListener('DOMContentLoaded', function() {
    // ========== DECLARACIÓN DE ELEMENTOS DEL DOM ==========
    const modal = document.querySelector('.modal');
    const modalImg = document.getElementById('main-product-image');
    const modalTitle = document.querySelector('.product-title');
    const modalDesc = document.querySelector('.product-description');
    const modalDesc2 = document.querySelector('.product-description2');
    const modalPrice = document.querySelector('.modal-price');
    const gallery = document.querySelector('.gallery');
    const form = document.querySelector('.form-agregar-carrito');
    const verMasBtn = document.getElementById('verMasBtn');

    // ========== FUNCIÓN PARA MOSTRAR MODAL ==========
    function mostrarModal(producto) {
        try {//se usa try catch para el manejo de errores
           
            if (!producto.id || !producto.nombre) {
                console.error('Datos del producto incompletos');
                return;
            }

            
             // Rellena el modal con los datos del producto RECIÉN CLICKEADO
            modalTitle.textContent = producto.nombre || 'Producto sin nombre';
            modalDesc.textContent = producto.descripcion || 'Descripción no disponible';
            modalPrice.textContent = `$${parseFloat(producto.precio || 0).toFixed(2)}`;
            modalImg.src = producto.imagen || 'placeholder.jpg';
            modalImg.alt = producto.nombre || 'Imagen del producto';

            // Actualizar sección derecha 
            modalDesc2.innerHTML = producto.descripcion2 
                ? producto.descripcion2.replace(/\n/g, '<br>') //(convierte saltos de linea \n/g, en etiquetas '<br>') para crear los parrafos
                : 'No hay especificaciones disponibles';

        
            if (verMasBtn) {
                verMasBtn.href = `http://localhost/project/detalle_producto.php?id=${producto.id}`;//lleva a la pagina de deta-pro segun  el id 
                verMasBtn.style.display = 'block'; 
            }

            // Actualizar formulario
            form.querySelector('input[name="id"]').value = producto.id;
            form.querySelector('input[name="nombre"]').value = producto.nombre;
            form.querySelector('input[name="precio"]').value = producto.precio || 0;
            form.querySelector('input[name="imagen"]').value = producto.imagen || '';

            // Mostrar modal
            modal.style.display = 'flex';
            
        } catch (error) {
            console.error('Error al mostrar modal:', error);
        }
    }

    // ========== EVENTOS PARA BOTONES "VER DETALLES" ==========
    document.querySelectorAll('.ver-detalles').forEach(btn => {
        btn.addEventListener('click', function() {//event listener espera a que ocurra un clic 
            // "this" => se refiere al botón CLICKEADO en este momento
            try {
                const producto = {
                    id: this.dataset.id,
                    nombre: this.dataset.nombre, 
                    descripcion: this.dataset.descripcion,
                    descripcion2: this.dataset.descripcion2,
                    precio: this.dataset.precio,
                    imagen: this.dataset.imagen,
                    stock: this.dataset.stock || '1'
                };
                mostrarModal(producto);
            } catch (error) {
                console.error('Error al procesar producto:', error);
            }
        });
    });

    // ========== FUNCIONALIDAD PARA CERRAR MODAL ==========
    document.querySelector('.regresar').addEventListener('click', () => {
        modal.style.display = 'none';
    });

    modal.addEventListener('click', (e) => { //escucha los clic en todo el  modal
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });

    // ========== FUNCIÓN PARA AGREGAR AL CARRITO ==========
    window.agregarAlCarrito = async function(form) {
        try {
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
         
            submitBtn.disabled = true;
            submitBtn.textContent = 'Añadiendo...';
            
            const response = await fetch(form.action, {//fetch: Hace una petición HTTP al servidor 
                //form.action: Usa la URL definida en el atributo action del formulario
                method: 'POST',
                body: new URLSearchParams(new FormData(form)),
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Error en la respuesta');
            }
            
            // Cerrar modal inmediatamente
            modal.style.display = 'none';
            
          
            location.reload();
           
        } catch (error) {
            console.error('Error:', error);
            
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }
    };
});
</script>