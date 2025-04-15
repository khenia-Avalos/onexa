console.log('cargando busqueda'); // Solo para desarrolladores


//CODIGO DE FUNCIONALIDAD DEL BOTON LUPA , con memoria al navegar entre paginas usando LOCALSTORAGE EN LUGAR DE LOCATION RELOAD 


document.addEventListener("DOMContentLoaded", function () {
    // Seleccionar el botón de lupa y la capa de búsqueda
    const lupaButton = document.querySelector(".lupa-button");
    const overlay = document.querySelector(".overlay");
    const searchClose = document.querySelector(".search-close");

    // Restaurar el estado de la búsqueda al cargar la página
    const busquedaAbierta = localStorage.getItem('busquedaAbierta') === 'true';
    if (busquedaAbierta) {
        overlay.style.display = "flex"; // Mostrar la barra de búsqueda si estaba abierta
    }

    // Función para abrir la barra de búsqueda
    lupaButton.addEventListener("click", function () {
        overlay.style.display = "flex"; // Mostrar la capa gris y barra de búsqueda
        localStorage.setItem('busquedaAbierta', 'true'); // Guardar el estado
    });

    // Función para cerrar la barra de búsqueda
    searchClose.addEventListener("click", function () {
        overlay.style.display = "none"; // Ocultar la capa y la barra de búsqueda
        localStorage.setItem('busquedaAbierta', 'false'); // Guardar el estado
    });

    // Cerrar la búsqueda si se hace clic fuera del contenedor de búsqueda
    overlay.addEventListener("click", function (event) {
        if (event.target === overlay) {
            overlay.style.display = "none"; // Ocultar la capa y barra de búsqueda
            localStorage.setItem('busquedaAbierta', 'false'); // Guardar el estado
        }
    });
});

//FUNCIONALIDAD DEL SWIPER O CARDS 

document.addEventListener('DOMContentLoaded', function () {
    var swiper = new Swiper(".mySwiper", {
        effect: "coverflow", //efecto 3d 
        grabCursor: true, //cambia cursor al pasar sobre las imagenes 
        centeredSlides: true, //centra la diapositiva
        loop: true, //hace que vuelva al inicio al finalizar con un producto
        slidesPerView: "auto", //muestra varias cards segun el tamaño
        coverflowEffect: {
            rotate: 0,
            stretch: 0,
            depth: 150,
            modifier: 2.5,
            slideShadows: true,
        },
        autoplay: { //cambio automatico de 3 segundos
            delay: 3000,
            disableOnInteraction: false,
        },
        navigation: { //botones de siguiente y atras
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
    });
});

