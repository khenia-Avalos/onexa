// Forzar la recarga de la página al navegar hacia atrás
window.addEventListener('pageshow', function(event) {  //cuando la pagina se muestra haz lo siguiente
    if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {   //es una version vieja y usando el historial atras adelante
        location.reload(true);//si, recarga la pagina desde 0 con los nuevos datos 
    }
});