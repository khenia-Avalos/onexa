console.log('cargando busqueda'); 


//CODIGO DE FUNCIONALIDAD DEL BOTON LUPA , con memoria al navegar entre paginas usando LOCALSTORAGE EN LUGAR DE LOCATION RELOAD 


document.addEventListener("DOMContentLoaded", function () {
    // Seleccionar el botón de lupa y la capa de búsqueda
    const lupaButton = document.querySelector(".lupa-button");
    const overlay = document.querySelector(".overlay");
    const searchClose = document.querySelector(".search-close");


    const busquedaAbierta = localStorage.getItem('busquedaAbierta') === 'true';
    if (busquedaAbierta) {
        overlay.style.display = "flex"; 
    }

    // Función para abrir la barra de búsqueda
    lupaButton.addEventListener("click", function () {
        overlay.style.display = "flex"; 
        localStorage.setItem('busquedaAbierta', 'true'); 
    });

    // Función para cerrar la barra de búsqueda
    searchClose.addEventListener("click", function () {
        overlay.style.display = "none"; 
        localStorage.setItem('busquedaAbierta', 'false'); 
    });

    // Cerrar la búsqueda si se hace clic fuera del contenedor de búsqueda
    overlay.addEventListener("click", function (event) {
        if (event.target === overlay) {
            overlay.style.display = "none"; 
            localStorage.setItem('busquedaAbierta', 'false');
        }
    });
});

//FUNCIONALIDAD DEL SWIPER O CARDS 

document.addEventListener('DOMContentLoaded', function () {
    var swiper = new Swiper(".mySwiper", {
        effect: "coverflow", //efecto 3d 
        grabCursor: true, //cambia cursor al pasar sobre las imagenes 
        centeredSlides: true, 
        loop: true, 
        slidesPerView: "auto", 
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

